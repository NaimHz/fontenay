import { useCallback, useEffect, useState } from 'react'
import {
  getReservations,
  getTables,
  seatReservation,
  updateTableStatus,
} from './api'
import type { Reservation, Table } from './api'

export function PlanDeSalle() {
  const [tables, setTables] = useState<Table[]>([])
  const [reservations, setReservations] = useState<Reservation[]>([])
  const [error, setError] = useState<string | null>(null)

  const refresh = useCallback(async () => {
    try {
      const [t, r] = await Promise.all([getTables(), getReservations()])
      setTables(t)
      setReservations(r)
      setError(null)
    } catch {
      setError('Connexion à l’API perdue.')
    }
  }, [])

  // Rafraîchissement automatique toutes les 3 s (temps réel).
  useEffect(() => {
    refresh()
    const id = setInterval(refresh, 3000)
    return () => clearInterval(id)
  }, [refresh])

  const firstFreeTable = tables.find((t) => t.status === 'free')

  async function toggleTable(table: Table) {
    await updateTableStatus(table.id, table.status === 'free' ? 'occupied' : 'free')
    refresh()
  }

  async function seat(reservation: Reservation) {
    if (!firstFreeTable) return
    await seatReservation(reservation.id, firstFreeTable.id)
    refresh()
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
          <button key={t.id} type="button" className={`table-card is-${t.status}`} onClick={() => toggleTable(t)}>
            <span className="num">{t.number}</span>
            <span className="seats">{t.seats} couv.</span>
          </button>
        ))}
      </div>

      <span className="eyebrow">Planning du jour</span>
      <div className="stack" style={{ gap: 8, marginTop: 12 }}>
        {reservations.length === 0 && <p className="muted">Aucune réservation aujourd’hui.</p>}
        {reservations.map((r) => (
          <div key={r.id} className="card" style={{ padding: 12 }}>
            <div className="row" style={{ justifyContent: 'space-between' }}>
              <div>
                <strong>{r.customerName}</strong>{' '}
                <span className="muted">· {r.partySize} couv. · {r.service}</span>
                {r.allergies && (
                  <div style={{ marginTop: 6 }}>
                    <span className="badge badge--allergy">⚠ {r.allergies}</span>
                  </div>
                )}
              </div>
              <div>
                {r.table ? (
                  <span className="badge">Table {r.table.number}</span>
                ) : (
                  <button
                    type="button"
                    className="btn btn--primary"
                    onClick={() => seat(r)}
                    disabled={!firstFreeTable}
                  >
                    Installer{firstFreeTable ? ` (T${firstFreeTable.number})` : ''}
                  </button>
                )}
              </div>
            </div>
          </div>
        ))}
      </div>
    </div>
  )
}
