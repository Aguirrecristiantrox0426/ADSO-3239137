
import { useEffect, useMemo, useState } from 'react'
import { api } from '../../services/api'
import './Customers.css'

const emptyForm = {
  document: '',
  name: '',
  email: '',
  phone: '',
}

const mapCustomer = (customer) => ({
  id: customer.id,
  document: customer.identification_number ?? '',
  name: customer.full_name ?? '',
  email: customer.email ?? '',
  phone: customer.phone ?? '',
  status: 'Activo',
})

const formatError = (error) => {
  if (error?.errors) {
    const messages = Object.values(error.errors).flat()
    if (messages.length) return messages.join('\n')
  }

  return error?.message || 'Ocurrió un error inesperado.'
}

function Customers() {
  const [customers, setCustomers] = useState([])
  const [loading, setLoading] = useState(true)
  const [saving, setSaving] = useState(false)
  const [error, setError] = useState('')
  const [search, setSearch] = useState('')
  const [statusFilter, setStatusFilter] = useState('Todos')
  const [showForm, setShowForm] = useState(false)
  const [selectedCustomer, setSelectedCustomer] = useState(null)
  const [editingCustomer, setEditingCustomer] = useState(null)
  const [form, setForm] = useState(emptyForm)
  const [currentPage, setCurrentPage] = useState(1)
  const [lastPage, setLastPage] = useState(1)
  const [totalCustomers, setTotalCustomers] = useState(0)
  const [notification, setNotification] = useState('')

  const loadCustomers = async (page = currentPage) => {
    try {
      setLoading(true)
      setError('')

      const token = localStorage.getItem('rent-tools-token')
      const response = await api(`/customers?page=${page}`, { token })

      const records = Array.isArray(response.data) ? response.data : []

      setCustomers(records.map(mapCustomer))
      setCurrentPage(response.meta?.current_page ?? page)
      setLastPage(response.meta?.last_page ?? 1)
      setTotalCustomers(response.meta?.total ?? records.length)
    } catch (requestError) {
      setError(formatError(requestError))
    } finally {
      setLoading(false)
    }
  }

  useEffect(() => {
    loadCustomers(1)
  }, [])

  const filteredCustomers = useMemo(() => {
    return customers.filter((customer) => {
      const searchText = [
        customer.document,
        customer.name,
        customer.email,
        customer.phone,
      ].join(' ').toLowerCase()

      const matchesSearch = searchText.includes(search.toLowerCase())
      const matchesStatus =
        statusFilter === 'Todos' || customer.status === statusFilter

      return matchesSearch && matchesStatus
    })
  }, [customers, search, statusFilter])

  const activeCustomers = totalCustomers
  const inactiveCustomers = 0

  const handleChange = (event) => {
    const { name, value } = event.target

    setForm((previous) => ({
      ...previous,
      [name]: value,
    }))
  }

  const openCreateForm = () => {
    setEditingCustomer(null)
    setForm(emptyForm)
    setError('')
    setShowForm(true)
  }

  const openEditForm = (customer) => {
    setEditingCustomer(customer)
    setForm({
      document: customer.document,
      name: customer.name,
      email: customer.email,
      phone: customer.phone,
    })
    setError('')
    setShowForm(true)
    setSelectedCustomer(null)
  }

  const handleSubmit = async (event) => {
    event.preventDefault()

    const normalizedDocument = form.document.trim()
    const normalizedEmail = form.email.trim().toLowerCase()

    if (!normalizedDocument || !form.name.trim()) {
      setError('El número de identificación y el nombre son obligatorios.')
      return
    }

    const duplicateDocument = customers.some(
      (customer) =>
        customer.document === normalizedDocument &&
        customer.id !== editingCustomer?.id,
    )

    if (duplicateDocument) {
      setError('Ya existe un cliente con ese número de identificación.')
      return
    }

    const customerData = {
      identification_number: normalizedDocument,
      full_name: form.name.trim(),
      email: normalizedEmail || null,
      phone: form.phone.trim() || null,
    }

    try {
      setSaving(true)
      setError('')

      const token = localStorage.getItem('rent-tools-token')

      if (editingCustomer) {
        await api(`/customers/${editingCustomer.id}`, {
          token,
          method: 'PUT',
          body: JSON.stringify(customerData),
        })
        setNotification('Cliente actualizado correctamente.')
      } else {
        await api('/customers', {
          token,
          method: 'POST',
          body: JSON.stringify(customerData),
        })
        setNotification('Cliente registrado correctamente.')
      }

      setForm(emptyForm)
      setEditingCustomer(null)
      setShowForm(false)
      await loadCustomers(1)
    } catch (requestError) {
      setError(formatError(requestError))
    } finally {
      setSaving(false)
    }
  }

  const handleDelete = async (customer) => {
    const confirmed = window.confirm(
      `¿Deseas eliminar al cliente ${customer.name}?`,
    )

    if (!confirmed) return

    try {
      setError('')
      const token = localStorage.getItem('rent-tools-token')

      await api(`/customers/${customer.id}`, {
        token,
        method: 'DELETE',
      })

      setSelectedCustomer(null)
      setNotification('Cliente eliminado correctamente.')
      await loadCustomers(currentPage)
    } catch (requestError) {
      setError(formatError(requestError))
    }
  }

  const handleReactivate = async (customer) => {
    const confirmed = window.confirm(
      `¿Deseas reactivar al cliente ${customer.name}?`,
    )

    if (!confirmed) return

    try {
      setError('')
      const token = localStorage.getItem('rent-tools-token')

      await api(`/customers/${customer.id}/reactivate`, {
        token,
        method: 'PATCH',
      })

      setSelectedCustomer(null)
      setNotification('Cliente reactivado correctamente.')
      await loadCustomers(1)
    } catch (requestError) {
      setError(formatError(requestError))
    }
  }

  const closeForm = () => {
    setShowForm(false)
    setEditingCustomer(null)
    setForm(emptyForm)
    setError('')
  }

  return (
    <section className="customers-page">
      <div className="customers-heading">
        <div>
          <span className="eyebrow">GESTIÓN DE CLIENTES</span>
          <h1>Clientes</h1>
          <p>
            Administra la información de los clientes registrados en
            RENT-TOOLS.
          </p>
        </div>

        <button className="primary-button" onClick={openCreateForm}>
          <span>＋</span> Registrar cliente
        </button>
      </div>

      {notification && (
        <div className="customers-notice" role="status">
          {notification}
          <button onClick={() => setNotification('')} aria-label="Cerrar aviso">
            ×
          </button>
        </div>
      )}

      {error && (
        <div className="customers-error" role="alert">
          {error}
        </div>
      )}

      <div className="customers-metrics">
        <article className="customers-metric">
          <span>Total de clientes</span>
          <strong>{totalCustomers}</strong>
          <small>Registros disponibles en el sistema</small>
        </article>

        <article className="customers-metric">
          <span>Clientes activos</span>
          <strong className="customer-green">{activeCustomers}</strong>
          <small>Clientes devueltos por la consulta normal</small>
        </article>

        <article className="customers-metric">
          <span>Clientes inactivos</span>
          <strong className="customer-orange">{inactiveCustomers}</strong>
          <small>La API no incluye los eliminados en esta consulta</small>
        </article>
      </div>

      <div className="customers-panel">
        <div className="customers-panel-header">
          <div>
            <h2>Directorio de clientes</h2>
            <p>Consulta y administra la información de tus clientes.</p>
          </div>

          <span className="customers-count">
            {filteredCustomers.length} resultados en esta página
          </span>
        </div>

        <div className="customers-filters">
          <input
            type="search"
            placeholder="Buscar por identificación, nombre, correo o teléfono..."
            value={search}
            onChange={(event) => setSearch(event.target.value)}
          />

          <select
            value={statusFilter}
            onChange={(event) => setStatusFilter(event.target.value)}
          >
            <option value="Todos">Todos los estados</option>
            <option value="Activo">Activos</option>
          </select>
        </div>

        <div className="customers-table-wrapper">
          <table className="customers-table">
            <thead>
              <tr>
                <th>IDENTIFICACIÓN</th>
                <th>CLIENTE</th>
                <th>CONTACTO</th>
                <th>ESTADO</th>
                <th>ACCIONES</th>
              </tr>
            </thead>

            <tbody>
              {loading ? (
                <tr>
                  <td colSpan="5" className="customers-empty">
                    Cargando clientes...
                  </td>
                </tr>
              ) : filteredCustomers.length === 0 ? (
                <tr>
                  <td colSpan="5" className="customers-empty">
                    No se encontraron clientes.
                  </td>
                </tr>
              ) : (
                filteredCustomers.map((customer) => (
                  <tr key={customer.id}>
                    <td className="customer-document">
                      {customer.document}
                    </td>
                    <td>
                      <strong className="customer-name">
                        {customer.name}
                      </strong>
                      <span className="customer-email">
                        {customer.email || 'Sin correo'}
                      </span>
                    </td>
                    <td>{customer.phone || 'Sin teléfono'}</td>
                    <td>
                      <span className="customer-status activo">Activo</span>
                    </td>
                    <td>
                      <button
                        className="customer-view-button"
                        onClick={() => setSelectedCustomer(customer)}
                      >
                        Ver detalle
                      </button>
                    </td>
                  </tr>
                ))
              )}
            </tbody>
          </table>
        </div>

        <div className="customers-pagination">
          <button
            className="secondary-button"
            disabled={loading || currentPage <= 1}
            onClick={() => loadCustomers(currentPage - 1)}
          >
            Anterior
          </button>

          <span>
            Página {currentPage} de {lastPage}
          </span>

          <button
            className="secondary-button"
            disabled={loading || currentPage >= lastPage}
            onClick={() => loadCustomers(currentPage + 1)}
          >
            Siguiente
          </button>
        </div>
      </div>

      {showForm && (
        <div className="customers-modal-overlay">
          <div className="customers-modal">
            <div className="customers-modal-header">
              <div>
                <h2>
                  {editingCustomer ? 'Editar cliente' : 'Registrar cliente'}
                </h2>
                <p>
                  {editingCustomer
                    ? 'Actualiza la información del cliente.'
                    : 'Ingresa los datos del nuevo cliente.'}
                </p>
              </div>

              <button
                className="customer-close"
                onClick={closeForm}
                aria-label="Cerrar formulario"
              >
                ×
              </button>
            </div>

            <form onSubmit={handleSubmit}>
              <label>
                Número de identificación
                <input
                  name="document"
                  value={form.document}
                  onChange={handleChange}
                  placeholder="Ej. 1075300123"
                  required
                />
              </label>

              <label>
                Nombre completo
                <input
                  name="name"
                  value={form.name}
                  onChange={handleChange}
                  placeholder="Nombre y apellidos"
                  required
                />
              </label>

              <div className="customers-form-row">
                <label>
                  Correo electrónico
                  <input
                    name="email"
                    type="email"
                    value={form.email}
                    onChange={handleChange}
                    placeholder="correo@ejemplo.com"
                  />
                </label>

                <label>
                  Teléfono
                  <input
                    name="phone"
                    type="tel"
                    value={form.phone}
                    onChange={handleChange}
                    placeholder="3001234567"
                  />
                </label>
              </div>

              <div className="customers-modal-actions">
                <button
                  type="button"
                  className="secondary-button"
                  onClick={closeForm}
                  disabled={saving}
                >
                  Cancelar
                </button>

                <button
                  type="submit"
                  className="primary-button"
                  disabled={saving}
                >
                  {saving
                    ? 'Guardando...'
                    : editingCustomer
                      ? 'Guardar cambios'
                      : 'Registrar cliente'}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}

      {selectedCustomer && (
        <div className="customers-modal-overlay">
          <div className="customers-modal">
            <div className="customers-modal-header">
              <div>
                <h2>Detalle del cliente</h2>
                <p>Información del registro seleccionado.</p>
              </div>

              <button
                className="customer-close"
                onClick={() => setSelectedCustomer(null)}
                aria-label="Cerrar detalle"
              >
                ×
              </button>
            </div>

            <div className="customer-detail-list">
              <div>
                <span>Identificación</span>
                <strong>{selectedCustomer.document}</strong>
              </div>
              <div>
                <span>Nombre completo</span>
                <strong>{selectedCustomer.name}</strong>
              </div>
              <div>
                <span>Correo electrónico</span>
                <strong>{selectedCustomer.email || 'No registrado'}</strong>
              </div>
              <div>
                <span>Teléfono</span>
                <strong>{selectedCustomer.phone || 'No registrado'}</strong>
              </div>
              <div>
                <span>Estado</span>
                <strong>Activo</strong>
              </div>
            </div>

            <div className="customers-modal-actions">
              <button
                className="secondary-button"
                onClick={() => openEditForm(selectedCustomer)}
              >
                Editar información
              </button>

              <button
                className="secondary-button"
                onClick={() => handleDelete(selectedCustomer)}
              >
                Eliminar cliente
              </button>

              <button
                className="primary-button"
                onClick={() => setSelectedCustomer(null)}
              >
                Cerrar
              </button>
            </div>
          </div>
        </div>
      )}
    </section>
  )
}

export default Customers