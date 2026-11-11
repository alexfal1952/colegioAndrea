export type TipoPokemon =
  | 'fuego'
  | 'agua'
  | 'planta'
  | 'electrico'
  | 'normal'
  | 'lucha'
  | 'volador'
  | 'psiquico'
  | 'bicho'
  | 'roca'
  | 'fantasma'
  | 'dragon'
  | 'hada'
  | 'tierra'
  | 'hielo'

export type Pokemon = {
  id: number
  nombre: string
  tipo: TipoPokemon
}
