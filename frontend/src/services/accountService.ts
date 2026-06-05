import {fetchWithAuth} from "./authService";
import {Account, ApiError, Card, PaginationResponse, Transaction} from "../types/types";

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

export const getDeleteAccount = async (id: string) => {
    const response = await fetchWithAuth(`/accounts/${id}`, {
        method: 'DELETE',
    });

    if (!response.ok) {
        const error: ApiError = await response.json();
        throw new Error(error.message || 'Błąd usuwania konta');
    }

    return await response.json();
};

// Card
export const getAllCards = async (page: number = 1, perPage: number = 20): Promise<PaginationResponse<Card>> => {
    const response = await fetchWithAuth(`/cards?page=${page}&per_page=${perPage}`, {
        method: 'GET',
    });

    if (!response.ok) {
        const error: ApiError = await response.json();
        throw new Error(error.message || 'Błąd pobierania kart');
    }

    return await response.json();
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

// Transactions
export const getTransactions = async (
    page: number = 1,
    perPage: number = 20,
    filters: Record<string, string> = {},
): Promise<PaginationResponse<Transaction>> => {
    const params = new URLSearchParams({ page: String(page), per_page: String(perPage), ...filters });
    const response = await fetchWithAuth(`/transactions?${params.toString()}`, { method: 'GET' });

    if (!response.ok) {
        const error: ApiError = await response.json();
        throw new Error(error.message || 'Błąd pobierania transakcji');
    }

    return await response.json();
};

export const getTransaction = async (id: string): Promise<Transaction> => {
    const response = await fetchWithAuth(`/transactions/${id}`, { method: 'GET' });

    if (!response.ok) {
        const error: ApiError = await response.json();
        throw new Error(error.message || 'Błąd pobierania transakcji');
    }

    return (await response.json()).data;
};

export const createTransfer = async (data: { to_account_number: string; amount: string; description?: string; reference?: string; }): Promise<Transaction> => {
    const response = await fetchWithAuth('/transactions', { method: 'POST', body: JSON.stringify(data) });

    if (!response.ok) {
        const error: ApiError = await response.json();
        throw new Error(error.message || 'Błąd tworzenia przelewu');
    }

    return (await response.json()).data;
};

// FLIK
export const requestFlikCode = async (payload: { card_id: string; account_id: string, amount: string }): Promise<{ code: string; expires_in_seconds: number, amount: number }> => {
    const response = await fetchWithAuth(`/flik/request-code/${payload.card_id}`, { method: 'POST', body: JSON.stringify(payload) });

    if (!response.ok) {
        const error: ApiError = await response.json();
        throw new Error(error.message || 'Błąd generowania kodu FLIK');
    }

    return await response.json();
};

export const payFlik = async (payload: { code: string }) : Promise<any> => {
    const response = await fetchWithAuth(`/flik/pay`, { method: 'POST', body: JSON.stringify(payload) });

    if (!response.ok) {
        const error: ApiError = await response.json();
        throw new Error(error.message || 'Błąd płatności FLIK');
    }

    return await response.json();
};
