import apiFetch from '@wordpress/api-fetch';

const getSubscriptionWithDetails = async (imageId) => {
  try {
    const subscriptions = await apiFetch({ path: `shutterstock/user/subscriptions` });
    const { assets, id, is_editorial } = await apiFetch({ path: `shutterstock/images/${imageId}` });
  
    const licensableAssets = Object.entries(assets)
      .filter(([key, value]) => value.is_licensable)
      .reduce((ac, [key, value]) => ({...ac, [key]: value }), {});
    
    const subscriptionsWithImageDetails = subscriptions.map((val) => {
      const formats = val?.formats?.filter(({ size, format }) =>
          !size.match(/supersize/i) && (format !== 'tiff') && (format !== 'eps') && (typeof format !== 'undefined')
        )
        .sort((a, b) => a.min_resolution - b.min_resolution)
        .map((val) => ({ ...val, details_for_image: { ...licensableAssets[`${val.size}_${val.format}`]} }))
  
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