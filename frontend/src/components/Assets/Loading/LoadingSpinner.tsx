import { CircularProgress, Box } from '@mui/material';

export const LoadingSpinner = ({ text = 'Ładowanie...' }) => {
    return (
        <Box sx={{ display: 'flex', alignItems: 'center', gap: 1 }}>
            <CircularProgress size={20} />{' '}
            <span style={{ fontSize: '0.875rem' }}>{text}</span>
        </Box>
    );
};