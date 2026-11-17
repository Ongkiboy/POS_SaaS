<script setup lang="ts">
import { onMounted, ref, watch } from 'vue'

const props = defineProps<{ open: boolean; title: string; message: string }>()
const emit = defineEmits<{ confirm: []; cancel: [] }>()

const dialog = ref<HTMLDialogElement | null>(null)

watch(
  () => props.open,
  (isOpen) => {
    if (isOpen) dialog.value?.showModal()
    else dialog.value?.close()
  },
)

// Dialog yang pertama kali dirender dengan open=true tidak memicu watch.
onMounted(() => {
  if (props.open) dialog.value?.showModal()
})
</script>

<template>
  <dialog
    ref="dialog"
    class="rounded p-6 shadow backdrop:bg-black/40"
    @cancel.prevent="emit('cancel')"
  >
    <h2 class="text-lg font-semibold">{{ props.title }}</h2>
    <p class="mt-2 text-sm text-slate-600">{{ props.message }}</p>
    <div class="mt-4 flex justify-end gap-2">
      <button type="button" class="rounded border px-3 py-1" @click="emit('cancel')">Batal</button>
      <button
        type="button"
        class="rounded bg-slate-900 px-3 py-1 text-white"
        @click="emit('confirm')"
      >
        Ya
      </button>
    </div>
  </dialog>
</template>
