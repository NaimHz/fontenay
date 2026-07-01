// Vitrine du design system côté app staff (plan de salle + prise de commande).
// Les écrans réels (login, salle, commande, cuisine) sont livrés aux lots suivants.
const tables = [
  { number: '01', seats: 2, status: 'reserved' },
  { number: '02', seats: 4, status: 'free' },
  { number: '03', seats: 4, status: 'occupied' },
  { number: '04', seats: 2, status: 'free' },
] as const

function App() {
  return (
    <div>
      <header className="app-header">
        <span className="brand">Fontenay</span>
        <nav className="app-nav">
          <a className="is-active" href="#">Plan de salle</a>
          <a href="#">Planning</a>
          <a href="#">Cuisine</a>
        </nav>
      </header>

      <div className="container" style={{ paddingBlock: 24 }}>
        <span className="eyebrow">Plan de salle</span>
        <div
          style={{
            display: 'grid',
            gridTemplateColumns: 'repeat(auto-fill, minmax(110px, 1fr))',
            gap: 12,
            marginTop: 12,
          }}
        >
          {tables.map((t) => (
            <div key={t.number} className={`table-card is-${t.status}`}>
              <span className="num">{t.number}</span>
              <span className="seats">{t.seats} couv.</span>
            </div>
          ))}
        </div>

        <div className="row" style={{ marginTop: 24, gap: 16 }}>
          <div className="dish-card is-selected" style={{ width: 180 }}>
            <strong>Tomates Mozza</strong>
            <span className="price">12,50 €</span>
            <span className="badge badge--allergy">⚠ Lait</span>
          </div>
          <div className="dish-card" style={{ width: 180 }}>
            <strong>Poulet Curry</strong>
            <span className="price">24,00 €</span>
          </div>
        </div>

        <div className="row" style={{ marginTop: 24 }}>
          <button className="btn btn--primary btn--lg">+ Ajout de commande</button>
          <button className="btn btn--danger btn--lg">✕ Facture &amp; clôture</button>
        </div>
      </div>
    </div>
  )
}

export default App
