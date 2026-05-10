import { BrowserRouter as Router, Routes, Route, Navigate } from 'react-router-dom';
import { ThemeProvider, createTheme } from '@mui/material/styles';
import { CssBaseline, Box } from '@mui/material';
import { AuthProvider } from './context/AuthContext';
import { ToastProvider } from './context/ToastContext';
import { LoadingProvider } from './context/LoadingContext';
import { ProtectedRoute } from './components/Layout/ProtectedRoute';
import { LoginPage } from './pages/Forms/LoginPage';
import { RegisterPage } from './pages/Forms/RegisterPage';
import { BalancePage } from './pages/User/BalancePage';
import {JSX} from "react";
import {ProfilePage} from "./pages/User/ProfilePage";
import {DashboardPage} from "./pages/Dashboard/DashboardPage";
import {GuestOnlyRoute} from "./components/Layout/GuestOnlyRoute";
import {DashboardLayout} from "./components/Layout/DashboardLayout";
import {AccountPage} from "./pages/Account/AccountPage";
import {AccountViewPage} from "./pages/Account/AccoutViewPage";

const theme = createTheme({
  palette: {
    primary: {
      main: '#1976d2',
    },
    secondary: {
      main: '#dc004e',
    },
    background: {
      default: '#f5f5f5',
      paper: '#ffffff',
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
    MuiSvgIcon: {
      defaultProps: {
          fontSize: 'small',
      },
    },
    MuiButton: {
      styleOverrides: {
        root: {
          textTransform: 'none',
          borderRadius: 4,
          fontWeight: 600,
        },
      },
    },
    MuiPaper: {
      styleOverrides: {
        root: {
          borderRadius: 8,
        },
      },
    },
    MuiTextField: {
      styleOverrides: {
        root: {
          '& .MuiOutlinedInput-root': {
            borderRadius: 8,
          },
        },
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