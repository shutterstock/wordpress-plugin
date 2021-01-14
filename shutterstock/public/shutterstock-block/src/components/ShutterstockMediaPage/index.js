import { render } from '@wordpress/element';

import ShutterstockUI from '../ShutterstockUI/ShutterstockUI';
import ShutterstockLogo from '../ShutterstockLogo/ShutterstockLogo.js';
import './ShutterstockMediaPage.scss';

window.onload = () => {
    render(
        <>
            <div className="components-shutterstock-media-page__logo">
                <ShutterstockLogo />
            </div>
            <ShutterstockUI
                isMediaPage
                canLicense
            />
        </>
        , document.getElementById('shutterstock-widget')
    );
}