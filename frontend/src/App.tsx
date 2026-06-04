import { BrowserRouter as Router, Routes, Route, Navigate } from 'react-router-dom';
import {ThemeProvider, createTheme, Theme} from '@mui/material/styles';
import { CssBaseline, Box } from '@mui/material';
import { AuthProvider } from './context/AuthContext';
import { ToastProvider } from './context/ToastContext';
import { LoadingProvider } from './context/LoadingContext';
import { ProtectedRoute } from './components/Layout/ProtectedRoute';
import { LoginPage } from './pages/Forms/LoginPage';
import { RegisterPage } from './pages/Forms/RegisterPage';
import { BalancePage } from './pages/User/BalancePage';
import { JSX } from "react";
import { ProfilePage } from "./pages/User/ProfilePage";
import { DashboardPage } from "./pages/Dashboard/DashboardPage";
import { GuestOnlyRoute } from "./components/Layout/GuestOnlyRoute";
import { DashboardLayout } from "./components/Layout/DashboardLayout";
import { AccountPage } from "./pages/Account/AccountPage";
import { AccountViewPage } from "./pages/Account/AccoutViewPage";
import { CardPage } from "./pages/Account/Card/CardPage";
import { GuardianApprovalPage } from "./pages/Guardian/GuardianApprovalPage";
import { NotificationBell } from "./components/Notification/NotificationBell";
import { NotificationsPage } from "./pages/Notification/NotificationsPage";

const theme = createTheme({
    palette: {
        mode: 'light',
        primary: {
            main: '#1976d2',
            light: '#42a5f5',
            dark: '#1565c0',
            contrastText: '#ffffff',
        },
        secondary: {
            main: '#dc004e',
            light: '#ff5983',
            dark: '#9a0036',
            contrastText: '#ffffff',
        },
        background: {
            default: '#f5f5f5',
            paper: '#ffffff',
        },
        text: {
            primary: 'rgba(0, 0, 0, 0.87)',
            secondary: 'rgba(0, 0, 0, 0.6)',
            disabled: 'rgba(0, 0, 0, 0.38)',
        },
        divider: 'rgba(0, 0, 0, 0.12)',
        error: {
            main: '#d32f2f',
            light: '#ef5350',
            dark: '#c62828',
            contrastText: '#ffffff',
        },
        warning: {
            main: '#ed6c02',
            light: '#ff9800',
            dark: '#e65100',
            contrastText: '#ffffff',
        },
        info: {
            main: '#0288d1',
            light: '#03a9f4',
            dark: '#01579b',
            contrastText: '#ffffff',
        },
        success: {
            main: '#2e7d32',
            light: '#4caf50',
            dark: '#1b5e20',
            contrastText: '#ffffff',
        },
        grey: {
            50: '#fafafa',
            100: '#f5f5f5',
            200: '#eeeeee',
            300: '#e0e0e0',
            400: '#bdbdbd',
            500: '#9e9e9e',
            600: '#757575',
            700: '#616161',
            800: '#424242',
            900: '#212121',
        },
    },
    typography: {
        fontFamily: '"Roboto", "Helvetica", "Arial", sans-serif',
        h4: {
            fontWeight: 600,
        },
        h5: {
            fontWeight: 600,
        },
        h6: {
            fontWeight: 600,
        },
    },
    components: {
        MuiCssBaseline: {
            styleOverrides: {
                body: {
                    backgroundColor: '#f5f5f5',
                    color: 'rgba(0, 0, 0, 0.87)',
                },
            },
        },
        MuiSvgIcon: {
            defaultProps: {
                fontSize: 'small',
            },
        },
        MuiButton: {
            styleOverrides: {
                root: ({ theme }) => ({
                    textTransform: 'none',
                    borderRadius: 4,
                    fontWeight: 600,
                    color: theme.palette.text.primary,
                }),
                contained: ({ theme }) => ({
                    color: theme.palette.primary.contrastText,
                }),
                containedSecondary: ({ theme }: any) => ({
                    color: theme.palette.secondary.contrastText,
                }),
                outlined: ({ theme }) => ({
                    color: theme.palette.primary.main,
                    borderColor: theme.palette.primary.main,
                }),
            },
        },
        MuiPaper: {
            styleOverrides: {
                root: ({ theme }) => ({
                    borderRadius: 8,
                    backgroundColor: theme.palette.background.paper,
                    color: theme.palette.getContrastText(theme.palette.background.paper),
                }),
            },
        },
        MuiCard: {
            styleOverrides: {
                root: ({ theme }) => ({
                    backgroundColor: theme.palette.background.paper,
                    color: theme.palette.getContrastText(theme.palette.background.paper),
                }),
            },
        },
        MuiCardContent: {
            styleOverrides: {
                root: {
                    color: 'inherit',
                },
            },
        },
        MuiTextField: {
            styleOverrides: {
                root: ({ theme }) => ({
                    '& .MuiOutlinedInput-root': {
                        borderRadius: 8,
                        backgroundColor: theme.palette.background.paper,
                        color: theme.palette.text.primary,
                    },
                    '& .MuiInputLabel-root': {
                        color: theme.palette.text.secondary,
                    },
                    '& .MuiOutlinedInput-notchedOutline': {
                        borderColor: theme.palette.divider,
                    },
                }),
            },
        },
        MuiTableCell: {
            styleOverrides: {
                root: ({ theme }) => ({
                    color: theme.palette.text.primary,
                    borderBottom: `1px solid ${theme.palette.divider}`,
                }),
                head: ({ theme }) => ({
                    color: theme.palette.text.primary,
                    fontWeight: 600,
                    backgroundColor: theme.palette.grey[50],
                }),
            },
        },
        MuiTableRow: {
            styleOverrides: {
                root: {
                    '&:hover': {
                        backgroundColor: 'rgba(0, 0, 0, 0.04)',
                    },
                },
            },
        },
        MuiAlert: {
            styleOverrides: {
                root: ({ theme }) => ({
                    color: theme.palette.text.primary,
                    '& .MuiAlert-message': {
                        color: theme.palette.text.primary,
                    },
                }),
            },
        },
        MuiChip: {
            styleOverrides: {
                root: {
                    fontWeight: 600,
                },
                filled: ({ theme }) => ({
                    color: theme.palette.primary.contrastText,
                }),
                outlined: ({ theme }) => ({
                    color: theme.palette.text.primary,
                    borderColor: theme.palette.grey[400],
                }),
            },
        },
        MuiTypography: {
            styleOverrides: {
                root: {
                    color: 'inherit',
                },
            },
        },
        MuiDivider: {
            styleOverrides: {
                root: ({ theme }) => ({
                    borderColor: theme.palette.divider,
                }),
            },
        },
        MuiIconButton: {
            styleOverrides: {
                root: ({ theme }) => ({
                    color: theme.palette.text.primary,
                }),
            },
        },
        MuiListItemText: {
            styleOverrides: {
                primary: ({ theme }) => ({
                    color: theme.palette.text.primary,
                }),
                secondary: ({ theme }) => ({
                    color: theme.palette.text.secondary,
                }),
            },
        },
        MuiMenuItem: {
            styleOverrides: {
                root: ({ theme }) => ({
                    color: theme.palette.text.primary,
                }),
            },
        },
        MuiAppBar: {
            styleOverrides: {
                root: ({ theme }) => ({
                    backgroundColor: theme.palette.primary.main,
                    color: theme.palette.primary.contrastText,
                }),
            },
        },
        MuiDrawer: {
            styleOverrides: {
                paper: ({ theme }) => ({
                    backgroundColor: theme.palette.background.paper,
                    color: theme.palette.text.primary,
                }),
            },
        },
        MuiTooltip: {
            styleOverrides: {
                tooltip: ({ theme }) => ({
                    backgroundColor: theme.palette.grey[800],
                    color: theme.palette.getContrastText(theme.palette.grey[800]),
                }),
            },
        },
    },
});

