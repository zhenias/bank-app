import { useState, useEffect } from 'react';
import { useAuth } from '../../context/AuthContext';
import {
    Container,
    Typography,
    Box,
    Grid,
    Alert,
    Divider, TextField, Button
} from '@mui/material';
import EditIcon from '@mui/icons-material/Edit';
import {LoadingSpinner} from "../../components/Assets/Svg/LoadingSpinner";
import {useToast} from "../../context/ToastContext";
import {useLoading} from "../../context/LoadingContext";
import {formatDate, formatDateShort} from "../../hooks/formatedDate";
import {updateUserProfile} from "../../services/authService";
import {ProfileUpdateData} from "../../types/types";

export const ProfilePage = () => {
    const { user } = useAuth();
    const [error, setError] = useState('');
    const [email, setEmail] = useState('');
    const [name, setName] = useState('');
    const [password, setPassword] = useState('');
    const [confirmPassword, setConfirmPassword] = useState('');
    const [oldPassword, setOldPassword] = useState('');
    const { showError, showSuccess } = useToast();
    const { isLoading, startLoading, stopLoading } = useLoading();

    useEffect(() => {
        if (user) {
            setName(user.name || '');
            setEmail(user.email || '');
        }
    }, [user]);

    const handleSubmit = async (e: React.FormEvent<HTMLFormElement>) => {
        e.preventDefault();
        setError('');
        startLoading();

        const payload: ProfileUpdateData = {
            name,
            email,
            password: password || undefined,
            password_confirmation: confirmPassword || undefined,
            old_password: oldPassword || undefined,
        };

        try {
            await updateUserProfile(payload);

            setPassword('');
            setConfirmPassword('');
            setOldPassword('');

            showSuccess('Profil zaktualizowany pomyślnie!');
        } catch (err: any) {
            const errorMessage = err.message || 'Błąd aktualizacji profilu';
            setError(errorMessage);
            showError(errorMessage);
        } finally {
            stopLoading();
        }
    };

    const hasChanges = () => {
        return (
            (name !== user?.name) ||
            (email !== user?.email) ||
            password.length > 0
        );
    };

    return (
        <Container maxWidth="lg" sx={{ py: 4 }}>
            <Box sx={{ mb: 4 }}>
                <Typography variant="subtitle2" color="text.secondary">
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
                        label="Data ostatniej zmiany"
                        value={user?.updated_at ? formatDate(user.updated_at) : 'Brak danych'}
                        disabled
                        variant="outlined"
                        slotProps={{
                            input: {
                                readOnly: true,
                            }
                        }}
                    />

                    <Divider sx={{ my: 2 }} />
                    <Typography variant="subtitle2" color="text.secondary">
                        Zmiana hasła (wypełnij tylko jeśli chcesz zmienić)
                    </Typography>

                    <TextField
                        fullWidth
                        label="Stare hasło"
                        type="password"
                        value={oldPassword}
                        onChange={(e) => setOldPassword(e.target.value)}
                        disabled={isLoading}
                        variant="outlined"
                    />

                    <TextField
                        fullWidth
                        label="Nowe hasło"
                        type="password"
                        value={password}
                        onChange={(e) => setPassword(e.target.value)}
                        disabled={isLoading}
                        variant="outlined"
                    />

                    <TextField
                        fullWidth
                        label="Potwierdź nowe hasło"
                        type="password"
                        value={confirmPassword}
                        onChange={(e) => setConfirmPassword(e.target.value)}
                        disabled={isLoading || !password}
                        variant="outlined"
                        error={confirmPassword.length > 0 && password !== confirmPassword}
                        helperText={confirmPassword.length > 0 && password !== confirmPassword ? 'Hasła nie są zgodne' : ''}
                    />

                    <Button
                        type="submit"
                        fullWidth
                        variant="contained"
                        size="large"
                        disabled={isLoading || !hasChanges()}
                        startIcon={isLoading ? <LoadingSpinner text="Zapisywanie..." /> : <EditIcon />}
                        sx={{ mt: 2, py: 1.5 }}
                    >
                        {isLoading ? '' : 'Zapisz dane'}
                    </Button>
                </Box>

            </Grid>
        </Container>
    );
};