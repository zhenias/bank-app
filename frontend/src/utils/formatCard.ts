/**
 * Format card number with spaces every 4 digits.
 * "4532123456789012" → "4532 1234 5678 9012"
 */
export const formatCardNumber = (number: string): string => {
    const cleaned = number.replace(/\s/g, '');
    return cleaned.replace(/(.{4})/g, '$1 ').trim();
};

/**
 * Mask card number - show first 4 and last 4 digits.
 * "4532123456789012" → "4532 **** **** 9012"
 */
export const maskCardNumber = (number: string): string => {
    const cleaned = number.replace(/\s/g, '');
    const first = cleaned.slice(0, 4);
    const last = cleaned.slice(-4);
    return `${first} **** **** ${last}`;
};

/**
 * Get last 4 digits of card number.
 * "4532123456789012" → "9012"
 */
export const lastFour = (number: string): string => {
    return number.replace(/\s/g, '').slice(-4);
};

/**
 * Format expiry date.
 * { exp_month: 5, exp_year: 2030 } → "05/2030"
 * { exp_month: 12, exp_year: 2024 } → "12/2024"
 */
export const formatExpiry = (month: number, year: number): string => {
    return `${String(month).padStart(2, '0')}/${year}`;
};

/**
 * Check if card is expired.
 */
export const isCardExpired = (month: number, year: number): boolean => {
    const now = new Date();
    const expiry = new Date(year, month, 0); // last day of the month
    return now > expiry;
};

/**
 * Detect card network from number prefix.
 * "4532..." → "Visa"
 * "5214..." → "Mastercard"
 */
export const detectNetwork = (number: string): string => {
    const cleaned = number.replace(/\s/g, '');
    if (cleaned.startsWith('4')) return 'Visa';
    if (/^5[1-5]/.test(cleaned)) return 'Mastercard';
    return 'Unknown';
};

/**
 * Get network color for UI.
 */
export const getNetworkColor = (network: string): string => {
    switch (network.toLowerCase()) {
        case 'visa': return '#1a1f71';
        case 'mastercard': return '#eb001b';
        default: return '#666';
    }
};

export const getStatusCard = (status: string) => {
    switch (status.toLowerCase()) {
        case 'active':
            return 'Aktywna';
        case 'blocked':
            return 'Zablokowana';
        case 'inactive':
            return 'Nieaktywna';
        case 'expired':
            return 'Wygasła';
        default:
            return status;
    }
}