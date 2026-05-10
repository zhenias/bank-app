import { useEffect, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import {
    Box,
    Typography,
    Card,
    CardContent,
    CardActionArea,
    Grid,
    Chip,
    Skeleton,
    Alert,
    Pagination,
    Stack,
    Button,
    Dialog,
    DialogTitle,
    DialogContent,
    DialogActions,
    TextField,
    MenuItem,
    FormControlLabel,
    Switch,
    CircularProgress,
} from '@mui/material';
import {
    AccountBalance as AccountIcon,
    Add as AddIcon,
} from '@mui/icons-material';
import { getAccounts, createAccount } from '../../services/accountService';
import type { Account, PaginationMeta } from '../../types/types';

export const AccountPage = () => {
    const [accounts, setAccounts] = useState<Account[]>([]);
    const [meta, setMeta] = useState<PaginationMeta | null>(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState<string | null>(null);
    const [page, setPage] = useState(1);
    const navigate = useNavigate();

    // Dialog state
    const [openDialog, setOpenDialog] = useState(false);
    const [submitting, setSubmitting] = useState(false);
    const [formError, setFormError] = useState<string | null>(null);
    const [formData, setFormData] = useState({
        name: 'Konto moje na dzisiaj',
        currency: 'PLN',
        type: 'current',
        with_card: true,
    });

    const loadAccounts = async (pageNumber: number = 1) => {
        setLoading(true);
        setError(null);
        try {
            const response = await getAccounts();
            setAccounts(response.data);
            setMeta(response.meta);
        } catch (err: any) {
            setError(err.message || 'Błąd pobierania kont');
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        loadAccounts(page);
    }, [page]);

    const handlePageChange = (_: React.ChangeEvent<unknown>, value: number) => {
        setPage(value);
    };

    const handleAccountClick = (id: string) => {
        navigate(`/accounts/${id}`);
    };

    const handleOpenDialog = () => {
        setFormData({ name: '', currency: 'PLN', type: 'current', with_card: true });
        setFormError(null);
        setOpenDialog(true);
    };

    const handleCloseDialog = () => {
        setOpenDialog(false);
    };

    const handleFormChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        const { name, value, type, checked } = e.target;
        setFormData(prev => ({
            ...prev,
            [name]: type === 'checkbox' ? checked : value,
        }));
    };

    const handleSubmit = async () => {
        setFormError(null);
        setSubmitting(true);

        try {
            await createAccount(formData);
            handleCloseDialog();
            await loadAccounts();
        } catch (err: any) {
            setFormError(err.message || 'Błąd tworzenia konta');
        } finally {
            setSubmitting(false);
        }
    };

    const getAccountTypeLabel = (type: string): string => {
        return type === 'savings' ? 'Oszczędnościowe' : 'Bieżące';
    };

    const getAccountTypeColor = (type: string): 'success' | 'primary' => {
        return type === 'savings' ? 'success' : 'primary';
    };

    return (
        <Box>
            <Box sx={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', mb: 4 }}>
                <Typography variant="h4">Konta bankowe</Typography>
                <Button variant="contained" startIcon={<AddIcon />} onClick={handleOpenDialog}>
                    Nowe konto
                </Button>
            </Box>

            {error && (
                <Alert severity="error" sx={{ mb: 3 }}>
                    {error}
                </Alert>
            )}

            {loading ? (
                <Grid container spacing={3}>
                    {[1, 2].map((i) => (
                        <Grid size={{ xs: 12, md: 6 }} key={i}>
                            <Skeleton variant="rounded" height={160} />
                        </Grid>
                    ))}
                </Grid>
            ) : accounts.length === 0 ? (
                <Card sx={{ textAlign: 'center', py: 8 }}>
                    <CardContent>
                        <AccountIcon sx={{ fontSize: 64, color: 'text.secondary', mb: 2 }} />
                        <Typography variant="h6" color="text.secondary">
                            Brak kont bankowych
                        </Typography>
                        <Typography variant="body2" color="text.secondary" sx={{ mb: 3 }}>
                            Utwórz swoje pierwsze konto bankowe
                        </Typography>
                        <Button variant="contained" startIcon={<AddIcon />} onClick={handleOpenDialog}>
                            Utwórz konto
                        </Button>
                    </CardContent>
                </Card>
            ) : (
                <>
                    <Grid container spacing={3}>
                        {accounts.map((account) => (
                            <Grid size={{ xs: 12, md: 6 }} key={account.id}>
                                <Card variant="outlined">
                                    <CardActionArea onClick={() => handleAccountClick(account.id)}>
                                        <CardContent>
                                            <Box sx={{ display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start', mb: 2 }}>
                                                <Box>
                                                    <Typography variant="h6" gutterBottom>
                                                        {account.name}
                                                    </Typography>
                                                    <Typography variant="body2" color="text.secondary" sx={{ wordBreak: 'break-all' }}>
                                                        {account.account_number.replace(/(\d{2})(\d{4})(\d{4})(\d{4})(\d{4})(\d{4})(\d{4})/, '$1 $2 $3 $4 $5 $6 $7')}
                                                    </Typography>
                                                </Box>
                                                <Chip
                                                    label={getAccountTypeLabel(account.type)}
                                                    color={getAccountTypeColor(account.type)}
                                                    size="small"
                                                />
                                            </Box>

                                            <Typography variant="h5" sx={{ mb: 1 }}>
                                                {account.balance} {account.currency}
                                            </Typography>

                                            <Box sx={{ display: 'flex', gap: 1, flexWrap: 'wrap' }}>
                                                {account.cards?.slice(0, 3).map((card) => (
                                                    <Chip
                                                        key={card.id}
                                                        label={`${card.network} •••• ${card.card_last_four}`}
                                                        size="small"
                                                        variant="outlined"
                                                        color={card.status === 'active' ? 'default' : 'error'}
                                                    />
                                                ))}
                                                {account.cards && account.cards.length > 3 && (
                                                    <Chip
                                                        label={`+${account.cards.length - 3} więcej`}
                                                        size="small"
                                                        variant="outlined"
                                                    />
                                                )}
                                                {(!account.cards || account.cards.length === 0) && (
                                                    <Typography variant="caption" color="text.secondary">
                                                        Brak kart
                                                    </Typography>
                                                )}
                                            </Box>
                                        </CardContent>
                                    </CardActionArea>
                                </Card>
                            </Grid>
                        ))}
                    </Grid>

                    {meta && meta.last_page > 1 && (
                        <Stack spacing={2} sx={{ mt: 4, alignItems: 'center' }}>
                            <Pagination
                                count={meta.last_page}
                                page={meta.current_page}
                                onChange={handlePageChange}
                                color="primary"
                                showFirstButton
                                showLastButton
                            />
                            <Typography variant="body2" color="text.secondary">
                                Wyświetlono {meta.from ?? 0}-{meta.to ?? 0} z {meta.total} kont
                            </Typography>
                        </Stack>
                    )}
                </>
            )}

            {/* Create Account Dialog */}
            <Dialog open={openDialog} onClose={handleCloseDialog} maxWidth="sm" fullWidth>
                <DialogTitle>Nowe konto bankowe</DialogTitle>
                <DialogContent>
                    {formError && (
                        <Alert severity="error" sx={{ mb: 2 }}>
                            {formError}
                        </Alert>
                    )}
                    <Stack spacing={2} sx={{ mt: 1 }}>
                        <TextField
                            name="name"
                            label="Nazwa konta"
                            value={formData.name}
                            onChange={handleFormChange}
                            required
                            fullWidth
                            placeholder="Np. Konto oszczędnościowe"
                        />
                        <TextField
                            name="currency"
                            label="Waluta"
                            value={formData.currency}
                            onChange={handleFormChange}
                            select
                            fullWidth
                            disabled
                        >
                            <MenuItem value="PLN">PLN - Polski złoty</MenuItem>
                            <MenuItem value="EUR">EUR - Euro</MenuItem>
                            <MenuItem value="USD">USD - Dolar amerykański</MenuItem>
                        </TextField>
                        <TextField
                            name="type"
                            label="Typ konta"
                            value={formData.type}
                            onChange={handleFormChange}
                            select
                            fullWidth
                        >
                            <MenuItem value="current">Bieżące</MenuItem>
                            <MenuItem value="savings">Oszczędnościowe</MenuItem>
                        </TextField>
                        <FormControlLabel
                            control={
                                <Switch
                                    name="with_card"
                                    checked={formData.with_card}
                                    onChange={handleFormChange}
                                    disabled
                                />
                            }
                            label="Utwórz kartę debetową do konta"
                        />
                    </Stack>
                </DialogContent>
                <DialogActions>
                    <Button onClick={handleCloseDialog} disabled={submitting}>
                        Anuluj
                    </Button>
                    <Button
                        onClick={handleSubmit}
                        variant="contained"
                        disabled={submitting || !formData.name}
                        startIcon={submitting ? <CircularProgress size={20} /> : null}
                    >
                        {submitting ? 'Tworzenie...' : 'Utwórz konto'}
                    </Button>
                </DialogActions>
            </Dialog>
        </Box>
    );
};