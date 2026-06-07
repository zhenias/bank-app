import { fetchWithAuth } from './authService';
import { ApiError } from '../types/types';

export const approveGuardian = async (wardId: string): Promise<void> => {
    const response = await fetchWithAuth(`user/guardian/approve/${wardId}`, {
        method: 'POST',
    });

    if (!response.ok) {
        const error: ApiError = await response.json();
        throw new Error(error.message || 'Błąd zatwierdzania opieki.');
    }
};

export const rejectGuardian = async (wardId: string): Promise<void> => {
    const response = await fetchWithAuth(`user/guardian/reject/${wardId}`, {
        method: 'POST',
    });

    if (!response.ok) {
        const error: ApiError = await response.json();
        throw new Error(error.message || 'Błąd odrzucania opieki.');
    }
};