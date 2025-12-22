<script setup lang="ts">
import AppLayout from "@/layouts/AppLayout.vue";
import { type BreadcrumbItem } from "@/types";
import { Head } from "@inertiajs/vue3";
import { ref, computed } from 'vue';

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


  // Sample data for report students
  const reportStudents = ref([
    {
      name: 'Emily Davis',
      id: 'STD001',
      grade: '92%',
      attendance: '95%',
      status: 'Excellent',
      statusClass: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
      avatar: 'https://i.pravatar.cc/150?img=4'
    },
    {
      name: 'Michael Wilson',
      id: 'STD002',
      grade: '85%',
      attendance: '88%',
      status: 'Good',
      statusClass: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
      avatar: 'https://i.pravatar.cc/150?img=5'
    },
    {
      name: 'Jessica Brown',
      id: 'STD003',
      grade: '78%',
      attendance: '92%',
      status: 'Average',
      statusClass: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
      avatar: 'https://i.pravatar.cc/150?img=6'
    },
    {
      name: 'David Lee',
      id: 'STD004',
      grade: '95%',
      attendance: '98%',
      status: 'Excellent',
      statusClass: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
      avatar: 'https://i.pravatar.cc/150?img=7'
    },
    {
      name: 'Sarah Johnson',
      id: 'STD005',
      grade: '65%',
      attendance: '75%',
      status: 'At Risk',
      statusClass: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
      avatar: 'https://i.pravatar.cc/150?img=8'
    },
  ]);


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

  const paginatedReportStudents = computed(() => {
    const { startIndex, endIndex } = pagination.value.reportStudents;
    return reportStudents.value.slice(startIndex, endIndex + 1);
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

</script>

<template>
    <Head title="Manage Users" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 pt-20">
                 <!-- Reports Page -->
            <div>
              <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden mb-6">
                <div class="p-6 border-b dark:border-gray-700">
                  <h3 class="font-medium">Performance Reports</h3>
                  <p class="text-sm text-gray-500 dark:text-gray-400">Generate and view student performance reports</p>
                </div>
                <div class="p-6">
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-4">
                      <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Department</label>
                        <select class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-500 focus:ring-opacity-50">
                          <option>All Departments</option>
                          <option>College of Computer Studies</option>
                          <option>College of Engineering</option>
                          <option>College of Business</option>
                          <option>College of Arts and Sciences</option>
                        </select>
                      </div>
                      <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Course</label>
                        <select class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-500 focus:ring-opacity-50">
                          <option>All Courses</option>
                          <option>BSIT</option>
                          <option>BSCS</option>
                          <option>BSECE</option>
                          <option>BSME</option>
                        </select>
                      </div>
                    </div>
                    <div class="space-y-4">
                      <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Major</label>
                        <select class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-500 focus:ring-opacity-50">
                          <option>All Majors</option>
                          <option>Web Development</option>
                          <option>Network Administration</option>
                          <option>Database Management</option>
                          <option>Software Engineering</option>
                        </select>
                      </div>
                      <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Year Level</label>
                        <select class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-500 focus:ring-opacity-50">
                          <option>All Year Levels</option>
                          <option>1st Year</option>
                          <option>2nd Year</option>
                          <option>3rd Year</option>
                          <option>4th Year</option>
                        </select>
                      </div>
                    </div>
                  </div>
                  <div class="mt-6 flex justify-end">
                    <button class="bg-primary-600 hover:bg-primary-700 text-white px-4 py-2 rounded-lg">
                      Generate Report
                    </button>
                  </div>
                </div>
              </div>

              <!-- Sample Report Preview -->
              <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
                <div class="p-6 border-b dark:border-gray-700 flex justify-between items-center">
                  <div>
                    <h3 class="font-medium">Student Performance Report</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">College of Computer Studies - BSIT - Section 1A</p>
                  </div>
                  <button class="bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 px-4 py-2 rounded-lg flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    Export PDF
                  </button>
                </div>
                <div class="p-6">
                  <div class="h-80 w-full mb-6">
                    <!-- Chart would be rendered here with a chart library -->
                    <div class="h-full w-full flex items-center justify-center bg-gray-100 dark:bg-gray-700 rounded-lg">
                      <div class="space-y-6 w-full px-8">
                        <h4 class="text-center text-gray-500 dark:text-gray-400">Average Grade by Subject</h4>
                        <!-- Programming -->
                        <div>
                          <div class="flex justify-between mb-1">
                            <span class="text-sm font-medium">Programming</span>
                            <span class="text-sm font-medium">85%</span>
                          </div>
                          <div class="w-full bg-gray-200 dark:bg-gray-600 rounded-full h-2.5">
                            <div class="bg-blue-600 h-2.5 rounded-full" style="width: 85%"></div>
                          </div>
                        </div>

                        <!-- Database -->
                        <div>
                          <div class="flex justify-between mb-1">
                            <span class="text-sm font-medium">Database</span>
                            <span class="text-sm font-medium">78%</span>
                          </div>
                          <div class="w-full bg-gray-200 dark:bg-gray-600 rounded-full h-2.5">
                            <div class="bg-green-600 h-2.5 rounded-full" style="width: 78%"></div>
                          </div>
                        </div>

                        <!-- Networking -->
                        <div>
                          <div class="flex justify-between mb-1">
                            <span class="text-sm font-medium">Networking</span>
                            <span class="text-sm font-medium">92%</span>
                          </div>
                          <div class="w-full bg-gray-200 dark:bg-gray-600 rounded-full h-2.5">
                            <div class="bg-purple-600 h-2.5 rounded-full" style="width: 92%"></div>
                          </div>
                        </div>

                        <!-- Web Development -->
                        <div>
                          <div class="flex justify-between mb-1">
                            <span class="text-sm font-medium">Web Development</span>
                            <span class="text-sm font-medium">88%</span>
                          </div>
                          <div class="w-full bg-gray-200 dark:bg-gray-600 rounded-full h-2.5">
                            <div class="bg-yellow-600 h-2.5 rounded-full" style="width: 88%"></div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>

                  <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                      <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                          <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Student</th>
                          <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">ID</th>
                          <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Average Grade</th>
                          <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Attendance</th>
                          <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                        </tr>
                      </thead>
                      <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        <tr v-for="(student, index) in paginatedReportStudents" :key="index">
                          <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                              <div class="flex-shrink-0 h-10 w-10">
                                <img class="h-10 w-10 rounded-full" :src="student.avatar" alt="" />
                              </div>
                              <div class="ml-4">
                                <div class="text-sm font-medium">{{ student.name }}</div>
                              </div>
                            </div>
                          </td>
                          <td class="px-6 py-4 whitespace-nowrap text-sm">{{ student.id }}</td>
                          <td class="px-6 py-4 whitespace-nowrap text-sm">{{ student.grade }}</td>
                          <td class="px-6 py-4 whitespace-nowrap text-sm">{{ student.attendance }}</td>
                          <td class="px-6 py-4 whitespace-nowrap">
                            <span :class="`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${student.statusClass}`">
                              {{ student.status }}
                            </span>
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                  <div class="p-4 border-t dark:border-gray-700 flex items-center justify-between mt-4">
                    <div class="text-sm text-gray-500 dark:text-gray-400">
                      Showing <span class="font-medium">{{ pagination.reportStudents.startIndex + 1 }}</span> to
                      <span class="font-medium">{{ Math.min(pagination.reportStudents.endIndex + 1, reportStudents.length) }}</span> of
                      <span class="font-medium">{{ reportStudents.length }}</span> entries
                    </div>
                    <div class="flex space-x-2">
                      <button
                        @click="prevPage('reportStudents')"
                        :disabled="pagination.reportStudents.currentPage === 1"
                        :class="[
                          'px-3 py-1 rounded border dark:border-gray-600 text-sm',
                          pagination.reportStudents.currentPage === 1 ? 'opacity-50 cursor-not-allowed' : ''
                        ]"
                      >
                        Previous
                      </button>
                      <button
                        @click="nextPage('reportStudents')"
                        :disabled="pagination.reportStudents.endIndex >= reportStudents.length - 1"
                        :class="[
                          'px-3 py-1 rounded text-sm',
                          pagination.reportStudents.endIndex >= reportStudents.length - 1
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
        </div>

    </AppLayout>
  </template>
