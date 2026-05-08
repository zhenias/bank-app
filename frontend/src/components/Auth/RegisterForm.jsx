import { useState } from 'react';
import { useToast } from '../../context/ToastContext';
import { useLoading } from '../../context/LoadingContext';
import {
    Paper,
    TextField,
    Button,
    Typography,
    Box,
    Link,
    Alert
} from '@mui/material';
import { Person, Email, Lock, PersonAdd } from '@mui/icons-material';
import {LoadingSpinner} from "../Assets/Svg/LoadingSpinner.jsx";

export const RegisterForm = ({ onRegister, onSwitchToLogin }) => {
    const [name, setName] = useState('');
    const [email, setEmail] = useState('');
    const [password, setPassword] = useState('');
    const [passwordConfirmation, setPasswordConfirmation] = useState('');
    const [error, setError] = useState('');
    const { showError, showSuccess } = useToast();
    const { isLoading, startLoading, stopLoading } = useLoading();

    const handleSubmit = async (e) => {
        e.preventDefault();
        setError('');

        if (password !== passwordConfirmation) {
            const errorMessage = 'Hasła nie są takie same';
            setError(errorMessage);
            // showError(errorMessage);
            return;
        }

        startLoading();

        try {
            await onRegister(name, email, password);
            showSuccess('Rejestracja zakończona sukcesem! Możesz teraz się zalogować. Przekierowanie..');
        } catch (err) {
            const errorMessage = err.message || 'Błąd rejestracji, spróbuj później.';
            setError(errorMessage);
            // showError(errorMessage);
        } finally {
            stopLoading();
        }
    };

    return (
        <Box
            sx={{
                display: 'flex',
                justifyContent: 'center',
                alignItems: 'center',
                minHeight: '100vh',
                p: 2
            }}
        >
            <Paper
                elevation={3}
                sx={{
                    p: 4,
                    maxWidth: 400,
                    width: '100%',
                    display: 'flex',
                    flexDirection: 'column',
                    gap: 3
                }}
            >
                <Box sx={{ textAlign: 'center' }}>
                    <Typography variant="h4" component="h1" gutterBottom>
                        Rejestracja
                    </Typography>
                    <Typography variant="body2" color="text.secondary">
                        Utwórz nowe konto bankowe
                    </Typography>
                </Box>

                {error && (
                    <Alert severity="error" sx={{ mb: 2 }}>
                        {error}
                    </Alert>
                )}

                <Box component="form" onSubmit={handleSubmit} sx={{ display: 'flex', flexDirection: 'column', gap: 2 }}>
                    <TextField
                        fullWidth
                        label="Imię i nazwisko"
                        value={name}
                        onChange={(e) => setName(e.target.value)}
                        required
                        disabled={isLoading}
                        InputProps={{
                            startAdornment: <Person sx={{ mr: 1, color: 'action.active' }} />,
                        }}
                        variant="outlined"
                    />

                    <TextField
                        fullWidth
                        label="Email"
                        type="email"
                        value={email}
                        onChange={(e) => setEmail(e.target.value)}
                        required
                        disabled={isLoading}
                        InputProps={{
                            startAdornment: <Email sx={{ mr: 1, color: 'action.active' }} />,
                        }}
                        variant="outlined"
                    />

                    <TextField
                        fullWidth
                        label="Hasło"
                        type="password"
                        value={password}
                        onChange={(e) => setPassword(e.target.value)}
                        required
                        disabled={isLoading}
                        InputProps={{
                            startAdornment: <Lock sx={{ mr: 1, color: 'action.active' }} />,
                        }}
                        variant="outlined"
                    />

                    <TextField
                        fullWidth
                        label="Potwierdź hasło"
                        type="password"
                        value={passwordConfirmation}
                        onChange={(e) => setPasswordConfirmation(e.target.value)}
                        required
                        disabled={isLoading}
                        InputProps={{
                            startAdornment: <Lock sx={{ mr: 1, color: 'action.active' }} />,
                        }}
                        variant="outlined"
                    />

                    <Button
                        type="submit"
                        fullWidth
                        variant="contained"
                        size="large"
                        disabled={isLoading}
                        startIcon={isLoading ? <LoadingSpinner text="Rejestracja..." /> : <PersonAdd />}
                        sx={{ mt: 2, py: 1.5 }}
                    >
                        {isLoading ? ' ' : 'Zarejestruj się'}
                    </Button>
                </Box>

                <Box sx={{ textAlign: 'center', mt: 2 }}>
                    <Typography variant="body2">
                        Masz już konto?{' '}
                        <Link
                            component="button"
                            variant="body2"
                            onClick={onSwitchToLogin}
                            sx={{ cursor: 'pointer' }}
                        >
                            Zaloguj się
                        </Link>
                    </Typography>
                </Box>
            </Paper>
        </Box>
    );
};