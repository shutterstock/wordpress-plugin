import insertPreviewDarkSvg from '../../../images/insert-preview-dark.svg';

const imageDetailsPageRoute = ({
  assetInfo,
  isMediaPage,
  commonInsertPreviewProps,
}) => ({
  name: 'imageDetailsPage',
  path: '/images/:id',
  component: ShutterstockWidget.components.ImageDetailsPage,
  props: {
    buttons: isMediaPage ? [] : [{
      ...commonInsertPreviewProps,
      icon: insertPreviewDarkSvg,
    }],
    assetInfo,
  }
});

export default imageDetailsPageRoute;