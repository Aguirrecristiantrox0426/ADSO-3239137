import { useState } from 'react'
import { ApiError } from '../services/api'
import { useAuth } from '../context/AuthContext'

export default function Login() {
  const { login } = useAuth(); const [email, setEmail] = useState(''); const [password, setPassword] = useState(''); const [error, setError] = useState(''); const [sending, setSending] = useState(false)
  const submit = async (event) => { event.preventDefault(); setError(''); setSending(true); try { await login(email, password) } catch (err) { setError(err instanceof ApiError && err.status === 401 ? 'Correo, contraseña o estado de cuenta no válidos.' : 'No fue posible iniciar sesión. Inténtalo nuevamente.') } finally { setSending(false) } }
  return <main className="login-page"><form className="login-card" onSubmit={submit}><div className="brand"><span>R</span> RENT-TOOLS</div><h1>Inicia sesión</h1><p>Accede al panel de gestión de alquileres.</p>{error && <p role="alert" className="login-error">{error}</p>}<label>Correo electrónico<input type="email" value={email} onChange={(e) => setEmail(e.target.value)} required autoComplete="email" /></label><label>Contraseña<input type="password" value={password} onChange={(e) => setPassword(e.target.value)} required autoComplete="current-password" /></label><button className="primary-button" disabled={sending}>{sending ? 'Validando…' : 'Ingresar'}</button></form></main>
}
