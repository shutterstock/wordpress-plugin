import apiFetch from '@wordpress/api-fetch';

const getSubscriptionWithDetails = async (imageId, mediaType = 'image') => {
  try {
    const country = (mediaType === 'editorial' && shutterstock?.country) ? `&country=${shutterstock?.country}` : '';
    const subscriptions = await apiFetch({ path: `shutterstock/user/subscriptions?mediaType=${mediaType}` });
    const { assets, id, is_editorial } = await apiFetch({ path: `shutterstock/images/${imageId}?mediaType=${mediaType}${country}` });

    const licensableAssets = Object.entries(assets)
      .filter(([key, value]) => value.is_licensable)
      .reduce((ac, [key, value]) => ({...ac, [key]: value }), {});
    
    const subscriptionsWithImageDetails = subscriptions.map((val) => {
      let formats = val?.formats?.filter(({ size, format }) =>
          !size.match(/supersize/i) && (format !== 'tiff') && (format !== 'eps') && (typeof format !== 'undefined')
        )
        .sort((a, b) => a.min_resolution - b.min_resolution)
        .map((val) => ({ ...val, details_for_image: { ...licensableAssets[`${val.size}_${val.format}`]} }))
      
      if (mediaType === 'editorial') {
        const sizeMap = (size) => ({ 
          small_jpg: 'small',
          medium_jpg: 'medium',
          original: 'original'
        })[size];

        formats = Object.entries(licensableAssets || {}).map(([key, value]) => ({ details_for_image: value, size: sizeMap(key) }));
      }

      return {
        ...val,
        formats,
      };
    });
  
    return subscriptionsWithImageDetails;
  } catch(e) {
    throw e;
  }
  
};

export default getSubscriptionWithDetails;