<script setup>
import { ref, defineEmits, computed } from "vue";
import { router, usePage } from "@inertiajs/vue3";

const emit = defineEmits(["close"]);

// Reactive form data
const course_name = ref("");
const course_code = ref("");
const units = ref(0);
const description = ref("");
const department_id = ref(""); // Store selected department ID

// Get departments from Inertia props
const departments = computed(() => usePage().props.departments || []);

// Submit form
const submit = () => {
  router.post(route("courses.store"), {
    course_name: course_name.value,
    course_code: course_code.value,
    units: units.value,
    description: description.value,
    department_id: department_id.value, // Send selected department ID
  }, {
    onSuccess: () => {
      closeModal();
    },
    onError: (errors) => {
      console.error("Submission failed:", errors);
    }
  });
};

// Close modal
const closeModal = () => {
  emit("close");
};
</script>

<template>
  <div class="fixed inset-0 flex items-center justify-center bg-gray-900 bg-opacity-50">
    <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg w-96">
      <h2 class="text-lg font-semibold mb-4">Add Course</h2>

      <label class="block mb-2">Department:</label>
      <select v-model="department_id" class="w-full p-2 border rounded mb-4">
        <option value="" disabled>Select a department</option>
        <option v-for="dept in departments" :key="dept.id" :value="dept.id">
          {{ dept.department_name }}
        </option>
      </select>

      <label class="block mb-2">Name:</label>
      <input v-model="course_name" type="text" class="w-full p-2 border rounded mb-4">

      <label class="block mb-2">Code:</label>
      <input v-model="course_code" type="text" class="w-full p-2 border rounded mb-4">

      <label class="block mb-2">Units:</label>
      <input v-model="units" type="number" class="w-full p-2 border rounded mb-4">

      <label class="block mb-2">Description:</label>
      <textarea v-model="description" class="w-full p-2 border rounded mb-4"></textarea>

      <div class="flex justify-end space-x-2">
        <button @click="closeModal" class="px-4 py-2 bg-gray-500 text-white rounded">Cancel</button>
        <button @click="submit" class="px-4 py-2 bg-blue-600 text-white rounded">Save</button>
      </div>
    </div>
  </div>
</template>
