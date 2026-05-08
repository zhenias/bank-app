import { BrowserRouter as Router, Routes, Route, Navigate } from 'react-router-dom';
import { ThemeProvider, createTheme } from '@mui/material/styles';
import { CssBaseline, Box } from '@mui/material';
import { AuthProvider } from './context/AuthContext';
import { ToastProvider } from './context/ToastContext';
import { LoadingProvider } from './context/LoadingContext';
import { Header } from './components/Layout/Header';
import { ProtectedRoute } from './components/Layout/ProtectedRoute';
import { LoadingOverlay } from './components/UI/LoadingOverlay';
import { LoginPage } from './pages/Forms/LoginPage';
import { RegisterPage } from './pages/Forms/RegisterPage';
import { BalancePage } from './pages/User/BalancePage';
import {JSX} from "react";
import {ProfilePage} from "./pages/User/ProfilePage";

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
                            <Box sx={{ display: 'flex', flexDirection: 'column', minHeight: '100vh', bgcolor: 'background.default' }}>
                                <Header />
                                <Box component="main" sx={{ flexGrow: 1, pt: 8, px: 3 }}>
                                    <Routes>
                                        <Route path="/login" element={<LoginPage />} />
                                        <Route path="/register" element={<RegisterPage />} />
                                        <Route
                                            path="/profile"
                                            element={
                                                <ProtectedRoute>
                                                    <ProfilePage />
                                                </ProtectedRoute>
                                            }
                                        />
                                        <Route
                                            path="/balance"
                                            element={
                                                <ProtectedRoute>
                                                    <BalancePage />
                                                </ProtectedRoute>
                                            }
                                        />
                                        <Route path="/" element={<Navigate to="/profile" replace />} />
                                    </Routes>
                                </Box>
                            </Box>
                            <LoadingOverlay />
                        </LoadingProvider>
                    </ToastProvider>
                </AuthProvider>
            </Router>
        </ThemeProvider>
    );
}

export default App;