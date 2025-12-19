<script setup>
import { ref, defineEmits, computed } from "vue";
import { router, usePage } from "@inertiajs/vue3";

const emit = defineEmits(["close"]);

// Reactive form data
const subject_name = ref("");
const subject_code = ref("");
const units = ref("");
const year_level = ref("");
const subject_type = ref("");
const semester = ref("");
const major_id = ref("");

// Error handling
const errorMessage = ref(null);
const validationErrors = ref({});

// Get available majors from Inertia props
const majors = computed(() => usePage().props.majors || []);

// Submit form to Laravel
const submit = () => {
  errorMessage.value = null;
  validationErrors.value = {}; // Reset errors

  router.post(
    route("subjects.store"), // Corrected route
    {
      subject_type: subject_type.value,
      subject_name: subject_name.value,
      subject_code: subject_code.value,
      units: units.value,
      year_level: year_level.value,
      semester: semester.value,
      major_id: major_id.value,
    },
    {
      onSuccess: () => {
        closeModal(); // Close modal on success
      },
      onError: (errors) => {
        console.error("Submission failed:", errors);
        validationErrors.value = errors;
        errorMessage.value = "Failed to add subject. Please check your inputs.";
      },
    }
  );
};

// Close modal function
const closeModal = () => {
  emit("close");
};
</script>

<template>
  <div class="fixed inset-0 flex items-center justify-center bg-gray-900 bg-opacity-50">
    <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg w-96">
      <h2 class="text-lg font-semibold mb-4">Add Subject</h2>

      <!-- Error Message -->
      <p v-if="errorMessage" class="text-red-500 mb-4">{{ errorMessage }}</p>

      <!-- Major Selection -->
      <label class="block mb-2">Major:</label>
      <select v-model="major_id" class="w-full p-2 border rounded mb-4">
        <option value="" disabled>Select a Major</option>
        <option v-for="major in majors" :key="major.id" :value="major.id">
          {{ major.major_name }}
        </option>
      </select>
      <p v-if="validationErrors.major_id" class="text-red-500 text-sm">
        {{ validationErrors.major_id[0] }}
      </p>

       <!-- subject type -->
       <label class="block mb-2">Subject Type:</label>
      <select v-model="subject_type" class="w-full p-2 border rounded mb-4">
        <option value="" disabled>Select</option>
        <option value="major">Major</option>
        <option value="minor">Minor</option>
      </select>
      <p v-if="validationErrors.subject_type" class="text-red-500 text-sm">
        {{ validationErrors.subject_type[0] }}
      </p>

      <!-- Subject Name -->
      <label class="block mb-2">Subject Name:</label>
      <input v-model="subject_name" type="text" class="w-full p-2 border rounded mb-4">
      <p v-if="validationErrors.subject_name" class="text-red-500 text-sm">
        {{ validationErrors.subject_name[0] }}
      </p>

      <!-- Subject Code -->
      <label class="block mb-2">Subject Code:</label>
      <input v-model="subject_code" type="text" class="w-full p-2 border rounded mb-4">
      <p v-if="validationErrors.subject_code" class="text-red-500 text-sm">
        {{ validationErrors.subject_code[0] }}
      </p>

      <!-- Units -->
      <label class="block mb-2">Units:</label>
      <input v-model="units" type="number" class="w-full p-2 border rounded mb-4">
      <p v-if="validationErrors.units" class="text-red-500 text-sm">
        {{ validationErrors.units[0] }}
      </p>

      <!-- Year Level -->
      <label class="block mb-2">Year Level:</label>
      <select v-model="year_level" class="w-full p-2 border rounded mb-4">
        <option value="" disabled>Select Year Level</option>
        <option value="1">1st Year</option>
        <option value="2">2nd Year</option>
        <option value="3">3rd Year</option>
        <option value="4">4th Year</option>
      </select>
      <p v-if="validationErrors.year_level" class="text-red-500 text-sm">
        {{ validationErrors.year_level[0] }}
      </p>

      <!-- Semester -->
      <label class="block mb-2">Semester:</label>
      <select v-model="semester" class="w-full p-2 border rounded mb-4">
        <option value="" disabled>Select Semester</option>
        <option value="1">1st Semester</option>
        <option value="2">2nd Semester</option>
      </select>
      <p v-if="validationErrors.semester" class="text-red-500 text-sm">
        {{ validationErrors.semester[0] }}
      </p>

      <div class="flex justify-end space-x-2">
        <button @click="closeModal" class="px-4 py-2 bg-gray-500 text-white rounded">Cancel</button>
        <button @click="submit" class="px-4 py-2 bg-blue-600 text-white rounded">Save</button>
      </div>
    </div>
  </div>
</template>
