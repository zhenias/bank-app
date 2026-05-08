import { useNavigate } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';
import { LoginForm } from '../components/Auth/LoginForm';

export const LoginPage = () => {
    const { handleLogin } = useAuth();
    const navigate = useNavigate();

    const onLogin = async (email, password) => {
        await handleLogin(email, password);
        navigate('/balance');
    };

    const onSwitchToRegister = () => {
        navigate('/register');
    };

    return <LoginForm onLogin={onLogin} onSwitchToRegister={onSwitchToRegister} />;
};