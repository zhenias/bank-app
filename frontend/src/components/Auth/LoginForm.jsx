import { useState } from 'react';

export const LoginForm = ({ onLogin, onSwitchToRegister, error: externalError }) => {
    const [email, setEmail] = useState('');
    const [password, setPassword] = useState('');
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState('');

    const handleSubmit = async (e) => {
        e.preventDefault();
        setLoading(true);
        setError('');

        try {
            await onLogin(email, password);
        } catch (err) {
            setError(err.message || 'Błąd logowania');
        } finally {
            setLoading(false);
        }
    };

    return (
        <div className="bg-white p-8 rounded-2xl shadow-xl w-full max-w-md">
            <h2 className="text-3xl font-bold text-center mb-8 text-gray-800">
                Logowanie
            </h2>

            {(error || externalError) && (
                <div className="mb-4 p-3 bg-red-50 border border-red-200 text-red-600 rounded-lg text-sm">
                    {error || externalError}
                </div>
            )}

            <form onSubmit={handleSubmit}>
                <div className="mb-4">
                    <label className="block text-gray-700 font-semibold mb-2">
                        Email
                    </label>
                    <input
                        type="email"
                        value={email}
                        onChange={(e) => setEmail(e.target.value)}
                        required
                        placeholder="twoj@email.com"
                        disabled={loading}
                        className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition disabled:bg-gray-100 disabled:cursor-not-allowed"
                    />
                </div>

                <div className="mb-6">
                    <label className="block text-gray-700 font-semibold mb-2">
                        Hasło
                    </label>
                    <input
                        type="password"
                        value={password}
                        onChange={(e) => setPassword(e.target.value)}
                        required
                        placeholder="••••••••"
                        disabled={loading}
                        className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition disabled:bg-gray-100 disabled:cursor-not-allowed"
                    />
                </div>

                <button
                    type="submit"
                    disabled={loading}
                    className="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg transition duration-200 disabled:bg-blue-300 disabled:cursor-not-allowed"
                >
                    {loading ? 'Logowanie...' : '🔐 Zaloguj się'}
                </button>
            </form>

            <div className="mt-6 text-center">
                <button
                    type="button"
                    onClick={onSwitchToRegister}
                    className="text-blue-600 hover:text-blue-700 text-sm hover:underline transition"
                >
                    Nie masz konta? Zarejestruj się
                </button>
            </div>
        </div>
    );
};