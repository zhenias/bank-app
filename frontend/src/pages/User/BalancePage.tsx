import { useState, useEffect } from 'react';
import { useAuth } from '../../context/AuthContext';
import { fetchWithAuth } from '../../services/authService';
import {
    Container,
    Paper,
    Typography,
    Box,
    Grid,
    Card,
    CardContent,
    Alert,
    CircularProgress,
    Divider
} from '@mui/material';
import { AccountBalance, AccountBalanceWallet, Receipt, Settings } from '@mui/icons-material';

export const BalancePage = () => {
    const { user } = useAuth();
    const [balance, setBalance] = useState(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);

    useEffect(() => {
        fetchBalance();
    }, []);

    const fetchBalance = async () => {
        try {
            const response = await fetchWithAuth('/user/balance');
            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || 'Błąd pobierania salda');
            }

            setBalance(data.balance);
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
            <Box sx={{ mb: 4 }}>
                <Typography variant="h4" component="h1" gutterBottom>
                    Witaj, {user?.name || user?.email}!
                </Typography>
                <Typography variant="body1" color="text.secondary">
                    Zarządzaj swoim kontem bankowym
                </Typography>
            </Box>

            <Grid container spacing={3}>
                {/* Saldo */}
                <Grid>
                    <Card elevation={2} sx={{ height: '100%' }}>
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
                                        {balance !== null ? `${balance} PLN` : '---'}
                                    </Typography>
                                </Box>
                            )}
                        </CardContent>
                    </Card>
                </Grid>

                {/* Menu boczne */}
                <Grid>
                    <Box sx={{ display: 'flex', flexDirection: 'column', gap: 2 }}>
                        <Card elevation={2} sx={{ cursor: 'pointer', '&:hover': { boxShadow: 4 } }}>
                            <CardContent sx={{ p: 3, display: 'flex', alignItems: 'center' }}>
                                <AccountBalance sx={{ mr: 2, color: 'primary.main' }} />
                                <Box>
                                    <Typography variant="h6">Moje konto</Typography>
                                    <Typography variant="body2" color="text.secondary">
                                        Zarządzaj danymi konta
                                    </Typography>
                                </Box>
                            </CardContent>
                        </Card>

                        <Card elevation={2} sx={{ cursor: 'pointer', '&:hover': { boxShadow: 4 } }}>
                            <CardContent sx={{ p: 3, display: 'flex', alignItems: 'center' }}>
                                <Receipt sx={{ mr: 2, color: 'primary.main' }} />
                                <Box>
                                    <Typography variant="h6">Moje transakcje</Typography>
                                    <Typography variant="body2" color="text.secondary">
                                        Historia przelewów
                                    </Typography>
                                </Box>
                            </CardContent>
                        </Card>

                        <Card elevation={2} sx={{ cursor: 'pointer', '&:hover': { boxShadow: 4 } }}>
                            <CardContent sx={{ p: 3, display: 'flex', alignItems: 'center' }}>
                                <Settings sx={{ mr: 2, color: 'primary.main' }} />
                                <Box>
                                    <Typography variant="h6">Ustawienia</Typography>
                                    <Typography variant="body2" color="text.secondary">
                                        Konfiguracja konta
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