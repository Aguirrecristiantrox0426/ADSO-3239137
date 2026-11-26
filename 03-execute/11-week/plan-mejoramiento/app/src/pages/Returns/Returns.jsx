import { useEffect, useMemo, useState } from 'react'
import { api } from '../../services/api'
import { useAuth } from '../../context/AuthContext'
import './Returns.css'

const date = (value) => value ? new Date(`${String(value).slice(0,10)}T00:00:00`).toLocaleDateString('es-CO') : '—'

export default function Returns() {
  const { user } = useAuth()
  const [returns, setReturns] = useState([])
  const [rentals, setRentals] = useState([])
  const [advisors, setAdvisors] = useState([])
  const [selected, setSelected] = useState(null)
  const [details, setDetails] = useState([])
  const [quantities, setQuantities] = useState({})
  const [advisorId, setAdvisorId] = useState('')
  const [modal, setModal] = useState(false)
  const [loading, setLoading] = useState(true)
  const [saving, setSaving] = useState(false)
  const [error, setError] = useState('')
  const [search, setSearch] = useState('')

  const load = async () => {
    setLoading(true); setError('')
    try {
      const token = localStorage.getItem('rent-tools-token')
      const [r, a, l] = await Promise.all([api('/returns',{token}),api('/advisors',{token}),api('/rentals',{token})])
      setReturns(r.data || []); setAdvisors(a.data || [])
      setRentals((l.data || []).filter(x => ['active','partially_returned'].includes(x.status)))
    } catch(e) { setError(e.message || 'No fue posible cargar las devoluciones.') }
    finally { setLoading(false) }
  }
  useEffect(() => { load() }, [])

  const filtered = useMemo(() => returns.filter(r => {
    const term = search.toLowerCase()
    return ` ${r.id} ${r.rental?.customer?.full_name || ''} ${r.rental_id}`.toLowerCase().includes(term)
  }), [returns, search])

  const chooseRental = async (id) => {
    setSelected(id); setDetails([]); setQuantities({}); setError('')
    if (!id) return
    try {
      const token = localStorage.getItem('rent-tools-token')
      const result = await api(`/rentals/${id}`, {token})
      const rows = result.data?.details || []
      const pending = rows.map(d => {
        const returned = (d.return_details || []).reduce((n,x) => n + Number(x.returned_quantity),0)
        return {...d, pending: Number(d.quantity) - returned}
      }).filter(d => d.pending > 0)
      setDetails(pending)
      setQuantities(Object.fromEntries(pending.map(d => [d.id, 0])))
    } catch(e) { setError(e.message || 'No fue posible consultar el detalle del alquiler.') }
  }

  const submit = async (event) => {
    event.preventDefault(); setSaving(true); setError('')
    try {
      const items = details.filter(d => Number(quantities[d.id]) > 0).map(d => ({rental_detail_id:d.id, returned_quantity:Number(quantities[d.id])}))
      if (!items.length) throw new Error('Indica la cantidad que vas a devolver.')
      const token = localStorage.getItem('rent-tools-token')
      await api('/returns', {token, method:'POST', body:JSON.stringify({rental_id:Number(selected), advisor_id:user?.role === 'administrator' && advisorId ? Number(advisorId) : undefined, items})})
      setModal(false); setSelected(null); setDetails([]); await load()
    } catch(e) {
      const detail = e.errors ? Object.values(e.errors).flat().join(' ') : ''
      setError(detail || e.message || 'No fue posible registrar la devolución.')
    } finally { setSaving(false) }
  }

  return <section className="returns-page">
    <div className="returns-heading"><div><span className="returns-eyebrow">CONTROL DE INVENTARIO</span><h1>Devoluciones</h1><p>Registra devoluciones parciales o completas de alquileres activos.</p></div><button className="returns-primary-button" onClick={() => {setError('');setModal(true)}}>+ Nueva devolución</button></div>
    {error && <p className="form-error" role="alert">{error}</p>}
    <div className="returns-toolbar"><input placeholder="Buscar por cliente o alquiler..." value={search} onChange={e=>setSearch(e.target.value)}/></div>
    {loading ? <p>Cargando devoluciones...</p> : <div className="returns-table-wrapper"><table><thead><tr><th>DEVOLUCIÓN</th><th>ALQUILER</th><th>CLIENTE</th><th>ASESOR</th><th>FECHA</th><th>UNIDADES</th></tr></thead><tbody>
      {filtered.map(r => <tr key={r.id}><td>#{r.id}</td><td>#{r.rental_id}</td><td>{r.rental?.customer?.full_name || '—'}</td><td>{r.advisor?.full_name || '—'}</td><td>{date(r.return_date)}</td><td>{(r.details || []).reduce((n,d)=>n+Number(d.returned_quantity||0),0)}</td></tr>)}
      {!filtered.length && <tr><td colSpan="6">No hay devoluciones registradas.</td></tr>}
    </tbody></table></div>}
    {modal && <div className="return-modal-backdrop"><form className="return-modal" onSubmit={submit}><h2>Registrar devolución</h2>
      <label>Alquiler<select required value={selected || ''} onChange={e=>chooseRental(e.target.value)}><option value="">Selecciona un alquiler activo</option>{rentals.map(r=><option key={r.id} value={r.id}>#{r.id} · {r.customer?.full_name || 'Cliente'}</option>)}</select></label>
      {user?.role === 'administrator' && <label>Asesor<select required value={advisorId} onChange={e=>setAdvisorId(e.target.value)}><option value="">Selecciona un asesor</option>{advisors.map(a=><option key={a.id} value={a.id}>{a.full_name}</option>)}</select></label>}
      {details.map(d=><label key={d.id}>{d.tool_name_snapshot} · pendiente: {d.pending}<input type="number" min="0" max={d.pending} value={quantities[d.id] ?? 0} onChange={e=>setQuantities({...quantities,[d.id]:e.target.value})}/></label>)}
      {selected && !details.length && <p>Este alquiler no tiene unidades pendientes o no se pudo cargar su detalle.</p>}
      {error && <p className="form-error" role="alert">{error}</p>}
      <div className="return-modal-actions"><button type="button" onClick={()=>setModal(false)}>Cancelar</button><button className="returns-primary-button" disabled={saving}>{saving?'Guardando…':'Registrar devolución'}</button></div>
    </form></div>}
  </section>
}
