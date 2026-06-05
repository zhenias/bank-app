export const formatStatusTransaction = (status: string): string => {
    status = status.toLowerCase();

    switch (status) {
        case 'pending':
            return 'Oczekujące';
        case 'completed':
            return 'Zakończone';
        case 'failed':
            return 'Nieudane';
        default:
            return status;
    }
}

export const formatTypeTransaction = (type: string): string => {
    type = type.toLowerCase();

    switch (type) {
        case 'transfer':
            return 'Przelew';
        case 'refund':
            return 'Zwrot';
        case 'flik_payment':
            return 'Płatność FLIK';
        default:
            return type;
    }
}