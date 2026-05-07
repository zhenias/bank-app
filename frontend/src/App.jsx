import { BrowserRouter as Router, Routes, Route, Navigate } from 'react-router-dom';
import { AuthProvider } from './context/AuthContext';
import { Header } from './components/Layout/Header';
import { ProtectedRoute } from './components/Layout/ProtectedRoute';
import { LoginPage } from './pages/LoginPage';
import { RegisterPage } from './pages/RegisterPage';
import { BalancePage } from './pages/BalancePage';

function App() {
    return (
        <Router>
            <AuthProvider>
                <div className="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100">
                    <Header />
                    <Routes>
                        <Route path="/login" element={<LoginPage />} />
                        <Route path="/register" element={<RegisterPage />} />
                        <Route
                            path="/balance"
                            element={
                                <ProtectedRoute>
                                    <BalancePage />
                                </ProtectedRoute>
                            }
                        />
                        <Route path="/" element={<Navigate to="/balance" replace />} />
                    </Routes>
                </div>
            </AuthProvider>
        </Router>
    );
}

export default App;