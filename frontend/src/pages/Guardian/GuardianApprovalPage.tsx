import { useState } from 'react';
import { useParams, useNavigate, useLocation } from 'react-router-dom';
import {
    Container,
    Typography,
    Box,
    Card,
    CardContent,
    Button,
    Alert,
} from '@mui/material';
import {
    CheckCircle as ApproveIcon,
    Cancel as RejectIcon,
} from '@mui/icons-material';
import { useToast } from '../../context/ToastContext';
import { fetchWithAuth } from '../../services/authService';
import {ApiError} from "../../types/types";

interface WardData {
    id: string;
    name: string;
    email: string;
    date_of_birth?: string;
    age?: number;
}

export const GuardianApprovalPage = () => {
    const { wardId } = useParams<{ wardId: string }>();
    const navigate = useNavigate();
    const location = useLocation();
    const { showSuccess, showError } = useToast();

    const ward = location.state?.ward as WardData | undefined;

    const [processing, setProcessing] = useState(false);

    if (!ward) {
        return (
            <Container maxWidth="sm" sx={{ py: 8 }}>
                <Alert severity="error">
                    Brak danych użytkownika. Otwórz tę stronę z powiadomienia.
                </Alert>
                <Button onClick={() => navigate('/dashboard')} sx={{ mt: 2 }}>
                    Wróć do dashboardu
                </Button>
            </Container>
        );
    }

    const handleApprove = async () => {
        setProcessing(true);
        try {
            const response = await fetchWithAuth(`/guardian/approve/${wardId}`, {
                method: 'POST',
            });

            showSuccess('Opieka została potwierdzona!');
            navigate('/dashboard');
        } catch (err: any) {
            showError(err?.errors?.guardian[0] || err?.message || 'Błąd zatwierdzania opieki');
        } finally {
            setProcessing(false);
        }
    };

    const handleReject = async () => {
        setProcessing(true);
        try {
            const response = await fetchWithAuth(`/guardian/reject/${wardId}`, {
                method: 'POST',
            });

            showSuccess('Prośba o opiekę została odrzucona.');
            navigate('/dashboard');
        } catch (err: any) {
            showError(err.message || 'Błąd odrzucania opieki');
        } finally {
            setProcessing(false);
        }
    };

    return (
        <Container maxWidth="sm" sx={{ py: 8 }}>
            <Card>
                <CardContent sx={{ textAlign: 'center', py: 4 }}>
                    <Typography variant="h5" gutterBottom>
                        Prośba o opiekę
                    </Typography>

                    <Alert severity="info" sx={{ mb: 3, textAlign: 'left' }}>
                        <strong>{ward.name}</strong> ({ward.email}) prosi Cię o potwierdzenie opieki prawnej nad swoim kontem bankowym.
                    </Alert>

                    {ward.date_of_birth && (
                        <Box sx={{ mb: 3 }}>
                            <Typography variant="body2" color="text.secondary">
                                Data urodzenia: {ward.date_of_birth}
                            </Typography>
                            {ward.age !== undefined && (
                                <Typography variant="body2" color="text.secondary">
                                    Wiek: {ward.age} lat
                                </Typography>
                            )}
                        </Box>
                    )}

                    <Typography variant="body2" color="warning.main" sx={{ mb: 3 }}>
                        ⚠️ Potwierdzając, bierzesz odpowiedzialność za operacje tego użytkownika.
                    </Typography>

                    <Box sx={{ display: 'flex', gap: 2, justifyContent: 'center' }}>
                        <Button
                            variant="contained"
                            color="success"
                            size="large"
                            startIcon={<ApproveIcon />}
                            onClick={handleApprove}
                            disabled={processing}
                        >
                            {processing ? 'Przetwarzanie...' : 'Potwierdź opiekę'}
                        </Button>

                        <Button
                            variant="outlined"
                            color="error"
                            size="large"
                            startIcon={<RejectIcon />}
                            onClick={handleReject}
                            disabled={processing}
                        >
                            Odrzuć
                        </Button>
                    </Box>
                </CardContent>
            </Card>
        </Container>
    );
};