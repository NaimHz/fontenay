import { useState } from 'react'
import { AuthProvider, useAuth } from './auth'
import type { Table } from './api'
import { Login } from './Login'
import { PlanDeSalle } from './PlanDeSalle'
import { Commande } from './Commande'

function Shell() {
  const { user, signOut } = useAuth()
  const [selectedTable, setSelectedTable] = useState<Table | null>(null)

  return (
    <div>
      <header className="app-header">
        <span className="brand">Fontenay</span>
        <nav className="app-nav">
          <a className="is-active" href="#">Plan de salle</a>
        </nav>
        <div className="row">
          <span className="muted">{user?.fullName}</span>
          <button className="btn btn--ghost" type="button" onClick={signOut}>
            Déconnexion
          </button>
        </div>
      </header>

      {selectedTable ? (
        <Commande table={selectedTable} onBack={() => setSelectedTable(null)} />
      ) : (
        <PlanDeSalle onSelectTable={setSelectedTable} />
      )}
    </div>
  )
}

function Gate() {
  const { user, ready } = useAuth()
  if (!ready) {
    return (
      <div className="container" style={{ paddingBlock: 64 }}>
        <span className="muted">Chargement…</span>
      </div>
    )
  }
  return user ? <Shell /> : <Login />
}

function App() {
  return (
    <AuthProvider>
      <Gate />
    </AuthProvider>
  )
}

export default App
