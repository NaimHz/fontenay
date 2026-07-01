import { useState } from 'react'
import { ReservationForm } from './ReservationForm'
import { Confirmation } from './Confirmation'
import type { ReservationResult } from './api'

function App() {
  const [result, setResult] = useState<ReservationResult | null>(null)

  return (
    <div className="container" style={{ paddingBlock: 32, maxWidth: 720 }}>
      <header className="row" style={{ justifyContent: 'space-between', marginBottom: 24 }}>
        <span className="brand">Fontenay</span>
        <span className="eyebrow">Réservation en ligne</span>
      </header>

      {result ? (
        <Confirmation result={result} onReset={() => setResult(null)} />
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
