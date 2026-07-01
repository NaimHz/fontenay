import { useState } from 'react'
import { ReservationForm } from './ReservationForm'
import type { ReservationResult } from './api'
import { formatDate, serviceLabel } from './lib/format'

function App() {
  const [result, setResult] = useState<ReservationResult | null>(null)

  return (
    <div className="container" style={{ paddingBlock: 32, maxWidth: 720 }}>
      <header className="row" style={{ justifyContent: 'space-between', marginBottom: 24 }}>
        <span className="brand">Fontenay</span>
        <span className="eyebrow">Réservation en ligne</span>
      </header>

      {result ? (
        <div className="stack">
          <div className="panel-gold">
            <h1 style={{ margin: 0 }}>Réservation enregistrée</h1>
            <p style={{ margin: 0 }}>{result.establishment}</p>
          </div>
          <div className="card">
            <p>
              {result.partySize} couverts · {serviceLabel(result.service as 'midi' | 'soir')} du{' '}
              {formatDate(result.date)}.
            </p>
            {result.waitlisted && (
              <p className="badge badge--allergy">
                Service complet : vous êtes en liste d'attente, nous vous rappellerons.
              </p>
            )}
            <button className="btn btn--ghost" type="button" onClick={() => setResult(null)}>
              Nouvelle réservation
            </button>
          </div>
        </div>
      ) : (
        <>
          <div className="panel-gold" style={{ marginBottom: 24 }}>
            <h1 style={{ margin: 0 }}>Réserver une table</h1>
            <p style={{ margin: 0 }}>Une cuisine gastronomique, au cœur de Lyon.</p>
          </div>
          <ReservationForm onDone={setResult} />
        </>
      )}
    </div>
  )
}

export default App
