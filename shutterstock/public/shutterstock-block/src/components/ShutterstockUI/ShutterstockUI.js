import React, { useRef, useEffect, useState } from 'react';
import apiFetch from '@wordpress/api-fetch';

import { __ } from '@wordpress/i18n';
import { __unstableStripHTML as stripHTML } from '@wordpress/dom';

import './ShutterstockUI.scss';
import insertPreviewSvg from '../../images/insert-preview.svg';
import licenseImageSvg from '../../images/shopping-cart.svg';
import insertPreviewDarkSvg from '../../images/insert-preview-dark.svg';
import getSubscriptionWithDetails, { getLicenseHistory } from './getSubscriptionWithDetails';
import redownloadAndInsertImage from './redownloadAndInsertImage';

import ShutterstockSnackbar, { useSnackbarTimeout } from '../ShutterstockSnackbar';

const regex = {
  HTMLRegExp: /<\/?[a-z][^>]*?>/gi,
  NewLineRegExp: /\r?\n|\r/gi,
};

const ShutterstockUI = ({
  setAttributes,
  closeModal,
  canLicense = false,
  assetInfo = {},
  licenseImage = false,
  subscriptions = [],
}) => {
  const userIsAbleToSearchEditorial = shutterstock?.permissions?.includes('can_user_search_editorial_images');
  const widgetRef = useRef();
  const [overlay, setOverlay] = useState({ show: false, text: '' });

  const toggleOverlay = (status, text = '') => setOverlay({
    ...overlay, 
    show: status,
    text,
  });

  const { snackbar, setSnackbar } = useSnackbarTimeout({});

  const showSnackbar = (text) => setSnackbar({
    ...snackbar,
    show: true,
    text,
  });

  const handleError = (error) => {
    let errorMessage = __('wordpress:text_something_went_wrong', 'shutterstock');
    if (error?.data?.statusCode !== 500 && error?.data?.message) {
      errorMessage = error?.data?.message;
    }
    
    showSnackbar(errorMessage);
    toggleOverlay(false);
  };

  const insertPreview = (e, item) => {
    e.preventDefault();
    setAttributes({
      img: item
    });
    closeModal();
  };

  const commonInsertPreviewProps = {
    label: __('wordpress:text_insert_preview', 'shutterstock'),
    onClick: insertPreview,
  };

  const overlayActions = [
    {
      ...commonInsertPreviewProps,
      icon: insertPreviewSvg,
    },
  ];

  const routesConfig = [{
    path: '/images/:id',
    component: ShutterstockWidget.components.ImageDetailsPage,
    props: {
      buttons: [{
          ...commonInsertPreviewProps,
          icon: insertPreviewDarkSvg,
      }],
      assetInfo,
    }
  }];

  if (canLicense) {
    const commonLicensingProps = {
      label: __('wordpress:text_license', 'shutterstock'),
      icon: licenseImageSvg,
      isPrimary: true,
      onClick: async (e, item, options) => {
        e.preventDefault();
        try {
          const mediaType = item.media_type;
          toggleOverlay(true, __('wordpress:text_loading_please_wait', 'shutterstock'));

          const subscriptionsWithImageDetails = await getSubscriptionWithDetails(item.id, mediaType);

            // Adding subscription to the route
          routesConfig[1].props = {
            ...routesConfig[1].props,
            assetInfo: item,
            subscriptions: subscriptionsWithImageDetails,
          }

          widgetRef.current.updateRoutes({
            routesConfig,
          });
          
          widgetRef.current.toggleLoadingIndicator(false);
          toggleOverlay(false);
          
          options.history.push(`/license/images/${item.id}`);
        } catch(error) {
          widgetRef.current.toggleLoadingIndicator(false);
          toggleOverlay(false);          
          handleError(error);          
        }        
      }
    };

    overlayActions.push({
      ...commonLicensingProps,
    });

    // Adding Licensing Button to the Image details page.
    routesConfig[0].props.buttons.push({
      ...commonLicensingProps,
    });

    // Adding Licensing Route
    routesConfig.push({
      path: '/license/images/:id',
      component: ShutterstockWidget.components.LicensingImagePage,
      props: {
        buttons: [
          {
            ...commonLicensingProps,
            onClick: async (e, item, options) => {
              const { subscription } = options;

              try {
                toggleOverlay(true, __('wordpress:text_licensing_image_please_wait', 'shutterstock'));
                const contributorId = item?.contributor?.id;
                const mediaType = item?.media_type;
                const isEditorial = mediaType === 'editorial';

                let contributorName = isEditorial ? item?.byline : '';

                if (contributorId && !isEditorial) {
                  const contributorDetails = await apiFetch({ path: `shutterstock/contributor/${contributorId}`});
                  contributorName = contributorDetails?.data?.[0]?.display_name || contributorId;
                }

                const licensing = await apiFetch({
                  path: 'shutterstock/images/licenses',
                  method: 'POST',
                  contentType: 'application/json',
                  data: {
                    subscription_id: subscription?.id,
                    size: subscription?.size, 
                    id: item.id,
                    description: item.description,
                    ...(subscription?.metadata ? { metadata: subscription.metadata } : {}),
                    contributorName,
                    ...(subscription?.details_for_image),
                    mediaType,
                    license: subscription?.license,
                    country: shutterstock?.country,
                  },
                });
                
                if (licensing?.success) {
                  const { url, id } = licensing.data;

                  setAttributes({
                    img: {
                      ...item,
                      licensedImageUrl: url,
                      contributorName,
                      uploadedImageId: id
                    }
                  });
                  closeModal();
                  toggleOverlay(false);
                } else {
                  handleError(licensing);
                }            
              } catch(error) {
                toggleOverlay(false);
                handleError(error);
              }      
            },
          }
        ],
        assetInfo,
        subscriptions: subscriptions
      }
    },
    {      
      path: '/license-history',
      component: ShutterstockWidget.components.LicenseHistoryPage,
      props: {
        onLicenseHistoryItemClick: (item, { history }) => {
          routesConfig[0].props = {
            ...routesConfig[0].props,
            assetInfo: item,
          }

          widgetRef.current.updateRoutes({
            routesConfig,
          });
          
          widgetRef.current.toggleLoadingIndicator(false);
          history.push(`/images/${item.id}`)
        },
        getMoreResults: async (page) => {
          const licenseHistory = await getLicenseHistory('images', page + 1);          
          return licenseHistory;
        },
        licenseHistory: [],
        overlayActions: [
          {
            label: __('wordpress:text_dowbload_and_insert', 'shutterstock'),
            icon: insertPreviewSvg,
            onClick: (e, item, redownloadProps) => {
              e.preventDefault();
              redownloadAndInsertImage(
                item, 
                { 
                  ...redownloadProps, 
                  toggleOverlay, 
                  handleError,
                  setAttributes,
                  closeModal,
                }
              );    
            }
          },                              
        ],
      },
    }
    );
  }

  useEffect(() => {

    const searchBarDropdownFilters = [
      {
        label: __('wordpress:text_images', 'shutterstock'),
        assetType: 'images',
      },
      {
        label: __('wordpress:text_editorial', 'shutterstock'),
        assetType: 'editorial',
      },      
    ];

    const widgetConfig = {
      mediaType: 'images',
      imageType: ['photo'],
      title: __('wordpress:text_add_shuttersock_content_to_post', 'shutterstock'),
      subtitle: '',
      container: widgetRef.current,
      showMore: true,
      key: shutterstock?.api_key,
      languageCode: shutterstock?.language,
      dynamicTitle: true,
      dynamicSubtitle: true,
      showSearchBar: true,
      assetsPerPage: 26,
      onItemClick: (e, item, options) => {
        e.preventDefault();
        routesConfig[0].props = {
          ...routesConfig[0].props,
          assetInfo: item,
        }

        widgetRef.current.updateRoutes({
          routesConfig,
        });
        
        widgetRef.current.toggleLoadingIndicator(false);
        options.history.push(`/images/${item.id}`)
      },
      theme: {
        searchBar: {
          searchForm: 'components-shutterstock-ui__searchForm',
          searchContainer: 'components-shutterstock-ui__searchContainer',
          inputGroup: 'components-shutterstock-ui__inputgroup',
          formControlInput: 'components-shutterstock-ui__input',
          filterDrawer: {
            filterDrawerContainer: 'components-shutterstock-ui__filterDrawerContainer',
            overlay: 'components-shutterstock-ui__widget-drawer-position-fixed',
            filterDrawer: 'components-shutterstock-ui__widget-drawer-position-fixed',
            filterButtonWrapper: 'components-shutterstock-ui__filterButtonWrapper'
          }
        },
      },
      extraRoutes: {
        ...(licenseImage ? { initialRoute: `/license/images/${assetInfo.id}` } : { } ),
        routesConfig,
        excludeSearchBarRoutes: ['^\/license-history$']
      },
      overlayActions,
      customHeaders: {
        'x-shutterstock-application': `Wordpress/${shutterstock?.version}`,
      },
      editorialCountry: shutterstock?.country,
      searchFilters: {
        showFilterDrawer: true,
        images: {          
          orientationFilter: true,
        },        
        ...(userIsAbleToSearchEditorial ? { searchBarDropdownFilters } : {})
      },
      searchSuggestions: {
        enable: true,
        textProvider: () => {
          const postTitle = wp.data.select('core/editor').getEditedPostAttribute('title') || '';
          const postContent = wp.data.select('core/editor').getEditedPostContent() || '';          

          const text = stripHTML(`${postTitle} ${postContent}`)
            .replace(regex.HTMLRegExp, '')
            .replace(regex.NewLineRegExp, '')
            .trim();

          return text;
        }
      }
    };

    // eslint-disable-next-line no-undef
    if (typeof window === 'object' && window.ShutterstockWidget) {
      // eslint-disable-next-line no-undef
      const widgetInstance = new window.ShutterstockWidget(widgetConfig);
      widgetInstance.search({
        query: '',
      });

      widgetRef.current = widgetInstance;
    }
  }, []);


  return (
    <>
      {canLicense && (
        <div className={`components-shutterstock-ui__navigation ${overlay.show ? 'disabled' : ''}`}>
          <a
            onClick={(e, options) => {
              widgetRef.current.getHistory().push(`/`);
            }}
          >
            {__('wordpress:text_home', 'shutterstock')}
          </a>
          <a
            className={`components-shutterstock-ui__download`}
            onClick={async (e) => {
              try {          
                toggleOverlay(true, __('wordpress:text_loading_please_wait', 'shutterstock'));
                const licenseHistory = await getLicenseHistory('images');
                
                routesConfig[2].props = {
                  ...routesConfig[2].props,
                  licenseHistory,
                }
                widgetRef.current.updateRoutes({
                  routesConfig,
                });
                
                widgetRef.current.toggleLoadingIndicator(false);
                toggleOverlay(false);
                widgetRef.current.getHistory().push(`/license-history`);

              } catch(error) {            
                widgetRef.current.toggleLoadingIndicator(false);
                toggleOverlay(false);          
                handleError(error);          
              }   
            }}
          >
            {__('wordpress:text_downloads', 'shutterstock')}
          </a>
        </div> 
      )}     
      <div ref={widgetRef} className="components-shutterstock-ui__widget-container" />
      {overlay.show && (
        <div className="components-shutterstock-ui__widget-container-overlay">
          {overlay.text && (
            <div className="text">
              {overlay.text}
            </div>  
          )}
        </div>
      )}
      {snackbar.show && (<ShutterstockSnackbar text={snackbar.text} />)}
    </>
  )

  
};

export default ShutterstockUI;
