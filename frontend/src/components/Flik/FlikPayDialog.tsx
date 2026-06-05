import { useEffect, useState } from 'react';
import {Dialog, DialogTitle, DialogContent, DialogActions, Button, TextField, Stack, AlertTitle, Alert} from '@mui/material';
import { payFlik } from '../../services/accountService';
import { useToast } from '../../context/ToastContext';

export const FlikPayDialog = ({ open, onClose, initialCode, callback }: { open: boolean; onClose: () => void; initialCode?: string; callback?: () => void }) => {
    const [code, setCode] = useState(initialCode ?? '');
    const [loading, setLoading] = useState(false);
    const { showError, showSuccess } = useToast();

    useEffect(() => {
        setCode(initialCode ?? '');
    }, [initialCode]);

    const handleSubmit = async () => {
        if (!/^\d{6}$/.test(code)) return showError('Kod musi zawierać 6 cyfr');

        setLoading(true);
        try {
            const res = await payFlik({ code });
            showSuccess(res.message || 'Płatność przyjęta');
            onClose();
            if (callback) callback();
        } catch (err: any) {
            showError(err.message || 'Błąd płatności');
        } finally {
            setLoading(false);
        }
    };

    return (
        <Dialog open={open} onClose={onClose}>
            <DialogTitle>Płatność FLIK</DialogTitle>
            <DialogContent>
                <Alert>
                    <AlertTitle>Informacja</AlertTitle>
                    Odbiór środków jest realizowany tylko i wyłącznie w złotówkach.
                </Alert>
                <Stack spacing={2} sx={{ mt: 1, minWidth: 360 }}>
                    <TextField
                       inputMode={'numeric'}
                       slotProps={{
                            htmlInput: {
                                maxLength: 6,
                                inputMode: 'numeric',
                                pattern: '[0-9]*'
                            }
                        }}
                       label="Kod (6 cyfr)"
                       value={code}
                       onChange={e => {
                           const val = e.target.value.replace(/\D/g, '').slice(0, 6);
                           setCode(val);
                       }}
                    />
                </Stack>
            </DialogContent>
            <DialogActions>
                <Button onClick={onClose} disabled={loading}>Anuluj</Button>
                <Button variant="contained" onClick={handleSubmit} disabled={loading}>Zapłać</Button>
            </DialogActions>
        </Dialog>
    );
};

