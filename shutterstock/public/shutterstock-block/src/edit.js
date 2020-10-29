/**
 * Retrieves the translation of text.
 *
 * @see https://developer.wordpress.org/block-editor/packages/packages-i18n/
 */
import { Button } from '@wordpress/components';
import { useState, useEffect } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { createBlock } from '@wordpress/blocks';
import { withDispatch } from '@wordpress/data';

/**
 * Lets webpack process CSS, SASS or SCSS files referenced in JavaScript files.
 * Those files can contain any CSS code that gets applied to the editor.
 *
 * @see https://www.npmjs.com/package/@wordpress/scripts#using-css
 */
import './editor.scss';
import ShutterstockIcon from './components/ShutterstockIcon/ShutterstockIcon.js';
import ShutterstockUIModal from './components/ShutterstockUIModal/ShutterstockUIModal.js';
import ShutterstockSnackbar, { useSnackbarTimeout } from './components/ShutterstockSnackbar';
import getSubscriptionWithDetails from './components/ShutterstockUI/getSubscriptionWithDetails';

/**
 * The edit function describes the structure of your block in the context of the
 * editor. This represents what the editor will render when the block is used.
 *
 * @see https://developer.wordpress.org/block-editor/developers/block-api/block-edit-save/#edit
 *
 * @param {Object} [props]           Properties passed from the editor.
 * @param {string} [props.className] Class name generated for the block.
 *
 * @return {WPElement} Element to render.
 */
const Edit = (props) => {
	const [isShutterstockModalOpen, setShutterstockModalOpen] = useState(false);
	const [licenseImage, setLicenseImage] = useState(false);
	const [imgLoaded, setImageLoaded] = useState(false);
	const [loading, setLoading] = useState(false);
	const [subscriptions, setSubscriptions] = useState([]);

	const openModal = () => setShutterstockModalOpen(true);
	const closeModal = () => setShutterstockModalOpen(false);
	const mediaType = props.attributes?.img?.media_type || 'image';
	const isEditorial = mediaType === 'editorial';

	const { permissions = {} } = shutterstock;
	const userIsAbleToLicenseAllImages = permissions.includes('can_user_license_all_shutterstock_images');
	const userIsAbleToLicenseEditorial = permissions.includes('can_user_license_shutterstock_editorial_image');
	const userIsAbleToLicensePhotos = permissions.includes('can_user_license_shutterstock_photos');
	let canLicense = false;

	if (
		userIsAbleToLicenseAllImages ||
		(isEditorial && userIsAbleToLicenseEditorial) || 
		(!isEditorial && userIsAbleToLicensePhotos)
	) {
		canLicense = true;
	}

	const toogleLoading = (status) => {
		setLicenseImage(status);
		setLoading(status);
	};

	const { snackbar, setSnackbar } = useSnackbarTimeout({
		onRemoveSnackbar: () => toogleLoading(false),		
	});

	const handleError = (error) => {
    let errorMessage = 'Something went wrong. Please try again.';
    if (error?.data?.statusCode !== 500 && error?.data?.message) {
      errorMessage = error.data.message;
    }
    
    setSnackbar({
			...snackbar,
    	show: true,
    	text: __(errorMessage, 'shutterstock-block'),
		});
	};

	useEffect(() => {
		if (props.attributes?.img?.licensedImageUrl) {
			const { attributes: { img: { 
				licensedImageUrl,
				contributorName,
				uploadedImageId,
				description
			}}} = props;

			const block = createBlock( "core/image", {
				url: licensedImageUrl,
				id: uploadedImageId,
				caption:`Image: ${contributorName}, Shutterstock`,
				alt: description,
				align: 'center',
			});
			
			props.replaceBlock(props.clientId, block);
		}
		
	}, [props.attributes?.img?.licensedImageUrl])

  return (
		<div className={ props.className }>
			<div>
				<span className="components-edit__shutterstock-icon"><ShutterstockIcon /></span>
				<span className="components-edit__heading">Shutterstock</span>
			</div>
      { props.attributes.img ?
				<div className="components-edit__image-container">
					<img src={props.attributes?.img?.licensedImageUrl || props.attributes?.img?.preview_1500?.url} onLoad={() => setImageLoaded(true)}/>
					{(canLicense && imgLoaded && !props.attributes?.img?.licensedImageUrl) &&
						<Button
							disabled={loading}
							onClick={async () => {
								try {
									toogleLoading(true);
									const subsWithImgDetails = await getSubscriptionWithDetails(props.attributes?.img.id, mediaType);
									setSubscriptions(subsWithImgDetails);
									openModal();
									setLoading(false);
								} catch(error) {
									handleError(error);
								}
							}}
							className="components-edit__license-image-button"
						>
							{loading && <span className="loading-spinner" />}
							<span>{__('License this image', 'shutterstock-block')}</span>
						</Button>
					}
				</div>
				: <span />
			}
			<div className="components-edit__paragraph">
				{__(
					'Add images from Shutterstock\'s library of over 340  million high-quality photos, vectors and illustrations.',
					'shutterstock-block'
				)}
			</div>
			<Button
				disabled={loading}
				onClick={() => {
					openModal();
					setLicenseImage(false);
				}}
				className="components-shutterstock-modal__open-modal-button "
			>
				{__('Browse', 'shutterstock-block')}
			</Button>
			{isShutterstockModalOpen && <ShutterstockUIModal
        setAttributes={props.setAttributes}
        closeModal={closeModal}
        canLicense={canLicense}
        assetInfo={props?.attributes?.img}
        licenseImage={licenseImage}
        subscriptions={subscriptions}
      />}
			{(snackbar.show && !isShutterstockModalOpen) && <ShutterstockSnackbar text={snackbar.text} />}
		</div>
	);
}

const EditContainer = withDispatch(dispatch => ({
	replaceBlock: dispatch('core/block-editor').replaceBlock,
}))(Edit);

export default EditContainer;