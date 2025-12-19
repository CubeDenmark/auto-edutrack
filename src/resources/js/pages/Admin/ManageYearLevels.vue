<script setup lang="ts">
import AppLayout from "@/layouts/AppLayout.vue";
import { type BreadcrumbItem } from "@/types";
import { Head, usePage } from "@inertiajs/vue3";
import { ref, computed, onMounted } from 'vue';

import AddSchoolYearModal from "@/components/AddSchoolYearModal.vue";

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: "Manage Users",
    href: "/school-year",
  },
];

defineProps<{
  name?: string;
}>();

// Sample data for demonstration
const sampleSchoolYears = [
  {
    id: '1',
    year: '2023-2024',
    semester: 'First Semester',
    students: 450,
    sections: 15
  },
  {
    id: '2',
    year: '2023-2024',
    semester: 'Second Semester',
    students: 430,
    sections: 14
  },
  {
    id: '3',
    year: '2022-2023',
    semester: 'First Semester',
    students: 420,
    sections: 14
  },
  {
    id: '4',
    year: '2022-2023',
    semester: 'Second Semester',
    students: 410,
    sections: 13
  },
  {
    id: '5',
    year: '2021-2022',
    semester: 'First Semester',
    students: 380,
    sections: 12
  },
  {
    id: '6',
    year: '2021-2022',
    semester: 'Second Semester',
    students: 375,
    sections: 12
  }
];

// School year selector
const selectedSchoolYear = ref('');
// Use actual data from usePage() if available, otherwise use sample data
const allSchoolYears = ref(sampleSchoolYears);

onMounted(() => {
  // Try to get data from Inertia props, fallback to sample data if not available
  const pageSchoolYears = usePage().props.schoolYears;
  if (pageSchoolYears && Array.isArray(pageSchoolYears) && pageSchoolYears.length > 0) {
    allSchoolYears.value = pageSchoolYears;
  }

  // Set default selected school year
  if (allSchoolYears.value.length > 0 && !selectedSchoolYear.value) {
    selectedSchoolYear.value = allSchoolYears.value[0].id;
  }
});

// Group school years by academic year for the dropdown
const groupedSchoolYears = computed(() => {
  const grouped = {};
  allSchoolYears.value.forEach(year => {
    if (!grouped[year.year]) {
      grouped[year.year] = [];
    }
    grouped[year.year].push(year);
  });
  return grouped;
});

// Pagination
const pagination = ref({
  users: { currentPage: 1, itemsPerPage: 5, startIndex: 0, endIndex: 4 },
  departments: { currentPage: 1, itemsPerPage: 5, startIndex: 0, endIndex: 4 },
  courses: { currentPage: 1, itemsPerPage: 5, startIndex: 0, endIndex: 4 },
  majors: { currentPage: 1, itemsPerPage: 5, startIndex: 0, endIndex: 4 },
  yearLevels: { currentPage: 1, itemsPerPage: 5, startIndex: 0, endIndex: 4 },
  sections: { currentPage: 1, itemsPerPage: 5, startIndex: 0, endIndex: 4 },
  reportStudents: { currentPage: 1, itemsPerPage: 5, startIndex: 0, endIndex: 4 }
});

// Filter year levels based on selected school year
const filteredYearLevels = computed(() => {
  if (!selectedSchoolYear.value) return allSchoolYears.value;
  return allSchoolYears.value.filter(year => year.id === selectedSchoolYear.value);
});

const paginatedYearLevels = computed(() => {
  const { startIndex, endIndex } = pagination.value.yearLevels;
  return filteredYearLevels.value.slice(startIndex, endIndex + 1);
});

// For demonstration purposes - to show all data when "All" is selected
const showAllYearLevels = computed(() => {
  return selectedSchoolYear.value === 'all';
});

const nextPage = (type: string) => {
  if (pagination.value[type].endIndex < (type === 'yearLevels' ? filteredYearLevels.value.length - 1 : 0)) {
    pagination.value[type].currentPage++;
    updatePaginationIndices(type);
  }
};

const prevPage = (type: string) => {
  if (pagination.value[type].currentPage > 1) {
    pagination.value[type].currentPage--;
    updatePaginationIndices(type);
  }
};

const updatePaginationIndices = (type: string) => {
  const itemsPerPage = pagination.value[type].itemsPerPage;
  const currentPage = pagination.value[type].currentPage;

  pagination.value[type].startIndex = (currentPage - 1) * itemsPerPage;
  pagination.value[type].endIndex = pagination.value[type].startIndex + itemsPerPage - 1;
};

// When school year changes, reset pagination
const handleSchoolYearChange = () => {
  pagination.value.yearLevels.currentPage = 1;
  updatePaginationIndices('yearLevels');
};

