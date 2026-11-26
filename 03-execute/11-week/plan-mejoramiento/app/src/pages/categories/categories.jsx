
import { useCallback, useEffect, useRef, useState } from 'react'
import { api } from '../../services/api'
import { useAuth } from '../../context/AuthContext'
import './categories.css'

const emptyForm = {
  name: '',
  description: '',
}

function getErrorMessage(error) {
  if (error?.errors && typeof error.errors === 'object') {
    const messages = Object.values(error.errors).flat()
    if (messages.length) return messages.join(' ')
  }

  return error?.message || 'No fue posible completar la operación.'
}

function Categories() {
  const { logout } = useAuth()
  const logoutRef = useRef(logout)

  const [categories, setCategories] = useState([])
  const [loading, setLoading] = useState(true)
  const [saving, setSaving] = useState(false)
  const [error, setError] = useState('')
  const [search, setSearch] = useState('')
  const [showForm, setShowForm] = useState(false)
  const [editingCategory, setEditingCategory] = useState(null)
  const [form, setForm] = useState(emptyForm)

  const token = localStorage.getItem('rent-tools-token')

  useEffect(() => {
    logoutRef.current = logout
  }, [logout])

  const loadCategories = useCallback(async () => {
    setLoading(true)
    setError('')

    try {
      const response = await api('/categories', {
        token,
        onUnauthorized: () => logoutRef.current?.(),
      })

      setCategories(
        Array.isArray(response?.data) ? response.data : [],
      )
    } catch (requestError) {
      setError(getErrorMessage(requestError))
      setCategories([])
    } finally {
      setLoading(false)
    }
  }, [token])

  useEffect(() => {
    loadCategories()
  }, [loadCategories])

  const filteredCategories = categories.filter((category) =>
    `${category.name || ''} ${category.description || ''}`
      .toLowerCase()
      .includes(search.trim().toLowerCase()),
  )

  const openCreateForm = () => {
    setEditingCategory(null)
    setForm(emptyForm)
    setError('')
    setShowForm(true)
  }

  const openEditForm = (category) => {
    setEditingCategory(category)
    setForm({
      name: category.name || '',
      description: category.description || '',
    })
    setError('')
    setShowForm(true)
  }

  const closeForm = () => {
    if (saving) return
    setShowForm(false)
    setEditingCategory(null)
    setForm(emptyForm)
  }

  const handleChange = (event) => {
    const { name, value } = event.target
    setForm((previous) => ({
      ...previous,
      [name]: value,
    }))
  }

  const handleSubmit = async (event) => {
    event.preventDefault()
    setError('')

    if (!form.name.trim()) {
      setError('El nombre de la categoría es obligatorio.')
      return
    }

    const payload = {
      name: form.name.trim(),
      description: form.description.trim() || null,
    }

    setSaving(true)

    try {
      if (editingCategory) {
        await api(`/categories/${editingCategory.id}`, {
          method: 'PUT',
          token,
          onUnauthorized: () => logoutRef.current?.(),
          body: JSON.stringify(payload),
        })
      } else {
        await api('/categories', {
          method: 'POST',
          token,
          onUnauthorized: () => logoutRef.current?.(),
          body: JSON.stringify(payload),
        })
      }

      setShowForm(false)
      setEditingCategory(null)
      setForm(emptyForm)
      await loadCategories()
    } catch (requestError) {
      setError(getErrorMessage(requestError))
    } finally {
      setSaving(false)
    }
  }

  const handleDelete = async (category) => {
    const confirmed = window.confirm(
      `¿Deseas desactivar la categoría "${category.name}"?`,
    )

    if (!confirmed) return

    setError('')

    try {
      await api(`/categories/${category.id}`, {
        method: 'DELETE',
        token,
        onUnauthorized: () => logoutRef.current?.(),
      })

      await loadCategories()
    } catch (requestError) {
      setError(getErrorMessage(requestError))
    }
  }

  return (
    <section className="categories-page">
      <div className="categories-heading">
        <div>
          <span className="eyebrow">CONFIGURACIÓN DEL INVENTARIO</span>
          <h1>Categorías</h1>
          <p>
            Organiza las herramientas mediante categorías
            para facilitar su administración.
          </p>
        </div>

        <button
          type="button"
          className="primary-button"
          onClick={openCreateForm}
        >
          + Nueva categoría
        </button>
      </div>

      {error && (
        <div className="categories-alert" role="alert">
          {error}
        </div>
      )}

      <div className="categories-panel">
        <div className="categories-panel-header">
          <div>
            <h2>Categorías registradas</h2>
            <p>Información almacenada en la base de datos.</p>
          </div>

          <span>{categories.length} categorías</span>
        </div>

        <div className="categories-search">
          <input
            type="search"
            placeholder="Buscar categoría..."
            value={search}
            onChange={(event) => setSearch(event.target.value)}
          />
        </div>

        <div className="categories-table-wrapper">
          <table className="categories-table">
            <thead>
              <tr>
                <th>ID</th>
                <th>NOMBRE</th>
                <th>DESCRIPCIÓN</th>
                <th>ACCIONES</th>
              </tr>
            </thead>

            <tbody>
              {loading ? (
                <tr>
                  <td colSpan="4" className="categories-empty">
                    Cargando categorías...
                  </td>
                </tr>
              ) : filteredCategories.length > 0 ? (
                filteredCategories.map((category) => (
                  <tr key={category.id}>
                    <td>#{category.id}</td>
                    <td className="category-name">{category.name}</td>
                    <td>
                      {category.description || 'Sin descripción'}
                    </td>
                    <td>
                      <div className="category-actions">
                        <button
                          type="button"
                          className="category-edit-button"
                          onClick={() => openEditForm(category)}
                        >
                          Editar
                        </button>

                        <button
                          type="button"
                          className="category-delete-button"
                          onClick={() => handleDelete(category)}
                        >
                          Desactivar
                        </button>
                      </div>
                    </td>
                  </tr>
                ))
              ) : (
                <tr>
                  <td colSpan="4" className="categories-empty">
                    {categories.length === 0
                      ? 'Todavía no hay categorías registradas.'
                      : 'No se encontraron categorías.'}
                  </td>
                </tr>
              )}
            </tbody>
          </table>
        </div>
      </div>

      {showForm && (
        <div className="categories-modal-overlay">
          <div className="categories-modal">
            <div className="categories-modal-header">
              <div>
                <h2>
                  {editingCategory ? 'Editar categoría' : 'Nueva categoría'}
                </h2>
                <p>
                  Completa la información de la categoría.
                </p>
              </div>

              <button
                type="button"
                className="categories-close"
                onClick={closeForm}
                disabled={saving}
                aria-label="Cerrar"
              >
                ×
              </button>
            </div>

            <form onSubmit={handleSubmit}>
              <label>
                Nombre
                <input
                  type="text"
                  name="name"
                  value={form.name}
                  onChange={handleChange}
                  maxLength="255"
                  placeholder="Nombre de la categoría"
                  required
                />
              </label>

              <label>
                Descripción
                <textarea
                  name="description"
                  value={form.description}
                  onChange={handleChange}
                  rows="4"
                  placeholder="Descripción opcional"
                />
              </label>

              {error && (
                <div className="categories-alert" role="alert">
                  {error}
                </div>
              )}

              <div className="categories-modal-actions">
                <button
                  type="button"
                  className="category-cancel-button"
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
                    : editingCategory
                      ? 'Guardar cambios'
                      : 'Crear categoría'}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </section>
  )
}

export default Categories
