// src/pages/Accounts/AccountViewPage.tsx

import { useEffect, useState } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import {
    Box,
    Typography,
    Card as CardPage,
    CardContent,
    Grid,
    Chip,
    Skeleton,
    Alert,
    Button,
    Stack,
    Divider,
    Table,
    TableBody,
    TableCell,
    TableContainer,
    TableHead,
    TableRow,
    Paper,
    IconButton,
    Dialog,
    DialogTitle,
    DialogContent,
    DialogActions,
    TextField,
    MenuItem,
    CircularProgress,
} from '@mui/material';
import {
    ArrowBack as BackIcon,
    CreditCard as CardIcon,
    Add as AddIcon,
    Block as BlockIcon,
    LockOpen as UnblockIcon,
    Delete as DeleteIcon,
} from '@mui/icons-material';
import {
    getAccount,
    getCards,
    createCard,
    blockCard,
    unblockCard,
    deleteCard, getDeleteAccount,
} from '../../services/accountService';
import type { Account, Card } from '../../types/types';
import {formatDate, formatDateShort} from "../../utils/formatDate";
import {formatAccountNumber} from "../../utils/formatAccount";
import {useToast} from "../../context/ToastContext";

export const AccountViewPage = () => {
    const { id } = useParams<{ id: string }>();
    const navigate = useNavigate();
    const { showError, showSuccess } = useToast();

    const [account, setAccount] = useState<Account | null>(null);
    const [cards, setCards] = useState<Card[]>([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState<string | null>(null);

    // Add card dialog
    const [openAddDialog, setOpenAddDialog] = useState(false);
    const [submitting, setSubmitting] = useState(false);
    const [network, setNetwork] = useState('visa');
    const [cardType, setCardType] = useState('debit');

    // Delete confirmation
    const [deleteCardId, setDeleteCardId] = useState<string | null>(null);

    // Delete account configmation
    const [deleteAccountId, setDeleteAccountId] = useState<string | null>(null);

    const loadData = async () => {
        if (!id) return;
        setLoading(true);
        setError(null);
        try {
            const [accountData, cardsData] = await Promise.all([
                getAccount(id),
                getCards(id),
            ]);
            setAccount(accountData);
            setCards(cardsData.data || []);
        } catch (err: any) {
            setError(err.message || 'Błąd pobierania danych');
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        loadData();
    }, [id]);

    const handleBlockCard = async (cardId: string) => {
        try {
            await blockCard(cardId);
            await loadData();

            showSuccess('Karta została zablokowana!');
        } catch (err: any) {
            setError(err.message);
        }
    };

    const handleUnblockCard = async (cardId: string) => {
        try {
            await unblockCard(cardId);
            await loadData();

            showSuccess('Karta została odblokowana!');
        } catch (err: any) {
            setError(err.message);
        }
    };

    const handleDeleteCard = async () => {
        if (!deleteCardId) return;
        try {
            await deleteCard(deleteCardId);
            setDeleteCardId(null);
            await loadData();

            showSuccess('Karta została usunięta!');
        } catch (err: any) {
            setError(err.message);
        }
    };

    const handleDeleteAccount = async () => {
        if (!deleteAccountId) return;
        try {
            await getDeleteAccount(deleteAccountId);
            setDeleteAccountId(null);
            navigate('/accounts');
            showSuccess('Konto bankowe zostało usunięte.');
        } catch (err: any) {
            setError(err.message);
        }
    };

    const handleAddCard = async () => {
        if (!id) return;
        setSubmitting(true);
        try {
            await createCard(id, network, cardType);
            setOpenAddDialog(false);
            setNetwork('visa');
            setCardType('debit');
            await loadData();
        } catch (err: any) {
            setError(err.message);
        } finally {
            setSubmitting(false);
        }
    };

    const getStatusColor = (status: string): 'success' | 'error' | 'warning' | 'default' => {
        switch (status) {
            case 'active': return 'success';
            case 'blocked': return 'error';
            case 'inactive': return 'warning';
            case 'expired': return 'default';
            default: return 'default';
        }
    };

    const getStatusLabel = (status: string): string => {
        switch (status) {
            case 'active': return 'Aktywna';
            case 'blocked': return 'Zablokowana';
            case 'inactive': return 'Nieaktywna';
            case 'expired': return 'Wygasła';
            default: return status;
        }
    };

    const getAccountTypeLabel = (type: string): string => {
        return type === 'savings' ? 'Oszczędnościowe' : 'Bieżące';
    };

    if (loading) {
        return (
            <Box>
                <Skeleton variant="rounded" height={200} sx={{ mb: 3 }} />
                <Skeleton variant="rounded" height={300} />
            </Box>
        );
    }

    if (error || !account) {
        return (
            <Box>
                <Button startIcon={<BackIcon />} onClick={() => navigate('/accounts')} sx={{ mb: 2 }}>
                    Powrót do kont
                </Button>
                <Alert severity="error">{error || 'Nie znaleziono konta'}</Alert>
            </Box>
        );
    }

    return (
        <Box sx={{ textAlign: 'left' }}>
            <Button startIcon={<BackIcon/>} onClick={() => navigate('/accounts')} sx={{mb: 3}}>
                Powrót do kont
            </Button>

            {error && <Alert severity="error" sx={{mb: 2}} onClose={() => setError(null)}>{error}</Alert>}

            {/* Account Details Card */}
            <CardPage sx={{mb: 4}}>
                <CardContent>
                    <Box sx={{display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start', mb: 3}}>
                        <Box>
                            <Typography variant="h4" sx={{textAlign: 'left'}} gutterBottom>
                                {account.name}
                            </Typography>
                            <Typography variant="body1" color="text.secondary" sx={{fontFamily: 'monospace', fontSize: '1.1rem'}}>
                                {formatAccountNumber(account.account_number)}
                            </Typography>
                        </Box>
                        <Chip
                            label={getAccountTypeLabel(account.type)}
                            color={account.type === 'savings' ? 'success' : 'primary'}
                            size="medium"
                        />
                    </Box>

                    <Grid container spacing={4}>
                        <Grid size={{xs: 12, sm: 4}}>
                            <Typography variant="body2" color="text.secondary">Saldo</Typography>
                            <Typography variant="h4">
                                {account.balance} {account.currency}
                            </Typography>
                        </Grid>
                        <Grid size={{xs: 12, sm: 4}}>
                            <Typography variant="body2" color="text.secondary">Waluta</Typography>
                            <Typography variant="h6">{account.currency}</Typography>
                        </Grid>
                        <Grid size={{xs: 12, sm: 4}}>
                            <Typography variant="body2" color="text.secondary">Utworzono</Typography>
                            <Typography variant="h6">
                                {formatDate(account.created_at)}
                            </Typography>
                        </Grid>

                        <IconButton color="default" disabled={account.balance !== 0} size="small" onClick={() => setDeleteAccountId(account.id)} title="Usuń">
                            <DeleteIcon/>
                        </IconButton>
                    </Grid>
                </CardContent>
            </CardPage>

            {/* Cards Section */}
            <Box sx={{display: 'flex', justifyContent: 'space-between', alignItems: 'center', mb: 2}}>
                <Typography variant="h5">
                    <CardIcon sx={{mr: 1, verticalAlign: 'middle'}}/>
                    Karty ({cards.length})
                </Typography>
                <Button variant="contained" startIcon={<AddIcon/>} size="small" onClick={() => setOpenAddDialog(true)}>
                    Dodaj kartę
                </Button>
            </Box>

            <Divider sx={{mb: 3}}/>

            {cards.length > 0 ? (
                <TableContainer component={Paper} variant="outlined">
                    <Table>
                        <TableHead>
                            <TableRow>
                                <TableCell>Karta</TableCell>
                                <TableCell>Network</TableCell>
                                <TableCell>Ważność</TableCell>
                                <TableCell>Status</TableCell>
                                <TableCell align="right">Akcje</TableCell>
                            </TableRow>
                        </TableHead>
                        <TableBody>
                            {cards.map((card) => (
                                <TableRow key={card.id} hover>
                                    <TableCell>
                                        <Box sx={{display: 'flex', alignItems: 'center', gap: 1}}>
                                            <CardIcon color="action"/>
                                            <Box>
                                                <Typography variant="body1">
                                                    •••• {card.card_last_four}
                                                </Typography>
                                                <Typography variant="caption" color="text.secondary">
                                                    {card.type === 'debit' ? 'Debetowa' : card.type}
                                                </Typography>
                                            </Box>
                                        </Box>
                                    </TableCell>
                                    <TableCell>
                                        <Chip label={card.network.toUpperCase()} size="small" variant="outlined"/>
                                    </TableCell>
                                    <TableCell>
                                        {String(card.exp_month).padStart(2, '0')}/{card.exp_year}
                                    </TableCell>
                                    <TableCell>
                                        <Chip
                                            label={getStatusLabel(card.status)}
                                            color={getStatusColor(card.status)}
                                            size="small"
                                        />
                                    </TableCell>
                                    <TableCell align="right">
                                        <Stack direction="row" spacing={0.5} sx={{justifyContent: 'flex-end'}}>
                                            {card.status === 'active' && (
                                                <IconButton color="error" size="small"
                                                            onClick={() => handleBlockCard(card.id)} title="Zablokuj">
                                                    <BlockIcon/>
                                                </IconButton>
                                            )}
                                            {card.status === 'blocked' && (
                                                <IconButton color="success" size="small"
                                                            onClick={() => handleUnblockCard(card.id)} title="Odblokuj">
                                                    <UnblockIcon/>
                                                </IconButton>
                                            )}
                                            <IconButton color="default" size="small"
                                                        onClick={() => setDeleteCardId(card.id)} title="Usuń">
                                                <DeleteIcon/>
                                            </IconButton>
                                        </Stack>
                                    </TableCell>
                                </TableRow>
                            ))}
                        </TableBody>
                    </Table>
                </TableContainer>
            ) : (
                <CardPage variant="outlined" sx={{textAlign: 'center', py: 6}}>
                    <CardContent>
                        <CardIcon sx={{fontSize: 48, color: 'text.secondary', mb: 2}}/>
                        <Typography variant="h6" color="text.secondary">
                            Brak kart
                        </Typography>
                        <Typography variant="body2" color="text.secondary" sx={{mb: 3}}>
                            To konto nie ma przypisanych kart płatniczych
                        </Typography>
                        <Button variant="outlined" startIcon={<AddIcon/>} onClick={() => setOpenAddDialog(true)}>
                            Dodaj pierwszą kartę
                        </Button>
                    </CardContent>
                </CardPage>
            )}

            {/* Add Card Dialog */}
            <Dialog open={openAddDialog} onClose={() => setOpenAddDialog(false)} maxWidth="xs" fullWidth>
                <DialogTitle>Dodaj nową kartę</DialogTitle>
                <DialogContent>
                    <Stack spacing={2} sx={{mt: 1}}>
                        <TextField
                            label="Network"
                            value={network}
                            onChange={(e) => setNetwork(e.target.value)}
                            select
                            fullWidth
                            disabled
                        >
                            <MenuItem value="visa">Visa</MenuItem>
                            <MenuItem value="mastercard">Mastercard</MenuItem>
                        </TextField>
                        <TextField
                            label="Typ karty"
                            value={cardType}
                            onChange={(e) => setCardType(e.target.value)}
                            select
                            fullWidth
                            disabled
                        >
                            <MenuItem value="debit">Debetowa</MenuItem>
                            <MenuItem value="credit">Kredytowa</MenuItem>
                            <MenuItem value="virtual">Wirtualna</MenuItem>
                        </TextField>
                    </Stack>
                </DialogContent>
                <DialogActions>
                    <Button onClick={() => setOpenAddDialog(false)} disabled={submitting}>Anuluj</Button>
                    <Button onClick={handleAddCard} variant="contained" disabled={submitting}
                            startIcon={submitting ? <CircularProgress size={20}/> : <AddIcon/>}>
                        {submitting ? 'Dodawanie...' : 'Dodaj kartę'}
                    </Button>
                </DialogActions>
            </Dialog>

            {/* Delete Confirmation Dialog */}
            <Dialog open={!!deleteCardId} onClose={() => setDeleteCardId(null)}>
                <DialogTitle>Usunąć kartę?</DialogTitle>
                <DialogContent>
                    <Typography>
                        Ta operacja jest nieodwracalna. Karta zostanie trwale usunięta.
                    </Typography>
                </DialogContent>
                <DialogActions>
                    <Button onClick={() => setDeleteCardId(null)}>Anuluj</Button>
                    <Button onClick={handleDeleteCard} color="error" variant="contained">
                        Usuń
                    </Button>
                </DialogActions>
            </Dialog>

            {/* Delete Confirmation Dialog */}
            <Dialog open={!!deleteAccountId} onClose={() => setDeleteAccountId(null)}>
                <DialogTitle>Usunąć konto bankowe?</DialogTitle>
                <DialogContent>
                    <Typography>
                        Ta operacja jest nieodwracalna. Konto zostanie trwale usunięta.
                        Jeśli jest więcej od 0 na koncie, to konto nie może zostać usunięte.
                    </Typography>
                </DialogContent>
                <DialogActions>
                    <Button onClick={() => setDeleteAccountId(null)}>Anuluj</Button>
                    <Button onClick={handleDeleteAccount} color="error" variant="contained">
                        Usuń
                    </Button>
                </DialogActions>
            </Dialog>
        </Box>
    );
};