const isModalOpen = ref(false);
const openModal = () => (isModalOpen.value = true);
const closeModal = () => (isModalOpen.value = false);

// Get the name of the currently selected school year for display
const selectedSchoolYearName = computed(() => {
  if (selectedSchoolYear.value === 'all') return 'All School Years';
  const selected = allSchoolYears.value.find(year => year.id === selectedSchoolYear.value);
  return selected ? `${selected.year} - ${selected.semester}` : '';
});
</script>

<template>
  <Head title="Manage Users" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-6 pt-20">
      <!-- Manage Year Levels Page -->
      <div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
          <div class="p-6 border-b dark:border-gray-700 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
              <h3 class="font-medium">Manage Year Levels</h3>
              <p class="text-sm text-gray-500 dark:text-gray-400">Add, edit, or remove year levels and assign students</p>
            </div>
            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4 w-full sm:w-auto">
              <!-- School Year Selector -->
              <div class="flex flex-col sm:flex-row items-start sm:items-center gap-2 w-full sm:w-auto">
                <label for="schoolYear" class="text-sm font-medium text-gray-700 dark:text-gray-300">School Year:</label>
                <select
                  id="schoolYear"
                  v-model="selectedSchoolYear"
                  @change="handleSchoolYearChange"
                  class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full sm:w-auto p-2 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500"
                >
                  <option value="all">All School Years</option>
                  <optgroup v-for="(years, academicYear) in groupedSchoolYears" :key="academicYear" :label="academicYear">
                    <option v-for="year in years" :key="year.id" :value="year.id">
                      {{ year.semester }}
                    </option>
                  </optgroup>
                </select>
              </div>

              <button @click="openModal" class="bg-primary-600 hover:bg-primary-700 text-white px-4 py-2 rounded-lg flex items-center w-full sm:w-auto justify-center sm:justify-start">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                Add School Year
              </button>
            </div>
          </div>

          <div v-if="isModalOpen">
            <AddSchoolYearModal @close="closeModal" />
          </div>

          <!-- Current selection indicator -->
          <div class="px-6 py-3 bg-gray-50 dark:bg-gray-700 border-b dark:border-gray-600">
            <p class="text-sm font-medium">
              Currently viewing: <span class="text-primary-600 dark:text-primary-400">{{ selectedSchoolYearName }}</span>
            </p>
          </div>

          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
              <thead class="bg-gray-50 dark:bg-gray-700">
                <tr>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Academic Year</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Semester</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Students</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Sections</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                </tr>
              </thead>
              <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                <template v-if="showAllYearLevels">
                  <tr v-for="(yearLevel, index) in paginatedYearLevels" :key="index">
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="text-sm font-medium">{{ yearLevel.year }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">{{ yearLevel.semester }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">{{ yearLevel.students }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">{{ yearLevel.sections }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                      <div class="flex space-x-2">
                        <button class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">Edit</button>
                        <button class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">Delete</button>
                      </div>
                    </td>
                  </tr>
                </template>
                <template v-else>
                  <tr v-for="(yearLevel, index) in paginatedYearLevels" :key="index">
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="text-sm font-medium">{{ yearLevel.year }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">{{ yearLevel.semester }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">{{ yearLevel.students }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">{{ yearLevel.sections }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                      <div class="flex space-x-2">
                        <button class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">Edit</button>
                        <button class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">Delete</button>
                      </div>
                    </td>
                  </tr>
                </template>
                <tr v-if="paginatedYearLevels.length === 0">
                  <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                    No data available for the selected school year
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          <div class="p-4 border-t dark:border-gray-700 flex items-center justify-between">
            <div class="text-sm text-gray-500 dark:text-gray-400">
              Showing <span class="font-medium">{{ pagination.yearLevels.startIndex + 1 }}</span> to
              <span class="font-medium">{{ Math.min(pagination.yearLevels.endIndex + 1, filteredYearLevels.length) }}</span> of
              <span class="font-medium">{{ filteredYearLevels.length }}</span> entries
            </div>
            <div class="flex space-x-2">
              <button
                @click="prevPage('yearLevels')"
                :disabled="pagination.yearLevels.currentPage === 1"
                :class="[
                  'px-3 py-1 rounded border dark:border-gray-600 text-sm',
                  pagination.yearLevels.currentPage === 1 ? 'opacity-50 cursor-not-allowed' : ''
                ]"
              >
                Previous
              </button>
              <button
                @click="nextPage('yearLevels')"
                :disabled="pagination.yearLevels.endIndex >= filteredYearLevels.length - 1"
                :class="[
                  'px-3 py-1 rounded text-sm',
                  pagination.yearLevels.endIndex >= filteredYearLevels.length - 1
                    ? 'bg-gray-300 text-gray-700 opacity-50 cursor-not-allowed'
                    : 'bg-primary-600 text-white'
                ]"
              >
                Next
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
