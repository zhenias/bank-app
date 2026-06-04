# Frontend Architecture & Design Patterns Guide

## Overview

Bank App Frontend jest zbudowany na React 18+ z TypeScript, Material-UI i React Router v6. Dokument ten opisuje główne wzorce i strukturę kodu, którą powinni trzymać się agenci AI (oraz programiści) podczas implementacji nowych funkcjonalności.

---

## 1. Struktura Katalogów

```
src/
├── assets/              # Statyczne zasoby (obrazy, ikony)
├── components/          # Komponenty wielokrotnego użytku
│   ├── Layout/         # Layout komponenty (Header, Sidebar)
│   ├── Fields/         # Form fields
│   ├── Auth/           # Auth-specifyczne komponenty
│   ├── Notification/   # Notification komponenty
│   └── Assets/         # Asset komponenty (Loading, etc.)
├── context/            # React Context Providers
├── hooks/              # Custom React Hooks
├── pages/              # Page komponenty (screen level)
│   ├── Account/
│   ├── User/
│   ├── Forms/
│   └── Guardian/
├── services/           # API communication layer
├── types/              # TypeScript typ definicje
├── utils/              # Utility funkcje
├── App.tsx             # Main App component
├── main.tsx            # Entry point
└── index.css           # Global styles
```

---

## 2. Wzorce Architektoniczne

### 2.1 React Context + Hooks Pattern

Używamy **React Context API** dla global state management (auth, toast, loading).

**Lokalizacja:** `src/context/`

**Przykład - AuthContext:**

```typescript
import { createContext, useContext, useState, useEffect } from 'react';
import type { AuthContextType, User } from '../types/types';

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

    useEffect(() => {
        loadUser();
    }, []);

    const handleLogin = async (email: string, password: string): Promise<User> => {
        const userData = await loginWithPassword(email, password);
        setUser(userData);
        return userData;
    };

    const value: AuthContextType = {
        user,
        setUser,
        loading,
        handleLogin,
        handleLogout,
        isAuthenticated: !!user,
    };

    return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
};
```

**Reguły:**
- ✅ Jeden Context = jeden aspekt state (auth, toast, loading)
- ✅ Custom Hook `useAuth()` zawsze sprawdza null
- ✅ Provider na top level (App.tsx)
- ✅ Nie mieszaj logiki - jeden context = jedna odpowiedzialność

---

### 2.2 API Service Layer

Services komunikują się z backend API.

**Lokalizacja:** `src/services/`

**Przykład - accountService.ts:**

```typescript
import { fetchWithAuth } from "./authService";
import { Account, Card, PaginationResponse, ApiError } from "../types/types";

export const getAllCards = async (
    page: number = 1,
    perPage: number = 20
): Promise<PaginationResponse<Card>> => {
    const response = await fetchWithAuth(
        `/cards?page=${page}&per_page=${perPage}`,
        { method: 'GET' }
    );

    if (!response.ok) {
        const error: ApiError = await response.json();
        throw new Error(error.message || 'Błąd pobierania kart');
    }

    return await response.json();
};

export const blockCard = async (cardId: string): Promise<void> => {
    const response = await fetchWithAuth(`/cards/${cardId}/block`, {
        method: 'PATCH',
    });

    if (!response.ok) {
        throw new Error('Nie udało się zablokować karty');
    }
};
```

**Reguły:**
- ✅ Funkcje asyncowe do każdej operacji
- ✅ Zawsze sprawdzaj `response.ok`
- ✅ Throw Error z descriptive message
- ✅ Zwracaj typed responses
- ✅ Nie zmieniaj stanu w services
- ✅ Używaj `fetchWithAuth()` dla auth headers

---

### 2.3 Custom Hooks Pattern

Custom Hooks enkapsulują logikę reutilizacyjną.

**Lokalizacja:** `src/hooks/`

**Przykład - useAuth Hook:**

```typescript
import { useContext } from 'react';
import { AuthContext } from '../context/AuthContext';

export const useAuth = () => {
    const context = useContext(AuthContext);
    if (!context) {
        throw new Error('useAuth must be used within AuthProvider');
    }
    return context;
};
```

**Reguły:**
- ✅ Hook names zaczynaj z `use` prefix
- ✅ Custom hooks enkapsulują complex useEffect logic
- ✅ Zwracaj objects z logią i state
- ✅ Rzucaj error jeśli hook used bez providera

---

### 2.4 Page Component Pattern

Page komponenty to top-level komponenty dla każdego screeningu.

**Lokalizacja:** `src/pages/{Feature}/`

**Przykład - CardPage.tsx:**

