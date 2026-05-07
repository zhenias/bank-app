import { useNavigate, Link } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';
import { LoginForm } from '../components/Auth/LoginForm';

export const LoginPage = () => {
    const { handleLogin } = useAuth();
    const navigate = useNavigate();

    const onLogin = async (email, password) => {
        await handleLogin(email, password);
        navigate('/balance');
    };

    return (
        <div className="min-h-screen flex items-center justify-center bg-gradient-to-br from-blue-50 to-indigo-100 p-4">
            <LoginForm onLogin={onLogin} />
        </div>
    );
};