const baseUrl = (import.meta.env.VITE_API_URL || '/api/v1').replace(/\/$/, '')

export class ApiError extends Error { constructor(message, status, errors = {}) { super(message); this.status = status; this.errors = errors } }

export async function api(path, { token, onUnauthorized, ...options } = {}) {
  const controller = new AbortController()
  const timeout = setTimeout(() => controller.abort(), 12000)
  try {
    const response = await fetch(`${baseUrl}${path}`, { ...options, signal: controller.signal, headers: { Accept: 'application/json', ...(options.body ? { 'Content-Type': 'application/json' } : {}), ...(token ? { Authorization: `Bearer ${token}` } : {}), ...options.headers } })
    const payload = await response.json().catch(() => null)
    if (!response.ok) { if (response.status === 401) onUnauthorized?.(); throw new ApiError(payload?.message ?? 'No fue posible completar la solicitud.', response.status, payload?.errors) }
    return payload
  } finally { clearTimeout(timeout) }
}