function App(): JSX.Element {
    return (
        <ThemeProvider theme={theme}>
            <CssBaseline />
            <Router>
                <AuthProvider>
                    <ToastProvider>
                        <LoadingProvider>
                            <Routes>
                                {/* Public - bez headera i menu */}
                                <Route element={<GuestOnlyRoute />}>
                                    <Route path="/login" element={<LoginPage />} />
                                    <Route path="/register" element={<RegisterPage />} />
                                </Route>

                                {/* Protected z menu bocznym i headerem */}
                                <Route element={<ProtectedRoute />}>
                                    <Route element={<DashboardLayout />}>
                                        <Route path="/dashboard" element={<DashboardPage />} />
                                        <Route path="/profile" element={<ProfilePage />} />
                                        <Route path="/balance" element={<BalancePage />} />
                                        <Route path="/accounts" element={<AccountPage />} />
                                        <Route path="/accounts/:id" element={<AccountViewPage />} />
                                        <Route path="/cards" element={<CardPage />} />
                                        <Route path="/notifications" element={<NotificationsPage />} />
                                        <Route path="/guardian/approve/:wardId" element={<GuardianApprovalPage />} />
                                    </Route>
                                </Route>

                                <Route path="*" element={<Navigate to="/dashboard" replace />} />
                            </Routes>
                        </LoadingProvider>
                    </ToastProvider>
                </AuthProvider>
            </Router>
        </ThemeProvider>
    );
}

export default App;