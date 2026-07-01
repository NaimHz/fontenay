import { useEffect, useState } from 'react'
import type { FormEvent } from 'react'
import {
  ApiError,
  checkAvailability,
  createReservation,
  getEstablishments,
} from './api'
import type { Availability, Establishment, ReservationResult } from './api'
import { formatDate, serviceLabel } from './lib/format'

type Errors = Record<string, string>

export function ReservationForm({ onDone }: { onDone: (result: ReservationResult) => void }) {
  const [establishments, setEstablishments] = useState<Establishment[]>([])
  const [establishmentId, setEstablishmentId] = useState('')
  const [date, setDate] = useState('')
  const [service, setService] = useState<'midi' | 'soir'>('soir')
  const [partySize, setPartySize] = useState(2)
  const [customerName, setName] = useState('')
  const [customerEmail, setEmail] = useState('')
  const [customerPhone, setPhone] = useState('')
  const [allergies, setAllergies] = useState('')
  const [specialRequest, setSpecialRequest] = useState('')

  const [availability, setAvailability] = useState<Availability | null>(null)
  const [errors, setErrors] = useState<Errors>({})
  const [globalError, setGlobalError] = useState<string | null>(null)
  const [submitting, setSubmitting] = useState(false)

  useEffect(() => {
    getEstablishments()
      .then((list) => {
        setEstablishments(list)
        if (list[0]) setEstablishmentId(String(list[0].id))
      })
      .catch(() => setGlobalError("Impossible de charger les établissements. L'API est-elle lancée ?"))
  }, [])

  const canCheck = establishmentId !== '' && date !== '' && partySize > 0

  async function onCheck() {
    setAvailability(null)
    setGlobalError(null)
    try {
      setAvailability(
        await checkAvailability({ establishmentId: Number(establishmentId), date, service, partySize }),
      )
    } catch {
      setGlobalError('Vérification de la disponibilité impossible.')
    }
  }

  async function onSubmit(event: FormEvent) {
    event.preventDefault()
    setErrors({})
    setGlobalError(null)
    setSubmitting(true)
    try {
      const result = await createReservation({
        establishmentId: Number(establishmentId),
        date,
        service,
        partySize,
        customerName,
        customerEmail,
        customerPhone,
        allergies: allergies || undefined,
        specialRequest: specialRequest || undefined,
      })
      onDone(result)
    } catch (error) {
      if (error instanceof ApiError && error.status === 422) {
        const body = error.body as { errors?: Errors }
        setErrors(body.errors ?? {})
      } else if (error instanceof ApiError && error.status === 409) {
        setGlobalError('Une réservation existe déjà pour ce service avec cet email.')
      } else {
        setGlobalError("L'envoi de la réservation a échoué.")
      }
    } finally {
      setSubmitting(false)
    }
  }

  return (
    <form className="card" onSubmit={onSubmit}>
      <div className="stack">
        <div className="field">
          <label>Établissement</label>
          <select
            className="select"
            value={establishmentId}
            onChange={(e) => setEstablishmentId(e.target.value)}
          >
            {establishments.map((e) => (
              <option key={e.id} value={e.id}>
                {e.name} — {e.city}
              </option>
            ))}
          </select>
        </div>

        <div className="row" style={{ gap: 12, alignItems: 'flex-start' }}>
          <div className="field" style={{ flex: 1 }}>
            <label>Date</label>
            <input className="input" type="date" value={date} onChange={(e) => setDate(e.target.value)} />
          </div>
          <div className="field" style={{ flex: 1 }}>
            <label>Service</label>
            <select
              className="select"
              value={service}
              onChange={(e) => setService(e.target.value as 'midi' | 'soir')}
            >
              <option value="midi">{serviceLabel('midi')}</option>
              <option value="soir">{serviceLabel('soir')}</option>
            </select>
          </div>
          <div className="field" style={{ width: 120 }}>
            <label>Couverts</label>
            <input
              className="input"
              type="number"
              min={1}
              value={partySize}
              onChange={(e) => setPartySize(Number(e.target.value))}
            />
          </div>
        </div>

        <div className="row">
          <button type="button" className="btn btn--gold" onClick={onCheck} disabled={!canCheck}>
            Vérifier la disponibilité
          </button>
          {availability && availability.available && (
            <span className="muted">{availability.remaining} couverts disponibles ✓</span>
          )}
          {availability && !availability.available && (
            <span className="badge badge--allergy">Service complet — liste d'attente</span>
          )}
        </div>

        <div className="row" style={{ gap: 12, alignItems: 'flex-start' }}>
          <div className="field" style={{ flex: 1 }}>
            <label>Nom</label>
            <input className="input" value={customerName} onChange={(e) => setName(e.target.value)} />
            {errors.customerName && <span className="badge badge--allergy">{errors.customerName}</span>}
          </div>
          <div className="field" style={{ flex: 1 }}>
            <label>Téléphone</label>
            <input className="input" value={customerPhone} onChange={(e) => setPhone(e.target.value)} />
            {errors.customerPhone && <span className="badge badge--allergy">{errors.customerPhone}</span>}
          </div>
        </div>

        <div className="field">
          <label>Email</label>
          <input className="input" type="email" value={customerEmail} onChange={(e) => setEmail(e.target.value)} />
          {errors.customerEmail && <span className="badge badge--allergy">{errors.customerEmail}</span>}
        </div>

        <div className="field">
          <label>Allergies et régime alimentaire</label>
          <input
            className="input"
            placeholder="ex. arachides, sans gluten"
            value={allergies}
            onChange={(e) => setAllergies(e.target.value)}
          />
        </div>

        <div className="field">
          <label>Demande particulière</label>
          <textarea
            className="textarea"
            value={specialRequest}
            onChange={(e) => setSpecialRequest(e.target.value)}
          />
        </div>

        {globalError && <div className="badge badge--allergy">{globalError}</div>}

        <button className="btn btn--primary btn--lg btn--block" type="submit" disabled={submitting}>
          {submitting ? 'Envoi…' : `Réserver pour le ${date ? formatDate(date) : '…'}`}
        </button>
      </div>
    </form>
  )
}
