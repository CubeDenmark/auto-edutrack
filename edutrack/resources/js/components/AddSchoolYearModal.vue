<script setup>
import { ref, defineEmits } from "vue";
import { router } from "@inertiajs/vue3";

const emit = defineEmits(["close"]);
const year = ref("");
const semester = ref("");

const submit = () => {
  if (!/^\d{4}-\d{4}$/.test(year.value)) {
    alert("Please enter a valid school year format (e.g., 2024-2025)");
    return;
  }

  if (!semester.value) {
    alert("Please select a semester.");
    return;
  }

  router.post(
    route("schoolYear.store"), {
    year: year.value,
    semester: semester.value,
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
      <h2 class="text-lg font-semibold mb-4">Add School Year</h2>

      <label class="block mb-2">Year:</label>
      <input v-model="year" type="text" class="w-full p-2 border rounded mb-4" placeholder="YYYY-YYYY">

      <label class="block mb-2">Semester:</label>
      <select v-model="semester" class="w-full p-2 border rounded mb-4">
        <option value="" disabled>Select Semester</option>
        <option value="1st Semester">1st Semester</option>
        <option value="2nd Semester">2nd Semester</option>
        <option value="Summer">Summer</option>
      </select>

      <div class="flex justify-end space-x-2">
        <button @click="closeModal" class="px-4 py-2 bg-gray-500 text-white rounded">Cancel</button>
        <button @click="submit" class="px-4 py-2 bg-blue-600 text-white rounded">Save</button>
      </div>
    </div>
  </div>
</template>
