// Client de l'API publique de réservation.
const API = (import.meta.env.VITE_API_URL as string | undefined) ?? 'http://localhost:8000'

export type Establishment = {
  id: number
  name: string
  slug: string
  city: string
  capacity: number
}

export type Availability = {
  available: boolean
  capacity: number
  booked: number
  remaining: number
}

export type ReservationResult = {
  id: number
  status: string
  waitlisted: boolean
  establishment: string
  date: string
  service: string
  partySize: number
}

export type ReservationInput = {
  establishmentId: number
  date: string
  service: 'midi' | 'soir'
  partySize: number
  customerName: string
  customerEmail: string
  customerPhone: string
  allergies?: string
  specialRequest?: string
}

/** Erreur d'API porteuse du statut HTTP et du corps de réponse. */
export class ApiError extends Error {
  readonly status: number
  readonly body: unknown

  constructor(status: number, body: unknown) {
    super('Erreur API')
    this.status = status
    this.body = body
  }
}

export async function getEstablishments(): Promise<Establishment[]> {
  const res = await fetch(`${API}/api/public/establishments`)
  if (!res.ok) throw new ApiError(res.status, await res.json().catch(() => null))
  return res.json()
}

export async function checkAvailability(params: {
  establishmentId: number
  date: string
  service: string
  partySize: number
}): Promise<Availability> {
  const query = new URLSearchParams({
    establishmentId: String(params.establishmentId),
    date: params.date,
    service: params.service,
    partySize: String(params.partySize),
  })
  const res = await fetch(`${API}/api/public/availability?${query}`)
  if (!res.ok) throw new ApiError(res.status, await res.json().catch(() => null))
  return res.json()
}

export async function createReservation(input: ReservationInput): Promise<ReservationResult> {
  const res = await fetch(`${API}/api/public/reservations`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(input),
  })
  const body = await res.json().catch(() => null)
  if (!res.ok) throw new ApiError(res.status, body)
  return body as ReservationResult
}
