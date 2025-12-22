<script setup lang="ts">
import AppLayout from "@/layouts/AppLayout.vue";
import { type BreadcrumbItem } from "@/types";
import { Head, usePage } from "@inertiajs/vue3";
import { ref, computed } from "vue";
import AddSubjectsModal from "@/components/AddSubjectsModal.vue";

const breadcrumbs: BreadcrumbItem[] = [
  { title: "Manage Subjects", href: "/subjects" },
];

const props = usePage().props;
const subjectTypes = ref([
  { label: "All Subjects", value: "" },
  ...props.subjectTypes.map((type: string) => ({ label: type.charAt(0).toUpperCase() + type.slice(1), value: type }))
]);
const selectedSubjectType = ref("");
const subjects = ref(props.subjects);
const isModalOpen = ref(false);
const showDropdown = ref(false);
const selectedMajor = ref("");
const majors = ref(props.majors || []);

// Computed Filtered Subjects
const filteredSubjects = computed(() => {
  return subjects.value.filter((subject: any) => 
    (!selectedSubjectType.value || subject.subject_type === selectedSubjectType.value) &&
    (!selectedMajor.value || subject.major === selectedMajor.value)
  );
});

const openModal = () => (isModalOpen.value = true);
const closeModal = () => (isModalOpen.value = false);
</script>

<template>
  <Head title="Manage Subjects" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-6 pt-20">
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
        <div class="p-6 border-b dark:border-gray-700 flex justify-between items-center">
          <div>
            <h3 class="font-medium">Manage Subjects</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400">Add, edit, or remove subjects</p>
          </div>
          <button @click="openModal" class="bg-primary-600 hover:bg-primary-700 text-white px-4 py-2 rounded-lg flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
            Add Subject
          </button>
        </div>

        <AddSubjectsModal v-if="isModalOpen" @close="closeModal" />

        <div class="p-4 border-b dark:border-gray-700 bg-gray-50 dark:bg-gray-700 flex justify-between items-center">
          <div class="flex space-x-2">
            <button v-for="type in subjectTypes" :key="type.value" @click="selectedSubjectType = type.value"
              :class="['px-3 py-1 rounded-lg text-sm font-medium',
                selectedSubjectType === type.value ? 'bg-primary-600 text-white' : 'bg-white dark:bg-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-500']">
              {{ type.label }}
            </button>
          </div>
          <div class="relative">
            <button @click="showDropdown = !showDropdown"
              class="flex items-center px-3 py-1 bg-white dark:bg-gray-600 text-gray-700 dark:text-gray-200 rounded-lg text-sm font-medium hover:bg-gray-100 dark:hover:bg-gray-500">
              {{ selectedMajor || "Select Major" }}
              <svg :class="{ 'rotate-180': showDropdown }" class="w-4 h-4 ml-2 transition-transform transform" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path>
              </svg>
            </button>
            <div v-if="showDropdown" class="absolute right-0 mt-2 w-[300px] p-2 bg-white dark:bg-gray-700 border dark:border-gray-600 rounded-lg shadow-lg">
              <a href="#" @click.prevent="selectedMajor = ''" class="flex items-center px-4 py-2 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-600 rounded-lg">
                <span>All Majors</span>
              </a>
              <a v-for="major in majors" :key="major.id" href="#" @click.prevent="selectedMajor = major.major_name"
                class="flex items-center px-4 py-2 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-600 rounded-lg">
                <span>{{ major.major_name }}</span>
              </a>
            </div>
          </div>
        </div>

        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-700">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Subject</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Code</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Major</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Units</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Teacher</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Students</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Actions</th>
              </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
              <tr v-for="(subject, index) in filteredSubjects" :key="index">
                <td class="px-6 py-4 text-sm font-medium">{{ subject.subject_name }}</td>
                <td class="px-6 py-4 text-sm">{{ subject.subject_code }}</td>
                <td class="px-6 py-4 text-sm">{{ subject.major }}</td>
                <td class="px-6 py-4 text-sm">{{ subject.units }}</td>
                <td class="px-6 py-4 text-sm">{{ subject.teacher }}</td>
                <td class="px-6 py-4 text-sm">{{ subject.students }}</td>
                <td class="px-6 py-4 text-right text-sm">
                  <button class="text-primary-600 hover:text-primary-900 mr-3">Edit</button>
                  <button class="text-red-600 hover:text-red-900">Delete</button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </AppLayout>
</template>