export const getCardStatusLabel = (status: string): string => {
    switch (status) {
        case 'active': return 'Aktywna';
        case 'blocked': return 'Zablokowana';
        case 'inactive': return 'Nieaktywna';
        case 'expired': return 'Wygasła';
        case 'deleted': return 'Usunięta';
        default: return status;
    }
};
export const getCardStatusColor = (status: string): 'success' | 'error' | 'warning' | 'default' => {
    switch (status) {
        case 'active': return 'success';
        case 'blocked': return 'error';
        case 'inactive': return 'warning';
        case 'expired': return 'default';
        case 'deleted': return 'error';
        default: return 'default';
    }
}

export const getCardTypeLabel = (type: string): string => {
    switch (type) {
        case 'debit':
            return 'Debetowa';
        case 'credit':
            return 'Kredytowa';
        case 'virtual':
            return 'Wirtualna';
        default:
            return type;
    }
};