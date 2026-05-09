import type {
    User,
    TokenResponse,
    OAuthCredentials,
    RegisterData,
    ProfileUpdateData,
    FetchOptions,
    ApiError
} from '../types/types';

const API_URL = import.meta.env.VITE_API_URL || '';

const getClientId = (): string => import.meta.env.VITE_OAUTH_CLIENT_ID || '';
const getClientSecret = (): string => import.meta.env.VITE_OAUTH_CLIENT_SECRET || '';

export const loginWithPassword = async (email: string, password: string): Promise<User> => {
    const credentials: OAuthCredentials = {
        grant_type: 'password',
        client_id: getClientId(),
        client_secret: getClientSecret(),
        username: email,
        password: password,
        scope: '*',
    };

    const response = await fetch(`/oauth/token`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        },
        body: JSON.stringify(credentials),
    });

    if (!response.ok) {
        const error: ApiError = await response.json();
        throw new Error(error.message || 'Błąd logowania');
    }

    const data: TokenResponse = await response.json();

    localStorage.setItem('access_token', data.access_token);
    localStorage.setItem('refresh_token', data.refresh_token);

    return await getUserInfo();
};

export const register = async (name: string, email: string, password: string): Promise<User> => {
    const registerData: RegisterData = {
        name,
        email,
        password,
        password_confirmation: password,
    };

    const response = await fetch(`/api/user/register`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        },
        body: JSON.stringify(registerData),
    });

    if (!response.ok) {
        const error: ApiError = await response.json();
        throw new Error(error.message || 'Błąd rejestracji');
    }

    return await loginWithPassword(email, password);
};

export const getUserInfo = async (): Promise<User> => {
    const token = localStorage.getItem('access_token');

    if (!token) {
        throw new Error('Brak tokena');
    }

    const response = await fetch(`/api/user/profile`, {
        headers: {
            'Authorization': `Bearer ${token}`,
            'Accept': 'application/json',
        },
    });

    if (!response.ok) {
        throw new Error('Nie udało się pobrać danych użytkownika');
    }

    return await response.json();
};

export const checkAuth = async (): Promise<User> => {
    const token = localStorage.getItem('access_token');

    if (!token) {
        throw new Error('Brak tokena');
    }

    return await getUserInfo();
};

export const refreshToken = async (): Promise<string> => {
    const storedRefreshToken = localStorage.getItem('refresh_token');

    if (!storedRefreshToken) {
        throw new Error('Brak refresh tokena');
    }

    const credentials: Partial<OAuthCredentials> = {
        grant_type: 'refresh_token',
        refresh_token: storedRefreshToken,
        client_id: getClientId(),
        client_secret: getClientSecret(),
        scope: '*',
    };

    const response = await fetch(`/oauth/token`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(credentials),
    });

    if (!response.ok) {
        localStorage.removeItem('access_token');
        localStorage.removeItem('refresh_token');
        throw new Error('Nie udało się odświeżyć tokena');
    }

    const data: TokenResponse = await response.json();

    localStorage.setItem('access_token', data.access_token);
    localStorage.setItem('refresh_token', data.refresh_token);

    return data.access_token;
};

export const logout = async (): Promise<void> => {
    const token = localStorage.getItem('access_token');

    if (token) {
        try {
            await fetch(`/api/user/profile/revoke-token`, {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Content-Type': 'application/json',
                },
            });
        } catch (error) {
            console.error('Błąd podczas logout:', error);
        }
    }

    localStorage.removeItem('access_token');
    localStorage.removeItem('refresh_token');
    window.location.href = '/login';
};

export const fetchWithAuth = async (url: string, options: FetchOptions = {}): Promise<Response> => {
    const makeRequest = async (token: string): Promise<Response> => {
        const headers: Record<string, string> = {
            ...options.headers,
            'Authorization': `Bearer ${token}`,
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        };

        return fetch(`/api${url}`, { ...options, headers });
    };

    let token = localStorage.getItem('access_token');

    if (!token) {
        throw new Error('Brak autoryzacji');
    }

    let response = await makeRequest(token);

    if (response.status === 401) {
        try {
            token = await refreshToken();
            response = await makeRequest(token);
        } catch (error) {
            await logout();
            throw error;
        }
    }

    return response;
};

export const updateUserProfile = async (profileData: ProfileUpdateData): Promise<User> => {

    const response = await fetchWithAuth(`/user/profile`, {
        method: 'PUT',
        body: JSON.stringify(profileData),
    });

    if (!response.ok) {
        const error: ApiError = await response.json();
        throw new Error(error.message || 'Błąd aktualizacji profilu');
    }

    return await response.json();
};