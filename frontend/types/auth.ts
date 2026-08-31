export interface Role {
  id: number
  name: string
  slug: string
  description: string | null
}

export interface User {
  id: number
  name: string
  email: string
  role_id?: number | null
  role?: Role | null
  email_verified_at: string | null
  created_at: string
  updated_at: string
}

export interface AuthResponse {
  user: User
  token: string
}

export interface LoginCredentials {
  email: string
  password: string
}

export interface RegisterCredentials extends LoginCredentials {
  name: string
  password_confirmation: string
}

export interface ForgotPasswordPayload {
  email: string
}

export interface ResetPasswordPayload {
  token: string
  email: string
  password: string
  password_confirmation: string
}

export interface MessageResponse {
  message: string
}
