<script setup lang="ts">
import AppLayout from "@/layouts/AppLayout.vue";
import { type BreadcrumbItem } from "@/types";
import { Head, usePage } from "@inertiajs/vue3";
import { ref, computed } from 'vue';

import AddMajorModal from "@/components/AddMajorModal.vue";

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: "Manage Users",
    href: "/users",
  },
];


defineProps<{
  name?: string;
}>();

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
    },
    {
      id: 'TCH002',
      name: 'Robert Johnson',
      email: 'robert.johnson@example.com',
      role: 'Teacher',
      status: 'Active',
      statusClass: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
      avatar: 'https://i.pravatar.cc/150?img=3',
      type: 'teacher'
    },
    {
      id: 'STD001',
      name: 'Emily Davis',
      email: 'emily.davis@example.com',
      role: 'Student',
      status: 'Active',
      statusClass: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
      avatar: 'https://i.pravatar.cc/150?img=4',
      type: 'student'
    },
    {
      id: 'STD002',
      name: 'Michael Wilson',
      email: 'michael.wilson@example.com',
      role: 'Student',
      status: 'Inactive',
      statusClass: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
      avatar: 'https://i.pravatar.cc/150?img=5',
      type: 'student'
    },
    {
      id: 'PAR001',
      name: 'Sarah Brown',
      email: 'sarah.brown@example.com',
      role: 'Parent',
      status: 'Active',
      statusClass: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
      avatar: 'https://i.pravatar.cc/150?img=6',
      type: 'parent'
    },
  ]);

  // Sample data for majors (NEW!)
  const majors = computed(() => usePage().props.majors || []);


const selectedUserType = ref('all');

const filteredUsers = computed(() => {
    if (selectedUserType.value === 'all') {
      return users.value;
    }
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

  const paginatedMajors = computed(() => {
    const { startIndex, endIndex } = pagination.value.majors;
    return majors.value.slice(startIndex, endIndex + 1);
  });

//   const editUser = (user: any) => {
//     modalType.value = 'user';
//     modalTitle.value = 'Edit User';
//     formData.value = { ...user };
//     showModal.value = true;
//   };

//   const deleteUser = (user: any) => {
//     modalType.value = 'user';
//     itemToDelete.value = user;
//     showDeleteModal.value = true;
//   };



  const nextPage = (type: string) => {
    if (pagination.value[type].endIndex < (type === 'users' ? filteredUsers.value.length - 1 :
        type === 'departments' ? departments.value.length - 1 :
        type === 'courses' ? courses.value.length - 1 :
        type === 'majors' ? majors.value.length - 1 :
        type === 'yearLevels' ? yearLevels.value.length - 1 :
        type === 'sections' ? sections.value.length - 1 :
        reportStudents.value.length - 1)) {
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


     // Add more sample data for pagination demonstration
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


    const isModalOpen = ref(false);
    const openModal = () => (isModalOpen.value = true);
    const closeModal = () => (isModalOpen.value = false);

</script>

<template>
    <Head title="Manage Users" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 pt-20">
                 <!-- Manage Majors Page (NEW!) -->
            <div>
              <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
                <div class="p-6 border-b dark:border-gray-700 flex justify-between items-center">
                  <div>
                    <h3 class="font-medium">Manage Majors</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Add, edit, or remove majors and assign to courses</p>
                  </div>
                  <button @click="openModal" class="bg-primary-600 hover:bg-primary-700 text-white px-4 py-2 rounded-lg flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    Add Major
                  </button>
                </div>

                <div v-if="isModalOpen">
                  <AddMajorModal @close="closeModal" />
                </div>

                <div class="overflow-x-auto">
                  <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                      <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Major Name</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Code</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Course</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Students</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                      </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                      <tr v-for="(major, index) in paginatedMajors" :key="index">
                        <td class="px-6 py-4 whitespace-nowrap">
                          <div class="text-sm font-medium">{{ major.major_name }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">{{ major.major_code }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">{{ major.course }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">{{ major.students }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                          <!-- <div class="flex space-x-2">
                            <button @click="editMajor(major)" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">Edit</button>
                            <button @click="deleteMajor(major)" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">Delete</button>
                          </div> -->
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
                <div class="p-4 border-t dark:border-gray-700 flex items-center justify-between">
                  <div class="text-sm text-gray-500 dark:text-gray-400">
                    Showing <span class="font-medium">{{ pagination.majors.startIndex + 1 }}</span> to
                    <span class="font-medium">{{ Math.min(pagination.majors.endIndex + 1, majors.length) }}</span> of
                    <span class="font-medium">{{ majors.length }}</span> entries
                  </div>
                  <div class="flex space-x-2">
                    <button
                      @click="prevPage('majors')"
                      :disabled="pagination.majors.currentPage === 1"
                      :class="[
                        'px-3 py-1 rounded border dark:border-gray-600 text-sm',
                        pagination.majors.currentPage === 1 ? 'opacity-50 cursor-not-allowed' : ''
                      ]"
                    >
                      Previous
                    </button>
                    <button
                      @click="nextPage('majors')"
                      :disabled="pagination.majors.endIndex >= majors.length - 1"
                      :class="[
                        'px-3 py-1 rounded text-sm',
                        pagination.majors.endIndex >= majors.length - 1
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
