// User related types
export interface User {
    id?: number;
    name?: string;
    email?: string;
    email_verified_at?: string;
    date_of_birth?: string;
    created_at?: string;
    updated_at?: string;
    guardian?: Guardian;
}

export interface Guardian {
    id?: string;
    name?: string;
    email?: string;
    guardian_approved_at?: string;
}

// Pagination
export interface PaginationLink {
    url: string | null;
    label: string;
    page: number | null;
    active: boolean;
}

export interface PaginationMeta {
    current_page: number;
    from: number | null;
    last_page: number;
    links: PaginationLink[];
    path: string;
    per_page: number;
    to: number | null;
    total: number;
}

export interface PaginationLinks {
    first: string;
    last: string;
    prev: string | null;
    next: string | null;
}

export interface PaginationResponse<T> {
    data: T[];
    links: PaginationLinks;
    meta: PaginationMeta;
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
    status?: string,
    message?: string;
    code?: number,
    errors?: Record<string, string[]>;
}

// Profile types
export interface ProfileUpdateData {
    name?: string;
    email?: string;
    password?: string;
    password_confirmation?: string;
    old_password?: string;
    date_of_birth?: string;
    guardian_email?: string;
    guardian_delete?: boolean;
}

export interface ProfileUpdateResponse {
    user: User;
    message?: string;
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
    setUser: (user: User | null) => void;
    loadUser: () => void;
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

// Accounts
export interface Account {
    id: string;
    name: string;
    account_number: string;
    balance: number;
    currency: string;
    type: string;
    cards: Card[];
    created_at: string;
    updated_at: string;
}

export interface Card {
    id: string;
    card_number: string;
    card_last_four: string;
    exp_month: number;
    exp_year: number;
    network: string;
    type: string;
    status: string;
    created_at: string;
    updated_at: string;
}

export interface Transaction {
    id: string;
    amount: number;
    description: string;
    type: string;
    status: string;
    created_at: string;
}

export interface TransactionAccountShort {
    id: string;
    name?: string;
    account_number?: string;
    user?: string;
}

export interface TransactionCardShort {
    id: string;
    last_four?: string;
    network?: string;
}

// Extended transaction returned by API
export interface Transaction {
    id: string;
    amount: number; // in cents
    description?: string;
    reference?: string;
    type: string;
    status: string;
    from_account?: TransactionAccountShort | null;
    to_account?: TransactionAccountShort | null;
    from_card?: TransactionCardShort | null;
    created_at: string;
    updated_at?: string;
}

// Notification
export interface Notification {
    id: string;
    type: string;
    title: string;
    body: string;
    data: {
        ward_id?: string;
        ward_name?: string;
        guardian_id?: string;
        guardian_name?: string;
        action?: string;
        action_url?: string;
        status?: string;
        ward_date_of_birth?: string;
        ward_age?: number;
        ward_email?: string;
    };
    read_at: string | null;
    created_at: string;
}

export interface NotificationCount {
    count: number;
}