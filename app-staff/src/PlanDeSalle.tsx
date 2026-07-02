import { useCallback, useEffect, useState } from 'react'
import { getReservations, getTables, seatReservation } from './api'
import type { Reservation, Table } from './api'

type View = 'jour' | 'semaine'

/** Plage lundi → dimanche de la semaine en cours (format AAAA-MM-JJ). */
function weekRange(): { from: string; to: string } {
  const now = new Date()
  const offset = (now.getDay() + 6) % 7 // 0 = lundi
  const monday = new Date(now)
  monday.setDate(now.getDate() - offset)
  const sunday = new Date(monday)
  sunday.setDate(monday.getDate() + 6)
  const fmt = (d: Date) => d.toLocaleDateString('sv-SE') // AAAA-MM-JJ
  return { from: fmt(monday), to: fmt(sunday) }
}

function dateLabel(iso: string): string {
  return new Date(`${iso}T00:00:00`).toLocaleDateString('fr-FR', {
    weekday: 'long',
    day: 'numeric',
    month: 'long',
  })
}

export function PlanDeSalle({ onSelectTable }: { onSelectTable: (table: Table) => void }) {
  const [tables, setTables] = useState<Table[]>([])
  const [reservations, setReservations] = useState<Reservation[]>([])
  const [error, setError] = useState<string | null>(null)
  const [view, setView] = useState<View>('jour')
  const [seatChoice, setSeatChoice] = useState<Record<number, number>>({})

  const refresh = useCallback(async () => {
    try {
      const [t, r] = await Promise.all([
        getTables(),
        view === 'semaine' ? getReservations(weekRange()) : getReservations(),
      ])
      setTables(t)
      setReservations(r)
      setError(null)
    } catch {
      setError('Connexion à l’API perdue.')
    }
  }, [view])

  // Rafraîchissement automatique toutes les 3 s (temps réel).
  useEffect(() => {
    refresh()
    const id = setInterval(refresh, 3000)
    return () => clearInterval(id)
  }, [refresh])

  const freeTables = tables.filter((t) => t.status === 'free')

  async function seat(reservationId: number) {
    const tableId = seatChoice[reservationId] ?? freeTables[0]?.id
    if (!tableId) return
    await seatReservation(reservationId, tableId)
    refresh()
  }

  function reservationRow(r: Reservation) {
    return (
      <div key={r.id} className="card" style={{ padding: 12 }}>
        <div className="row" style={{ justifyContent: 'space-between', gap: 12 }}>
          <div>
            <strong>{r.customerName}</strong>{' '}
            <span className="muted">· {r.partySize} couv. · {r.service}</span>
            {r.allergies && (
              <div style={{ marginTop: 6 }}>
                <span className="badge badge--allergy">⚠ {r.allergies}</span>
              </div>
            )}
          </div>
          <div className="row" style={{ gap: 8 }}>
            {r.table ? (
              <span className="badge">Table {r.table.number}</span>
            ) : (
              <>
                <select
                  className="select"
                  style={{ width: 'auto' }}
                  value={seatChoice[r.id] ?? freeTables[0]?.id ?? ''}
                  onChange={(e) => setSeatChoice((s) => ({ ...s, [r.id]: Number(e.target.value) }))}
                >
                  {freeTables.map((t) => (
                    <option key={t.id} value={t.id}>
                      Table {t.number} ({t.seats}p)
                    </option>
                  ))}
                </select>
                <button
                  type="button"
                  className="btn btn--primary"
                  onClick={() => seat(r.id)}
                  disabled={freeTables.length === 0}
                >
                  Installer
                </button>
              </>
            )}
          </div>
        </div>
      </div>
    )
  }

  // Vue semaine : réservations groupées par jour.
  const byDate: Record<string, Reservation[]> = {}
  for (const r of reservations) {
    ;(byDate[r.date] ??= []).push(r)
  }

  return (
    <div className="container" style={{ paddingBlock: 24 }}>
      {error && <div className="badge badge--allergy" style={{ marginBottom: 12 }}>{error}</div>}

      <span className="eyebrow">Plan de salle</span>
      <div
        style={{
          display: 'grid',
          gridTemplateColumns: 'repeat(auto-fill, minmax(110px, 1fr))',
          gap: 12,
          margin: '12px 0 32px',
        }}
      >
        {tables.map((t) => (
          <button key={t.id} type="button" className={`table-card is-${t.status}`} onClick={() => onSelectTable(t)}>
            <span className="num">{t.number}</span>
            <span className="seats">{t.seats} couv.</span>
            {t.server && <span className="seats" style={{ color: 'var(--gold)' }}>{t.server}</span>}
          </button>
        ))}
      </div>

      <div className="row" style={{ justifyContent: 'space-between', marginBottom: 12 }}>
        <span className="eyebrow">Planning · {view === 'jour' ? "aujourd'hui" : 'cette semaine'}</span>
        <div className="row" style={{ gap: 6 }}>
          <button
            type="button"
            className={view === 'jour' ? 'btn btn--gold' : 'btn btn--ghost'}
            onClick={() => setView('jour')}
          >
            Jour
          </button>
          <button
            type="button"
            className={view === 'semaine' ? 'btn btn--gold' : 'btn btn--ghost'}
            onClick={() => setView('semaine')}
          >
            Semaine
          </button>
        </div>
      </div>

      {reservations.length === 0 && <p className="muted">Aucune réservation sur cette période.</p>}

      {view === 'jour' ? (
        <div className="stack" style={{ gap: 8 }}>{reservations.map(reservationRow)}</div>
      ) : (
        <div className="stack" style={{ gap: 20 }}>
          {Object.keys(byDate)
            .sort()
            .map((date) => (
              <div key={date} className="stack" style={{ gap: 8 }}>
                <span className="muted" style={{ textTransform: 'capitalize' }}>{dateLabel(date)}</span>
                {byDate[date].map(reservationRow)}
              </div>
            ))}
        </div>
      )}
    </div>
  )
}
