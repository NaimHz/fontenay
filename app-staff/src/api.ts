// Client de l'API staff (authentifiée par JWT).
const API = (import.meta.env.VITE_API_URL as string | undefined) ?? 'http://localhost:8000'
const TOKEN_KEY = 'fontenay_token'

export function getToken(): string | null {
  return localStorage.getItem(TOKEN_KEY)
}
export function setToken(token: string): void {
  localStorage.setItem(TOKEN_KEY, token)
}
export function clearToken(): void {
  localStorage.removeItem(TOKEN_KEY)
}

export class ApiError extends Error {
  readonly status: number
  readonly body: unknown

  constructor(status: number, body: unknown) {
    super('Erreur API')
    this.status = status
    this.body = body
  }
}

async function request<T>(path: string, init: RequestInit = {}): Promise<T> {
  const token = getToken()
  const res = await fetch(`${API}${path}`, {
    ...init,
    headers: {
      'Content-Type': 'application/json',
      ...(token ? { Authorization: `Bearer ${token}` } : {}),
      ...(init.headers ?? {}),
    },
  })
  const body = res.status === 204 ? null : await res.json().catch(() => null)
  if (!res.ok) throw new ApiError(res.status, body)
  return body as T
}

export type StaffUser = {
  id: number
  email: string
  fullName: string
  roles: string[]
  establishment: { id: number; name: string; slug: string } | null
}

export type TableStatus = 'free' | 'reserved' | 'occupied'

export type Table = {
  id: number
  number: string
  seats: number
  status: TableStatus
}

export type Reservation = {
  id: number
  customerName: string
  partySize: number
  service: string
  date: string
  allergies: string | null
  specialRequest: string | null
  status: string
  table: { id: number; number: string } | null
}

export async function login(email: string, password: string): Promise<void> {
  const { token } = await request<{ token: string }>('/api/login', {
    method: 'POST',
    body: JSON.stringify({ email, password }),
  })
  setToken(token)
}

export const getMe = () => request<StaffUser>('/api/me')
export const getTables = () => request<Table[]>('/api/tables')
export const getReservations = () => request<Reservation[]>('/api/reservations')

export const updateTableStatus = (id: number, status: TableStatus) =>
  request<Table>(`/api/tables/${id}`, { method: 'PATCH', body: JSON.stringify({ status }) })

export const seatReservation = (id: number, tableId: number) =>
  request<Reservation>(`/api/reservations/${id}/seat`, {
    method: 'POST',
    body: JSON.stringify({ tableId }),
  })
