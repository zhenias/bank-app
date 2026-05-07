export const LoginButton = ({ onLogin }) => {
    return (
        <div className="login-box">
            <p>Zaloguj się przez bezpieczne OAuth</p>
            <button type="button" className="login-btn" onClick={onLogin}>
                🔐 Zaloguj się
            </button>
        </div>
    );
};