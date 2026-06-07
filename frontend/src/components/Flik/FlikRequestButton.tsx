import { useEffect, useRef, useState } from 'react';
import {
    Button,
    Dialog,
    DialogTitle,
    DialogContent,
    Typography,
    Box,
    IconButton,
    MenuItem,
    TextField,
    CircularProgress,
    Stack,
    AlertTitle, Alert
} from '@mui/material';
import ContentCopyIcon from '@mui/icons-material/ContentCopy';
import {requestFlikCode, getAccounts, getCards, checkFlikStatus} from '../../services/accountService';
import { useToast } from '../../context/ToastContext';
import type { Account, Card } from '../../types/types';
import {formatAmount} from "../../utils/formatMoney";
import {getStatusCard} from "../../utils/formatCard";

export const FlikRequestButton = ({ open, onClose }: { open: boolean; onClose: () => void }) => {
    const [code, setCode] = useState<string | null>(null);
    const [codeAmount, setCodeAmount] = useState<number | null>(null);
    const [remaining, setRemaining] = useState<number>(0);
    const { showError, showSuccess } = useToast();
    const [, setExpiresIn] = useState<number>(0);

    const [accounts, setAccounts] = useState<Account[]>([]);
    const [cards, setCards] = useState<Card[]>([]);
    const [selectedAccountId, setSelectedAccountId] = useState<string>('');
    const [selectedCardId, setSelectedCardId] = useState<string>('');
    const [loadingAccounts, setLoadingAccounts] = useState(false);
    const [loadingCards, setLoadingCards] = useState(false);

    const [amount, setAmount] = useState('');
    const statusIntervalRef = useRef<ReturnType<typeof setInterval> | null>(null);

    useEffect(() => {
        let id: ReturnType<typeof setInterval>;
        if (remaining > 0) {
            id = setInterval(() => setRemaining(r => r - 1), 1000);
        }
        return () => clearInterval(id);
    }, [remaining]);

    useEffect(() => {
        if (remaining <= 0) {
            setCode(null);
            if (statusIntervalRef.current) {
                clearInterval(statusIntervalRef.current);
                statusIntervalRef.current = null;
            }
        }
    }, [remaining]);

    useEffect(() => {
        if (!open) return;

        const loadAccounts = async () => {
            setLoadingAccounts(true);
            try {
                const response = await getAccounts();
                setAccounts(response.data);
                if (response.data.length > 0) {
                    setSelectedAccountId(response.data[0].id);
                }
            } catch (err: any) {
                showError(err.message || 'Błąd pobierania kont');
            } finally {
                setLoadingAccounts(false);
            }
        };

        loadAccounts();
    }, [open, showError]);

    useEffect(() => {
        if (!selectedAccountId || !open) return;

        const loadCards = async () => {
            setLoadingCards(true);
            try {
                const response = await getCards(selectedAccountId);
                setCards(response.data);
                if (response.data.length > 0) {
                    setSelectedCardId(response.data[0].id);
                }
            } catch (err: any) {
                showError(err.message || 'Błąd pobierania kart');
            } finally {
                setLoadingCards(false);
            }
        };

        loadCards();
    }, [selectedAccountId, open, showError]);

    useEffect(() => {
        if (!open) {
            if (statusIntervalRef.current) {
                clearInterval(statusIntervalRef.current);
                statusIntervalRef.current = null;
            }
            setCode(null);
            setCodeAmount(null);
            setRemaining(0);
            setSelectedAccountId('');
            setSelectedCardId('');
            setAccounts([]);
            setCards([]);
        }
    }, [open]);

    useEffect(() => {
        return () => {
            if (statusIntervalRef.current) {
                clearInterval(statusIntervalRef.current);
            }
        };
    }, []);

    const handleRequest = async () => {
        if (!selectedAccountId || !selectedCardId) {
            showError('Wybierz konto i kartę');
            return;
        }
        if (!amount) {
            showError('Podaj kwotę');
            return;
        }

        try {
            const res = await requestFlikCode({
                card_id: selectedCardId,
                account_id: selectedAccountId,
                amount,
            });
            setCode(res.code);
            setCodeAmount(res.amount);
            setExpiresIn(res.expires_in_seconds ?? 120);
            setRemaining(res.expires_in_seconds ?? 120);
            showSuccess('Kod FLIK wygenerowany');

            const codeValue = res.code;
            const cardIdValue = selectedCardId;

            statusIntervalRef.current = setInterval(async () => {
                try {
                    const statusRes = await checkFlikStatus(codeValue);
                    if (statusRes.status === 'used') {
                        showSuccess('Kod FLIK został wykorzystany. Środki powinny być już dostępne na koncie odbiorcy.');
                        if (statusIntervalRef.current) {
                            clearInterval(statusIntervalRef.current);
                            statusIntervalRef.current = null;
                        }
                        setCode(null);
                        setCodeAmount(null);
                        setRemaining(0);
                    }
                } catch {
                    // Polling errors are silently ignored
                }
            }, 2000);
        } catch (err: any) {
            showError(err.message || 'Błąd generowania kodu');
        }
    };

    const checkIfAccountHasBalance = () => {
        if (!selectedAccountId) return false;

        const account = accounts.find(acc => acc.account_number === selectedAccountId);
        if (!account) return false;

        const balance = parseFloat(String(account.balance));
        return !isNaN(balance) && balance > 0;
    };

    const checkIfCardActive = () => {
        if (!selectedCardId) return false;

        const card = cards.find(c => c.id === selectedCardId);
        if (!card) return false;

        return card.status !== 'active';
    };

    const handleCopy = async () => {
        if (!code) return;
        await navigator.clipboard.writeText(code);
        showSuccess('Skopiowano kod do schowka');
    };

    return (
        <Dialog open={open} onClose={onClose} maxWidth="sm" fullWidth>
            <DialogTitle>Generowanie kodu FLIK</DialogTitle>
            <DialogContent>
                <Box sx={{ display: 'flex', flexDirection: 'column', gap: 3, pt: 2 }}>
                    {code ? (
                        <Box sx={{ display: 'flex', flexDirection: 'column', alignItems: 'center', gap: 2 }}>
                            <Typography variant="body2" color="text.secondary">Wygenerowany kod FLIK</Typography>
                            <Typography variant="h4" sx={{ fontFamily: 'monospace', fontWeight: 'bold' }}>{code}</Typography>
                            {codeAmount && (
                                <Box sx={{ display: 'flex', flexDirection: 'column', alignItems: 'center', gap: 0.5 }}>
                                    <Typography variant="body2" color="text.secondary">Kwota:</Typography>
                                    <Typography variant="h6" sx={{ fontWeight: 'bold' }}>{formatAmount(codeAmount)} PLN</Typography>
                                </Box>
                            )}
                            <Box sx={{ display: 'flex', flexDirection: 'row', gap: 1, alignItems: 'center' }}>
                                <Typography variant="body2">Wygasa za:</Typography>
                                <Typography variant="body2" sx={{ fontWeight: 'bold' }}>
                                    {Math.floor(remaining/60)}:{String(remaining%60).padStart(2, '0')}
                                </Typography>
                                <IconButton onClick={handleCopy} size="small">
                                    <ContentCopyIcon fontSize="small"/>
                                </IconButton>
                            </Box>
                            <Button variant="contained" onClick={onClose} sx={{ mt: 2, alignSelf: 'stretch' }}>
                                Zamknij
                            </Button>
                        </Box>
                    ) : (
                        <>
                            <Alert>
                                <AlertTitle>Informacja</AlertTitle>
                                Możesz wygenerować kod FLIK, który pozwoli na odbiór środków z Twojego konta. Odbiór jest realizowany tylko i wyłącznie w złotówkach. Kod jest ważny przez 2 minuty.
                            </Alert>
                            <Stack spacing={2}>
                                <Box>
                                    <Typography variant="caption" color="text.secondary" sx={{display: 'block', mb: 1}}>
                                        Konto
                                    </Typography>
                                    <TextField
                                        select
                                        fullWidth
                                        value={selectedAccountId}
                                        onChange={(e) => setSelectedAccountId(e.target.value)}
                                        disabled={loadingAccounts || accounts.length === 0}
                                        size="small"
                                    >
                                        {accounts.filter((account) => account.currency === 'PLN' ).map((account) => (
                                            <MenuItem key={account.id} value={account.id}>
                                                {account.name} {account.balance} {account.currency} ({account.account_number})
                                            </MenuItem>
                                        ))}
                                    </TextField>
                                </Box>

                                <Box>
                                    <Typography variant="caption" color="text.secondary" sx={{display: 'block', mb: 1}}>
                                        Karta
                                    </Typography>
                                    {loadingCards ? (
                                        <Box sx={{display: 'flex', justifyContent: 'center', py: 2}}>
                                            <CircularProgress size={30}/>
                                        </Box>
                                    ) : (
                                        <TextField
                                            select
                                            fullWidth
                                            value={selectedCardId}
                                            onChange={(e) => setSelectedCardId(e.target.value)}
                                            disabled={cards.length === 0}
                                            size="small"
                                        >
                                            {cards.map((card) => (
                                                <MenuItem key={card.id} value={card.id}>
                                                    {card.network.toUpperCase()} •••• {card.card_last_four} {card.status !== 'active' ? `(${getStatusCard(card.status)})` : ''}
                                                </MenuItem>
                                            ))}
                                        </TextField>
                                    )}
                                </Box>

                                <Box>
                                    <Typography variant="caption" color="text.secondary" sx={{display: 'block', mb: 1}}>
                                        Kwota
                                    </Typography>

                                    <TextField
                                        fullWidth
                                        label="Kwota (np. 10.00)"
                                        disabled={cards.length === 0 || accounts.length === 0}
                                        value={amount}
                                        onChange={e => setAmount(e.target.value)}
                                        size="small"/>
                                </Box>

                                <Button
                                    variant="contained"
                                    onClick={handleRequest}
                                    disabled={!selectedAccountId || !selectedCardId || !amount || checkIfAccountHasBalance() || checkIfCardActive() || loadingAccounts || loadingCards}
                                    fullWidth
                                >
                                    Wygeneruj kod FLIK
                                </Button>
                            </Stack>
                        </>
                    )}
                </Box>
            </DialogContent>
        </Dialog>
    );
};