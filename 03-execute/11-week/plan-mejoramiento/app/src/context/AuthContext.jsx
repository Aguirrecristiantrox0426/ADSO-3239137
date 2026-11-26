import { createContext, useCallback, useContext, useEffect, useState } from 'react'
import { api } from '../services/api'

const AuthContext = createContext(null)
const tokenKey = 'rent-tools-token'
export function AuthProvider({ children }) {
  const [user, setUser] = useState(null); const [loading, setLoading] = useState(true)
  const clear = useCallback(() => { localStorage.removeItem(tokenKey); setUser(null) }, [])
  const refresh = useCallback(async () => { const token = localStorage.getItem(tokenKey); if (!token) return; try { const result = await api('/auth/me', { token, onUnauthorized: clear }); setUser(result.data) } catch { clear() } }, [clear])
  useEffect(() => { Promise.resolve().then(refresh).finally(() => setLoading(false)) }, [refresh])
  const login = async (email, password) => { const result = await api('/auth/login', { method: 'POST', body: JSON.stringify({ email, password }) }); localStorage.setItem(tokenKey, result.data.token); setUser(result.data.user) }
  const logout = async () => { const token = localStorage.getItem(tokenKey); try { if (token) await api('/auth/logout', { method: 'POST', token }) } finally { clear() } }
  return <AuthContext.Provider value={{ user, loading, login, logout }}>{children}</AuthContext.Provider>
}
// eslint-disable-next-line react-refresh/only-export-components
export const useAuth = () => useContext(AuthContext)
