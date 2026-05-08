import { useEffect, useState } from 'react';
import { SnackbarProvider, VariantType, useSnackbar } from 'notistack';

export const Toast = ({ id, message, type = 'error', onClose }) => {
    const [isExiting, setIsExiting] = useState(false);
    const { enqueueSnackbar } = useSnackbar();

    enqueueSnackbar(message, { variant: type });
};
