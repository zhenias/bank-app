import { createContext, useContext, useState, useEffect } from 'react';
import {
    checkAuth,
    loginWithPassword,
    register as registerUser,
    logout
} from '../services/authService';

const AuthContext = createContext();

export const useAuth = () => {
    const context = useContext(AuthContext);
    if (!context) {
        throw new Error('useAuth must be used within AuthProvider');
    }
    return context;
};

export const AuthProvider = ({ children }) => {
    const [user, setUser] = useState(null);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        loadUser();
    }, []);

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

    const handleLogin = async (email, password) => {
        const userData = await loginWithPassword(email, password);
        setUser(userData);
    };

    const handleRegister = async (name, email, password) => {
        const userData = await registerUser(name, email, password);
        setUser(userData);
    };

    const handleLogout = async () => {
        await logout();
        setUser(null);
    };

    return (
        <AuthContext.Provider value={{
            user,
            loading,
            handleLogin,
            handleRegister,
            handleLogout,
            loadUser
        }}>
            {children}
        </AuthContext.Provider>
    );
};