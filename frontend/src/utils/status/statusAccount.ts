export const getAccountStatusLabel = (status: string): string => {
    switch (status) {
        case 'open':
            return 'Aktywne';
        case 'frozen':
            return 'Zamrożone';
        case 'closed':
            return 'Zamknięte';
        default:
            return 'Nieznany status';
    }
}

export const getAccountStatusColor = (status: string) => {
    switch (status) {
        case 'open': return 'success';
        case 'frozen': return 'error';
        case 'closed': return 'error';
        default: return 'default';
    }
}

export const getAccountTypeLabel = (type: string): string => {
    return type === 'savings' ? 'Oszczędnościowe' : 'Bieżące';
};

export const getAccountTypeColor = (type: string): 'success' | 'primary' => {
    return type === 'savings' ? 'success' : 'primary';
};