import { useNavigate } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';
import { RegisterForm } from '../components/Auth/RegisterForm';

export const RegisterPage = () => {
    const { handleRegister } = useAuth();
    const navigate = useNavigate();

    const onRegister = async (name, email, password) => {
        await handleRegister(name, email, password);
        navigate('/balance');
    };

    const onSwitchToLogin = () => {
        navigate('/login');
    };

    return <RegisterForm onRegister={onRegister} onSwitchToLogin={onSwitchToLogin} />;
};