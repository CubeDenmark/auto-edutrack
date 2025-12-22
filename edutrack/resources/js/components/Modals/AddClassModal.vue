<script setup lang="ts">
import { ref, reactive } from 'vue';

// Props to receive data from the parent
defineProps<{
  showModal: boolean;
}>();

// Emit event to notify the parent when modal is closed or class is added
const emit = defineEmits(['close', 'addClass']);

// Available icons
const availableIcons = [
  '📚', '📝', '📖', '🧪', '🔬', '🧬', '⚗️', '🌍', '🦠', '📡', '🚀',
  '💻', '🖥️', '⌨️', '📟', '🗄️', '🧑‍💻', '📊', '📡', '🖲️', '⚙️',
  '📁', '💰', '📈', '📉', '📝', '🔖', '🏦', '🏛️', '💳', '🗂️',
  '📑', '📠', '⚖️', '🔎', '🕵️‍♂️', '🚔', '👮‍♂️', '🩸', '🔫',
  '🚨', '🏛️', '🔐', '🧑‍⚖️', '📜', '🛡️', '🏗️', '🛠️', '🔋',
  '📡', '⚙️', '🛩️', '📷', '🎥', '🎙️'
];

// Form state
const newClass = reactive({
  id: '',
  name: '',
  icon: '📚',
  term: 'Fall Semester 2023'
});

// Generate a unique ID for the class
function generateId() {
  return 'class_' + Math.random().toString(36).substr(2, 9);
}

// Function to reset form and open modal
function resetForm() {
  Object.assign(newClass, {
    id: generateId(),
    name: '',
    icon: '📚',
    term: 'Fall Semester 2023'
  });
}

// Function to emit event when adding a class
function addClass() {
  emit('addClass', { ...newClass });
  emit('close'); // Close modal after adding
}
</script>

<template>
  <!-- Add Class Modal -->
  <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg w-full max-w-md p-6">
      <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-medium">Add New Class</h3>
        <button @click="emit('close')" class="text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>
      <form @submit.prevent="addClass" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Class Name</label>
          <input
            type="text"
            v-model="newClass.name"
            required
            placeholder="e.g., Mathematics 101"
            class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm focus:ring-primary-500 focus:border-primary-500 w-full"
          />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Term</label>
          <input
            type="text"
            v-model="newClass.term"
            placeholder="e.g., Fall Semester 2023"
            class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm focus:ring-primary-500 focus:border-primary-500 w-full"
          />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Icon</label>
          <div class="grid grid-cols-5 gap-2">
            <button
              v-for="icon in availableIcons"
              :key="icon"
              type="button"
              @click="newClass.icon = icon"
              :class="[
                'h-10 w-10 flex items-center justify-center text-lg rounded-md',
                newClass.icon === icon
                  ? 'bg-primary-100 dark:bg-primary-900 border-2 border-primary-500'
                  : 'bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600'
              ]"
            >
              {{ icon }}
            </button>
          </div>
        </div>
        <div class="flex justify-end space-x-2 pt-4">
          <button
            type="button"
            @click="emit('close')"
            class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm"
          >
            Cancel
          </button>
          <button
            type="submit"
            class="px-4 py-2 bg-primary-600 text-white rounded-md text-sm"
          >
            Add Class
          </button>
        </div>
      </form>
    </div>
  </div>
</template>
