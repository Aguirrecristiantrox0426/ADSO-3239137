
import { useEffect, useState } from 'react'
import './App.css'

import Tools from './pages/Tools/Tools'
import Categories from './pages/categories/categories'
import Customers from './pages/Customers/Customers'
import Advisors from './pages/Advisors/Advisors'
import Rentals from './pages/Rentals/Rentals'
import Returns from './pages/Returns/Returns'
import Login from './pages/Login'

import { useAuth } from './context/AuthContext'
import { api } from './services/api'

const navigation = [
  { id: 'dashboard', label: 'Inicio', icon: '⌂' },
  { id: 'tools', label: 'Herramientas', icon: '🔧' },
  {
    id: 'categories',
    label: 'Categorías',
    icon: '▦',
    adminOnly: true,
  },
  { id: 'customers', label: 'Clientes', icon: '♙' },
  { id: 'advisors', label: 'Asesores', icon: '♧' },
  { id: 'rentals', label: 'Alquileres', icon: '▤' },
  { id: 'returns', label: 'Devoluciones', icon: '↩' },
]

function App() {
  const { user, loading, logout } = useAuth()

  const [activePage, setActivePage] = useState('dashboard')
  const [search, setSearch] = useState('')

  const [dashboard, setDashboard] = useState(null)
  const [dashboardLoading, setDashboardLoading] = useState(true)
  const [dashboardError, setDashboardError] = useState('')

  const isAdministrator = user?.role === 'administrator'

  const visibleNavigation = navigation.filter(
    (item) => !item.adminOnly || isAdministrator,
  )

  // Evita que un usuario sin permisos permanezca en Categorías.
  useEffect(() => {
    if (user && !isAdministrator && activePage === 'categories') {
      setActivePage('dashboard')
    }
  }, [user, isAdministrator, activePage])

  // Carga la información real del dashboard.
  useEffect(() => {
    if (!user) {
      setDashboard(null)
      setDashboardLoading(false)
      return
    }

    let cancelled = false

    const loadDashboard = async () => {
      try {
        setDashboardLoading(true)
        setDashboardError('')

        const token = localStorage.getItem('rent-tools-token')

        const response = await api('/dashboard', {
          token,
          onUnauthorized: logout,
        })

        if (!cancelled) {
          setDashboard(response.data)
        }
      } catch (error) {
        if (!cancelled) {
          setDashboardError(
            error.message || 'No fue posible cargar el dashboard.',
          )
        }
      } finally {
        if (!cancelled) {
          setDashboardLoading(false)
        }
      }
    }

    loadDashboard()

    return () => {
      cancelled = true
    }
  }, [user, logout])

  if (loading) {
    return <main className="login-page">Verificando sesión...</main>
  }

  if (!user) {
    return <Login />
  }

  const activeNavigation =
    visibleNavigation.find((item) => item.id === activePage) ||
    visibleNavigation.find((item) => item.id === 'dashboard')

  const recentRentals = dashboard?.recent_rentals ?? []

  const filteredActivities = recentRentals.filter((rental) =>
    `${rental.id} ${rental.customer ?? ''}`
      .toLowerCase()
      .includes(search.toLowerCase()),
  )

  const formatDate = (date) => {
    if (!date) return '—'

    const parsedDate = new Date(date)

    if (Number.isNaN(parsedDate.getTime())) return '—'

    return parsedDate.toLocaleDateString('es-CO')
  }

  const formatStatus = (status) => {
    if (status === 'active') return 'Activo'

    if (status === 'partially_returned') {
      return 'Devolución parcial'
    }

    return status || 'Sin estado'
  }

  const formatStatusClass = (status) => {
    if (status === 'active') return 'activo'

    if (status === 'partially_returned') {
      return 'pendiente'
    }

    return 'sin-estado'
  }

  const inventoryTotal = dashboard?.inventory?.total ?? 0
  const inventoryAvailable = dashboard?.inventory?.available ?? 0
  const inventoryPendingReturn =
    dashboard?.inventory?.pending_return ?? 0

  const availablePercentage =
    inventoryTotal > 0
      ? Math.min((inventoryAvailable / inventoryTotal) * 100, 100)
      : 0

  const pendingReturnPercentage =
    inventoryTotal > 0
      ? Math.min((inventoryPendingReturn / inventoryTotal) * 100, 100)
      : 0

  const currentDate = new Date().toLocaleDateString('es-CO', {
    day: 'numeric',
    month: 'long',
    year: 'numeric',
  })

  return (
    <div className="app-container">
      <aside className="sidebar">
        <div className="brand">
          <div className="brand-icon">R</div>

          <div className="brand-text">
            <h2>RENT-TOOLS</h2>
            <span>Equipment Management</span>
          </div>
        </div>

        <div className="sidebar-section">
          <span className="sidebar-label">MENÚ PRINCIPAL</span>

          <nav className="navigation">
            {visibleNavigation.map((item) => (
              <button
                key={item.id}
                type="button"
                className={`nav-item ${
                  activePage === item.id ? 'active' : ''
                }`}
                onClick={() => setActivePage(item.id)}
              >
                <span className="nav-icon">{item.icon}</span>
                <span>{item.label}</span>
              </button>
            ))}
          </nav>
        </div>

        <div className="sidebar-bottom">
          <div className="sidebar-help">
            <span className="help-icon">?</span>

            <div>
              <strong>¿Necesitas ayuda?</strong>
              <span>Consulta la información del sistema.</span>
            </div>
          </div>

          <div className="sidebar-version">
            RENT-TOOLS · Versión 1.0
          </div>
        </div>
      </aside>

      <main className="main-content">
        <header className="topbar">
          <div className="breadcrumb">
            <span>RENT-TOOLS</span>
            <span className="breadcrumb-separator">/</span>
            <strong>{activeNavigation?.label}</strong>
          </div>

          <div className="topbar-actions">
            <span className="environment-label">
              Entorno de desarrollo
            </span>

            <div className="user-profile">
              <div className="user-avatar">AD</div>

              <div className="user-information">
                <strong>{user.name}</strong>
                <span>
                  {isAdministrator ? 'Administrador' : 'Asesor'}
                </span>
              </div>
            </div>
          </div>

          <button
            type="button"
            className="secondary-button"
            onClick={logout}
          >
            Cerrar sesión
          </button>
        </header>

        <div className="page-content">
          {activePage === 'dashboard' ? (
            <>
              <section className="welcome-section">
                <div>
                  <span className="eyebrow">
                    CENTRO DE OPERACIONES
                  </span>

                  <h1>Bienvenido a RENT-TOOLS</h1>

                  <p>
                    Bienvenido a RENT-TOOLS. Aquí puedes consultar
                    el estado general de las operaciones.
                  </p>
                </div>

                <div className="welcome-date">
                  <span>FECHA DE REFERENCIA</span>
                  <strong>{currentDate}</strong>
                </div>
              </section>

              {dashboardError && (
                <div className="empty-state">
                  No fue posible cargar la información:
                  {' '}{dashboardError}
                </div>
              )}

              <section className="metrics-grid">
                <article className="metric-card">
                  <div className="metric-top">
                    <span className="metric-label">
                      Total de herramientas
                    </span>
                    <span className="metric-icon blue">🔧</span>
                  </div>

                  <strong className="metric-value">
                    {dashboardLoading
                      ? '...'
                      : dashboard?.metrics?.total_tools ?? 0}
                  </strong>

                  <span className="metric-description">
                    Herramientas registradas
                  </span>
                </article>

                <article className="metric-card">
                  <div className="metric-top">
                    <span className="metric-label">
                      Disponibles
                    </span>
                    <span className="metric-icon green">✓</span>
                  </div>

                  <strong className="metric-value">
                    {dashboardLoading
                      ? '...'
                      : dashboard?.metrics?.available_tools ?? 0}
                  </strong>

                  <span className="metric-description">
                    Disponibles para alquiler
                  </span>
                </article>

                <article className="metric-card">
                  <div className="metric-top">
                    <span className="metric-label">
                      Alquileres activos
                    </span>
                    <span className="metric-icon orange">▤</span>
                  </div>

                  <strong className="metric-value">
                    {dashboardLoading
                      ? '...'
                      : dashboard?.metrics?.active_rentals ?? 0}
                  </strong>

                  <span className="metric-description">
                    Alquileres actualmente activos
                  </span>
                </article>

                <article className="metric-card">
                  <div className="metric-top">
                    <span className="metric-label">
                      Clientes registrados
                    </span>
                    <span className="metric-icon purple">♙</span>
                  </div>

                  <strong className="metric-value">
                    {dashboardLoading
                      ? '...'
                      : dashboard?.metrics?.registered_customers ?? 0}
                  </strong>

                  <span className="metric-description">
                    Clientes en el sistema
                  </span>
                </article>
              </section>

              <section className="dashboard-grid">
                <article className="panel activity-panel">
                  <div className="panel-header">
                    <div>
                      <h2>Actividad reciente</h2>
                      <p>Últimos movimientos de alquileres</p>
                    </div>

                    <button
                      type="button"
                      className="secondary-button"
                      onClick={() => setActivePage('rentals')}
                    >
                      Ver alquileres
                    </button>
                  </div>

                  <div className="table-search">
                    <input
                      type="search"
                      placeholder="Buscar por ID de alquiler o cliente..."
                      value={search}
                      onChange={(event) => setSearch(event.target.value)}
                    />
                  </div>

                  <div className="table-wrapper">
                    <table>
                      <thead>
                        <tr>
                          <th>ID ALQUILER</th>
                          <th>CLIENTE</th>
                          <th>FECHA DE ALQUILER</th>
                          <th>FECHA DE DEVOLUCIÓN</th>
                          <th>UNIDADES</th>
                          <th>ESTADO</th>
                        </tr>
                      </thead>

                      <tbody>
                        {dashboardLoading ? (
                          <tr>
                            <td colSpan="6" className="empty-state">
                              Cargando actividad reciente...
                            </td>
                          </tr>
                        ) : filteredActivities.map((rental) => (
                          <tr key={rental.id}>
                            <td className="table-code">
                              {rental.id}
                            </td>

                            <td>{rental.customer || '—'}</td>

                            <td>
                              {formatDate(rental.rental_date)}
                            </td>

                            <td>
                              {formatDate(rental.due_date)}
                            </td>

                            <td>{rental.units ?? 0}</td>

                            <td>
                              <span
                                className={`status-badge ${formatStatusClass(
                                  rental.status,
                                )}`}
                              >
                                {formatStatus(rental.status)}
                              </span>
                            </td>
                          </tr>
                        ))}

                        {!dashboardLoading &&
                          filteredActivities.length === 0 && (
                            <tr>
                              <td colSpan="6" className="empty-state">
                                {dashboardError
                                  ? 'No se pudo cargar la actividad.'
                                  : 'No se encontraron alquileres.'}
                              </td>
                            </tr>
                          )}
                      </tbody>
                    </table>
                  </div>
                </article>

                <article className="panel inventory-panel">
                  <div className="panel-header">
                    <div>
                      <h2>Inventario</h2>
                      <p>Distribución de herramientas</p>
                    </div>
                  </div>

                  <div className="inventory-total">
                    <strong>
                      {dashboardLoading ? '...' : inventoryTotal}
                    </strong>
                    <span>Herramientas en total</span>
                  </div>

                  <div className="inventory-bar">
                    <span
                      className="available-bar"
                      style={{
                        width: `${availablePercentage}%`,
                      }}
                    ></span>

                    <span
                      className="rented-bar"
                      style={{
                        width: `${pendingReturnPercentage}%`,
                      }}
                    ></span>
                  </div>

                  <div className="inventory-legend">
                    <div>
                      <span className="legend-dot available-dot"></span>
                      <span>Disponibles</span>
                      <strong>
                        {dashboardLoading ? '...' : inventoryAvailable}
                      </strong>
                    </div>

                    <div>
                      <span className="legend-dot rented-dot"></span>
                      <span>Unidades pendientes de devolución</span>
                      <strong>
                        {dashboardLoading
                          ? '...'
                          : inventoryPendingReturn}
                      </strong>
                    </div>
                  </div>

                  <button
                    type="button"
                    className="primary-button full-button"
                    onClick={() => setActivePage('tools')}
                  >
                    Gestionar herramientas
                  </button>
                </article>
              </section>

              <section className="quick-actions">
                <div>
                  <h2>Accesos rápidos</h2>
                  <p>
                    Accede directamente a las operaciones principales.
                  </p>
                </div>

                <div className="quick-actions-grid">
                  <button
                    type="button"
                    onClick={() => setActivePage('tools')}
                  >
                    <span>🔧</span>
                    <strong>Herramientas</strong>
                    <small>Consultar inventario</small>
                  </button>

                  <button
                    type="button"
                    onClick={() => setActivePage('customers')}
                  >
                    <span>♙</span>
                    <strong>Clientes</strong>
                    <small>Gestionar clientes</small>
                  </button>

                  <button
                    type="button"
                    onClick={() => setActivePage('rentals')}
                  >
                    <span>▤</span>
                    <strong>Alquileres</strong>
                    <small>Registrar alquileres</small>
                  </button>

                  <button
                    type="button"
                    onClick={() => setActivePage('returns')}
                  >
                    <span>↩</span>
                    <strong>Devoluciones</strong>
                    <small>Gestionar devoluciones</small>
                  </button>
                </div>
              </section>
            </>
          ) : activePage === 'tools' ? (
            <Tools />
          ) : activePage === 'categories' && isAdministrator ? (
            <Categories />
          ) : activePage === 'customers' ? (
            <Customers />
          ) : activePage === 'advisors' ? (
            <Advisors />
          ) : activePage === 'rentals' ? (
            <Rentals />
          ) : activePage === 'returns' ? (
            <Returns />
          ) : (
            <section className="module-placeholder">
              <span className="module-icon">
                {activeNavigation?.icon}
              </span>

              <span className="eyebrow">MÓDULO RENT-TOOLS</span>

              <h1>{activeNavigation?.label}</h1>

              <p>
                Este módulo está preparado para su implementación.
                Aquí construiremos las funciones de gestión correspondientes
                y su conexión con la API.
              </p>

              <button
                type="button"
                className="primary-button"
                onClick={() => setActivePage('dashboard')}
              >
                Volver al dashboard
              </button>
            </section>
          )}

          <footer className="page-footer">
            <span>© 2026 RENT-TOOLS</span>
            <span>
              Sistema de gestión de alquiler de herramientas
            </span>
          </footer>
        </div>
      </main>
    </div>
  )
}

export default App
