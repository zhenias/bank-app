import { useState, useEffect } from 'react';
import { checkAuth, logout } from '../services/authService';

export const useAuth = () => {
    const [user, setUser] = useState(null);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        const loadUser = async () => {
            try {
                const userData = await checkAuth();
                setUser(userData);
            } catch (error) {
                console.log('Nie zalogowany');
            } finally {
                setLoading(false);
            }
        };

        loadUser();
    }, []);

    const handleLogout = async () => {
        await logout();
        setUser(null);
    };

    const handleLogin = () => {
        window.location.href = '/login';
    };

    return { user, loading, handleLogin, handleLogout };
};