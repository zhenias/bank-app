import { CircularProgress, Box } from '@mui/material';
import {size} from "zod";

export const LoadingSpinner = ({ text = 'Ładowanie...' }) => {
    return (
        <Box sx={{ display: 'flex', alignItems: 'center', gap: 1 }}>
            <CircularProgress size={20} />{' '}
            <span style={{ fontSize: '0.875rem' }}>{text}</span>
        </Box>
    );
};