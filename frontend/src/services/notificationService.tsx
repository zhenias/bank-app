import { fetchWithAuth } from "./authService";
import {ApiError, Notification, NotificationCount, PaginationResponse} from "../types/types";

export const getNotifications = async (
    page: number = 1,
    perPage: number = 10
): Promise<PaginationResponse<Notification>> => {
    const response = await fetchWithAuth(`/notifications?page=${page}&per_page=${perPage}`, {
        method: 'GET',
    });

    if (!response.ok) {
        const error: ApiError = await response.json();
        throw new Error(error.message || 'Błąd pobierania powiadomień.');
    }

    return await response.json();
};

export const getUnreadCount = async (): Promise<number> => {
    const response = await fetchWithAuth('/notifications/unread-count', {
        method: 'GET',
    });

    if (!response.ok) {
        const error: ApiError = await response.json();
        throw new Error(error.message || 'Błąd pobierania liczby nieprzeczytanych powiadomień.');
    }

    const data: NotificationCount = await response.json();
    return data.count;
};

export const markAsRead = async (id: string): Promise<void> => {
    const response = await fetchWithAuth(`/notifications/${id}/read`, {
        method: 'PATCH',
    });

    if (!response.ok) {
        const error: ApiError = await response.json();
        throw new Error(error.message || 'Błąd oznaczania powiadomienia jako przeczytane.');
    }
};

export const markAllAsRead = async (): Promise<void> => {
    const response = await fetchWithAuth('/notifications/read-all', {
        method: 'PATCH',
    });

    if (!response.ok) {
        const error: ApiError = await response.json();
        throw new Error(error.message || 'Błąd oznaczania wszystkich powiadomień jako przeczytane.');
    }
};