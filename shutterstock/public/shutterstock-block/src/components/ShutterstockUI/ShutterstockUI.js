import React, { useRef, useEffect, useState } from 'react';
import apiFetch from '@wordpress/api-fetch';

import { __ } from '@wordpress/i18n';
import { __unstableStripHTML as stripHTML } from '@wordpress/dom';

import './ShutterstockUI.scss';
import insertPreviewSvg from '../../images/insert-preview.svg';
import licenseImageSvg from '../../images/shopping-cart.svg';
import getSubscriptionWithDetails, { getLicenseHistory } from './getSubscriptionWithDetails';
import ShutterstockSnackbar, { useSnackbarTimeout } from '../ShutterstockSnackbar';
import {
  imageDetailsPageRoute,
  licenseHistoryPageRoute,
  licensingPageRoute,
  searchPage,
} from './routes';

const regex = {
  HTMLRegExp: /<\/?[a-z][^>]*?>/gi,
  NewLineRegExp: /\r?\n|\r/gi,
};

const ShutterstockUI = ({
  setAttributes,
  closeModal,
  canLicense = false,
  item = {},
  licenseImage = false,
  subscriptions = [],
  isMediaPage = false,
}) => {
  const userIsAbleToSearchEditorial = shutterstock?.permissions?.includes('can_user_search_editorial_images');
  const widgetRef = useRef();
  const [overlay, setOverlay] = useState({ show: false, text: '' });

  const toggleOverlay = (status, text = '') => setOverlay({
    ...overlay,
    show: status,
    text,
  });

  const { snackbar, setSnackbar } = useSnackbarTimeout({ timeout: 5000 });

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

  const overlayActions = isMediaPage
    ? []
    : [{ ...commonInsertPreviewProps, icon: insertPreviewSvg }];

  const routesConfig = [
    imageDetailsPageRoute({ item, commonInsertPreviewProps, isMediaPage }),
  ];

  const imageDetailsPage = imageDetailsPageRoute({ item, commonInsertPreviewProps, isMediaPage });
  let licensingPage;
  let licenseHistoryPage;

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

          // Adding subscription + search id to the route
          widgetRef.current.navigateTo('imageLicensingPage', {
            item,
            subscriptions: subscriptionsWithImageDetails,
            searchId: item.searchId,
          })
          toggleOverlay(false);
        } catch(error) {
          toggleOverlay(false);
          handleError(error);
        }
      }
    };

    overlayActions.push({
      ...commonLicensingProps,
    });

    // Adding Licensing Button to the Image details page.
    imageDetailsPage.props.buttons.push({
      ...commonLicensingProps,
    });

    // Adding Licensing Route
    licensingPage = licensingPageRoute({
      item,
      closeModal,
      commonLicensingProps,
      handleError,
      isMediaPage,
      licenseImage,
      setAttributes,
      showSnackbar,
      subscriptions,
      toggleOverlay,
    });

    licenseHistoryPage = licenseHistoryPageRoute({
      closeModal,
      handleError,
      isMediaPage,
      routesConfig,
      setAttributes,
      showSnackbar,
      toggleOverlay,
      widgetRef,
    })
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

    const searchImagePage = searchPage({
      isMediaPage,
      overlayActions,
      shutterstock,
      searchBarDropdownFilters,
      userIsAbleToSearchEditorial,
      widgetRef,
    })
    /**
     * We have to show licensingPage & licenseHistoryPage only for users who have access to it
     * so we do ...() and all.
     * The condition on line 176 is specific to a case where a user already inserted an image in wordpress post
     * and then clicks on `License Image` button. In that case we want to display licensingPage first to user.
     */
    const pages = [
      ...((licenseImage && licensingPage) ? [licensingPage] : []),
      searchImagePage,
      imageDetailsPage,
      ...((!licenseImage && licensingPage) ? [licensingPage] : []),
      ...((licenseHistoryPage) ? [licenseHistoryPage] : []),
    ];

    const widgetConfig = {
      container: widgetRef.current,
      key: shutterstock?.api_key,
      languageCode: shutterstock?.language,
      customHeaders: {
        'x-shutterstock-application': `Wordpress/${shutterstock?.version}`,
      },
      pages,
    };

    // eslint-disable-next-line no-undef
    if (typeof window === 'object' && window.ShutterstockWidget) {
      // eslint-disable-next-line no-undef
      const widgetInstance = new window.ShutterstockWidget(widgetConfig);
      widgetInstance.render({});

      widgetRef.current = widgetInstance;
    }
  }, []);


  const mediaPageClass = isMediaPage ? 'media-page' : '';

  return (
    <>
      {canLicense && (
        <div className={`components-shutterstock-ui__navigation ${overlay.show ? 'disabled' : ''} ${mediaPageClass}`}>
          <a
            onClick={(e, options) => {
              widgetRef.current.navigateTo('searchPage')
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

                widgetRef.current.navigateTo('imageLicenseHistoryPage', {
                  licenseHistory
                })
                toggleOverlay(false);

              } catch(error) {
                toggleOverlay(false);
                handleError(error);
              }
            }}
          >
            {__('wordpress:text_downloads', 'shutterstock')}
          </a>
        </div>
      )}
      <div ref={widgetRef} className={`components-shutterstock-ui__widget-container ${mediaPageClass}`} />
      {overlay.show && (
        <div className={`components-shutterstock-ui__widget-container-overlay ${mediaPageClass}`}>
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
