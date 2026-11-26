
import { useCallback, useEffect, useMemo, useRef, useState } from 'react'
import './Tools.css'
import { api } from '../../services/api'
import { useAuth } from '../../context/AuthContext'

const emptyForm = {
  name: '',
  description: '',
  category_id: '',
  total_quantity: '1',
}

function getToolStatus(tool) {
  const total = Number(tool.total_quantity ?? 0)
  const available = Number(tool.available_quantity ?? 0)

  if (available <= 0) return 'Agotada'
  if (available >= total) return 'Disponible'
  return 'Parcialmente disponible'
}

function getErrorMessage(error) {
  if (error?.errors && typeof error.errors === 'object') {
    const messages = Object.values(error.errors).flat()
    if (messages.length) return messages.join(' ')
  }

  return error?.message || 'Ocurrió un error al procesar la solicitud.'
}

function Tools() {
  const { user, logout } = useAuth()
  const logoutRef = useRef(logout)

  const [tools, setTools] = useState([])
  const [categories, setCategories] = useState([])
  const [meta, setMeta] = useState({
    current_page: 1,
    last_page: 1,
    total: 0,
  })

  const [page, setPage] = useState(1)
  const [search, setSearch] = useState('')
  const [categoryFilter, setCategoryFilter] = useState('Todas')
  const [statusFilter, setStatusFilter] = useState('Todos')

  const [loading, setLoading] = useState(true)
  const [saving, setSaving] = useState(false)
  const [error, setError] = useState('')
  const [categoryWarning, setCategoryWarning] = useState('')

  const [showForm, setShowForm] = useState(false)
  const [editingTool, setEditingTool] = useState(null)
  const [selectedTool, setSelectedTool] = useState(null)
  const [form, setForm] = useState(emptyForm)

  const token = localStorage.getItem('rent-tools-token')
  const isAdministrator = user?.role === 'administrator'

  useEffect(() => {
    logoutRef.current = logout
  }, [logout])

  const loadData = useCallback(async () => {
    setLoading(true)
    setError('')

    try {
      const [toolsResult, categoriesResult] = await Promise.allSettled([
        api(`/tools?page=${page}`, {
          token,
          onUnauthorized: () => logoutRef.current?.(),
        }),
        api('/categories', {
          token,
          onUnauthorized: () => logoutRef.current?.(),
        }),
      ])

      if (toolsResult.status === 'rejected') {
        throw toolsResult.reason
      }

      const toolsResponse = toolsResult.value
      setTools(Array.isArray(toolsResponse?.data) ? toolsResponse.data : [])
      setMeta({
        current_page: toolsResponse?.meta?.current_page ?? page,
        last_page: toolsResponse?.meta?.last_page ?? 1,
        total: toolsResponse?.meta?.total ?? 0,
      })

      if (categoriesResult.status === 'fulfilled') {
        const categoryData = categoriesResult.value?.data
        setCategories(Array.isArray(categoryData) ? categoryData : [])
        setCategoryWarning('')
      } else {
        setCategories([])
        setCategoryWarning(
          'No fue posible cargar las categorías. Puedes continuar registrando herramientas sin categoría.',
        )
      }
    } catch (requestError) {
      setError(getErrorMessage(requestError))
      setTools([])
      setMeta({ current_page: 1, last_page: 1, total: 0 })
    } finally {
      setLoading(false)
    }
  }, [page, token])

  useEffect(() => {
    loadData()
  }, [loadData])

  const filteredTools = useMemo(() => {
    return tools.filter((tool) => {
      const categoryName = tool.category?.name || ''
      const status = getToolStatus(tool)

      const matchesSearch = [
        tool.id,
        tool.name,
        tool.description,
        categoryName,
      ]
        .join(' ')
        .toLowerCase()
        .includes(search.trim().toLowerCase())

      const matchesCategory =
        categoryFilter === 'Todas' ||
        String(tool.category_id ?? '') === categoryFilter

      const matchesStatus =
        statusFilter === 'Todos' || status === statusFilter

      return matchesSearch && matchesCategory && matchesStatus
    })
  }, [tools, search, categoryFilter, statusFilter])

  const totalUnits = tools.reduce(
    (total, tool) => total + Number(tool.total_quantity || 0),
    0,
  )

  const availableUnits = tools.reduce(
    (total, tool) => total + Number(tool.available_quantity || 0),
    0,
  )

  const unavailableUnits = Math.max(totalUnits - availableUnits, 0)

  const handleFormChange = (event) => {
    const { name, value } = event.target

    setForm((previous) => ({
      ...previous,
      [name]: value,
    }))
  }

  const openCreateForm = () => {
    setEditingTool(null)
    setForm(emptyForm)
    setError('')
    setShowForm(true)
  }

  const openEditForm = (tool) => {
    setEditingTool(tool)
    setForm({
      name: tool.name || '',
      description: tool.description || '',
      category_id: tool.category_id ? String(tool.category_id) : '',
      total_quantity: String(tool.total_quantity ?? 1),
    })
    setError('')
    setSelectedTool(null)
    setShowForm(true)
  }

  const closeForm = () => {
    if (saving) return
    setShowForm(false)
    setEditingTool(null)
    setForm(emptyForm)
  }

  const handleSubmit = async (event) => {
    event.preventDefault()
    setError('')

    const totalQuantity = Number(form.total_quantity)

    if (!form.name.trim()) {
      setError('El nombre de la herramienta es obligatorio.')
      return
    }

    if (!Number.isInteger(totalQuantity) || totalQuantity < 1) {
      setError('La cantidad total debe ser un número entero mayor que cero.')
      return
    }

    const payload = {
      name: form.name.trim(),
      description: form.description.trim() || null,
      category_id: form.category_id ? Number(form.category_id) : null,
      total_quantity: totalQuantity,
    }

    setSaving(true)

    try {
      if (editingTool) {
        await api(`/tools/${editingTool.id}`, {
          method: 'PUT',
          token,
          onUnauthorized: () => logoutRef.current?.(),
          body: JSON.stringify(payload),
        })
      } else {
        await api('/tools', {
          method: 'POST',
          token,
          onUnauthorized: () => logoutRef.current?.(),
          body: JSON.stringify(payload),
        })
      }

      setShowForm(false)
      setEditingTool(null)
      setForm(emptyForm)
      await loadData()
    } catch (requestError) {
      setError(getErrorMessage(requestError))
    } finally {
      setSaving(false)
    }
  }

  const handleDelete = async (tool) => {
    const confirmed = window.confirm(
      `¿Estás seguro de que deseas eliminar la herramienta "${tool.name}"?`,
    )

    if (!confirmed) return

    setError('')

    try {
      await api(`/tools/${tool.id}`, {
        method: 'DELETE',
        token,
        onUnauthorized: () => logoutRef.current?.(),
      })

      if (tools.length === 1 && page > 1) {
        setPage((currentPage) => currentPage - 1)
      } else {
        await loadData()
      }

      setSelectedTool(null)
    } catch (requestError) {
      setError(getErrorMessage(requestError))
    }
  }

  const changePage = (nextPage) => {
    if (nextPage < 1 || nextPage > meta.last_page) return
    setPage(nextPage)
  }

  return (
    <section className="tools-page">
      <div className="tools-heading">
        <div>
          <span className="eyebrow">GESTIÓN DE INVENTARIO</span>
          <h1>Herramientas</h1>
          <p>
            Administra el inventario, consulta la disponibilidad
            y registra los equipos de alquiler.
          </p>
        </div>

        <button className="primary-button" onClick={openCreateForm}>
          <span>＋</span> Registrar herramienta
        </button>
      </div>

      {error && (
        <div className="tools-alert tools-alert-error" role="alert">
          {error}
        </div>
      )}

      {categoryWarning && (
        <div className="tools-alert tools-alert-warning" role="status">
          {categoryWarning}
        </div>
      )}

      <div className="tools-metrics">
        <article className="tools-metric">
          <span>Total de unidades</span>
          <strong>{totalUnits}</strong>
          <small>Unidades registradas en esta página</small>
        </article>

        <article className="tools-metric">
          <span>Disponibles</span>
          <strong className="green-text">{availableUnits}</strong>
          <small>Unidades disponibles para alquilar</small>
        </article>

        <article className="tools-metric">
          <span>No disponibles</span>
          <strong className="orange-text">{unavailableUnits}</strong>
          <small>Unidades que no están disponibles</small>
        </article>

        <article className="tools-metric">
          <span>Referencias</span>
          <strong>{meta.total}</strong>
          <small>Herramientas registradas</small>
        </article>
      </div>

      <div className="tools-panel">
        <div className="tools-panel-header">
          <div>
            <h2>Inventario registrado</h2>
            <p>Consulta y administra las herramientas del sistema.</p>
          </div>

          <span className="results-count">
            {filteredTools.length} resultados en esta página
          </span>
        </div>

        <div className="tools-filters">
          <input
            type="search"
            placeholder="Buscar por nombre, descripción o categoría..."
            value={search}
            onChange={(event) => setSearch(event.target.value)}
          />

          <select
            value={categoryFilter}
            onChange={(event) => setCategoryFilter(event.target.value)}
          >
            <option value="Todas">Todas las categorías</option>
            {categories.map((category) => (
              <option key={category.id} value={String(category.id)}>
                {category.name}
              </option>
            ))}
          </select>

          <select
            value={statusFilter}
            onChange={(event) => setStatusFilter(event.target.value)}
          >
            <option value="Todos">Todos los estados</option>
            <option value="Disponible">Disponible</option>
            <option value="Parcialmente disponible">
              Parcialmente disponible
            </option>
            <option value="Agotada">Agotada</option>
          </select>
        </div>

        <div className="tools-table-wrapper">
          <table className="tools-table">
            <thead>
              <tr>
                <th>ID</th>
                <th>HERRAMIENTA</th>
                <th>CATEGORÍA</th>
                <th>DISPONIBILIDAD</th>
                <th>ESTADO</th>
                <th>ACCIONES</th>
              </tr>
            </thead>

            <tbody>
              {loading ? (
                <tr>
                  <td colSpan="6" className="tools-empty">
                    Cargando herramientas...
                  </td>
                </tr>
              ) : filteredTools.length > 0 ? (
                filteredTools.map((tool) => {
                  const status = getToolStatus(tool)
                  const statusClass =
                    status === 'Disponible'
                      ? 'disponible'
                      : status === 'Agotada'
                        ? 'agotada'
                        : 'parcialmente-disponible'

                  return (
                    <tr key={tool.id}>
                      <td className="tool-code">#{tool.id}</td>
                      <td className="tool-name">{tool.name}</td>
                      <td>{tool.category?.name || 'Sin categoría'}</td>
                      <td>
                        <strong>
                          {tool.available_quantity} / {tool.total_quantity}
                        </strong>
                        <span className="availability-label">
                          unidades disponibles
                        </span>
                      </td>
                      <td>
                        <span className={`tool-status ${statusClass}`}>
                          {status}
                        </span>
                      </td>
                      <td>
                        <button
                          className="view-tool-button"
                          onClick={() => setSelectedTool(tool)}
                        >
                          Ver detalle
                        </button>
                      </td>
                    </tr>
                  )
                })
              ) : (
                <tr>
                  <td colSpan="6" className="tools-empty">
                    {tools.length === 0
                      ? 'Todavía no hay herramientas registradas.'
                      : 'No se encontraron herramientas con esos filtros.'}
                  </td>
                </tr>
              )}
            </tbody>
          </table>
        </div>

        <div className="tools-pagination">
          <span>
            Página {meta.current_page} de {meta.last_page}
          </span>

          <div>
            <button
              type="button"
              className="secondary-button"
              disabled={loading || page <= 1}
              onClick={() => changePage(page - 1)}
            >
              Anterior
            </button>
            <button
              type="button"
              className="secondary-button"
              disabled={loading || page >= meta.last_page}
              onClick={() => changePage(page + 1)}
            >
              Siguiente
            </button>
          </div>
        </div>
      </div>

      {showForm && (
        <div className="tools-modal-overlay">
          <div className="tools-modal">
            <div className="tools-modal-header">
              <div>
                <h2>
                  {editingTool ? 'Editar herramienta' : 'Registrar herramienta'}
                </h2>
                <p>
                  {editingTool
                    ? 'Actualiza la información de la herramienta.'
                    : 'Ingresa la información de la nueva herramienta.'}
                </p>
              </div>

              <button
                className="modal-close"
                onClick={closeForm}
                aria-label="Cerrar formulario"
                disabled={saving}
              >
                ×
              </button>
            </div>

            <form onSubmit={handleSubmit}>
              <label>
                Nombre de la herramienta
                <input
                  name="name"
                  value={form.name}
                  onChange={handleFormChange}
                  placeholder="Ej. Taladro percutor"
                  maxLength="255"
                  required
                />
              </label>

              <label>
                Descripción
                <textarea
                  name="description"
                  value={form.description}
                  onChange={handleFormChange}
                  placeholder="Describe las características de la herramienta"
                  rows="3"
                />
              </label>

              <label>
                Categoría
                <select
                  name="category_id"
                  value={form.category_id}
                  onChange={handleFormChange}
                >
                  <option value="">Sin categoría</option>
                  {categories.map((category) => (
                    <option key={category.id} value={String(category.id)}>
                      {category.name}
                    </option>
                  ))}
                </select>
              </label>

              <label>
                Cantidad total
                <input
                  name="total_quantity"
                  type="number"
                  min="1"
                  step="1"
                  value={form.total_quantity}
                  onChange={handleFormChange}
                  required
                />
              </label>

              {error && (
                <div className="tools-alert tools-alert-error" role="alert">
                  {error}
                </div>
              )}

              <div className="tools-modal-actions">
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
                    : editingTool
                      ? 'Guardar cambios'
                      : 'Registrar herramienta'}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}

      {selectedTool && (
        <div className="tools-modal-overlay">
          <div className="tools-modal">
            <div className="tools-modal-header">
              <div>
                <h2>Detalle de herramienta</h2>
                <p>Información consultada desde el inventario.</p>
              </div>

              <button
                className="modal-close"
                onClick={() => setSelectedTool(null)}
                aria-label="Cerrar detalle"
              >
                ×
              </button>
            </div>

            <div className="tool-detail-list">
              <div>
                <span>ID</span>
                <strong>#{selectedTool.id}</strong>
              </div>
              <div>
                <span>Nombre</span>
                <strong>{selectedTool.name}</strong>
              </div>
              <div>
                <span>Categoría</span>
                <strong>
                  {selectedTool.category?.name || 'Sin categoría'}
                </strong>
              </div>
              <div>
                <span>Descripción</span>
                <strong>{selectedTool.description || 'Sin descripción'}</strong>
              </div>
              <div>
                <span>Cantidad total</span>
                <strong>{selectedTool.total_quantity}</strong>
              </div>
              <div>
                <span>Unidades disponibles</span>
                <strong>{selectedTool.available_quantity}</strong>
              </div>
              <div>
                <span>Estado</span>
                <strong>{getToolStatus(selectedTool)}</strong>
              </div>
            </div>

            <div className="tools-modal-actions">
              <button
                type="button"
                className="secondary-button"
                onClick={() => openEditForm(selectedTool)}
              >
                Editar
              </button>

              {isAdministrator && (
                <button
                  type="button"
                  className="secondary-button"
                  onClick={() => handleDelete(selectedTool)}
                >
                  Eliminar
                </button>
              )}

              <button
                type="button"
                className="primary-button"
                onClick={() => setSelectedTool(null)}
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

export default Tools
