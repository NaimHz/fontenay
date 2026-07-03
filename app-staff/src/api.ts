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

// Établissement courant (utile pour le propriétaire, multi-sites). Ajouté aux
// requêtes ; ignoré par l'API pour les utilisateurs rattachés à un établissement.
let currentEstablishmentId: number | null = null
export function setEstablishment(id: number | null): void {
  currentEstablishmentId = id
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
  if (currentEstablishmentId !== null) {
    path += (path.includes('?') ? '&' : '?') + `establishmentId=${currentEstablishmentId}`
  }
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
  server: string | null
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

export type StaffEstablishment = { id: number; name: string }
export const getEstablishments = () => request<StaffEstablishment[]>('/api/public/establishments')

export const getTables = () => request<Table[]>('/api/tables')

/** Planning : sans argument = aujourd'hui ; avec { from, to } = plage (vue semaine). */
export const getReservations = (params?: { from: string; to: string }) => {
  const query = params ? `?from=${params.from}&to=${params.to}` : ''
  return request<Reservation[]>(`/api/reservations${query}`)
}

export const updateTableStatus = (id: number, status: TableStatus) =>
  request<Table>(`/api/tables/${id}`, { method: 'PATCH', body: JSON.stringify({ status }) })

export const seatReservation = (id: number, tableId: number) =>
  request<Reservation>(`/api/reservations/${id}/seat`, {
    method: 'POST',
    body: JSON.stringify({ tableId }),
  })

export type DishCategory = 'entree' | 'plat' | 'dessert' | 'boisson' | 'digestif'

export type Dish = {
  id: number
  name: string
  category: DishCategory
  price: string
  allergens: string | null
}

export type OrderItem = {
  id: number
  dishId: number
  name: string
  category: string
  quantity: number
  seatNumber: number | null
  status: string
  unitPrice: string
}

export type Order = {
  id: number
  tableId: number
  tableNumber: string
  status: string
  total: string
  items: OrderItem[]
}

export const getDishes = () => request<Dish[]>('/api/dishes')

export const getActiveOrder = (tableId: number) =>
  request<Order | null>(`/api/orders?tableId=${tableId}`)

export const createOrder = (tableId: number, items: { dishId: number; quantity: number }[]) =>
  request<Order>('/api/orders', { method: 'POST', body: JSON.stringify({ tableId, items }) })

export const closeOrder = (orderId: number) =>
  request<Order>(`/api/orders/${orderId}/close`, { method: 'POST' })

export type OrderItemStatus = 'pending' | 'in_preparation' | 'served'

export type KitchenOrder = {
  id: number
  tableNumber: string
  sentAt: string | null
  allergies: string | null
  items: OrderItem[]
}

export const getKitchenOrders = () => request<KitchenOrder[]>('/api/kitchen/orders')

export const updateOrderItem = (id: number, status: OrderItemStatus) =>
  request<OrderItem>(`/api/order-items/${id}`, {
    method: 'PATCH',
    body: JSON.stringify({ status }),
  })
