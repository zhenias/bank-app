import { useNavigate } from 'react-router-dom';
import { useAuth } from '../../context/AuthContext.jsx';
import { RegisterForm } from '../../components/Auth/RegisterForm.jsx';

export const RegisterPage = () => {
    const { handleRegister } = useAuth();
    const navigate = useNavigate();

    const onRegister = async (name: string, email: string, password: string) => {
        const user = await handleRegister(name, email, password);
        navigate('/');

        return user;
    };

    const onSwitchToLogin = () => {
        navigate('/login');
    };

    return <RegisterForm onRegister={onRegister} onSwitchToLogin={onSwitchToLogin} />;
};