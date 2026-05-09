export const format_date = (date: Date | string | undefined) => {
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