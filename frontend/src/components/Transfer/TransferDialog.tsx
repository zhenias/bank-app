import { useState } from 'react';
import {
    Dialog,
    DialogTitle,
    DialogContent,
    DialogActions,
    Button,
    TextField,
    Stack,
    AlertTitle,
    Alert
} from '@mui/material';
import { createTransfer } from '../../services/accountService';
import { useToast } from '../../context/ToastContext';

export const TransferDialog = ({ open, onClose, callback }: { open: boolean; onClose: () => void; callback?: () => void }) => {
    const [toAccountNumber, setToAccountNumber] = useState('');
    const [amount, setAmount] = useState('');
    const [description, setDescription] = useState('');
    const [reference, setReference] = useState('');
    const [loading, setLoading] = useState(false);
    const { showError, showSuccess } = useToast();

    const handleSubmit = async () => {
        if (!/^[0-9]{26}$/.test(toAccountNumber)) return showError('Numer konta musi zawierać 26 cyfr');
        if (!/\d+(\.\d{1,2})?/.test(amount)) return showError('Nieprawidłowa kwota');

        setLoading(true);
        try {
            const tx = await createTransfer({ to_account_number: toAccountNumber, amount, description, reference });
            showSuccess('Przelew wykonany');
            onClose();
            if (callback) callback();
        } catch (err: any) {
            showError(err.message || 'Błąd wykonywania przelewu');
        } finally {
            setLoading(false);
        }
    };

    return (
        <Dialog open={open} onClose={onClose}>
            <DialogTitle>Nowy przelew</DialogTitle>
            <DialogContent>
                <Alert>
                    <AlertTitle>Informacja</AlertTitle>
                    Przelew środków jest realizowany tylko i wyłącznie w złotówkach.
                    W innych walutach transakcje nie są akceptowane.
                </Alert>
                <Stack spacing={2} sx={{ mt: 1, minWidth: 360 }}>
                    <TextField label="Numer konta odbiorcy (26 cyfr)" value={toAccountNumber} onChange={e => setToAccountNumber(e.target.value)} />
                    <TextField label="Kwota (np. 10.00)" value={amount} onChange={e => setAmount(e.target.value)} />
                    <TextField label="Tytuł (opcjonalne)" value={description} onChange={e => setDescription(e.target.value)} />
                    <TextField label="Referencja (opcjonalne)" value={reference} onChange={e => setReference(e.target.value)} />
                </Stack>
            </DialogContent>
            <DialogActions>
                <Button onClick={onClose} disabled={loading}>Anuluj</Button>
                <Button variant="contained" onClick={handleSubmit} disabled={loading}>Wyślij</Button>
            </DialogActions>
        </Dialog>
    );
};

