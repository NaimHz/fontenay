import { createContext, useContext, useEffect, useState } from 'react'
import type { ReactNode } from 'react'
import {
  clearToken,
  getEstablishments,
  getMe,
  getToken,
  login as apiLogin,
  setEstablishment,
} from './api'
import type { StaffEstablishment, StaffUser } from './api'

type AuthState = {
  user: StaffUser | null
  ready: boolean
  establishments: StaffEstablishment[]
  establishmentId: number | null
  selectEstablishment: (id: number) => void
  signIn: (email: string, password: string) => Promise<void>
  signOut: () => void
}

const AuthContext = createContext<AuthState | null>(null)

export function AuthProvider({ children }: { children: ReactNode }) {
  const [user, setUser] = useState<StaffUser | null>(null)
  const [ready, setReady] = useState(false)
  const [establishments, setEstablishments] = useState<StaffEstablishment[]>([])
  const [establishmentId, setEstablishmentIdState] = useState<number | null>(null)

  function selectEstablishment(id: number) {
    setEstablishment(id) // client API
    setEstablishmentIdState(id)
  }

  // Détermine l'établissement de travail : celui de l'utilisateur, ou — pour le
  // propriétaire (multi-sites) — le premier de la liste par défaut.
  async function loadEstablishment(u: StaffUser) {
    if (u.establishment) {
      setEstablishments([{ id: u.establishment.id, name: u.establishment.name }])
      selectEstablishment(u.establishment.id)
    } else {
      const list = await getEstablishments()
      setEstablishments(list)
      if (list[0]) selectEstablishment(list[0].id)
    }
  }

  useEffect(() => {
    if (!getToken()) {
      setReady(true)
      return
    }
    getMe()
      .then(async (u) => {
        await loadEstablishment(u) // fixe l'établissement avant d'afficher l'app
        setUser(u)
      })
      .catch(() => clearToken())
      .finally(() => setReady(true))
  }, [])

  async function signIn(email: string, password: string) {
    await apiLogin(email, password)
    const u = await getMe()
    await loadEstablishment(u) // fixe l'établissement avant d'afficher l'app
    setUser(u)
  }

  function signOut() {
    clearToken()
    setEstablishment(null)
    setUser(null)
    setEstablishments([])
    setEstablishmentIdState(null)
  }

  return (
    <AuthContext.Provider
      value={{ user, ready, establishments, establishmentId, selectEstablishment, signIn, signOut }}
    >
      {children}
    </AuthContext.Provider>
  )
}

export function useAuth(): AuthState {
  const ctx = useContext(AuthContext)
  if (!ctx) throw new Error('useAuth doit être utilisé dans un AuthProvider')
  return ctx
}