```typescript
export const CardPage = () => {
    const [cards, setCards] = useState<CardType[]>([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState<string | null>(null);
    const { showSuccess, showError } = useToast();

    const loadCards = async (pageNumber: number = 1) => {
        setLoading(true);
        setError(null);
        try {
            const response = await getAllCards(pageNumber);
            setCards(response.data);
        } catch (err: any) {
            setError(err.message);
            showError(err.message);
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        loadCards();
    }, []);

    return (
        <Box>
            {loading && <LoadingSpinner />}
            {error && <Alert severity="error">{error}</Alert>}
            {/* Render content */}
        </Box>
    );
};
```

**Reguły:**
- ✅ Pages zarządzają własnym local state
- ✅ Pages fetchują dane w useEffect
- ✅ Obierz context hooks dla global state
- ✅ Deleguj complex logikę do custom hooks
- ✅ Render error/loading states

---

### 2.5 Material-UI Component Pattern

Używamy **Material-UI (MUI)** do UI komponentów.

```typescript
import {
    Box,
    Card,
    CardContent,
    Button,
    TextField,
    Dialog,
    Table,
    TableHead,
    TableBody,
    TableRow,
    TableCell,
    Alert,
} from '@mui/material';
import { Add as AddIcon } from '@mui/icons-material';

export const MyComponent = () => {
    const [open, setOpen] = useState(false);

    return (
        <Box sx={{ display: 'flex', gap: 2, mb: 4 }}>
            <Card>
                <CardContent>
                    <TextField label="Name" />
                    <Button
                        variant="contained"
                        startIcon={<AddIcon />}
                        onClick={() => setOpen(true)}
                    >
                        Add
                    </Button>
                </CardContent>
            </Card>

            <Dialog open={open} onClose={() => setOpen(false)}>
                {/* Dialog content */}
            </Dialog>
        </Box>
    );
};
```

**Reguły:**
- ✅ Używaj Box zamiast div dla layout
- ✅ sx prop dla inline styles
- ✅ Icons from `@mui/icons-material`
- ✅ Zmieniaj warianty (variant="contained", variant="outlined")
- ✅ Używaj Material-UI components zamiast HTML

---

### 2.6 Type Definitions

TypeScript types dla całej aplikacji.

**Lokalizacja:** `src/types/types.ts`

```typescript
// User related
export interface User {
    id?: number;
    name?: string;
    email?: string;
    email_verified_at?: string;
    created_at?: string;
}

// Card related
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

// Pagination
export interface PaginationMeta {
    current_page: number;
    last_page: number;
    total: number;
    from: number | null;
    to: number | null;
}

export interface PaginationResponse<T> {
    data: T[];
    meta: PaginationMeta;
    links: PaginationLinks;
}

// API Errors
export interface ApiError {
    status?: string;
    message?: string;
    errors?: Record<string, string[]>;
}
```

**Reguły:**
- ✅ Centralizuj wszystkie typy w `types.ts`
- ✅ Exportuj interfaces, nie types
- ✅ Czytaj structury API response dokładnie
- ✅ Optional pola z `?`
- ✅ Dokumentuj complex types

---

### 2.7 Form Pattern

Formularze w Reactcie ze state managementem.

```typescript
const [formData, setFormData] = useState({
    email: '',
    password: '',
    rememberMe: false,
});

const [errors, setErrors] = useState<Record<string, string>>({});

const handleFormChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const { name, value, type, checked } = e.target;
    setFormData(prev => ({
        ...prev,
        [name]: type === 'checkbox' ? checked : value,
    }));
};

const handleSubmit = async () => {
    setErrors({});
    try {
        await login(formData.email, formData.password);
    } catch (err: any) {
        setErrors(err.errors || { general: err.message });
    }
};

return (
    <Box component="form" sx={{ mt: 1 }}>
        <TextField
            name="email"
            label="Email"
            value={formData.email}
            onChange={handleFormChange}
            error={!!errors.email}
            helperText={errors.email}
        />
        <Button onClick={handleSubmit} variant="contained" disabled={submitting}>
            Login
        </Button>
    </Box>
);
```

**Reguły:**
- ✅ Każdy input ma name attribute
- ✅ Centralizuj form state w jednym object
- ✅ Error state as Record<string, string>
- ✅ Disable submit button kiedy submitting
- ✅ Show helperText na error fields

---

### 2.8 Error Handling & Toast Notifications

Używamy Toast Context do notifications.

```typescript
const { showSuccess, showError, showWarning } = useToast();

const handleDeleteCard = async () => {
    try {
        await deleteCard(cardId);
        showSuccess('Karta została usunięta!');
        await loadCards();
    } catch (err: any) {
        const errorMsg = err.message || 'Błąd usuwania karty';
        showError(errorMsg);
    }
};
```

**Reguły:**
- ✅ Toast context dla notifications
- ✅ showSuccess/showError metody
- ✅ Zawsze catch error i show toast
- ✅ Error message do użytkownika
- ✅ Loading state podczas async operacji

