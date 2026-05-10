import {Navigate, Outlet} from 'react-router-dom';
import { useAuth } from '../../context/AuthContext';

export const GuestOnlyRoute = ({ children }: any) => {
    const { user, loading } = useAuth();

    if (loading) {
        return (
            <div className="min-h-screen flex items-center justify-center">
                <div className="text-gray-600 text-lg">Ładowanie...</div>
            </div>
        );
    }

    if (user) {
        return <Navigate to="/" replace />;
    }

    return <Outlet />;
};