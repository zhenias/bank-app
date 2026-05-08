import { CircularProgress, Box } from '@mui/material';

interface LoadingSpinnerProps {
    text?: string;
}

export const LoadingSpinner = ({ text = 'Ładowanie...' }: LoadingSpinnerProps) => {
    return (
        <Box sx={{ display: 'flex', alignItems: 'center', gap: 1 }}>
            <CircularProgress size={20} aria-label="Loading…" />
            <span>{text}</span>
        </Box>
    );
};
