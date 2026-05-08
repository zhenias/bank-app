import { useEffect, useState } from 'react';
import { SnackbarProvider, VariantType, useSnackbar, SnackbarKey } from 'notistack';

interface ToastProps {
    id: SnackbarKey;
    message: string;
    type?: VariantType;
    onClose: (id: SnackbarKey) => void;
}

export const Toast = ({ id, message, type = 'error', onClose }: ToastProps) => {
    const [isExiting, setIsExiting] = useState(false);
    const { enqueueSnackbar } = useSnackbar();

    useEffect(() => {
        enqueueSnackbar(message, {
            variant: type,
            onClose: () => onClose(id)
        });
    }, [id, message, type, enqueueSnackbar, onClose]);

    return null;
};