import { __ } from '@wordpress/i18n';
import insertPreviewSvg from '../../../images/insert-preview.svg';
import { getLicenseHistory } from '../getSubscriptionWithDetails';
import redownloadAndInsertImage from '../redownloadAndInsertImage';

// License History Route
const licenseHistoryPageRoute = ({
  closeModal,
  handleError,
  isMediaPage,
  routesConfig,
  setAttributes,
  showSnackbar,
  toggleOverlay,
  widgetRef,
}) => ({
  name: 'imageLicenseHistoryPage',
  component: ShutterstockWidget.components.ImageLicenseHistoryPage,
  props: {
    theme: {
      container: 'components-shutterstock-media-page__license-history-container',
    },
    onLicenseHistoryItemClick: (item, { history }) => {

      widgetRef.current.navigateTo('imageDetailsPage', {
        item,
      });

    },
    getMoreResults: async (page) => {
      const licenseHistory = await getLicenseHistory('images', page + 1);
      return licenseHistory;
    },
    licenseHistory: [],
    overlayActions: [
      {
        label: __(isMediaPage ? 'wordpress:text_download' : 'wordpress:text_dowbload_and_insert', 'shutterstock'),
        icon: insertPreviewSvg,
        onClick: (e, item, redownloadProps) => {
          e.preventDefault();
          redownloadAndInsertImage(
            item,
            {
              ...redownloadProps,
              toggleOverlay,
              handleError,
              isMediaPage,
              setAttributes,
              showSnackbar,
              closeModal,
            }
          );
        }
      },
    ],
  },
});

export default licenseHistoryPageRoute;