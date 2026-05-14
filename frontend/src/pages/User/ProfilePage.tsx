import React, { useState, useEffect } from 'react';
import { useAuth } from '../../context/AuthContext';
import {
    Container,
    Typography,
    Box,
    Alert,
    Divider,
    TextField,
    Button,
    Card,
    InputLabel,
    Stack,
    Paper,
} from '@mui/material';
import EditIcon from '@mui/icons-material/Edit';
import { LoadingSpinner } from "../../components/Assets/Svg/LoadingSpinner";
import { useToast } from "../../context/ToastContext";
import { useLoading } from "../../context/LoadingContext";
import { calculateAge, formatDateInput, formatDateTime, isAdult, today } from "../../utils/formatDate";
import { updateUserProfile } from "../../services/authService";
import { ProfileUpdateData } from "../../types/types";
import { DateField } from "../../components/Field/DateField";

export const ProfilePage = () => {
    const { user, loadUser } = useAuth();
    const [error, setError] = useState('');
    const [email, setEmail] = useState('');
    const [name, setName] = useState('');
    const [password, setPassword] = useState('');
    const [dateOfBirth, setDateOfBirth] = useState('');
    const [guardianEmail, setGuardianEmail] = useState('');
    const [confirmPassword, setConfirmPassword] = useState('');
    const [oldPassword, setOldPassword] = useState('');
    const { showError, showSuccess } = useToast();
    const { isLoading, startLoading, stopLoading } = useLoading();

    useEffect(() => {
        if (user) {
            setName(user.name || '');
            setEmail(user.email || '');
            setDateOfBirth(user.date_of_birth || '');
        }
    }, [user]);

    const handleRemoveGuardian = async () => {
        setError('');
        startLoading();

        const payload: ProfileUpdateData = {
            name: user?.name || '',
            email: user?.email || '',
            guardian_delete: true,
        };

        try {
            await updateUserProfile(payload);
            showSuccess('Opiekun został odłączony.');
            loadUser();
        } catch (err: any) {
            const errorMessage = err.message || 'Błąd odłączania opiekuna';
            setError(errorMessage);
            showError(errorMessage);
        } finally {
            stopLoading();
        }
    };

    const handleSetGuardian = async () => {
        setError('');
        startLoading();

        if (user?.guardian?.id) {
            showError('Twoje konto zostało przypisane do opiekuna, nie możesz jeszcze raz wysyłać informacji do opiekuna.');
            return;
        }

        const payload: ProfileUpdateData = {
            guardian_email: guardianEmail || undefined,
        };

        try {
            await updateUserProfile(payload);

            setGuardianEmail('');

            showSuccess('Powiadomienie zostało wysłane do opiekuna w celu potwierdzenia.');
            loadUser();
        } catch (err: any) {
            const errorMessage = err.errors.guardian_email[0] || err.message || 'Błąd odłączania opiekuna';
            setError(errorMessage);
            showError(errorMessage);
        } finally {
            stopLoading();
        }
    };

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
            date_of_birth: !user?.date_of_birth && dateOfBirth ? dateOfBirth : undefined,
        };

        try {
            await updateUserProfile(payload);

            setPassword('');
            setConfirmPassword('');
            setOldPassword('');

            showSuccess('Profil zaktualizowany pomyślnie!');
            loadUser();
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
            (dateOfBirth !== user?.date_of_birth) ||
            (guardianEmail !== user?.guardian?.email) ||
            password.length > 0 ||
            confirmPassword.length > 0 ||
            oldPassword.length > 0
        );
    };

    const age = calculateAge(dateOfBirth);
    const adult = isAdult(dateOfBirth);
    const hasGuardian = Boolean(user?.guardian?.id);

    return (
        <Container maxWidth="md" sx={{ py: 4 }}>
            <Box sx={{ mb: 3 }}>
                <Typography variant="h5" gutterBottom>
                    Profil użytkownika
                </Typography>
                <Typography variant="body2" color="text.secondary">
                    Zmiana danych osobowych i ustawień konta.
                </Typography>
            </Box>

            <Paper elevation={1} sx={{ p: { xs: 2, sm: 3 } }}>
                <Box component="form" onSubmit={handleSubmit}>
                    {!user?.email_verified_at && (
                        <Alert severity="warning" sx={{ mb: 3 }}>
                            Twój adres email nie został zweryfikowany. Sprawdź skrzynkę odbiorczą.
                        </Alert>
                    )}

                    {!user?.date_of_birth && (
                        <Alert severity="error" sx={{ mb: 3 }}>
                            Nie wpisano daty urodzenia. Bez tego nie można kontynuować działania w systemie.
                            Jeżeli nie masz 18 lat, poproś opiekuna o założenie konta i podpięcie Cię do swojego konta.
                        </Alert>
                    )}

                    {error && (
                        <Alert severity="error" sx={{ mb: 3 }}>
                            {error}
                        </Alert>
                    )}

                    <Stack spacing={2.5}>
                        <Typography variant="subtitle1" sx={{ fontWeight: 600 }}>
                            Dane osobowe
                        </Typography>

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

                        <DateField
                            value={dateOfBirth}
                            onChange={(value: string) => setDateOfBirth(value)}
                            formatDate={formatDateInput}
                            label="Data urodzenia"
                            maxDate={today()}
                            required
                            disabled={!!user?.date_of_birth}
                        />

                        {age !== null && !adult && (
                            <Typography
                                variant="body2"
                                sx={{ color: adult ? 'success.main' : 'warning.main', textAlign: 'left' }}
                            >
                                {adult
                                    ? ''
                                    : `Wiek: ${age} lat (niepełnoletni)`
                                }
                            </Typography>
                        )}

                        {dateOfBirth && (
                            <Box>
                                {adult && hasGuardian && (
                                    <>
                                        <Typography variant="subtitle1" sx={{ mb: 1.5, fontWeight: 600 }}>
                                            Opiekun prawny
                                        </Typography>

                                        <Card variant="outlined" sx={{ p: 2 }}>
                                            <Stack spacing={0.5}>
                                                <Typography variant="body1">
                                                    {user?.guardian?.name}
                                                </Typography>
                                                <Typography variant="body2" color="text.secondary">
                                                    {user?.guardian?.email}
                                                </Typography>
                                                <Typography variant="body2" color="text.secondary">
                                                    Zatwierdzono: {formatDateTime(user?.guardian?.guardian_approved_at)}
                                                </Typography>
                                            </Stack>
                                            <Button
                                                size="small"
                                                color="error"
                                                variant="outlined"
                                                sx={{ mt: 1.5 }}
                                                onClick={handleRemoveGuardian}
                                            >
                                                Odłącz opiekuna
                                            </Button>
                                        </Card>
                                    </>
                                )}

                                {!adult && (
                                    <>
                                        <Typography variant="subtitle1" sx={{ mb: 1.5, fontWeight: 600 }}>
                                            Opiekun prawny
                                        </Typography>

                                        {!hasGuardian && (
                                            <Alert severity="warning" sx={{ mb: 2 }}>
                                                Masz mniej niż 18 lat. Aby korzystać z konta, musisz mieć przypisanego opiekuna prawnego.
                                            </Alert>
                                        )}

                                        {hasGuardian ? (
                                            <Card variant="outlined" sx={{ p: 2 }}>
                                                <Stack spacing={0.5}>
                                                    <Typography variant="body1">
                                                        {user?.guardian?.name}
                                                    </Typography>
                                                    <Typography variant="body2" color="text.secondary">
                                                        {user?.guardian?.email}
                                                    </Typography>
                                                    <Typography variant="body2" color="text.secondary">
                                                        Zatwierdzono: {formatDateTime(user?.guardian?.guardian_approved_at)}
                                                    </Typography>
                                                </Stack>
                                                {!user?.guardian?.guardian_approved_at && (
                                                    <Alert severity="info" sx={{ mt: 1.5 }}>
                                                        Oczekuje na zatwierdzenie przez opiekuna.
                                                    </Alert>
                                                )}
                                            </Card>
                                        ) : (
                                            <>
                                                <Typography variant="body2" color="text.secondary" sx={{ mb: 1 }}>
                                                    Wpisz email opiekuna, który otrzyma zaproszenie:
                                                </Typography>
                                                <TextField
                                                    fullWidth
                                                    placeholder="Email opiekuna"
                                                    value={guardianEmail}
                                                    onChange={(e) => setGuardianEmail(e.target.value)}
                                                    helperText="Opiekun musi mieć ukończone 18 lat i posiadać konto w banku."
                                                />

                                                <Button
                                                    type="submit"
                                                    fullWidth
                                                    variant="contained"
                                                    size="large"
                                                    disabled={isLoading || guardianEmail.length <= 0}
                                                    onClick={handleSetGuardian}
                                                    startIcon={isLoading ? <LoadingSpinner text="Wysyłam potwierdzenie..." /> : <EditIcon />}
                                                    sx={{ mt: 1, py: 1.5 }}
                                                >
                                                    {isLoading ? '' : 'Wyślij potwierdzenie'}
                                                </Button>
                                            </>
                                        )}
                                    </>
                                )}
                            </Box>
                        )}

                        <Divider sx={{ my: 1 }} />

                        <Typography variant="subtitle1" sx={{ fontWeight: 600 }}>
                            Informacje o koncie
                        </Typography>

                        <Box sx={{ textAlign: 'left' }}>
                            <InputLabel sx={{ mb: 0.5, color: 'text.secondary', fontSize: '0.75rem' }}>
                                Data ostatniej zmiany
                            </InputLabel>
                            <Typography variant="body1">
                                {user?.updated_at ? formatDateTime(user.updated_at) : 'Brak danych'}
                            </Typography>
                        </Box>

                        <Box sx={{ textAlign: 'left' }}>
                            <InputLabel sx={{ mb: 0.5, color: 'text.secondary', fontSize: '0.75rem' }}>
                                Data potwierdzenia e-mail
                            </InputLabel>
                            <Typography variant="body1">
                                {user?.email_verified_at ? formatDateTime(user.email_verified_at) : 'Brak danych'}
                            </Typography>
                        </Box>

                        <Divider sx={{ my: 1 }} />

                        <Typography variant="subtitle1" sx={{ fontWeight: 600 }}>
                            Zmiana hasła
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
                            sx={{ mt: 1, py: 1.5 }}
                        >
                            {isLoading ? '' : 'Zapisz dane'}
                        </Button>
                    </Stack>
                </Box>
            </Paper>
        </Container>
    );
};