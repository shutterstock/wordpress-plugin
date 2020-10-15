import { useEffect, useState } from '@wordpress/element';

const useSnackbarTimeout = ({
  timeout = 5000,  
  onRemoveSnackbar
}) => {
  const [snackbar, setSnackbar] = useState({ show: false, text: '' });
  useEffect(() => {
		const timeoutHandle = setTimeout( () => {
      setSnackbar({ 
        ...snackbar, 
        show: false, 
        text: ''
      });

      if(onRemoveSnackbar) onRemoveSnackbar();
		}, timeout);

		return () => clearTimeout(timeoutHandle);
  }, [snackbar.show]);

  return { snackbar, setSnackbar };
};

export default useSnackbarTimeout;