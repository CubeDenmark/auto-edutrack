<script setup>
import { ref, defineEmits } from "vue";
import { router } from "@inertiajs/vue3";

const emit = defineEmits(["close"]);
const department_name = ref("");
const department_code = ref("");  // Fixed naming to 'code'
const description = ref("");

const submit = () => {
  router.post("/departments", {
    department_name: department_name.value,
    department_code: department_code.value,  // Ensure code is included
    description: description.value,
  }, {
    onSuccess: () => {
      closeModal();
    }
  });
};

const closeModal = () => {
  emit("close");
};
</script>

<template>
  <div class="fixed inset-0 flex items-center justify-center bg-gray-900 bg-opacity-50">
    <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg w-96">
      <h2 class="text-lg font-semibold mb-4">Add Department</h2>

      <label class="block mb-2">Name:</label>
      <input v-model="department_name" type="text" class="w-full p-2 border rounded mb-4">

      <label class="block mb-2">Code:</label>
      <input v-model="department_code" type="text" class="w-full p-2 border rounded mb-4">

      <label class="block mb-2">Description:</label>
      <textarea v-model="description" class="w-full p-2 border rounded mb-4"></textarea>

      <div class="flex justify-end space-x-2">
        <button @click="closeModal" class="px-4 py-2 bg-gray-500 text-white rounded">Cancel</button>
        <button @click="submit" class="px-4 py-2 bg-blue-600 text-white rounded">Save</button>
      </div>
    </div>
  </div>
</template>
