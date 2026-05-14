import { useState, useEffect } from 'react';
import { useAuth } from '../../context/AuthContext';
import { fetchWithAuth } from '../../services/authService';
import {
    Container,
    Typography,
    Box,
    Grid,
    Card,
    CardContent,
    Alert,
    CircularProgress,
    Divider,
    Chip,
    Table,
    TableBody,
    TableCell,
    TableContainer,
    TableHead,
    TableRow,
    Paper,
    Tooltip
} from '@mui/material';
import {
    AccountBalanceWallet,
    AccountBalance,
    Receipt,
    InfoOutlined,
    Euro,
    AttachMoney,
    CurrencyExchange
} from '@mui/icons-material';

interface CurrencyBalance {
    currency: string;
    balance: string;
}

interface BalanceResponse {
    balances: CurrencyBalance[];
    total: string;
}

const getCurrencyIcon = (currency: string) => {
    switch (currency) {
        case 'EURO': return <Euro sx={{ fontSize: 20 }} />;
        case 'USD': return <AttachMoney sx={{ fontSize: 20 }} />;
        case 'PLN': return <CurrencyExchange sx={{ fontSize: 20 }} />;
        default: return <AttachMoney sx={{ fontSize: 20 }} />;
    }
};

const getCurrencyColor = (currency: string) => {
    switch (currency) {
        case 'PLN': return 'success';
        case 'EURO': return 'info';
        case 'USD': return 'warning';
        default: return 'default';
    }
};

