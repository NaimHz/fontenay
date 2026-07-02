import { AuthProvider, useAuth } from './auth'
import { Login } from './Login'
import { PlanDeSalle } from './PlanDeSalle'

function Shell() {
  const { user, signOut } = useAuth()
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
      <PlanDeSalle />
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
