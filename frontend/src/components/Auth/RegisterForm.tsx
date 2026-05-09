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
import type { User } from '../../types/types';

interface RegisterFormProps {
    onRegister: (name: string, email: string, password: string) => Promise<User>;
    onSwitchToLogin: () => void;
}

export const RegisterForm = ({ onRegister, onSwitchToLogin }: RegisterFormProps) => {
    const [name, setName] = useState<string>('');
    const [email, setEmail] = useState<string>('');
    const [password, setPassword] = useState<string>('');
    const [passwordConfirmation, setPasswordConfirmation] = useState<string>('');
    const [error, setError] = useState<string>('');
    const { showError, showSuccess } = useToast();
    const { isLoading, startLoading, stopLoading } = useLoading();

    const handleSubmit = async (e: React.FormEvent<HTMLFormElement>) => {
        e.preventDefault();
        setError('');

        if (password !== passwordConfirmation) {
            const errorMessage = 'Hasła nie są takie same';
            setError(errorMessage);
            showError(errorMessage);
            return;
        }

        startLoading();

        try {
            await onRegister(name, email, password);
            showSuccess('Rejestracja pomyślna!');
        } catch (err) {
            const errorMessage = err instanceof Error ? err.message : 'Błąd rejestracji';
            setError(errorMessage);
            showError(errorMessage);
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

                    <TextField
                        fullWidth
                        label="Potwierdź hasło"
                        type="password"
                        value={passwordConfirmation}
                        onChange={(e) => setPasswordConfirmation(e.target.value)}
                        required
                        disabled={isLoading}
                        variant="outlined"
                    />

                    <Button
                        type="submit"
                        fullWidth
                        variant="contained"
                        size="large"
                        disabled={isLoading}
                        startIcon={isLoading ? null : <PersonAdd />}
                        sx={{ mt: 2, py: 1.5 }}
                    >
                        {isLoading ? 'Rejestracja...' : 'Zarejestruj się'}
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