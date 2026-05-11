export const formatDate = (date: Date | string | undefined): string => {
    if (!date) return 'Brak danych';
    const d = new Date(date);

    return d.toLocaleDateString('pl-PL', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
};

export const formatDateShort = (date: Date | string | undefined): string => {
    if (!date) return 'Brak danych';
    const d = new Date(date);

    return d.toLocaleDateString('pl-PL', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
    });
};

export const formatDateTime = (date: Date | string | undefined): string => {
    if (!date) return 'Brak danych';
    const d = new Date(date);

    return d.toLocaleString('pl-PL', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

export const formatDateTimeShort = (date: Date | string | undefined): string => {
    if (!date) return 'Brak danych';
    const d = new Date(date);

    return d.toLocaleString('pl-PL', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit',
    });
};

export const formatDateTimeWithSeconds = (date: Date | string | undefined): string => {
    if (!date) return 'Brak danych';
    const d = new Date(date);

    return d.toLocaleString('pl-PL', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
    });
};

export const formatTime = (date: Date | string | undefined): string => {
    if (!date) return '--:--';
    const d = new Date(date);

    return d.toLocaleTimeString('pl-PL', {
        hour: '2-digit',
        minute: '2-digit',
    });
};

export const formatRelative = (date: Date | string | undefined): string => {
    if (!date) return 'Brak danych';
    const d = new Date(date);
    const now = new Date();
    const diff = now.getTime() - d.getTime();
    const seconds = Math.floor(diff / 1000);
    const minutes = Math.floor(seconds / 60);
    const hours = Math.floor(minutes / 60);
    const days = Math.floor(hours / 24);

    if (seconds < 60) return 'Przed chwilą';
    if (minutes < 60) return `${minutes} min. temu`;
    if (hours < 24) return `${hours} godz. temu`;
    if (days < 7) return `${days} dni temu`;
    return formatDate(d);
};