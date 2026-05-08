import { useNavigate } from 'react-router-dom';
import { useAuth } from '../../context/AuthContext.jsx';
import { LoginForm } from '../../components/Auth/LoginForm.jsx';

export const LoginPage = () => {
    const { handleLogin } = useAuth();
    const navigate = useNavigate();

    const onLogin = async (email: string, password: string) => {
        const user = await handleLogin(email, password);
        navigate('/');

        return user;
    };

    const onSwitchToRegister = () => {
        navigate('/register');
    };

    return <LoginForm onLogin={onLogin} onSwitchToRegister={onSwitchToRegister} />;
};