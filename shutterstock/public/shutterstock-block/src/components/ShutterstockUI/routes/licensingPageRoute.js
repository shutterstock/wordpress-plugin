import apiFetch from '@wordpress/api-fetch';
import { __ } from '@wordpress/i18n';

const licensingPageRoute = ({
  item,
  closeModal,
  commonLicensingProps,
  handleError,
  isMediaPage,
  setAttributes,
  showSnackbar,
  subscriptions,
  toggleOverlay,
}) => ({
    name: 'imageLicensingPage',
    component: ShutterstockWidget.components.ImageLicensingPage,
    props: {
      showSearchBar: true,
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
                  search_id: item.searchId
                },
              });

              if (licensing?.success) {
                const { url, id } = licensing.data;
                if (!isMediaPage) {
                  setAttributes({
                    img: {
                      ...item,
                      licensedImageUrl: url,
                      contributorName,
                      uploadedImageId: id
                    }
                  });
                  closeModal();
                } else if (isMediaPage) {
                  showSnackbar(__('wordpress:text_image_stored_in_media_library', 'shutterstock'));
                }

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
      item,
      subscriptions: subscriptions
    }
});

export default licensingPageRoute;