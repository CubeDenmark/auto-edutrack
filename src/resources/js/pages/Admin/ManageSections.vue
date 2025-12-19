<script setup lang="ts">
import AppLayout from "@/layouts/AppLayout.vue";
import { Head, usePage } from "@inertiajs/vue3";
import { ref, computed } from 'vue';
import AddSectionsModal from "@/components/AddSectionsModal.vue";

const breadcrumbs = [
  {
    title: "Manage Users",
    href: "/users",
  },
];

// Pagination
const pagination = ref({
  sections: { currentPage: 1, itemsPerPage: 5, startIndex: 0, endIndex: 4 }
});

// Fetch sections from props
const sections = computed(() => {
  return usePage().props.sections || [];
});

// Paginate sections
const paginatedSections = computed(() => {
  const { startIndex, endIndex } = pagination.value.sections;
  return sections.value.slice(startIndex, endIndex + 1);
});

// Modal state
const isModalOpen = ref(false);
const openModal = () => (isModalOpen.value = true);
const closeModal = () => (isModalOpen.value = false);

// Pagination Functions
const nextPage = () => {
  const page = pagination.value.sections;
  if (page.endIndex < sections.value.length - 1) {
    page.currentPage++;
    page.startIndex += page.itemsPerPage;
    page.endIndex = Math.min(page.startIndex + page.itemsPerPage - 1, sections.value.length - 1);
  }
};

const prevPage = () => {
  const page = pagination.value.sections;
  if (page.currentPage > 1) {
    page.currentPage--;
    page.startIndex -= page.itemsPerPage;
    page.endIndex = page.startIndex + page.itemsPerPage - 1;
  }
};
</script>

<template>
  <Head title="Manage Users" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-6 pt-20">
      <!-- Manage Sections Page -->
      <div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
          <div class="p-6 border-b dark:border-gray-700 flex justify-between items-center">
            <div>
              <h3 class="font-medium">Manage Sections</h3>
              <p class="text-sm text-gray-500 dark:text-gray-400">Add, edit, or remove sections and assign courses and teachers</p>
            </div>
            <button @click="openModal" class="bg-primary-600 hover:bg-primary-700 text-white px-4 py-2 rounded-lg flex items-center">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
              </svg>
              Add Sections
            </button>
          </div>

          <AddSectionsModal v-if="isModalOpen" @close="closeModal" />

          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
              <thead class="bg-gray-50 dark:bg-gray-700">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Section Name</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Course</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Major</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Year Level</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Teacher</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Students</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Actions</th>
                </tr>
              </thead>
              <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                <tr v-for="(section, index) in paginatedSections" :key="index">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium">{{ section.section_name }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm">{{ section.course_name }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm">{{ section.major_name }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm">{{ section.yearLevel }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm">{{ section.teacher }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm">{{ section.students }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                    <!-- Edit & Delete Buttons -->
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <div class="p-4 border-t dark:border-gray-700 flex items-center justify-between">
            <div class="text-sm text-gray-500 dark:text-gray-400">
              Showing <span class="font-medium">{{ pagination.sections.startIndex + 1 }}</span> to
              <span class="font-medium">{{ Math.min(pagination.sections.endIndex + 1, sections.length) }}</span> of
              <span class="font-medium">{{ sections.length }}</span> entries
            </div>
            <div class="flex space-x-2">
              <button @click="prevPage" :disabled="pagination.sections.currentPage === 1" class="px-3 py-1 rounded border text-sm">
                Previous
              </button>
              <button @click="nextPage" :disabled="pagination.sections.endIndex >= sections.length - 1"
                class="px-3 py-1 rounded text-sm bg-primary-600 text-white">
                Next
              </button>
            </div>
          </div>

        </div>
      </div>
    </div>
  </AppLayout>
</template>
