
<script setup lang="ts">
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3';

const fileInput = ref<HTMLInputElement | null>(null)
const successMessage = ref<string>('')
const errorMessage = ref<string>('')

const form = useForm<{ file: File | null }>({ file: null })

function submit(): void {
  if (!fileInput.value || !fileInput.value.files || fileInput.value.files.length === 0) {
    errorMessage.value = 'Please select a file to upload.'
    successMessage.value = ''
    return
  }

  errorMessage.value = ''
  successMessage.value = ''

  form.clearErrors()

  form.file = fileInput.value.files[0]

  form.post('/students/import', {
    preserveScroll: true,
    onSuccess: () => {
      successMessage.value = 'Import successful!'
      errorMessage.value = ''
      if (fileInput.value) fileInput.value.value = '' // reset file input
    },
    onError: () => {
      successMessage.value = ''
      errorMessage.value = 'Failed to import. Please check your file and try again.'
    },
    forceFormData: true
  })
}
</script>



<template lang="html">
  <div>
    <h1>Upload Students and Parents Excel</h1>

    <form @submit.prevent="submit">
      <input type="file" ref="fileInput" aria-label="Upload students and parents Excel file" />
      <button type="submit">Upload</button>
    </form>

    <p v-if="successMessage">{{ successMessage }}</p>
    <p v-if="errorMessage" style="color: red;">{{ errorMessage }}</p>
  </div>
</template>
