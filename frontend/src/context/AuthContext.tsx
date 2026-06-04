import { createContext, useContext, useState, useEffect } from 'react';
import type { User, AuthContextType } from '../types/types';
import {
    checkAuth,
    loginWithPassword,
    register as registerUser,
    logout
} from '../services/authService';

const AuthContext = createContext<AuthContextType | null>(null);

export const useAuth = (): AuthContextType => {
    const context = useContext(AuthContext);
    if (!context) {
        throw new Error('useAuth must be used within AuthProvider');
    }
    return context;
};

export const AuthProvider = ({ children }: { children: React.ReactNode }) => {
    const [user, setUser] = useState<User | null>(null);
    const [loading, setLoading] = useState<boolean>(true);

    const loadUser = async (): Promise<void> => {
        try {
            const userData = await checkAuth();
            setUser(userData);
        } catch (error) {
            console.log('Nie zalogowany');
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        loadUser();
    }, []);

    const handleLogin = async (email: string, password: string): Promise<User> => {
        const userData = await loginWithPassword(email, password);
        setUser(userData);
        return userData;
    };

    const handleRegister = async (name: string, email: string, password: string): Promise<User> => {
        const userData = await registerUser(name, email, password);
        setUser(userData);
        return userData;
    };

    const handleLogout = async (): Promise<void> => {
        await logout();
        setUser(null);
    };

    const isAuthenticated = !!user;

    const value: AuthContextType = {
        loading,
        user,
        setUser,
        loadUser,
        isAuthenticated,
        login: handleLogin,
        register: handleRegister,
        logout: handleLogout,
        handleLogin,
        handleRegister,
        handleLogout,
    };

    return (
        <AuthContext.Provider value={value}>
            {children}
        </AuthContext.Provider>
    );
};