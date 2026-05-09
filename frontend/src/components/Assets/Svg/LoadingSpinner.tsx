import { CircularProgress, Box } from '@mui/material';

interface LoadingSpinnerProps {
    text?: string;
}

export const LoadingSpinner = ({ text = 'Ładowanie...' }: LoadingSpinnerProps) => {
    return (
        <Box sx={{ display: 'flex', alignItems: 'center', gap: 1 }}>
            <CircularProgress size={14} />
            <span style={{ fontSize: '0.875rem' }}>{text}</span>
        </Box>
    );
};
