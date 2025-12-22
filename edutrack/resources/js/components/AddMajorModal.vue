<script setup>
import { ref, defineEmits, computed } from "vue";
import { router, usePage } from "@inertiajs/vue3";

const emit = defineEmits(["close"]);

// Reactive form data
const major_name = ref("");
const major_code = ref("");
const description = ref("");
const course_id = ref(""); // Store selected course ID
const errorMessage = ref(null); // Handle errors

// Get courses from Inertia props
const courses = computed(() => usePage().props.courses || []);

// Submit form
const submit = () => {
  errorMessage.value = null; // Reset errors

  router.post(
    route("majors.store"),
    {
      major_name: major_name.value,
      major_code: major_code.value,
      description: description.value,
      course_id: course_id.value, // Send selected course ID
    },
    {
      onSuccess: () => {
        closeModal(); // Close modal on success
      },
      onError: (errors) => {
        console.error("Submission failed:", errors);
        errorMessage.value = "Failed to add major. Please check your inputs.";
      },
    }
  );
};

// Close modal
const closeModal = () => {
  emit("close");
};
</script>

<template>
  <div class="fixed inset-0 flex items-center justify-center bg-gray-900 bg-opacity-50">
    <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg w-96">
      <h2 class="text-lg font-semibold mb-4">Add Major</h2>

      <!-- Error Message -->
      <p v-if="errorMessage" class="text-red-500 mb-4">{{ errorMessage }}</p>

      <label class="block mb-2">Courses:</label>
      <select v-model="course_id" class="w-full p-2 border rounded mb-4">
        <option value="" disabled>Select a Course</option>
        <option v-for="cour in courses" :key="cour.id" :value="cour.id">
          {{ cour.course_name }}
        </option>
      </select>

      <label class="block mb-2">Name:</label>
      <input v-model="major_name" type="text" class="w-full p-2 border rounded mb-4">

      <label class="block mb-2">Code:</label>
      <input v-model="major_code" type="text" class="w-full p-2 border rounded mb-4">

      <label class="block mb-2">Description:</label>
      <textarea v-model="description" class="w-full p-2 border rounded mb-4"></textarea>

      <div class="flex justify-end space-x-2">
        <button @click="closeModal" class="px-4 py-2 bg-gray-500 text-white rounded">Cancel</button>
        <button @click="submit" class="px-4 py-2 bg-blue-600 text-white rounded">Save</button>
      </div>
    </div>
  </div>
</template>
