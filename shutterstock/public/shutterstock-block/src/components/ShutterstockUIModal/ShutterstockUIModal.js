import { Modal } from '@wordpress/components';
import { __ } from '@wordpress/i18n';


import ShutterstockUI from '../ShutterstockUI/ShutterstockUI.js';
import ShutterstockLogo from '../ShutterstockLogo/ShutterstockLogo.js';

import './ShutterstockUIModal.scss';

const ShutterstockUIModal = ({
  setAttributes,
  closeModal,
  canLicense,
  item,
  licenseImage,
  subscriptions,
}) => {
  return (
    <>
      <Modal
        overlayClassName="overlay"
        shouldCloseOnClickOutside={false}
        className="components-shutterstock-modal__content"
        title={<ShutterstockLogo />}
        onRequestClose={closeModal}>
        <div style={{width: '100%'}}>
          <ShutterstockUI
            setAttributes={setAttributes}
            closeModal={closeModal}
            canLicense={canLicense}
            item={item}
            licenseImage={licenseImage}
            subscriptions={subscriptions}
          />
        </div>
      </Modal>
    </>
  );
}

export default ShutterstockUIModal;
