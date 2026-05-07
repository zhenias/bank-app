import { useState, useEffect } from 'react';
import { useAuth } from '../context/AuthContext';
import { fetchWithAuth } from '../services/authService';

export const BalancePage = () => {
    const { user } = useAuth();
    const [balance, setBalance] = useState(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);

    useEffect(() => {
        fetchBalance();
    }, []);

    const fetchBalance = async () => {
        try {
            const response = await fetchWithAuth('/api/user/balance');
            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || 'Błąd pobierania salda');
            }

            setBalance(data.balance);
        } catch (err) {
            setError(err.message || 'Nie udało się pobrać salda');
            console.error(err);
        } finally {
            setLoading(false);
        }
    };

    if (loading) {
        return (
            <div className="min-h-screen flex items-center justify-center bg-gradient-to-br from-blue-50 to-indigo-100">
                <div className="text-gray-600">Ładowanie salda...</div>
            </div>
        );
    }

    return (
        <div className="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 p-8">
            <div className="max-w-4xl mx-auto">
                <div className="bg-white rounded-2xl shadow-xl p-8">
                    <h1 className="text-3xl font-bold text-gray-800 mb-6">
                        Witaj, {user?.name || user?.email}!
                    </h1>

                    <div className="border-t-2 border-gray-200 pt-6">
                        <h2 className="text-2xl font-semibold text-gray-700 mb-4">
                            Stan konta
                        </h2>

                        {error ? (
                            <div className="bg-red-50 text-red-600 p-4 rounded-lg">
                                {error}
                            </div>
                        ) : (
                            <div className="bg-gradient-to-r from-blue-500 to-blue-600 rounded-xl p-8 text-white">
                                <p className="text-sm opacity-90 mb-2">Twoje saldo wynosi:</p>
                                <p className="text-5xl font-bold">
                                    {balance !== null ? `${balance.toFixed(2)} PLN` : '---'}
                                </p>
                            </div>
                        )}
                    </div>
                </div>
            </div>
        </div>
    );
};