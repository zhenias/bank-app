export const UserInfo = ({ user, onLogout }) => {
    return (
        <div className="bg-gradient-to-r from-blue-500 to-blue-600 p-8 rounded-2xl shadow-xl w-full max-w-md">
            <div className="text-center text-white">
                <div className="text-6xl mb-4">👤</div>
                <h3 className="text-2xl font-bold mb-2">
                    Witaj!
                </h3>
                <p className="text-xl font-semibold mb-1">
                    {user.name || user.email}
                </p>
                <p className="text-blue-100 text-sm mb-6">
                    {user.email}
                </p>
                <button
                    onClick={onLogout}
                    className="bg-red-500 hover:bg-red-600 text-white font-semibold py-2 px-6 rounded-lg transition duration-200"
                >
                    Wyloguj się
                </button>
            </div>
        </div>
    );
};