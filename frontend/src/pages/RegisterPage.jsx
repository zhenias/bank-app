import { useNavigate, Link } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';
import { RegisterForm } from '../components/Auth/RegisterForm';

export const RegisterPage = () => {
    const { handleRegister } = useAuth();
    const navigate = useNavigate();

    const onRegister = async (name, email, password) => {
        await handleRegister(name, email, password);
        navigate('/balance');
    };

    return (
        <div className="min-h-screen flex items-center justify-center bg-gradient-to-br from-blue-50 to-indigo-100 p-4">
            <RegisterForm onRegister={onRegister} />
        </div>
    );
};