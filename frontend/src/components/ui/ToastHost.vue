<script setup lang="ts">
import { useToast } from './useToast'

const { toasts, dismiss } = useToast()

const tone: Record<string, string> = {
  success: 'bg-green-600',
  error: 'bg-red-600',
  info: 'bg-slate-700',
}
</script>

<template>
  <div class="fixed right-4 top-4 z-50 flex w-80 flex-col gap-2" aria-live="polite">
    <button
      v-for="toast in toasts"
      :key="toast.id"
      type="button"
      class="rounded px-4 py-3 text-left text-sm text-white shadow"
      :class="tone[toast.kind]"
      @click="dismiss(toast.id)"
    >
      {{ toast.message }}
    </button>
  </div>
</template>
