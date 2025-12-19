<script setup>
import { ref, defineEmits, onMounted } from "vue";
import { router, usePage } from "@inertiajs/vue3";

const emit = defineEmits(["close"]);
const section_name = ref("");
const year_level = ref("");
const major_id = ref(""); // Course selection
const user_id = ref(""); // Adviser selection
const majors = ref([]); // Stores course list
const advisers = ref([]); // Stores adviser list
const isSubmitting = ref(false);
const errors = ref({});

// Fetch Courses & Advisers
onMounted(() => {
  const page = usePage();
  majors.value = page.props.majors || [];
  advisers.value = page.props.advisers || [];
});

const submit = () => {
  isSubmitting.value = true;
  errors.value = {};

  router.post(
    route("sections.store"),
    {
      section_name: section_name.value,
      year_level: year_level.value,
      major_id: major_id.value,
      user_id: user_id.value,
    },
    {
      onSuccess: () => {
        closeModal();
      },
      onError: (err) => {
        errors.value = err;
      },
      onFinish: () => {
        isSubmitting.value = false;
      },
    }
  );
};

const closeModal = () => {
  emit("close");
};
</script>

<template>
  <div class="fixed inset-0 flex items-center justify-center bg-gray-900 bg-opacity-50">
    <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg w-96">
      <h2 class="text-lg font-semibold mb-4">Add Section</h2>

      <!-- Section Name -->
      <label class="block mb-2">Section Name:</label>
      <input 
        v-model="section_name" 
        type="text" 
        class="w-full p-2 border rounded mb-1"
        :class="{'border-red-500': errors.section_name}"
      >
      <p v-if="errors.section_name" class="text-red-500 text-sm mb-2">{{ errors.section_name[0] }}</p>

      <!-- Year Level -->
      <label class="block mb-2">Year Level:</label>
      <input 
        v-model="year_level" 
        type="text" 
        class="w-full p-2 border rounded mb-1"
        :class="{'border-red-500': errors.year_level}"
      >
      <p v-if="errors.year_level" class="text-red-500 text-sm mb-2">{{ errors.year_level[0] }}</p>

      <!-- Course Selection -->
      <label class="block mb-2">Major:</label>
      <select v-model="major_id" class="w-full p-2 border rounded mb-1">
        <option value="" disabled>Select Major</option>
        <option v-for="major in majors" :key="major.id" :value="major.id">
          {{ major.major_name }}
        </option>
      </select>
      <p v-if="errors.major" class="text-red-500 text-sm mb-2">{{ errors.major_id[0] }}</p>

      <!-- Adviser Selection -->
      <label class="block mb-2">Adviser:</label>
      <select v-model="user_id" class="w-full p-2 border rounded mb-1">
        <option value="" disabled>Select Adviser</option>
        <option v-for="adviser in advisers" :key="adviser.id" :value="adviser.id">
          {{ adviser.name }}
        </option>
      </select>
      <p v-if="errors.user_id" class="text-red-500 text-sm mb-2">{{ errors.user_id[0] }}</p>

      <!-- Buttons -->
      <div class="flex justify-end space-x-2 mt-4">
        <button @click="closeModal" class="px-4 py-2 bg-gray-500 text-white rounded">Cancel</button>
        <button 
          @click="submit" 
          :disabled="isSubmitting"
          class="px-4 py-2 bg-blue-600 text-white rounded disabled:opacity-50"
        >
          {{ isSubmitting ? "Saving..." : "Save" }}
        </button>
      </div>
    </div>
  </div>
</template>
