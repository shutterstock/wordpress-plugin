import React, { useRef, useEffect, useState } from 'react';
import apiFetch from '@wordpress/api-fetch';

import { __ } from '@wordpress/i18n';
import './ShutterstockUI.scss';
import insertPreviewSvg from '../../images/insert-preview.svg';
import licenseImageSvg from '../../images/shopping-cart.svg';
import insertPreviewDarkSvg from '../../images/insert-preview-dark.svg';
import getSubscriptionWithDetails from './getSubscriptionWithDetails';

import ShutterstockSnackbar, { useSnackbarTimeout } from '../ShutterstockSnackbar';

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
    text: __(text, 'shutterstock-block')
  });

  const { snackbar, setSnackbar } = useSnackbarTimeout({});

  const showSnackbar = (text) => setSnackbar({
    ...snackbar,
    show: true,
    text: __(text, 'shutterstock-block'),
  });

  const handleError = (error) => {
    let errorMessage = 'Something went wrong. Please try again.';
    if (error?.data?.statusCode !== 500 && error?.data?.message) {
      errorMessage = error?.data?.message;
    }
    
    showSnackbar(errorMessage);
  };

  const insertPreview = (e, item) => {
    e.preventDefault();
    setAttributes({
      img: item
    });
    closeModal();
  };

  const commonInsertPreviewProps = {
    label: __('Insert preview', 'shutterstock-block'),
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
    const showOverlay = (status, text = '') => setOverlay({
      ...overlay, 
      show: status,
      text: __(text) 
    });

    const commonLicensingProps = {
      label: __('License', 'shutterstock-block'),
      icon: licenseImageSvg,
      isPrimary: true,
      onClick: async (e, item, options) => {
        e.preventDefault();
        try {
          const mediaType = item.media_type;
          toggleOverlay(true, 'Loading. Please wait.');

          const subscriptionsWithImageDetails = await getSubscriptionWithDetails(item.id, mediaType);

            // Adding subscription to the route
          routesConfig[1].props = {
            ...routesConfig[1].props,
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
                toggleOverlay(true, 'Licensing Image. Please wait.');
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
    });
  }

  useEffect(() => {

    const searchBarDropdownFilters = [
      {
        label: __('Images', 'shutterstock'),
        assetType: 'images',
      },
      {
        label: __('Editorial', 'shutterstock'),
        assetType: 'editorial',
      },      
    ];

    const widgetConfig = {
      mediaType: 'images',
      title: __('Add Shutterstock content to your post', 'shutterstock-block'),
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
        options.history.push(`/images/${item.id}`)
      },
      theme: {
        searchBar: {
          inputGroup: 'components-shutterstock-ui__inputgroup',
          formControlInput: 'components-shutterstock-ui__input'
        },
      },
      extraRoutes: {
        ...(licenseImage ? { initialRoute: `/license/images/${assetInfo.id}` } : { } ),
        routesConfig,
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
