import { useState } from 'react'
import type { FormEvent } from 'react'
import { useAuth } from './auth'

export function Login() {
  const { signIn } = useAuth()
  const [email, setEmail] = useState('')
  const [password, setPassword] = useState('')
  const [error, setError] = useState<string | null>(null)
  const [busy, setBusy] = useState(false)

  async function onSubmit(event: FormEvent) {
    event.preventDefault()
    setError(null)
    setBusy(true)
    try {
      await signIn(email, password)
    } catch {
      setError('Identifiants invalides.')
    } finally {
      setBusy(false)
    }
  }

  return (
    <div className="container" style={{ maxWidth: 380, paddingBlock: 64 }}>
      <div className="stack" style={{ alignItems: 'center', marginBottom: 24 }}>
        <span className="brand" style={{ fontSize: '1.4rem' }}>Fontenay</span>
        <span className="eyebrow">Espace personnel</span>
      </div>

      <form className="card" onSubmit={onSubmit}>
        <div className="stack">
          <div className="field">
            <label>Email</label>
            <input
              className="input"
              type="email"
              value={email}
              onChange={(e) => setEmail(e.target.value)}
              autoComplete="username"
            />
          </div>
          <div className="field">
            <label>Mot de passe</label>
            <input
              className="input"
              type="password"
              value={password}
              onChange={(e) => setPassword(e.target.value)}
              autoComplete="current-password"
            />
          </div>

          {error && <div className="badge badge--allergy">{error}</div>}

          <button className="btn btn--gold btn--lg btn--block" type="submit" disabled={busy}>
            {busy ? 'Connexion…' : 'Se connecter'}
          </button>
        </div>
      </form>
    </div>
  )
}
