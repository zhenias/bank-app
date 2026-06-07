/**
 * Convert cents to zloty string.
 * 499 → "4.99"
 * 0 → "0.00"
 */
export const centsToZloty = (cents: number): string => {
    return (cents / 100).toFixed(2);
};

/**
 * Convert zloty to cents.
 * 4.99 → 499
 * 0.01 → 1
 */
export const zlotyToCents = (zloty: number): number => {
    return Math.round(zloty * 100);
};

/**
 * Format amount in PLN with currency symbol.
 * 499 → "4,99 PLN"
 * 250000 → "2 500,00 PLN"
 */
export const formatMoney = (cents?: string | number, currency: string = 'PLN'): string => {
    const centsValue = typeof cents === 'string' ? parseFloat(cents) : cents !== undefined ? cents : 0;

    return new Intl.NumberFormat('pl-PL', {
        style: 'currency',
        currency: currency,
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(centsValue / 100);
};

/**
 * Format amount without currency symbol.
 * 499 → "4,99"
 * 250000 → "2 500,00"
 */
export const formatAmount = (cents: number): string => {
    return new Intl.NumberFormat('pl-PL', {
        style: 'decimal',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(cents / 100);
};

/**
 * Format amount with space separator, no currency.
 * 499 → "4.99" (API format, kropka zamiast przecinka)
 */
export const formatAmountForApi = (cents: number): string => {
    return (cents / 100).toFixed(2);
};

/**
 * Parse formatted money string to cents.
 * "4,99 PLN" → 499
 * "2 500,00" → 250000
 */
export const parseMoneyToCents = (formatted: string): number => {
    const cleaned = formatted
        .replace(/[^\d,.-]/g, '')
        .replace(',', '.');
    return Math.round(parseFloat(cleaned) * 100);
};

/**
 * Format balance with color indicator.
 * +499 → "+4,99" (green)
 * -499 → "-4,99" (red)
 * 0 → "0,00"
 */
export const formatBalance = (cents?: string): { text: string; isPositive: boolean; isZero: boolean } => {
    const amount = cents !== undefined ? parseFloat(cents) / 100 : 0;
    return {
        text: new Intl.NumberFormat('pl-PL', {
            style: 'decimal',
            signDisplay: 'always',
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        }).format(amount),
        isPositive: cents !== undefined && parseFloat(cents) > 0,
        isZero: cents === '0' || cents === '0.00',
    };
};

/**
 * Short format for large amounts.
 * 100000 → "1 000,00"
 * 1000000 → "10 000,00"
 * For bigger: use formatMoney
 */
export const formatMoneyShort = (cents?: number | string, currency: string = 'PLN'): string => {
    const amount = typeof cents === 'string' ? parseFloat(cents) : cents !== undefined ? cents : 0;

    if (Math.abs(amount) >= 1_000_000) {
        return new Intl.NumberFormat('pl-PL', {
            style: 'currency',
            currency: currency,
            notation: 'compact',
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        }).format(amount);
    }

    return formatMoney(cents, currency);
};