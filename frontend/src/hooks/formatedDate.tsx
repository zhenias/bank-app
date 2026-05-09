export const formatDate = (date: Date | string | undefined) => {
    if (!date) return 'Brak danych';
    const d = new Date(date);

    return d.toLocaleString('pl-PL', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit'
    });
}

export const formatDateShort = (date: Date | string | undefined) => {
    if (!date) return 'Brak danych';
    const d = new Date(date);

    return d.toLocaleString('pl-PL', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit'
    });
}