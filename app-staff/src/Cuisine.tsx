import { useCallback, useEffect, useState } from 'react'
import { getKitchenOrders, updateOrderItem } from './api'
import type { KitchenOrder, OrderItemStatus } from './api'

export function Cuisine() {
  const [orders, setOrders] = useState<KitchenOrder[]>([])
  const [error, setError] = useState<string | null>(null)

  const refresh = useCallback(async () => {
    try {
      setOrders(await getKitchenOrders())
      setError(null)
    } catch {
      setError('Connexion à l’API perdue.')
    }
  }, [])

  // Fil de la cuisine rafraîchi automatiquement toutes les 3 s.
  useEffect(() => {
    refresh()
    const id = setInterval(refresh, 3000)
    return () => clearInterval(id)
  }, [refresh])

  async function advance(itemId: number, status: OrderItemStatus) {
    await updateOrderItem(itemId, status)
    refresh()
  }

  return (
    <div className="container" style={{ paddingBlock: 24 }}>
      {error && <div className="badge badge--allergy" style={{ marginBottom: 12 }}>{error}</div>}

      <span className="eyebrow">Cuisine — commandes en cours</span>
      {orders.length === 0 && (
        <p className="muted" style={{ marginTop: 12 }}>Aucune commande en attente.</p>
      )}

      <div
        style={{
          display: 'grid',
          gridTemplateColumns: 'repeat(auto-fill, minmax(400px, 1fr))',
          gap: 16,
          marginTop: 12,
        }}
      >
        {orders.map((o) => (
          <div key={o.id} className="card">
            <div className="row" style={{ justifyContent: 'space-between' }}>
              <h3 style={{ margin: 0 }}>Table {o.tableNumber}</h3>
              {o.sentAt && (
                <span className="muted">
                  {new Date(o.sentAt).toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' })}
                </span>
              )}
            </div>

            {o.allergies && (
              <div style={{ marginTop: 8 }}>
                <span className="badge badge--allergy">⚠ Allergies : {o.allergies}</span>
              </div>
            )}

            <div className="stack" style={{ gap: 8, marginTop: 12 }}>
              {o.items.map((it) => (
                <div key={it.id} className="row" style={{ justifyContent: 'space-between', gap: 8 }}>
                  <span>
                    {it.name} <span className="muted">×{it.quantity}</span>
                  </span>
                  {it.status === 'served' ? (
                    <span className="badge">✓ Servi</span>
                  ) : (
                    <div className="row" style={{ gap: 6 }}>
                      {it.status === 'pending' && (
                        <button
                          type="button"
                          className="btn btn--ghost"
                          onClick={() => advance(it.id, 'in_preparation')}
                        >
                          En prépa.
                        </button>
                      )}
                      <button
                        type="button"
                        className="btn btn--primary"
                        onClick={() => advance(it.id, 'served')}
                      >
                        Servi
                      </button>
                    </div>
                  )}
                </div>
              ))}
            </div>
          </div>
        ))}
      </div>
    </div>
  )
}