---

### 2.9 Routing & Navigation

React Router v6 dla nawigacji.

**Lokalizacja:** `App.tsx`

```typescript
<BrowserRouter>
    <Routes>
        {/* Public routes */}
        <Route element={<GuestOnlyRoute />}>
            <Route path="/login" element={<LoginPage />} />
            <Route path="/register" element={<RegisterPage />} />
        </Route>

        {/* Protected routes */}
        <Route element={<ProtectedRoute />}>
            <Route element={<DashboardLayout />}>
                <Route path="/dashboard" element={<DashboardPage />} />
                <Route path="/cards" element={<CardPage />} />
                <Route path="/accounts/:id" element={<AccountViewPage />} />
                <Route path="/accounts" element={<AccountPage />} />
            </Route>
        </Route>

        <Route path="*" element={<Navigate to="/dashboard" replace />} />
    </Routes>
</BrowserRouter>
```

**Reguły:**
- ✅ Route guards (ProtectedRoute, GuestOnlyRoute)
- ✅ Nested routes dla shared layout
- ✅ Named params z `:`
- ✅ 404 fallback do default page
- ✅ useNavigate() do programmatic navigation

---

### 2.10 Data Formatting Utilities

Utility funkcje do formatowania danych.

**Lokalizacja:** `src/utils/`

```typescript
// formatDate.ts
export const formatDate = (dateString: string): string => {
    return new Date(dateString).toLocaleDateString('pl-PL');
};

export const formatDateShort = (dateString: string): string => {
    return new Date(dateString).toLocaleDateString('pl-PL', {
        year: '2-digit',
        month: '2-digit',
        day: '2-digit',
    });
};

// formatAccount.ts
export const formatAccountNumber = (accountNumber: string): string => {
    return accountNumber.replace(/(\d{2})/g, '$1 ').trim();
};
```

**Reguły:**
- ✅ Pure functions bez side effects
- ✅ Eksportuj helpers do reuse
- ✅ Consistent formatting (locale, format)
- ✅ Zapamiętaj formatowanie dla UI display

---

## 3. Responsive Design Pattern

Material-UI Grid systemu do responsywności.

```typescript
import { Grid } from '@mui/material';

<Grid container spacing={3}>
    <Grid size={{ xs: 12, sm: 6, md: 4 }}>
        {/* Full width na mobile, 50% na tablet, 33% na desktop */}
    </Grid>
</Grid>
```

**Breakpoints:**
- `xs` - 0px (mobile)
- `sm` - 600px (tablet)
- `md` - 960px (laptop)
- `lg` - 1280px (desktop)
- `xl` - 1920px (wide desktop)

---

## 4. Loading & Error States

Zawsze renderuj loading i error states.

```typescript
if (loading) {
    return (
        <Grid container spacing={3}>
            {[1, 2, 3].map((i) => (
                <Grid size={{ xs: 12 }} key={i}>
                    <Skeleton variant="rounded" height={120} />
                </Grid>
            ))}
        </Grid>
    );
}

if (error || !data) {
    return (
        <Alert severity="error">
            {error || 'Nie znaleziono danych'}
        </Alert>
    );
}

return (
    <Box>{/* Render data */}</Box>
);
```

**Reguły:**
- ✅ Loading state z Skeleton
- ✅ Error state z Alert
- ✅ Empty state message
- ✅ Try/catch gdzie async

---

## 5. State Management Rules

### Local State (useState)
- UI state (modal open, form data, focused field)
- Loading/error flags
- Temporary data

### Global State (Context)
- User auth info
- Toast notifications
- Global loading indicator
- Theme preferences (future)

### Backend State (Services)
- Data fetched from API
- Pagination info
- Never store in Context unless really global

```typescript
// ❌ Wrong
const [allCards, setAllCards] = useState([]); // Too much global
const [isGlobalLoading, setIsGlobalLoading] = useState(false); // Each page loads its own

// ✅ Right
const [cards, setCards] = useState([]); // Local to page
const [loading, setLoading] = useState(true); // Local to page
const { showSuccess } = useToast(); // Global toast
const { user } = useAuth(); // Global auth
```

---

## 6. Component Composition

Mniejsze, reusable komponenty zamiast mega-komponentów.

```typescript
// ❌ Bad - All in one
export const CardListPage = () => {
    // 500 lines of code
};

// ✅ Good - Decomposed
export const CardListPage = () => {
    return (
        <Box>
            <CardListHeader />
            <CardListTable cards={cards} />
            <CardListPagination />
        </Box>
    );
};

const CardListHeader = () => {
    return <Box sx={{ mb: 4 }}>{/* ... */}</Box>;
};

const CardListTable = ({ cards }: { cards: Card[] }) => {
    return <Table>{/* ... */}</Table>;
};
```

