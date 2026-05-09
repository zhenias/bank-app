import { useState, useEffect } from 'react';
import { useAuth } from '../../context/AuthContext';
import {
    Container,
    Paper,
    Typography,
    Box,
    Grid,
    Alert,
    CircularProgress,
    Divider, TextField, Button
} from '@mui/material';
import {LoadingSpinner} from "../../components/Assets/Svg/LoadingSpinner";
import {useToast} from "../../context/ToastContext";
import {useLoading} from "../../context/LoadingContext";
import {format_date} from "../../hooks/formatedDate";

export const ProfilePage = () => {
    const { user } = useAuth();
    const [error, setError] = useState('');
    const [email, setEmail] = useState('');
    const [name, setName] = useState('');
    const { showError, showSuccess } = useToast();
    const { isLoading, startLoading, stopLoading } = useLoading();

    // useEffect(() => {
    //     setEmail('test@example.com');
    //     setName('John Doe');
    //     setPassword('password');
    // }, []);

    const handleSubmit = async (e: React.FormEvent<HTMLFormElement>) => {
        e.preventDefault();
        setError('');
        startLoading();

        try {
            // await onLogin(email, password);
            showSuccess('Zalogowano pomyślnie!');
        } catch (err: any) {
            const errorMessage = err.message || 'Błąd logowania';
            setError(errorMessage);
            showError(errorMessage);
        } finally {
            stopLoading();
        }
    };

    return (
        <Container maxWidth="lg" sx={{ py: 4 }}>
            <Box sx={{ mb: 4 }}>
                <Typography variant="body1" color="text.secondary">
                    Zmiana danych osobowych i ustawień konta.
                </Typography>
            </Box>

            <Grid container spacing={3}>
                {error && (
                    <Alert severity="error" sx={{ mb: 2 }}>
                        {error}
                    </Alert>
                )}

                <Box component="form" onSubmit={handleSubmit} sx={{ display: 'flex', flexDirection: 'column', gap: 2, width: '50%', mx: 'auto' }}>
                    <TextField
                        fullWidth
                        label="Imię i nazwisko"
                        type="text"
                        value={user?.name}
                        onChange={(e) => setName(e.target.value)}
                        required
                        disabled={isLoading}
                        variant="outlined"
                    />

                    <TextField
                        fullWidth
                        label="Email"
                        type="email"
                        value={user?.email}
                        onChange={(e) => setEmail(e.target.value)}
                        required
                        disabled={isLoading}
                        variant="outlined"
                    />

                    <TextField
                        fullWidth
                        label="Data ostatniej zmiany"
                        value={user?.updated_at ? format_date(user.updated_at) : 'Brak danych'}
                        disabled
                        variant="outlined"
                        slotProps={{
                            input: {
                                readOnly: true,
                            }
                        }}
                    />

                    <Button
                        type="submit"
                        fullWidth
                        variant="contained"
                        size="large"
                        disabled={isLoading}
                        startIcon={isLoading ? <LoadingSpinner /> : ''}
                        sx={{ mt: 2, py: 1.5 }}
                    >
                        {isLoading ? ' ' : 'Zapisz dane'}
                    </Button>
                </Box>

            </Grid>
        </Container>
    );
};