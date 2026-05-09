// User related types
export interface User {
    id?: number;
    name?: string;
    email: string;
    email_verified_at?: string;
    created_at?: string;
    updated_at?: string;
}

// Authentication types
export interface LoginCredentials {
    email: string;
    password: string;
}

export interface RegisterData {
    name: string;
    email: string;
    password: string;
    password_confirmation?: string;
}

export interface TokenResponse {
    access_token: string;
    refresh_token: string;
    token_type?: string;
    expires_in?: number;
}

export interface AuthTokens {
    access_token: string;
    refresh_token: string;
}

// API Response types
export interface ApiResponse<T = any> {
    data?: T;
    message?: string;
    errors?: Record<string, string[]>;
}

export interface ApiError {
    message: string;
    errors?: Record<string, string[]>;
}

// Profile types
export interface ProfileUpdateData {
    name?: string;
    email?: string;
}

// Balance types
export interface BalanceResponse {
    balance: number;
    currency?: string;
}

// Request/Response types for fetchWithAuth
export interface FetchOptions extends RequestInit {
    headers?: Record<string, string>;
    body?: BodyInit;
    method?: string;
}

// OAuth types
export interface OAuthCredentials {
    grant_type: 'password' | 'refresh_token';
    client_id: string;
    client_secret: string;
    username?: string;
    password?: string;
    refresh_token?: string;
    scope?: string;
}

// Generic types
export type AuthState = {
    user: User | null;
    isAuthenticated: boolean;
    isLoading: boolean;
};

export type LoadingState = {
    isLoading: boolean;
};

// Toast types
export type ToastType = 'success' | 'error' | 'warning' | 'info';

export interface ToastData {
    message: string;
    type: ToastType;
    duration?: number;
}

// Auth Context types
export interface AuthContextType {
    loading?: boolean;
    user: User | null;
    login: (email: string, password: string) => Promise<User>;
    register: (name: string, email: string, password: string) => Promise<User>;
    logout: () => void;
    handleLogin: (email: string, password: string) => Promise<User>;
    handleRegister: (name: string, email: string, password: string) => Promise<User>;
    handleLogout: () => Promise<void>;
    isAuthenticated: boolean;
}

// Toast Context types
export interface ToastContextType {
    showToast: (message: string, type: ToastType, duration?: number) => void;
    showSuccess: (message: string) => void;
    showError: (message: string) => void;
    showWarning: (message: string) => void;
    showInfo: (message: string) => void;
}

// Loading Context types
export interface LoadingContextType {
    isLoading: boolean;
    startLoading: () => void;
    stopLoading: () => void;
}
