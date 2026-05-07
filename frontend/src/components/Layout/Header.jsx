import { Link, useNavigate } from 'react-router-dom';
import { useAuth } from '../../context/AuthContext';

export const Header = () => {
    const { user, handleLogout } = useAuth();
    const navigate = useNavigate();

    const onLogout = async () => {
        await handleLogout();
        navigate('/login');
    };

    return (
        <nav className="bg-white shadow-lg">
            <div className="max-w-7xl mx-auto px-4">
                <div className="flex justify-between items-center h-16">
                    <div className="flex items-center space-x-8">
                        <Link to="/" className="text-xl font-bold text-gray-800">
                            Bank Online
                        </Link>
                        {user && (
                            <div className="flex space-x-4">
                                <Link to="/balance" className="text-gray-600 hover:text-gray-900">
                                    Saldo
                                </Link>
                            </div>
                        )}
                    </div>

                    <div className="flex items-center space-x-4">
                        {user ? (
                            <>
                                <span className="text-sm text-gray-600">
                                  Witaj, {user.name || user.email}
                                </span>
                                <button
                                    onClick={onLogout}
                                    className="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg text-sm"
                                >
                                    Wyloguj
                                </button>
                            </>
                        ) : (
                            <>
                                <Link
                                    to="/login"
                                    className="text-gray-600 hover:text-gray-900"
                                >
                                    Logowanie
                                </Link>
                                <Link
                                    to="/register"
                                    className="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg"
                                >
                                    Rejestracja
                                </Link>
                            </>
                        )}
                    </div>
                </div>
            </div>
        </nav>
    );
};