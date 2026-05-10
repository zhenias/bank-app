import {fetchWithAuth} from "./authService";
import {Account, ApiError, Card, PaginationResponse} from "../types/types";

export const getAccounts = async (page: number = 1, perPage: number = 20): Promise<PaginationResponse<Account>> => {
    const response = await fetchWithAuth(`/accounts?page=${page}&per_page=${perPage}`, {
        method: 'GET',
    });

    if (!response.ok) {
        const error: ApiError = await response.json();
        throw new Error(error.message || 'Błąd pobierania danych o kontach bankowych.');
    }

    return await response.json();
};

export const createAccount = async (data: {
    name: string;
    currency?: string;
    type?: string;
    with_card?: boolean;
}): Promise<Account> => {
    const response = await fetchWithAuth('/accounts', {
        method: 'POST',
        body: JSON.stringify(data),
    });

    if (!response.ok) {
        const error: ApiError = await response.json();
        throw new Error(error.message || 'Błąd tworzenia konta');
    }

    return await response.json();
};

export const getAccount = async (id: string): Promise<Account> => {
    const response = await fetchWithAuth(`/accounts/${id}`, {
        method: 'GET',
    });

    if (!response.ok) {
        const error: ApiError = await response.json();
        throw new Error(error.message || 'Błąd pobierania konta');
    }

    return (await response.json()).data;
};


export const getCards = async (accountId: string): Promise<PaginationResponse<Card>> => {
    const response = await fetchWithAuth(`/accounts/${accountId}/cards`, {
        method: 'GET',
    });

    if (!response.ok) {
        const error: ApiError = await response.json();
        throw new Error(error.message || 'Błąd pobierania kart');
    }

    return await response.json();
};

export const createCard = async (accountId: string, network: string, type: string): Promise<Card> => {
    const response = await fetchWithAuth(`/accounts/${accountId}/cards`, {
        method: 'POST',
        body: JSON.stringify({ network, type }),
    });

    if (!response.ok) {
        const error: ApiError = await response.json();
        throw new Error(error.message || 'Błąd tworzenia karty');
    }

    return (await response.json()).data;
};

export const blockCard = async (cardId: string): Promise<void> => {
    const response = await fetchWithAuth(`/cards/${cardId}/block`, {
        method: 'PATCH',
    });

    if (!response.ok) {
        throw new Error('Nie udało się zablokować karty');
    }
};

export const unblockCard = async (cardId: string): Promise<void> => {
    const response = await fetchWithAuth(`/cards/${cardId}/unblock`, {
        method: 'PATCH',
    });

    if (!response.ok) {
        throw new Error('Nie udało się odblokować karty');
    }
};

export const deleteCard = async (cardId: string): Promise<void> => {
    const response = await fetchWithAuth(`/cards/${cardId}`, {
        method: 'DELETE',
    });

    if (!response.ok) {
        throw new Error('Nie udało się usunąć karty');
    }
};