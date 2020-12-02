import apiFetch from '@wordpress/api-fetch';
import { __ } from '@wordpress/i18n';

const redownloadAndInsertImage = async (item, props) => {
	const { toggleOverlay, licenseId, size, setAttributes, closeModal, handleError } = props;
	try {
		toggleOverlay(true, __('wordpress:downloading_image', 'shutterstock'));
		const contributorId = item?.contributor?.id;
		let contributorName = '';
		
		if (contributorId) {
			const contributorDetails = await apiFetch({ path: `shutterstock/contributor/${contributorId}`});
			contributorName = contributorDetails?.data?.[0]?.display_name || contributorId;
		}
		
		const { assets } = await apiFetch({ path: `shutterstock/images/${item.id}?mediaType=images` });

		const redownloadedImage = await apiFetch({
			path: `shutterstock/images/licenses/${licenseId}/downloads`,
			method: 'POST',
			contentType: 'application/json',
			data: {	  
				mediaType: 'images',
				size,
				contributorName,
				imageId: item.id,
				description: item.description,
				...(assets?.[`${size}_jpg`]),
			},
		});

	 if (redownloadedImage?.success) {
			const { url, id } = redownloadedImage.data;

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
			handleError(redownloadedImage);
		}
	} catch(error) {
		toggleOverlay(false);
		handleError(error);
	}
};

export default redownloadAndInsertImage;