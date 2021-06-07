import { render } from '@wordpress/element';

import ShutterstockUI from '../ShutterstockUI/ShutterstockUI';
import ShutterstockLogo from '../ShutterstockLogo/ShutterstockLogo.js';
import './ShutterstockMediaPage.scss';

window.onload = () => {
    const { permissions = {} } = shutterstock;
	const userIsAbleToLicenseAllImages = permissions.includes('can_user_license_all_shutterstock_images');
	const userIsAbleToLicenseEditorial = permissions.includes('can_user_license_shutterstock_editorial_image');
	const userIsAbleToLicensePhotos = permissions.includes('can_user_license_shutterstock_photos');
	let canLicense = false;

	if (
		userIsAbleToLicenseAllImages ||
		(userIsAbleToLicenseEditorial) || 
		(userIsAbleToLicensePhotos)
	) {
		canLicense = true;
	}

    render(
        <>
            <div className="components-shutterstock-media-page__logo">
                <ShutterstockLogo />
            </div>
            <ShutterstockUI
                isMediaPage
                canLicense={canLicense}
            />
        </>
        , document.getElementById('shutterstock-widget')
    );
}