export const BalancePage = () => {
    const { user } = useAuth();
    const [balance, setBalance] = useState<BalanceResponse>();
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState<string | null>(null);

    useEffect(() => {
        fetchBalance();
    }, []);

    const fetchBalance = async () => {
        try {
            const response = await fetchWithAuth('/accounts/balance');
            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || 'Błąd pobierania salda');
            }

            setBalance(data);
        } catch (err: any) {
            setError(err.message || 'Nie udało się pobrać salda');
            console.error(err);
        } finally {
            setLoading(false);
        }
    };

    if (loading) {
        return (
            <Container maxWidth="lg" sx={{ display: 'flex', justifyContent: 'center', alignItems: 'center', minHeight: '60vh' }}>
                <Box sx={{ display: 'flex', flexDirection: 'column', alignItems: 'center', gap: 2 }}>
                    <CircularProgress size={60} />
                    <Typography variant="h6" color="text.secondary">
                        Ładowanie danych konta...
                    </Typography>
                </Box>
            </Container>
        );
    }

    return (
        <Container maxWidth="lg" sx={{ py: 4 }}>
            {/* Nagłówek */}
            <Box sx={{ mb: 4 }}>
                <Typography variant="h4" component="h1" gutterBottom>
                    Witaj, {user?.name || user?.email}!
                </Typography>
                <Typography variant="body1" color="text.secondary">
                    Zarządzaj swoim kontem bankowym
                </Typography>
            </Box>

            <Grid container spacing={3}>
                {/* Lewa kolumna - Saldo i lista walut */}
                <Grid size={{ xs: 12, md: 8 }}>
                    {/* Główne saldo PLN */}
                    <Card elevation={2} sx={{ mb: 3 }}>
                        <CardContent sx={{ p: 4 }}>
                            <Box sx={{ display: 'flex', alignItems: 'center', mb: 3 }}>
                                <AccountBalanceWallet sx={{ mr: 2, color: 'primary.main', fontSize: 32 }} />
                                <Typography variant="h5" component="h2">
                                    Stan konta
                                </Typography>
                            </Box>

                            <Divider sx={{ mb: 3 }} />

                            {error ? (
                                <Alert severity="error" sx={{ mb: 2 }}>
                                    {error}
                                </Alert>
                            ) : (
                                <Box sx={{
                                    bgcolor: 'primary.main',
                                    color: 'primary.contrastText',
                                    p: 4,
                                    borderRadius: 2,
                                    textAlign: 'center'
                                }}>
                                    <Typography variant="body2" sx={{ mb: 1, opacity: 0.9 }}>
                                        Twoje saldo wynosi:
                                    </Typography>
                                    <Typography variant="h3" component="div" sx={{ fontWeight: 'bold', fontSize: '2.5rem' }}>
                                        {balance?.total || '0.00'} PLN
                                    </Typography>
                                    <Tooltip title="Suma obejmuje wyłącznie konta w walucie PLN. Waluty obce (EUR, USD) wymagają przeliczenia.">
                                        <Box sx={{
                                            display: 'inline-flex',
                                            alignItems: 'center',
                                            mt: 2,
                                            gap: 1,
                                            cursor: 'pointer',
                                            bgcolor: 'rgba(255,255,255,0.15)',
                                            px: 2,
                                            py: 0.5,
                                            borderRadius: 4
                                        }}>
                                            <InfoOutlined sx={{ fontSize: 16, opacity: 0.9 }} />
                                            <Typography variant="caption" sx={{ opacity: 0.9 }}>
                                                Tylko waluta PLN
                                            </Typography>
                                        </Box>
                                    </Tooltip>
                                </Box>
                            )}
                        </CardContent>
                    </Card>

                    {/* Lista walut */}
                    <Card elevation={2}>
                        <CardContent sx={{ p: 4 }}>
                            <Box sx={{ display: 'flex', alignItems: 'center', mb: 3 }}>
                                <AccountBalance sx={{ mr: 2, color: 'primary.main', fontSize: 28 }} />
                                <Typography variant="h5" component="h2">
                                    Salda na kontach
                                </Typography>
                            </Box>

                            <Divider sx={{ mb: 3 }} />

                            {!balance?.balances || balance.balances.length === 0 ? (
                                <Alert severity="info">Brak dostępnych kont</Alert>
                            ) : (
                                <TableContainer component={Paper} variant="outlined" sx={{ borderRadius: 2 }}>
                                    <Table>
                                        <TableHead>
                                            <TableRow sx={{ bgcolor: 'grey.50' }}>
                                                <TableCell sx={{ fontWeight: 'bold', pl: 3 }}>Waluta</TableCell>
                                                <TableCell sx={{ fontWeight: 'bold' }} align="right">Saldo</TableCell>
                                                <TableCell sx={{ fontWeight: 'bold', pr: 3 }} align="right">Status</TableCell>
                                            </TableRow>
                                        </TableHead>
                                        <TableBody>
                                            {balance.balances.map((item, index) => (
                                                <TableRow
                                                    key={item.currency}
                                                    sx={{
                                                        '&:hover': { bgcolor: 'grey.50' },
                                                        '&:last-child td, &:last-child th': { border: 0 }
                                                    }}
                                                >
                                                    <TableCell sx={{ pl: 3 }}>
                                                        <Box sx={{ display: 'flex', alignItems: 'center', gap: 1.5 }}>
                                                            <Box sx={{
                                                                color: item.currency === 'PLN' ? 'success.main' : 'text.secondary',
                                                                display: 'flex',
                                                                alignItems: 'center'
                                                            }}>
                                                                {getCurrencyIcon(item.currency)}
                                                            </Box>
                                                            <Typography
                                                                variant="body1"
                                                                sx={{
                                                                    fontWeight: 'medium'
                                                                }}
                                                            >
                                                                {item.currency}
                                                            </Typography>
                                                        </Box>
                                                    </TableCell>
                                                    <TableCell align="right">
                                                        <Typography
                                                            variant="body1"
                                                            sx={{
                                                                color: item.currency === 'PLN' ? 'success.main' : 'text.primary',
                                                                fontFamily: 'monospace',
                                                                fontSize: '1.1rem',
                                                                fontWeight: 'bold'
                                                            }}
                                                        >
                                                            {item.balance}
                                                        </Typography>
                                                    </TableCell>
                                                    <TableCell align="right" sx={{ pr: 3 }}>
                                                        <Chip
                                                            icon={item.currency === 'PLN' ? <CurrencyExchange sx={{ fontSize: 16 }} /> : undefined}
                                                            label={item.currency === 'PLN' ? 'Podsumowanie' : 'Wymaga przeliczenia'}
                                                            size="small"
                                                            color={getCurrencyColor(item.currency)}
                                                            variant={item.currency === 'PLN' ? 'filled' : 'outlined'}
                                                            sx={{ fontWeight: 'bold' }}
                                                        />
                                                    </TableCell>
                                                </TableRow>
                                            ))}
                                        </TableBody>
                                    </Table>
                                </TableContainer>
                            )}
                        </CardContent>
                    </Card>
                </Grid>

                {/* Prawa kolumna - Menu */}
                <Grid size={{ xs: 12, md: 4 }}>
                    <Box sx={{ display: 'flex', flexDirection: 'column', gap: 2 }}>
                        <Card
                            elevation={2}
                            sx={{
                                cursor: 'pointer',
                                transition: 'box-shadow 0.2s, transform 0.2s',
                                '&:hover': { boxShadow: 6, transform: 'translateY(-2px)' }
                            }}
                        >
                            <CardContent sx={{ p: 3, display: 'flex', alignItems: 'center' }}>
                                <AccountBalance sx={{ mr: 2, color: 'primary.main', fontSize: 28 }} />
                                <Box>
                                    <Typography variant="h6">Moje konto</Typography>
                                    <Typography variant="body2" color="text.secondary">
                                        Zarządzaj danymi konta
                                    </Typography>
                                </Box>
                            </CardContent>
                        </Card>

                        <Card
                            elevation={2}
                            sx={{
                                cursor: 'pointer',
                                transition: 'box-shadow 0.2s, transform 0.2s',
                                '&:hover': { boxShadow: 6, transform: 'translateY(-2px)' }
                            }}
                        >
                            <CardContent sx={{ p: 3, display: 'flex', alignItems: 'center' }}>
                                <Receipt sx={{ mr: 2, color: 'primary.main', fontSize: 28 }} />
                                <Box>
                                    <Typography variant="h6">Moje transakcje</Typography>
                                    <Typography variant="body2" color="text.secondary">
                                        Historia przelewów
                                    </Typography>
                                </Box>
                            </CardContent>
                        </Card>
                    </Box>
                </Grid>
            </Grid>
        </Container>
    );
};