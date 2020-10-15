import { Snackbar } from '@wordpress/components';
import './ShutterstockSnackbar.scss';

const ShutterstockSnackbar = ({
  text,
}) => {  
  return text && (
    <div className="components-shutterstock-snackbar__container">
      <Snackbar>
        Shutterstock: {text}
      </Snackbar>
    </div>
  )
};

export default ShutterstockSnackbar;