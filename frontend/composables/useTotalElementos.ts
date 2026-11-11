import type { Ref } from 'vue'
export function useTotalElementos<T>(elementos: Ref<T[]>) {
  const total = computed(() => elementos.value.length)

  return {
    total,
  }
}