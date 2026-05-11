/**
 * Format IBAN/NRB account number with spaces every 4 digits.
 * "98123456780000000012345678" → "98 1234 5678 0000 0000 1234 5678"
 */
export const formatAccountNumber = (number: string): string => {
    const cleaned = number.replace(/\s/g, '');
    return cleaned.replace(/(.{4})/g, '$1 ').trim();
};

/**
 * Mask account number - show only first 2 and last 4 digits.
 * "98123456780000000012345678" → "98 **** **** **** **** 5678"
 */
export const maskAccountNumber = (number: string): string => {
    const cleaned = number.replace(/\s/g, '');
    const first = cleaned.slice(0, 2);
    const last = cleaned.slice(-4);
    return `${first} **** **** **** **** ${last}`;
};

/**
 * Get last 4 digits of account number.
 * "98123456780000000012345678" → "5678"
 */
export const lastFourAccount = (number: string): string => {
    return number.replace(/\s/g, '').slice(-4);
};

/**
 * Convert 26-digit NRB to IBAN (add "PL" prefix).
 * "98123456780000000012345678" → "PL98123456780000000012345678"
 */
export const toIBAN = (number: string): string => {
    return 'PL' + number.replace(/\s/g, '');
};

/**
 * Check if account number has valid length (26 digits).
 */
export const isValidAccountNumber = (number: string): boolean => {
    const cleaned = number.replace(/\s/g, '');
    return /^\d{26}$/.test(cleaned);
};