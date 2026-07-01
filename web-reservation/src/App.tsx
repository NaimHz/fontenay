// Vitrine du design system (thème sombre + or). L'écran de réservation réel
// est livré au lot suivant. Le style vient entièrement de @ui/theme.css.
function App() {
  return (
    <div className="container" style={{ paddingBlock: 32 }}>
      <header className="row" style={{ justifyContent: 'space-between', marginBottom: 24 }}>
        <span className="brand">Fontenay</span>
        <span className="eyebrow">Réservation en ligne</span>
      </header>

      <div className="panel-gold" style={{ marginBottom: 24 }}>
        <h1 style={{ margin: 0 }}>Réserver une table</h1>
        <p style={{ margin: 0 }}>Le Clos Fontenay · Le Cellier Fontenay</p>
      </div>

      <div className="card">
        <div className="stack">
          <div className="field">
            <label>Nom</label>
            <input className="input" placeholder="Votre nom" />
          </div>
          <div className="field">
            <label>Allergies et régime</label>
            <input className="input" placeholder="ex. arachides, sans gluten" />
          </div>
          <div className="row">
            <span className="badge badge--allergy">⚠ Arachides</span>
          </div>
          <div className="row">
            <button className="btn btn--primary">Vérifier la disponibilité</button>
            <button className="btn btn--ghost">Annuler</button>
          </div>
        </div>
      </div>
    </div>
  )
}

export default App
