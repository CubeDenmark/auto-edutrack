<template>
  <div>
    <form @submit.prevent="submit" class="space-y-4">
      <!-- Class Dropdown -->
      <div>
        <label for="class_id" class="block text-sm font-medium">Select Class</label>
        <select
          v-model="selectedClassId"
          id="class_id"
          required
          class="border rounded p-2 w-full"
        >
          <option disabled value="">-- Choose a class --</option>
          <option
            v-for="classItem in classes"
            :key="classItem.id"
            :value="classItem.id"
          >
            {{ classItem.name }} ({{ classItem.term }})
          </option>
        </select>
      </div>

      <!-- File Input -->
      <div>
        <label class="block text-sm font-medium">Upload Excel File</label>
        <input type="file" @change="handleFile" accept=".xlsx,.xls,.csv" required />
      </div>

      <!-- Submit Button -->
      <button
        type="submit"
        class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600"
      >
        Upload
      </button>
    </form>

    <!-- Success Message -->
    <div v-if="successMessage" class="text-green-500 mt-4">
      {{ successMessage }}
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'

// Props from Laravel
interface ClassItem {
  id: number
  name: string
  term: string
}

const props = defineProps<{
  classes: ClassItem[]
}>()

// State
const selectedClassId = ref<string | number>('')
const file = ref<File | null>(null)
const successMessage = ref<string>('')

// Handle file input
function handleFile(e: Event) {
  const target = e.target as HTMLInputElement
  if (target.files && target.files.length > 0) {
    file.value = target.files[0]
  }
}

// Handle form submission
function submit() {
  if (!file.value || !selectedClassId.value) {
    alert('Please select a class and upload a file.')
    return
  }

  const formData = new FormData()
  formData.append('students_file', file.value)
  formData.append('class_id', String(selectedClassId.value))

  router.post('/students/import', formData, {
    forceFormData: true,
    onSuccess: () => {
      successMessage.value = 'Students uploaded successfully!'
      file.value = null
      selectedClassId.value = ''
    },
    onError: (errors) => {
      console.error('Upload error:', errors)
    },
  })
}
</script>