**Reguły:**
- ✅ Komponenty do 200 linii
- ✅ Reusable komponenty w `components/`
- ✅ Page-specific w `pages/`
- ✅ Props typing

---

## 7. Testing Patterns (Future)

```typescript
import { render, screen, waitFor } from '@testing-library/react';
import { CardPage } from './CardPage';

describe('CardPage', () => {
    it('should display cards list', async () => {
        render(<CardPage />);
        
        await waitFor(() => {
            expect(screen.getByText('Moje karty')).toBeInTheDocument();
        });
    });
});
```

---

## 8. Common Mistakes to Avoid

❌ SetState w loop  
✅ Initialize state z array, map w render

❌ Render bez key prop  
✅ Zawsze key na list items

❌ API call w render  
✅ Umieść w useEffect

❌ useEffect bez dependencies  
✅ Spróbuj zawsze specify dependencies

❌ Inline funkcje w onClick  
✅ Define funkcje poza render lub useCallback

❌ State w Context dla wszystkiego  
✅ Rozdziel local vs global

❌ prop drilling 10 levels deep  
✅ Użyj Context dla deeply nested data

❌ Mixing UI logic i business logic  
✅ Umieść busines logic w services

---

## 9. Dependencies & Frameworks

- **React 18+** - UI library
- **React Router v6** - Routing
- **TypeScript** - Type safety
- **Material-UI v5+** - Component library
- **MUI Icons** - Icon library
- **Fetch API** - HTTP (no axios)
- **Vite** - Build tool
- **Biome** - Linter/Formatter

---

## 10. Project Structure Best Practices

```
src/
├── assets/
│   ├── icons/
│   └── images/
├── components/
│   ├── Common/           # Shared always
│   ├── Layout/           # Layout wrappers
│   ├── Fields/           # Form fields
│   └── UI/               # UI components
├── context/              # React Context
│   ├── AuthContext.tsx
│   ├── ToastContext.tsx
│   └── LoadingContext.tsx
├── hooks/                # Custom hooks
│   └── useAuth.tsx
├── pages/
│   ├── Account/
│   │   ├── AccountPage.tsx
│   │   ├── Card/
│   │   │   └── CardPage.tsx
│   │   └── AccoutViewPage.tsx
│   ├── User/
│   ├── Dashboard/
│   └── ...
├── services/             # API Services
│   ├── accountService.ts
│   ├── authService.ts
│   └── ...
├── types/                # Type definitions
│   └── types.ts
├── utils/                # Utility functions
│   ├── formatDate.ts
│   └── formatAccount.ts
├── App.tsx
├── App.css
├── main.tsx
└── index.css
```

---

## 11. Useful Commands

```bash
# Start dev server
npm run dev

# Build for production
npm run build

# Run linter
npm run lint

# Format code
npm run format
```

---

## 12. AI Agent Instructions

Kiedy dodajesz nowe features:

1. **ZAWSZE** start z page komponentem
2. **NIGDY** nie mieszaj API logic z UI
3. **ZAWSZE** używaj Services dla API calls
4. **ZAWSZE** handle loading/error states
5. **ZAWSZE** type everything with TypeScript
6. **ZAWSZE** use React Context dla global state
7. **ZAWSZE** show toast notification na success/error
8. Keep components small and focused
9. Extract reusable components early
10. Test responsiveness on mobile

## Sekcja Komponentów Material-UI - Quick Reference

```typescript
// Box - Flexbox container
<Box sx={{ display: 'flex', gap: 2, mb: 3 }}>

// Card - Elevated container
<Card>
    <CardContent>
        {/* Content */}
    </CardContent>
</Card>

// Button - Actions
<Button variant="contained">Save</Button>
<Button variant="outlined">Cancel</Button>

// TextField - Input
<TextField label="Name" value={name} onChange={handleChange} />

// Table - Data grid
<TableContainer>
    <Table>
        <TableHead><TableRow><TableCell></TableCell></TableRow></TableHead>
        <TableBody>{items.map(...)}</TableBody>
    </Table>
</TableContainer>

// Dialog - Modal
<Dialog open={open} onClose={handleClose}>
    <DialogTitle>Title</DialogTitle>
    <DialogContent>Content</DialogContent>
    <DialogActions><Button>Close</Button></DialogActions>
</Dialog>

// Alert - Messages
<Alert severity="error">Error message</Alert>

// Chip - Tags
<Chip label="Active" color="success" size="small" />

// Pagination
<Pagination count={10} page={page} onChange={handleChange} />

// Grid - Layout
<Grid container spacing={3}>
    <Grid size={{ xs: 12, md: 6 }} />
</Grid>
```

Powodzenia! 🚀

