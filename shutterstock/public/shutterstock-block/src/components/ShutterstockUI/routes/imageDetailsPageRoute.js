import insertPreviewDarkSvg from '../../../images/insert-preview-dark.svg';

const imageDetailsPageRoute = ({
  item,
  isMediaPage,
  commonInsertPreviewProps,
}) => ({
  name: 'imageDetailsPage',
  component: ShutterstockWidget.components.ImageDetailsPage,
  props: {
    showSearchBar: true,
    buttons: isMediaPage ? [] : [{
      ...commonInsertPreviewProps,
      icon: insertPreviewDarkSvg,
    }],
    item,
  }
});

export default imageDetailsPageRoute;