import { ref } from 'vue'
import { defineStore } from 'pinia'

export const useOutletStore = defineStore('outlet', () => {
  const outletId = ref<number | null>(null)

  function setOutlet(id: number | null): void {
    outletId.value = id
  }

  return { outletId, setOutlet }
})
