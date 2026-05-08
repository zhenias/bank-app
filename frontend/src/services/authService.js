const API_URL = import.meta.env.VITE_API_URL;

export const loginWithPassword = async (email, password) => {
    const response = await fetch('/oauth/token', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        },
        body: JSON.stringify({
            grant_type: 'password',
            client_id: import.meta.env.VITE_OAUTH_CLIENT_ID,
            client_secret: import.meta.env.VITE_OAUTH_CLIENT_SECRET,
            username: email,
            password: password,
            scope: '*',
        }),
    });

    if (!response.ok) {
        const error = await response.json();
        throw new Error(error.message || 'Błąd logowania');
    }

    const data = await response.json();

    localStorage.setItem('access_token', data.access_token);
    localStorage.setItem('refresh_token', data.refresh_token);

    setAuthToken(data.access_token);

    const user = await getUserInfo();
    return user;
};

export const register = async (name, email, password) => {
    const response = await fetch('/api/register', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        },
        body: JSON.stringify({
            name,
            email,
            password,
            password_confirmation: password,
        }),
    });

    if (!response.ok) {
        const error = await response.json();
        throw new Error(error.message || 'Błąd rejestracji');
    }

    return await loginWithPassword(email, password);
};

export const getUserInfo = async () => {
    const token = localStorage.getItem('access_token');

    if (!token) {
        throw new Error('Brak tokena');
    }

    const response = await fetch('/api/user', {
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

export const checkAuth = async () => {
    const token = localStorage.getItem('access_token');

    if (!token) {
        throw new Error('Brak tokena');
    }

    setAuthToken(token);
    return await getUserInfo();
};

export const refreshToken = async () => {
    const refreshToken = localStorage.getItem('refresh_token');

    if (!refreshToken) {
        throw new Error('Brak refresh tokena');
    }

    const response = await fetch('/oauth/token', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            grant_type: 'refresh_token',
            refresh_token: refreshToken,
            client_id: import.meta.env.VITE_OAUTH_CLIENT_ID,
            client_secret: import.meta.env.VITE_OAUTH_CLIENT_SECRET,
            scope: '*',
        }),
    });

    if (!response.ok) {
        throw new Error('Nie udało się odświeżyć tokena');
    }

    const data = await response.json();
    localStorage.setItem('access_token', data.access_token);
    localStorage.setItem('refresh_token', data.refresh_token);
    setAuthToken(data.access_token);

    return data.access_token;
};

export const logout = async () => {
    const token = localStorage.getItem('access_token');

    if (token) {
        try {
            await fetch('/api/token/revoke', {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Content-Type': 'application/json',
                },
            });
        } catch (error) {
            console.log('Błąd podczas logout:', error);
        }
    }

    localStorage.removeItem('access_token');
    localStorage.removeItem('refresh_token');
    setAuthToken(null);
};

export const setAuthToken = (token) => {
    if (token) {
        window.defaultHeaders = {
            'Authorization': `Bearer ${token}`,
            'Accept': 'application/json',
        };
    } else {
        delete window.defaultHeaders;
    }
};

// Interceptor do automatycznego odświeżania tokena
export const fetchWithAuth = async (url, options = {}) => {
    const makeRequest = async (token) => {
        const headers = {
            ...options.headers,
            'Authorization': `Bearer ${token}`,
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        };

        return fetch(url, { ...options, headers });
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
            window.location.href = '/login';
            throw error;
        }
    }

    return response;
};