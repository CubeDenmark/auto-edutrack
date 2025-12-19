<script setup lang="ts">
import AppLayout from "@/layouts/AppLayout.vue";
import { type BreadcrumbItem } from "@/types";
import { Head, usePage } from "@inertiajs/vue3";
import { ref, computed } from 'vue';
import AddDepartmentModal from "@/components/AddDepartmentModal.vue";

// Breadcrumbs
const breadcrumbs: BreadcrumbItem[] = [
  {
    title: "Manage Department",
    href: "/departments",
  },
];

// Fetch departments dynamically from Inertia
const departments = ref(usePage().props.departments || []);

// Pagination state
const pagination = ref({
  users: { currentPage: 1, itemsPerPage: 5, startIndex: 0, endIndex: 4 },
  departments: { currentPage: 1, itemsPerPage: 5, startIndex: 0, endIndex: 4 },
  courses: { currentPage: 1, itemsPerPage: 5, startIndex: 0, endIndex: 4 },
  majors: { currentPage: 1, itemsPerPage: 5, startIndex: 0, endIndex: 4 },
  yearLevels: { currentPage: 1, itemsPerPage: 5, startIndex: 0, endIndex: 4 },
  sections: { currentPage: 1, itemsPerPage: 5, startIndex: 0, endIndex: 4 },
  reportStudents: { currentPage: 1, itemsPerPage: 5, startIndex: 0, endIndex: 4 }
});

// Computed property for paginated departments
const paginatedDepartments = computed(() => {
  const { startIndex, endIndex } = pagination.value.departments;
  return departments.value.slice(startIndex, endIndex + 1);
});

// Sample data for users
const users = ref([
  {
    id: 'ADM001',
    name: 'John Doe',
    email: 'john.doe@example.com',
    role: 'Admin',
    status: 'Active',
    statusClass: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
    avatar: 'https://i.pravatar.cc/150?img=1',
    type: 'admin'
  },
  {
    id: 'TCH001',
    name: 'Jane Smith',
    email: 'jane.smith@example.com',
    role: 'Teacher',
    status: 'Active',
    statusClass: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
    avatar: 'https://i.pravatar.cc/150?img=2',
    type: 'teacher'
  }
]);

// User type filter
const selectedUserType = ref('all');
const filteredUsers = computed(() => {
  if (selectedUserType.value === 'all') return users.value;
  return users.value.filter(user => user.type === selectedUserType.value);
});

// User types for filtering
const userTypes = ref([
  { id: 'all', name: 'All Users' },
  { id: 'admin', name: 'Admins' },
  { id: 'teacher', name: 'Teachers' },
  { id: 'student', name: 'Students' },
  { id: 'parent', name: 'Parents' },
]);

// Edit user function
const editUser = (user: any) => {
  modalType.value = 'user';
  modalTitle.value = 'Edit User';
  formData.value = { ...user };
  showModal.value = true;
};

// Delete user function
const deleteUser = (user: any) => {
  modalType.value = 'user';
  itemToDelete.value = user;
  showDeleteModal.value = true;
};

// Pagination Functions
const nextPage = (type: string) => {
  const totalItems = getTotalItems(type);
  if (pagination.value[type].endIndex < totalItems - 1) {
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
  const { itemsPerPage, currentPage } = pagination.value[type];
  pagination.value[type].startIndex = (currentPage - 1) * itemsPerPage;
  pagination.value[type].endIndex = pagination.value[type].startIndex + itemsPerPage - 1;
};

// Get total items for pagination
const getTotalItems = (type: string) => {
  return type === 'users' ? filteredUsers.value.length :
    type === 'departments' ? departments.value.length :
    type === 'courses' ? courses.value.length :
    type === 'majors' ? majors.value.length :
    type === 'yearLevels' ? yearLevels.value.length :
    type === 'sections' ? sections.value.length :
    reportStudents.value.length;
};

// Add extra users for pagination demonstration
for (let i = 1; i <= 15; i++) {
  if (i <= 10) {
    users.value.push({
      id: `STD${(10 + i).toString().padStart(3, '0')}`,
      name: `Student ${i + 10}`,
      email: `student${i + 10}@example.com`,
      role: 'Student',
      status: 'Active',
      statusClass: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
      avatar: `https://i.pravatar.cc/150?img=${20 + i}`,
      type: 'student'
    });
  }
}

// Modal state
const isModalOpen = ref(false);
const openModal = () => (isModalOpen.value = true);
const closeModal = () => (isModalOpen.value = false);

</script>


<template>
    <Head title="Manage Departments" />

    <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-6 pt-20">
      <!-- Manage Departments Page -->
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
        <div class="p-6 border-b dark:border-gray-700 flex justify-between items-center">
          <div>
            <h3 class="font-medium">Manage Departments</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400">Add, edit, or remove departments</p>
          </div>
          <button @click="openModal" class="bg-primary-600 hover:bg-primary-700 text-white px-4 py-2 rounded-lg flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
            Add Department
          </button>
        </div>

        <div v-if="isModalOpen">
          <AddDepartmentModal @close="closeModal" />
        </div>

        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-700">
              <tr>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Department Name</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Code</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Head</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Courses</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
              </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
              <tr v-for="(department, index) in paginatedDepartments" :key="index">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm font-medium">{{ department.department_name }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm">{{ department.department_code }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm">{{ department.head }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm">{{ department.courses }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                  <!-- Actions -->
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="p-4 border-t dark:border-gray-700 flex items-center justify-between">
          <div class="text-sm text-gray-500 dark:text-gray-400">
            Showing <span class="font-medium">{{ pagination.departments.startIndex + 1 }}</span> to
            <span class="font-medium">{{ Math.min(pagination.departments.endIndex + 1, departments.length) }}</span> of
            <span class="font-medium">{{ departments.length }}</span> entries
          </div>
          <div class="flex space-x-2">
            <button
              @click="prevPage('departments')"
              :disabled="pagination.departments.currentPage === 1"
              :class="[
                'px-3 py-1 rounded border dark:border-gray-600 text-sm',
                pagination.departments.currentPage === 1 ? 'opacity-50 cursor-not-allowed' : ''
              ]"
            >
              Previous
            </button>
            <button
              @click="nextPage('departments')"
              :disabled="pagination.departments.endIndex >= departments.length - 1"
              :class="[
                'px-3 py-1 rounded text-sm',
                pagination.departments.endIndex >= departments.length - 1
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
  </AppLayout>
  </template>
