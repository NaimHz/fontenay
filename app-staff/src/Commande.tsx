import { useEffect, useMemo, useState } from 'react'
import { closeOrder, createOrder, getActiveOrder, getDishes } from './api'
import type { Dish, DishCategory, Order, Table } from './api'
import { formatPrice } from './lib/format'

const CATEGORIES: { value: DishCategory; label: string }[] = [
  { value: 'entree', label: 'Entrée' },
  { value: 'plat', label: 'Plats' },
  { value: 'dessert', label: 'Desserts' },
  { value: 'boisson', label: 'Boissons' },
  { value: 'digestif', label: 'Digestifs' },
]

export function Commande({ table, onBack }: { table: Table; onBack: () => void }) {
  const [dishes, setDishes] = useState<Dish[]>([])
  const [activeOrder, setActiveOrder] = useState<Order | null>(null)
  const [category, setCategory] = useState<DishCategory>('entree')
  const [cart, setCart] = useState<Record<number, number>>({})
  const [busy, setBusy] = useState(false)
  const [error, setError] = useState<string | null>(null)

  useEffect(() => {
    getDishes().then(setDishes).catch(() => setError('Chargement de la carte impossible.'))
    getActiveOrder(table.id).then(setActiveOrder).catch(() => undefined)
  }, [table.id])

  const dishById = useMemo(() => new Map(dishes.map((d) => [d.id, d])), [dishes])
  const visible = dishes.filter((d) => d.category === category)
  const lines = Object.entries(cart).filter(([, q]) => q > 0)
  const total = lines.reduce(
    (sum, [id, q]) => sum + parseFloat(dishById.get(Number(id))?.price ?? '0') * q,
    0,
  )

  function setQty(dishId: number, delta: number) {
    setCart((c) => ({ ...c, [dishId]: Math.max(0, (c[dishId] ?? 0) + delta) }))
  }

  async function send() {
    setBusy(true)
    setError(null)
    try {
      await createOrder(
        table.id,
        lines.map(([id, q]) => ({ dishId: Number(id), quantity: q })),
      )
      onBack()
    } catch {
      setError("L'envoi en cuisine a échoué.")
    } finally {
      setBusy(false)
    }
  }

  async function close() {
    if (!activeOrder) return
    setBusy(true)
    try {
      await closeOrder(activeOrder.id)
      onBack()
    } catch {
      setError('La clôture a échoué.')
    } finally {
      setBusy(false)
    }
  }

  return (
    <div className="container" style={{ paddingBlock: 24 }}>
      <div className="row" style={{ justifyContent: 'space-between', marginBottom: 16 }}>
        <div className="row">
          <button type="button" className="btn btn--ghost" onClick={onBack}>
            ← Plan de salle
          </button>
          <h2 style={{ margin: 0 }}>Table {table.number}</h2>
        </div>
        {activeOrder && (
          <button type="button" className="btn btn--danger" onClick={close} disabled={busy}>
            ✕ Facture &amp; clôture
          </button>
        )}
      </div>

      {error && <div className="badge badge--allergy" style={{ marginBottom: 12 }}>{error}</div>}

      {/* Onglets de catégories */}
      <div className="row" style={{ gap: 8, flexWrap: 'wrap', marginBottom: 16 }}>
        {CATEGORIES.map((c) => (
          <button
            key={c.value}
            type="button"
            className={c.value === category ? 'btn btn--gold' : 'btn btn--ghost'}
            onClick={() => setCategory(c.value)}
          >
            {c.label}
          </button>
        ))}
      </div>

      {/* Cartes plats */}
      <div
        style={{
          display: 'grid',
          gridTemplateColumns: 'repeat(auto-fill, minmax(200px, 1fr))',
          gap: 12,
        }}
      >
        {visible.map((d) => {
          const qty = cart[d.id] ?? 0
          return (
            <div key={d.id} className={qty > 0 ? 'dish-card is-selected' : 'dish-card'}>
              <div className="row" style={{ justifyContent: 'space-between' }}>
                <strong>{d.name}</strong>
                <span className="price">{formatPrice(d.price)}</span>
              </div>
              {d.allergens && <span className="badge badge--allergy">⚠ {d.allergens}</span>}
              <div className="row" style={{ justifyContent: 'space-between', marginTop: 4 }}>
                <button type="button" className="btn btn--ghost" onClick={() => setQty(d.id, -1)}>
                  −
                </button>
                <strong>{qty}</strong>
                <button type="button" className="btn btn--primary" onClick={() => setQty(d.id, 1)}>
                  +
                </button>
              </div>
            </div>
          )
        })}
      </div>

      {/* Récapitulatif */}
      <div className="card" style={{ marginTop: 24 }}>
        <h3 style={{ marginTop: 0 }}>Récap de la commande</h3>
        {lines.length === 0 && <p className="muted">Aucun plat sélectionné.</p>}
        <div className="stack" style={{ gap: 6 }}>
          {lines.map(([id, q]) => {
            const dish = dishById.get(Number(id))
            return (
              <div key={id} className="row" style={{ justifyContent: 'space-between' }}>
                <span>
                  {dish?.name} <span className="muted">×{q}</span>
                </span>
                <span className="price">{formatPrice(parseFloat(dish?.price ?? '0') * q)}</span>
              </div>
            )
          })}
        </div>
        <div className="row" style={{ justifyContent: 'space-between', marginTop: 12 }}>
          <strong>Total</strong>
          <strong className="price">{formatPrice(total)}</strong>
        </div>
        <button
          type="button"
          className="btn btn--primary btn--lg btn--block"
          style={{ marginTop: 12 }}
          onClick={send}
          disabled={busy || lines.length === 0}
        >
          {busy ? 'Envoi…' : 'Envoyer en cuisine'}
        </button>
      </div>
    </div>
  )
}
