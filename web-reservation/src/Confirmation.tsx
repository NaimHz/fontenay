import type { ReservationResult } from './api'
import { formatDate, serviceLabel } from './lib/format'

/** Écran de confirmation affiché après l'enregistrement d'une réservation. */
export function Confirmation({
  result,
  onReset,
}: {
  result: ReservationResult
  onReset: () => void
}) {
  const reference = `FR-${String(result.id).padStart(4, '0')}`

  return (
    <div className="stack">
      <div className="panel-gold">
        <span className="eyebrow" style={{ color: '#5a4420' }}>
          {result.waitlisted ? 'Liste d’attente' : 'Réservation confirmée'}
        </span>
        <h1 style={{ margin: '4px 0 0' }}>
          {result.waitlisted ? 'Vous êtes en liste d’attente' : 'C’est réservé, merci !'}
        </h1>
        <p style={{ margin: '4px 0 0' }}>{result.establishment}</p>
      </div>

      <div className="card">
        <div className="stack" style={{ gap: 12 }}>
          <div className="row" style={{ justifyContent: 'space-between' }}>
            <span className="muted">Référence</span>
            <strong className="price">{reference}</strong>
          </div>
          <div className="row" style={{ justifyContent: 'space-between' }}>
            <span className="muted">Date</span>
            <strong>{formatDate(result.date)}</strong>
          </div>
          <div className="row" style={{ justifyContent: 'space-between' }}>
            <span className="muted">Service</span>
            <strong>{serviceLabel(result.service as 'midi' | 'soir')}</strong>
          </div>
          <div className="row" style={{ justifyContent: 'space-between' }}>
            <span className="muted">Couverts</span>
            <strong>{result.partySize}</strong>
          </div>
        </div>
      </div>

      <p className="muted" style={{ margin: 0 }}>
        {result.waitlisted
          ? 'Le service est complet. Nous vous rappellerons en cas de désistement.'
          : 'Un email de confirmation vous sera envoyé. À très bientôt au Fontenay.'}
      </p>

      <button className="btn btn--ghost" type="button" onClick={onReset}>
        Nouvelle réservation
      </button>
    </div>
  )
}
