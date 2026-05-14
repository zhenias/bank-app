export const formatDate = (date: Date | string | undefined): string => {
    if (!date) return 'Brak danych';
    const d = new Date(date);
    return d.toLocaleDateString('pl-PL', { year: 'numeric', month: 'long', day: 'numeric' });
};

export const formatDateShort = (date: Date | string | undefined): string => {
    if (!date) return 'Brak danych';
    const d = new Date(date);
    return d.toLocaleDateString('pl-PL', { year: 'numeric', month: '2-digit', day: '2-digit' });
};

export const formatDateTime = (date: Date | string | undefined): string => {
    if (!date) return 'Brak danych';
    const d = new Date(date);
    return d.toLocaleString('pl-PL', { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' });
};

export const formatDateTimeShort = (date: Date | string | undefined): string => {
    if (!date) return 'Brak danych';
    const d = new Date(date);
    return d.toLocaleString('pl-PL', { year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit' });
};

export const formatDateTimeWithSeconds = (date: Date | string | undefined): string => {
    if (!date) return 'Brak danych';
    const d = new Date(date);
    return d.toLocaleString('pl-PL', { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit', second: '2-digit' });
};

export const formatTime = (date: Date | string | undefined): string => {
    if (!date) return '--:--';
    const d = new Date(date);
    return d.toLocaleTimeString('pl-PL', { hour: '2-digit', minute: '2-digit' });
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

export const formatDateInput = (date: Date | string | null | undefined): string => {
    if (!date) return '';
    const d = typeof date === 'string' ? new Date(date) : date;
    if (isNaN(d.getTime())) return '';
    const year = d.getFullYear();
    const month = String(d.getMonth() + 1).padStart(2, '0');
    const day = String(d.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
};

export const today = (): string => new Date().toISOString().split('T')[0];

export const yearsAgo = (years: number): string => {
    const d = new Date();
    d.setFullYear(d.getFullYear() - years);
    return d.toISOString().split('T')[0];
};

export const yearsFromNow = (years: number): string => {
    const d = new Date();
    d.setFullYear(d.getFullYear() + years);
    return d.toISOString().split('T')[0];
};

export const calculateAge = (dateOfBirth: Date | string | undefined): number | null => {
    if (!dateOfBirth) return null;
    const birth = new Date(dateOfBirth);
    const now = new Date();
    let age = now.getFullYear() - birth.getFullYear();
    const monthDiff = now.getMonth() - birth.getMonth();
    if (monthDiff < 0 || (monthDiff === 0 && now.getDate() < birth.getDate())) {
        age--;
    }
    return age;
};

export const isAdult = (dateOfBirth: Date | string | undefined): boolean => {
    const age = calculateAge(dateOfBirth);
    return age !== null && age >= 18;
};