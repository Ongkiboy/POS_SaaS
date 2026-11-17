<script setup lang="ts">
import { ref } from 'vue'
import { api, ApiError } from '@/lib/api'
import { useToast } from '@/components/ui/useToast'

const toast = useToast()
const result = ref<string>('')

async function ping(): Promise<void> {
  try {
    const response = await api('/ping')
    result.value = JSON.stringify(response)
    toast.success('Ping berhasil')
  } catch (error) {
    const message = error instanceof ApiError ? `${error.status} ${error.code}` : 'Gagal'
    result.value = message
    toast.error(message)
  }
}
</script>

<template>
  <main class="p-6">
    <h1 class="text-2xl font-semibold">F&amp;B POS</h1>
    <button type="button" class="mt-4 rounded bg-slate-900 px-3 py-1 text-white" @click="ping">
      Tes API /ping
    </button>
    <pre class="mt-4 text-sm">{{ result }}</pre>
    <RouterLink class="mt-4 inline-block underline" to="/app">Ke /app (butuh login)</RouterLink>
  </main>
</template>
