import { useLoading } from '../../context/LoadingContext';
import { Backdrop, CircularProgress, Typography, Paper } from '@mui/material';

export const LoadingOverlay = () => {
    const { isLoading } = useLoading();

    if (!isLoading) return null;

    return (
        <Backdrop
            sx={{ color: '#fff', zIndex: (theme) => theme.zIndex.drawer + 1 }}
            open={isLoading}
        >
            <Paper
                elevation={6}
                sx={{
                    p: 4,
                    display: 'flex',
                    flexDirection: 'column',
                    alignItems: 'center',
                    gap: 2,
                    minWidth: 200
                }}
            >
                <CircularProgress size={48} sx={{ color: 'primary.main' }} />
                <Typography variant="h6" sx={{ color: 'text.primary' }}>
                    Ładowanie...
                </Typography>
            </Paper>
        </Backdrop>
    );
};
