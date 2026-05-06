import { useState, useEffect } from 'react'
import reactLogo from './assets/react.svg'
import viteLogo from './assets/vite.svg'
import heroImg from './assets/hero.png'
import './App.css'

function App() {
  const [count, setCount] = useState(0)
  const [user, setUser] = useState(null)
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    checkAuth()
  }, [])

  const checkAuth = async () => {
    try {
      // ✅ Używaj relatywnego URL - proxy to przekieruje
      const response = await fetch('/api/user', {
        credentials: 'include',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        }
      })
      if (response.ok) {
        const userData = await response.json()
        setUser(userData)
      }
    } catch (error) {
      console.log('Nie zalogowany')
    } finally {
      setLoading(false)
    }
  }

  const handleLogin = () => {
    // ✅ Przekieruj przez proxy na backend
    window.location.href = '/login'
  }

  const handleLogout = async () => {
    await fetch('/logout', {
      method: 'GET',
      credentials: 'include',
    })
    setUser(null)
  }

  if (loading) {
    return <div className="loading">Ładowanie...</div>
  }

  return (
      <>
        <section id="center">
          <div className="hero">
            <img src={heroImg} className="base" width="170" height="179" alt="" />
            <img src={reactLogo} className="framework" alt="React logo" />
            <img src={viteLogo} className="vite" alt="Vite logo" />
          </div>

          <div>
            <h1>Bank Online System</h1>

            {user ? (
                <div className="user-info">
                  <p>Witaj, <strong>{user.name || user.email}</strong>!</p>
                  <p className="user-email">{user.email}</p>
                  <button type="button" className="logout-btn" onClick={handleLogout}>
                    Wyloguj się
                  </button>
                </div>
            ) : (
                <div className="login-box">
                  <p>Zaloguj się przez bezpieczne OAuth</p>
                  <button type="button" className="login-btn" onClick={handleLogin}>
                    🔐 Zaloguj się
                  </button>
                </div>
            )}
          </div>

          <button
              type="button"
              className="counter"
              onClick={() => setCount((count) => count + 1)}
          >
            Count is {count}
          </button>
        </section>

        {/* ... reszta Twojego JSX ... */}
      </>
  )
}

export default App