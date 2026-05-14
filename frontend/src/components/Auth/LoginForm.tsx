import { useState, useEffect } from 'react';
import { useToast } from '../../context/ToastContext';
import { useLoading } from '../../context/LoadingContext';
import { LoadingSpinner } from "../Assets/Svg/LoadingSpinner.jsx";
import {
    Paper,
    TextField,
    Button,
    Typography,
    Box,
    Link,
    Alert
} from '@mui/material';
import { Email, Lock, Login as LoginIcon } from '@mui/icons-material';
import type { User } from '../../types/types';

interface LoginFormProps {
    onLogin: (email: string, password: string) => Promise<User>;
    onSwitchToRegister: () => void;
}

export const LoginForm = ({ onLogin, onSwitchToRegister }: LoginFormProps) => {
    const [email, setEmail] = useState<string>('');
    const [password, setPassword] = useState<string>('');
    const [error, setError] = useState<string>('');
    const { showError, showSuccess } = useToast();
    const { isLoading, startLoading, stopLoading } = useLoading();

    useEffect(() => {
        setEmail('test@example.com');
        setPassword('password');
    }, []);

    const handleSubmit = async (e: any): Promise<void> => {
        e.preventDefault();

        setError('');
        startLoading();

        try {
            await onLogin(email, password);
            showSuccess('Zalogowano pomyślnie!');
        } catch (err) {
            const errorMessage = err instanceof Error ? err.message : 'Błąd logowania';
            setError(errorMessage);
        } finally {
            stopLoading();
        }
    };

    const canLogin = () => {
        return (
            email.length > 0 &&
            password.length > 0
        );
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
                        Logowanie
                    </Typography>
                    <Typography variant="body2" color="text.secondary">
                        Zaloguj się do swojego konta bankowego
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
                        label="Email"
                        type="email"
                        value={email}
                        onChange={(e) => setEmail(e.target.value)}
                        required
                        disabled={isLoading}
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
                        variant="outlined"
                    />

                    <Button
                        type="submit"
                        fullWidth
                        variant="contained"
                        size="large"
                        disabled={isLoading || !canLogin()}
                        startIcon={isLoading ? <LoadingSpinner /> : <LoginIcon />}
                        sx={{ mt: 2, py: 1.5 }}
                    >
                        {isLoading ? '' : 'Zaloguj się'}
                    </Button>
                </Box>

                <Box sx={{ textAlign: 'center', mt: 2 }}>
                    <Typography variant="body2">
                        Nie masz konta?{' '}
                        <Link
                            component="button"
                            variant="body2"
                            onClick={onSwitchToRegister}
                            sx={{ cursor: 'pointer' }}
                        >
                            Zarejestruj się
                        </Link>
                    </Typography>
                </Box>
            </Paper>
        </Box>
    );
};