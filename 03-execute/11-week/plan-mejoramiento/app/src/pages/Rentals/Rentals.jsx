import { useEffect, useMemo, useState } from 'react'
import { api } from '../../services/api'
import { useAuth } from '../../context/AuthContext'
import './Rentals.css'

const emptyForm = { customer_id: '', advisor_id: '', tool_id: '', quantity: 1 }
const currency = (value) => new Intl.NumberFormat('es-CO', { style: 'currency', currency: 'COP', maximumFractionDigits: 0 }).format(Number(value) || 0)
const date = (value) => value ? new Date(`${String(value).slice(0, 10)}T00:00:00`).toLocaleDateString('es-CO') : '—'

export default function Rentals() {
  const { user } = useAuth()
  const [rentals, setRentals] = useState([])
  const [customers, setCustomers] = useState([])
  const [advisors, setAdvisors] = useState([])
  const [tools, setTools] = useState([])
  const [loading, setLoading] = useState(true)
  const [saving, setSaving] = useState(false)
  const [error, setError] = useState('')
  const [modal, setModal] = useState(false)
  const [form, setForm] = useState(emptyForm)
  const [search, setSearch] = useState('')

  const load = async () => {
    setLoading(true); setError('')
    try {
      const token = localStorage.getItem('rent-tools-token')
      const [r, c, a, t] = await Promise.all([
        api('/rentals', { token }), api('/customers', { token }),
        api('/advisors', { token }), api('/tools', { token }),
      ])
      setRentals(r.data || []); setCustomers(c.data || []); setAdvisors(a.data || []); setTools(t.data || [])
    } catch (e) { setError(e.message || 'No fue posible cargar los alquileres.') }
    finally { setLoading(false) }
  }
  useEffect(() => { load() }, [])

  const filtered = useMemo(() => rentals.filter((r) => {
    const text = `${r.id} ${r.customer?.full_name || ''} ${r.advisor?.full_name || ''}`.toLowerCase()
    return text.includes(search.toLowerCase())
  }), [rentals, search])

  const submit = async (event) => {
    event.preventDefault(); setSaving(true); setError('')
    try {
      const token = localStorage.getItem('rent-tools-token')
      const payload = {
        customer_id: Number(form.customer_id),
        advisor_id: user?.role === 'administrator' ? Number(form.advisor_id) : undefined,
        items: [{ tool_id: Number(form.tool_id), quantity: Number(form.quantity) }],
      }
      await api('/rentals', { token, method: 'POST', body: JSON.stringify(payload) })
      setModal(false); setForm(emptyForm); await load()
    } catch (e) {
      const details = e.errors ? Object.values(e.errors).flat().join(' ') : ''
      setError(details || e.message || 'No fue posible registrar el alquiler.')
    } finally { setSaving(false) }
  }

  const active = rentals.filter((r) => ['active', 'partially_returned'].includes(r.status)).length
  return <section className="rentals-page">
    <div className="rentals-heading"><div><span className="rentals-eyebrow">GESTIÓN DE OPERACIONES</span><h1>Alquileres</h1><p>Registra y consulta alquileres guardados en la base de datos.</p></div><button className="rental-primary-button" onClick={() => { setError(''); setModal(true) }}>+ Nuevo alquiler</button></div>
    <div className="rentals-stats"><article className="rental-stat-card"><span>Total de alquileres</span><strong>{rentals.length}</strong></article><article className="rental-stat-card"><span>Activos</span><strong>{active}</strong></article><article className="rental-stat-card"><span>Unidades disponibles</span><strong>{tools.reduce((n,t) => n + Number(t.available_quantity || 0),0)}</strong></article></div>
    <div className="rentals-toolbar"><input aria-label="Buscar alquiler" placeholder="Buscar por cliente o número..." value={search} onChange={e => setSearch(e.target.value)} /></div>
    {error && <p className="form-error" role="alert">{error}</p>}
    {loading ? <p>Cargando alquileres...</p> : <div className="rentals-table-wrapper"><table><thead><tr><th>ALQUILER</th><th>CLIENTE</th><th>ASESOR</th><th>FECHA</th><th>VENCIMIENTO</th><th>ESTADO</th><th>UNIDADES</th></tr></thead><tbody>
      {filtered.map(r => <tr key={r.id}><td>#{r.id}</td><td>{r.customer?.full_name || '—'}</td><td>{r.advisor?.full_name || '—'}</td><td>{date(r.rental_date)}</td><td>{date(r.due_date)}</td><td>{r.status === 'active' ? 'Activo' : r.status === 'partially_returned' ? 'Devolución parcial' : r.status === 'completed' ? 'Finalizado' : r.status}</td><td>{(r.details || []).reduce((n,d) => n + Number(d.quantity || 0),0)}</td></tr>)}
      {!filtered.length && <tr><td colSpan="7">No hay alquileres registrados.</td></tr>}
    </tbody></table></div>}
    {modal && <div className="rental-modal-backdrop"><form className="rental-modal" onSubmit={submit}><h2>Registrar alquiler</h2><p>El sistema registra un periodo inicial de siete días y valida el inventario disponible.</p>
      <label>Cliente<select required value={form.customer_id} onChange={e => setForm({...form, customer_id:e.target.value})}><option value="">Selecciona un cliente</option>{customers.map(c => <option key={c.id} value={c.id}>{c.full_name} · {c.identification_number}</option>)}</select></label>
      {user?.role === 'administrator' && <label>Asesor<select required value={form.advisor_id} onChange={e => setForm({...form, advisor_id:e.target.value})}><option value="">Selecciona un asesor</option>{advisors.map(a => <option key={a.id} value={a.id}>{a.full_name}</option>)}</select></label>}
      <label>Herramienta<select required value={form.tool_id} onChange={e => setForm({...form, tool_id:e.target.value})}><option value="">Selecciona una herramienta</option>{tools.filter(t => Number(t.available_quantity)>0).map(t => <option key={t.id} value={t.id}>{t.name} · disponibles: {t.available_quantity}</option>)}</select></label>
      <label>Cantidad<input type="number" min="1" required value={form.quantity} onChange={e => setForm({...form, quantity:e.target.value})}/></label>
      {error && <p role="alert" className="form-error">{error}</p>}
      <div className="rental-modal-actions"><button type="button" onClick={() => setModal(false)}>Cancelar</button><button className="rental-primary-button" disabled={saving}>{saving ? 'Guardando…' : 'Registrar'}</button></div>
    </form></div>}
  </section>
}
