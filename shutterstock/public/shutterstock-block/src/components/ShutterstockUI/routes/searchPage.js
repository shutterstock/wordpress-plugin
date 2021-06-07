import insertPreviewDarkSvg from '../../../images/insert-preview-dark.svg';
import { __unstableStripHTML as stripHTML } from '@wordpress/dom';
import { __ } from '@wordpress/i18n';

const regex = {
  HTMLRegExp: /<\/?[a-z][^>]*?>/gi,
  NewLineRegExp: /\r?\n|\r/gi,
};

const searchPage = ({
  isMediaPage,
  overlayActions,
  shutterstock,
  searchBarDropdownFilters,
  userIsAbleToSearchEditorial,
  widgetRef,
}) => ({
  name: 'searchPage',
  component: ShutterstockWidget.components.SearchPage,
  props: {
    mediaType: 'images',
    imageType: ['photo'],
    subtitle: '',
    showMore: true,
    dynamicTitle: true,
    dynamicSubtitle: true,
    showSearchBar: true,
    assetsPerPage: 26,
    onItemClick: (e, item) => {
      e.preventDefault();
      widgetRef.current.navigateTo('imageDetailsPage', {
        item
      })
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
    overlayActions,
    editorialCountry: shutterstock?.country,
    searchFilters: {
      showFilterDrawer: true,
      images: {
        orientationFilter: true,
      },
      ...(userIsAbleToSearchEditorial ? { searchBarDropdownFilters } : {})
    },
    ...(isMediaPage ? {} : {
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
      },
      title: __('wordpress:text_add_shuttersock_content_to_post', 'shutterstock'),
    }),
  }
});

export default searchPage;