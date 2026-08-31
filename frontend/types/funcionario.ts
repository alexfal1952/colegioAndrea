export interface Role {
  id: number
  name: string
  slug: string
  description: string | null
  created_at?: string
  updated_at?: string
}

export interface Funcionario {
  id: number
  name: string
  email: string
  role_id: number | null
  role: Role | null
  email_verified_at: string | null
  created_at: string
  updated_at: string
}

export interface FuncionarioPayload {
  name: string
  email: string
  role_id: number | null
}

export interface FuncionarioCreateResponse {
  funcionario: Funcionario
  message: string
  mail_sent: boolean
  reset_url?: string | null
  mail_error?: string | null
}

export interface PasswordResetResponse {
  message: string
  mail_sent: boolean
  reset_url?: string | null
  mail_error?: string | null
}

export interface MessageResponse {
  message: string
}
