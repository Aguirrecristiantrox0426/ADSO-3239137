
import { useEffect, useMemo, useState } from 'react'
import { api } from '../../services/api'
import { useAuth } from '../../context/AuthContext'
import './Advisors.css'

const emptyForm = {
  document: '',
  name: '',
  email: '',
}

const mapAdvisor = (advisor) => ({
  id: advisor.id,
  userId: advisor.user_id ?? null,
  document: advisor.identification_number ?? '',
  name: advisor.full_name ?? '',
  email: advisor.email ?? '',
})

const getErrorMessage = (error) => {
  if (error?.errors) {
    const messages = Object.values(error.errors).flat()
    if (messages.length) return messages.join('\n')
  }

  return error?.message || 'No fue posible completar la operación.'
}

function Advisors() {
  const { user } = useAuth()
  const isAdministrator = user?.role === 'administrator'

  const [advisors, setAdvisors] = useState([])
  const [loading, setLoading] = useState(true)
  const [saving, setSaving] = useState(false)
  const [error, setError] = useState('')
  const [search, setSearch] = useState('')
  const [modal, setModal] = useState('')
  const [selectedAdvisor, setSelectedAdvisor] = useState(null)
  const [form, setForm] = useState(emptyForm)
  const [editingId, setEditingId] = useState(null)
  const [notification, setNotification] = useState('')
  const [currentPage, setCurrentPage] = useState(1)
  const [lastPage, setLastPage] = useState(1)
  const [totalAdvisors, setTotalAdvisors] = useState(0)

  const loadAdvisors = async (page = currentPage) => {
    try {
      setLoading(true)
      setError('')

      const token = localStorage.getItem('rent-tools-token')
      const response = await api(`/advisors?page=${page}`, { token })

      const records = Array.isArray(response.data) ? response.data : []

      setAdvisors(records.map(mapAdvisor))
      setCurrentPage(response.meta?.current_page ?? page)
      setLastPage(response.meta?.last_page ?? 1)
      setTotalAdvisors(response.meta?.total ?? records.length)
    } catch (requestError) {
      setError(getErrorMessage(requestError))
    } finally {
      setLoading(false)
    }
  }

  useEffect(() => {
    loadAdvisors(1)
  }, [])

  const filteredAdvisors = useMemo(() => {
    const term = search.trim().toLowerCase()

    return advisors.filter((advisor) => {
      const searchText = [
        advisor.document,
        advisor.name,
        advisor.email,
      ].join(' ').toLowerCase()

      return searchText.includes(term)
    })
  }, [advisors, search])

  const handleFormChange = (event) => {
    const { name, value } = event.target

    setForm((previous) => ({
      ...previous,
      [name]: value,
    }))
  }

  const openCreateModal = () => {
    if (!isAdministrator) return

    setForm(emptyForm)
    setEditingId(null)
    setError('')
    setModal('create')
  }

  const openEditModal = (advisor) => {
    if (!isAdministrator) return

    setForm({
      document: advisor.document,
      name: advisor.name,
      email: advisor.email,
    })

    setEditingId(advisor.id)
    setSelectedAdvisor(null)
    setError('')
    setModal('edit')
  }

  const handleSubmit = async (event) => {
    event.preventDefault()

    if (!isAdministrator || saving) return

    const document = form.document.trim()
    const name = form.name.trim()
    const email = form.email.trim().toLowerCase()

    if (!document || !name || !email) {
      setError('Todos los campos son obligatorios.')
      return
    }

    const duplicate = advisors.find(
      (advisor) =>
        advisor.id !== editingId &&
        (
          advisor.document === document ||
          advisor.email.toLowerCase() === email
        ),
    )

    if (duplicate) {
      setError('Ya existe un asesor con ese documento o correo.')
      return
    }

    const advisorData = {
      identification_number: document,
      full_name: name,
      email,
    }

    try {
      setSaving(true)
      setError('')

      const token = localStorage.getItem('rent-tools-token')

      if (editingId !== null) {
        await api(`/advisors/${editingId}`, {
          token,
          method: 'PUT',
          body: JSON.stringify(advisorData),
        })

        setNotification('Asesor actualizado correctamente.')
      } else {
        await api('/advisors', {
          token,
          method: 'POST',
          body: JSON.stringify(advisorData),
        })

        setNotification('Asesor registrado correctamente.')
      }

      setModal('')
      setForm(emptyForm)
      setEditingId(null)

      await loadAdvisors(1)
    } catch (requestError) {
      setError(getErrorMessage(requestError))
    } finally {
      setSaving(false)
    }
  }

  const handleDeactivate = async (advisor) => {
    if (!isAdministrator) return

    const confirmed = window.confirm(
      `¿Estás seguro de que deseas desactivar a ${advisor.name}?`,
    )

    if (!confirmed) return

    try {
      setError('')

      const token = localStorage.getItem('rent-tools-token')

      await api(`/advisors/${advisor.id}`, {
        token,
        method: 'DELETE',
      })

      setSelectedAdvisor(null)
      setModal('')
      setNotification('Asesor desactivado correctamente.')

      await loadAdvisors(currentPage)
    } catch (requestError) {
      setError(getErrorMessage(requestError))
    }
  }

  const closeModal = () => {
    setModal('')
    setSelectedAdvisor(null)
    setEditingId(null)
    setForm(emptyForm)
    setError('')
  }

  return (
    <div className="advisors-page">
      <div className="advisors-heading">
        <div>
          <span className="advisors-eyebrow">GESTIÓN DEL PERSONAL</span>
          <h1>Asesores</h1>
          <p>
            Consulta y administra la información de los asesores
            registrados en RENT-TOOLS.
          </p>
        </div>

        {isAdministrator && (
          <button
            className="advisor-primary-button"
            onClick={openCreateModal}
          >
            + Registrar asesor
          </button>
        )}
      </div>

      {notification && (
        <div className="customers-notice" role="status">
          {notification}
          <button
            onClick={() => setNotification('')}
            aria-label="Cerrar aviso"
          >
            ×
          </button>
        </div>
      )}

      {error && !modal && (
        <div className="customers-error" role="alert">
          {error}
        </div>
      )}

      <div className="advisors-stats">
        <article className="advisor-stat-card">
          <span>Total de asesores</span>
          <strong>{totalAdvisors}</strong>
        </article>

        <article className="advisor-stat-card">
          <span>Asesores consultados</span>
          <strong>{advisors.length}</strong>
        </article>

        <article className="advisor-stat-card">
          <span>Coincidencias</span>
          <strong>{filteredAdvisors.length}</strong>
        </article>
      </div>

      <section className="advisors-panel">
        <div className="advisors-toolbar">
          <div className="advisor-search">
            <span>⌕</span>
            <input
              type="search"
              placeholder="Buscar por documento, nombre o correo..."
              value={search}
              onChange={(event) => setSearch(event.target.value)}
            />
          </div>
        </div>

        <div className="advisors-table-wrapper">
          <table className="advisors-table">
            <thead>
              <tr>
                <th>ASESOR</th>
                <th>DOCUMENTO</th>
                <th>CORREO ELECTRÓNICO</th>
                <th>ACCIONES</th>
              </tr>
            </thead>

            <tbody>
              {loading ? (
                <tr>
                  <td colSpan="4" className="advisor-empty">
                    Cargando asesores...
                  </td>
                </tr>
              ) : filteredAdvisors.length > 0 ? (
                filteredAdvisors.map((advisor) => (
                  <tr key={advisor.id}>
                    <td>
                      <strong>{advisor.name}</strong>
                    </td>

                    <td>{advisor.document}</td>

                    <td>{advisor.email}</td>

                    <td>
                      <div className="advisor-actions">
                        <button
                          onClick={() => {
                            setSelectedAdvisor(advisor)
                            setModal('detail')
                          }}
                        >
                          Ver
                        </button>

                        {isAdministrator && (
                          <button
                            className="advisor-status-action"
                            onClick={() => handleDeactivate(advisor)}
                          >
                            Desactivar
                          </button>
                        )}
                      </div>
                    </td>
                  </tr>
                ))
              ) : (
                <tr>
                  <td colSpan="4" className="advisor-empty">
                    No se encontraron asesores.
                  </td>
                </tr>
              )}
            </tbody>
          </table>
        </div>

        <div className="advisors-table-footer">
          Mostrando {filteredAdvisors.length} de {totalAdvisors} asesores

          <div className="advisor-actions">
            <button
              disabled={loading || currentPage <= 1}
              onClick={() => loadAdvisors(currentPage - 1)}
            >
              Anterior
            </button>

            <span>
              Página {currentPage} de {lastPage}
            </span>

            <button
              disabled={loading || currentPage >= lastPage}
              onClick={() => loadAdvisors(currentPage + 1)}
            >
              Siguiente
            </button>
          </div>
        </div>
      </section>

      {(modal === 'create' || modal === 'edit') && (
        <div className="advisor-modal-overlay">
          <section className="advisor-modal">
            <div className="advisor-modal-heading">
              <div>
                <span className="advisors-eyebrow">DATOS DEL PERSONAL</span>

                <h2>
                  {modal === 'create' ? 'Registrar asesor' : 'Editar asesor'}
                </h2>
              </div>

              <button onClick={closeModal} aria-label="Cerrar">
                ×
              </button>
            </div>

            {error && (
              <div className="customers-error" role="alert">
                {error}
              </div>
            )}

            <form onSubmit={handleSubmit}>
              <div className="advisor-form-grid">
                <label>
                  Documento
                  <input
                    name="document"
                    value={form.document}
                    onChange={handleFormChange}
                    required
                  />
                </label>

                <label>
                  Nombre completo
                  <input
                    name="name"
                    value={form.name}
                    onChange={handleFormChange}
                    required
                  />
                </label>

                <label className="advisor-full-field">
                  Correo electrónico
                  <input
                    name="email"
                    type="email"
                    value={form.email}
                    onChange={handleFormChange}
                    required
                  />
                </label>
              </div>

              <div className="advisor-modal-actions">
                <button
                  type="button"
                  onClick={closeModal}
                  disabled={saving}
                >
                  Cancelar
                </button>

                <button
                  type="submit"
                  className="advisor-primary-button"
                  disabled={saving}
                >
                  {saving
                    ? 'Guardando...'
                    : modal === 'create'
                      ? 'Registrar asesor'
                      : 'Guardar cambios'}
                </button>
              </div>
            </form>
          </section>
        </div>
      )}

      {modal === 'detail' && selectedAdvisor && (
        <div className="advisor-modal-overlay">
          <section className="advisor-modal advisor-detail-modal">
            <div className="advisor-modal-heading">
              <div>
                <span className="advisors-eyebrow">
                  INFORMACIÓN DEL PERSONAL
                </span>
                <h2>Detalle del asesor</h2>
              </div>

              <button onClick={closeModal} aria-label="Cerrar">
                ×
              </button>
            </div>

            <div className="advisor-detail-profile">
              <div className="advisor-avatar">
                {selectedAdvisor.name.charAt(0).toUpperCase()}
              </div>

              <div>
                <h3>{selectedAdvisor.name}</h3>
                <span className="advisor-status is-active">
                  Registrado
                </span>
              </div>
            </div>

            <div className="advisor-detail-grid">
              <div>
                <span>Documento</span>
                <strong>{selectedAdvisor.document}</strong>
              </div>

              <div>
                <span>Correo</span>
                <strong>{selectedAdvisor.email}</strong>
              </div>

              <div>
                <span>Cuenta asociada</span>
                <strong>
                  {selectedAdvisor.userId !== null
                    ? 'Sí'
                    : 'No asociada'}
                </strong>
              </div>
            </div>

            <div className="advisor-modal-actions">
              <button onClick={closeModal}>
                Cerrar
              </button>

              {isAdministrator && (
                <>
                  <button
                    className="advisor-primary-button"
                    onClick={() => openEditModal(selectedAdvisor)}
                  >
                    Editar información
                  </button>

                  <button
                    className="advisor-status-action"
                    onClick={() => handleDeactivate(selectedAdvisor)}
                  >
                    Desactivar asesor
                  </button>
                </>
              )}
            </div>
          </section>
        </div>
      )}
    </div>
  )
}

export default Advisors
