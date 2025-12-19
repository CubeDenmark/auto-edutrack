<template>
    <div class="h-screen flex overflow-hidden" :class="{ 'dark': darkMode }">
      <!-- Sidebar -->
      <div
        class="bg-white dark:bg-gray-800 w-64 flex-shrink-0 transition-all duration-300 ease-in-out border-r dark:border-gray-700"
        :class="{ '-ml-64 md:ml-0': !sidebarOpen }"
      >
        <div class="h-16 flex items-center justify-between px-4 border-b dark:border-gray-700">
          <div class="flex items-center">
            <div class="h-8 w-8 rounded-md bg-indigo-600 flex items-center justify-center text-white font-bold mr-2">
              ED
            </div>
            <h1 class="text-lg font-semibold dark:text-white">EduTrack</h1>
          </div>
        </div>

        <div class="p-4">
          <div class="flex items-center mb-6">
            <div class="h-10 w-10 rounded-full bg-gray-200 dark:bg-gray-700 mr-3">
              <img src="https://i.pravatar.cc/40?img=8" alt="Teacher avatar" class="h-10 w-10 rounded-full" />
            </div>
            <div>
              <p class="text-sm font-medium dark:text-white">Ms. Johnson</p>
              <p class="text-xs text-gray-500 dark:text-gray-400">Science Teacher</p>
            </div>
          </div>

          <nav class="space-y-1">
            <a
              v-for="(item, index) in navItems"
              :key="index"
              @click="activeTab = item.id"
              class="flex items-center px-3 py-2 text-sm font-medium rounded-md cursor-pointer"
              :class="activeTab === item.id ?
                'bg-indigo-50 text-indigo-600 dark:bg-indigo-900/50 dark:text-indigo-300' :
                'text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-700/50'"
            >
              <component :is="item.icon" class="h-5 w-5 mr-2" />
              {{ item.name }}
            </a>
          </nav>
        </div>
      </div>

      <!-- Main content -->
      <div class="flex-1 flex flex-col overflow-hidden bg-gray-50 dark:bg-gray-900">
        <!-- Top navbar -->
        <header class="bg-white dark:bg-gray-800 border-b dark:border-gray-700">
          <div class="h-16 flex items-center justify-between px-4">
            <button
              @click="sidebarOpen = !sidebarOpen"
              class="text-gray-500 dark:text-gray-400 focus:outline-none"
            >
              <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
              </svg>
            </button>

            <div class="flex items-center space-x-4">
              <button
                @click="darkMode = !darkMode"
                class="text-gray-500 dark:text-gray-400 focus:outline-none"
              >
                <svg v-if="darkMode" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
                <svg v-else class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                </svg>
              </button>

              <div class="relative">
                <button class="text-gray-500 dark:text-gray-400 focus:outline-none">
                  <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                  </svg>
                  <span class="absolute top-0 right-0 h-2 w-2 rounded-full bg-red-500"></span>
                </button>
              </div>
            </div>
          </div>
        </header>

        <!-- Content area -->
        <main class="flex-1 overflow-auto p-4">
          <!-- Dashboard -->
          <div v-if="activeTab === 'dashboard'">
            <h2 class="text-2xl font-bold mb-6 dark:text-white">Dashboard</h2>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
              <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center">
                  <div class="p-3 rounded-full bg-indigo-100 dark:bg-indigo-900/50 text-indigo-600 dark:text-indigo-300 mr-4">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                  </div>
                  <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Students</p>
                    <p class="text-2xl font-semibold dark:text-white">28</p>
                  </div>
                </div>
              </div>

              <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center">
                  <div class="p-3 rounded-full bg-green-100 dark:bg-green-900/50 text-green-600 dark:text-green-300 mr-4">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                  </div>
                  <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Attendance Today</p>
                    <p class="text-2xl font-semibold dark:text-white">92%</p>
                  </div>
                </div>
              </div>

              <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center">
                  <div class="p-3 rounded-full bg-yellow-100 dark:bg-yellow-900/50 text-yellow-600 dark:text-yellow-300 mr-4">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z" />
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z" />
                    </svg>
                  </div>
                  <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Class Average</p>
                    <p class="text-2xl font-semibold dark:text-white">B+</p>
                  </div>
                </div>
              </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
              <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold mb-4 dark:text-white">Attendance Overview</h3>
                <div class="h-64 flex items-end space-x-2">
                  <div v-for="(day, index) in weekdays" :key="index" class="flex-1 flex flex-col items-center">
                    <div
                      class="w-full bg-indigo-500 dark:bg-indigo-600 rounded-t"
                      :style="{ height: `${attendanceData[index]}%` }"
                    ></div>
                    <p class="text-xs mt-2 text-gray-500 dark:text-gray-400">{{ day }}</p>
                  </div>
                </div>
              </div>

              <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold mb-4 dark:text-white">Grade Distribution</h3>
                <div class="h-64 flex items-end space-x-2">
                  <div v-for="(grade, index) in grades" :key="index" class="flex-1 flex flex-col items-center">
                    <div
                      class="w-full rounded-t"
                      :class="gradeColors[index]"
                      :style="{ height: `${gradeData[index]}%` }"
                    ></div>
                    <p class="text-xs mt-2 text-gray-500 dark:text-gray-400">{{ grade }}</p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Attendance -->
          <div v-if="activeTab === 'attendance'">
            <h2 class="text-2xl font-bold mb-6 dark:text-white">Attendance</h2>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
              <div class="flex items-center justify-between p-4 border-b dark:border-gray-700">
                <div class="flex items-center">
                  <span class="text-gray-700 dark:text-gray-300 mr-2">Date:</span>
                  <span class="font-medium dark:text-white">{{ currentDate }}</span>
                </div>
                <div>
                  <button class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                    Save Attendance
                  </button>
                </div>
              </div>

              <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                  <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Student</th>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">ID</th>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Notes</th>
                    </tr>
                  </thead>
                  <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    <tr v-for="(student, index) in students" :key="index">
                      <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                          <div class="h-10 w-10 flex-shrink-0">
                            <img :src="student.avatar" alt="" class="h-10 w-10 rounded-full" />
                          </div>
                          <div class="ml-4">
                            <div class="text-sm font-medium text-gray-900 dark:text-white">{{ student.name }}</div>
                          </div>
                        </div>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-500 dark:text-gray-400">{{ student.id }}</div>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap">
                        <select
                          v-model="student.status"
                          class="block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white text-sm"
                        >
                          <option value="present">Present</option>
                          <option value="absent">Absent</option>
                          <option value="late">Late</option>
                        </select>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap">
                        <input
                          type="text"
                          v-model="student.notes"
                          class="block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white text-sm"
                          placeholder="Add notes..."
                        />
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>

          <!-- Grades -->
          <div v-if="activeTab === 'grades'">
            <h2 class="text-2xl font-bold mb-6 dark:text-white">Grades</h2>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
              <div class="flex items-center justify-between p-4 border-b dark:border-gray-700">
                <div>
                  <select class="block rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white">
                    <option>Science Quiz #1</option>
                    <option>Science Midterm</option>
                    <option>Lab Report #2</option>
                    <option>Final Project</option>
                  </select>
                </div>
                <div>
                  <button class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                    Save Grades
                  </button>
                </div>
              </div>

              <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                  <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Student</th>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">ID</th>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Grade</th>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Comments</th>
                    </tr>
                  </thead>
                  <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    <tr v-for="(student, index) in students" :key="index">
                      <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                          <div class="h-10 w-10 flex-shrink-0">
                            <img :src="student.avatar" alt="" class="h-10 w-10 rounded-full" />
                          </div>
                          <div class="ml-4">
                            <div class="text-sm font-medium text-gray-900 dark:text-white">{{ student.name }}</div>
                          </div>
                        </div>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-500 dark:text-gray-400">{{ student.id }}</div>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap">
                        <input
                          type="text"
                          v-model="student.grade"
                          class="block w-20 rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white text-sm"
                        />
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap">
                        <input
                          type="text"
                          v-model="student.gradeComments"
                          class="block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white text-sm"
                          placeholder="Add comments..."
                        />
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>

          <!-- Behavior Reports -->
          <div v-if="activeTab === 'behavior'">
            <div class="flex justify-between items-center mb-6">
              <h2 class="text-2xl font-bold dark:text-white">Behavior Reports</h2>
              <button class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                New Report
              </button>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
              <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                  <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Date</th>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Student</th>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Type</th>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Description</th>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                    </tr>
                  </thead>
                  <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    <tr v-for="(report, index) in behaviorReports" :key="index">
                      <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900 dark:text-white">{{ report.date }}</div>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                          <div class="h-8 w-8 flex-shrink-0">
                            <img :src="report.avatar" alt="" class="h-8 w-8 rounded-full" />
                          </div>
                          <div class="ml-4">
                            <div class="text-sm font-medium text-gray-900 dark:text-white">{{ report.student }}</div>
                          </div>
                        </div>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap">
                        <span
                          class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                          :class="{
                            'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300': report.type === 'Positive',
                            'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-300': report.type === 'Concern',
                            'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-300': report.type === 'Incident'
                          }"
                        >
                          {{ report.type }}
                        </span>
                      </td>
                      <td class="px-6 py-4">
                        <div class="text-sm text-gray-500 dark:text-gray-400">{{ report.description }}</div>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <a href="#" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 mr-3">Edit</a>
                        <a href="#" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">Delete</a>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </main>
      </div>
    </div>
  </template>

  <script setup>
  import { ref, computed } from 'vue';

  // State
  const sidebarOpen = ref(true);
  const darkMode = ref(false);
  const activeTab = ref('dashboard');

  // Navigation items
  const navItems = [
    { id: 'dashboard', name: 'Dashboard', icon: 'HomeIcon' },
    { id: 'attendance', name: 'Attendance', icon: 'CheckCircleIcon' },
    { id: 'grades', name: 'Grades', icon: 'AwardIcon' },
    { id: 'behavior', name: 'Behavior Reports', icon: 'ClipboardIcon' }
  ];

  // Mock data
  const weekdays = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri'];
  const attendanceData = [85, 92, 78, 95, 88];

  const grades = ['A', 'B', 'C', 'D', 'F'];
  const gradeData = [35, 40, 15, 7, 3];
  const gradeColors = [
    'bg-green-500 dark:bg-green-600',
    'bg-blue-500 dark:bg-blue-600',
    'bg-yellow-500 dark:bg-yellow-600',
    'bg-orange-500 dark:bg-orange-600',
    'bg-red-500 dark:bg-red-600'
  ];

  const students = [
    { id: 'S001', name: 'Emma Thompson', avatar: 'https://i.pravatar.cc/40?img=1', status: 'present', notes: '', grade: 'A', gradeComments: 'Excellent work!' },
    { id: 'S002', name: 'James Wilson', avatar: 'https://i.pravatar.cc/40?img=2', status: 'present', notes: '', grade: 'B+', gradeComments: 'Good effort' },
    { id: 'S003', name: 'Olivia Martinez', avatar: 'https://i.pravatar.cc/40?img=3', status: 'absent', notes: 'Doctor appointment', grade: 'A-', gradeComments: '' },
    { id: 'S004', name: 'Noah Johnson', avatar: 'https://i.pravatar.cc/40?img=4', status: 'present', notes: '', grade: 'C', gradeComments: 'Needs improvement' },
    { id: 'S005', name: 'Sophia Lee', avatar: 'https://i.pravatar.cc/40?img=5', status: 'late', notes: 'Bus delay', grade: 'B', gradeComments: '' }
  ];

  const behaviorReports = [
    { date: '2023-03-15', student: 'Emma Thompson', avatar: 'https://i.pravatar.cc/40?img=1', type: 'Positive', description: 'Helped another student with their assignment.' },
    { date: '2023-03-14', student: 'James Wilson', avatar: 'https://i.pravatar.cc/40?img=2', type: 'Concern', description: 'Distracted during class discussion.' },
    { date: '2023-03-12', student: 'Noah Johnson', avatar: 'https://i.pravatar.cc/40?img=4', type: 'Incident', description: 'Argument with another student during lunch break.' },
    { date: '2023-03-10', student: 'Sophia Lee', avatar: 'https://i.pravatar.cc/40?img=5', type: 'Positive', description: 'Excellent presentation in class.' }
  ];

  // Computed properties
  const currentDate = computed(() => {
    const date = new Date();
    return date.toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
  });

  // Icons (simplified for this example)
  const HomeIcon = {
    template: `<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
    </svg>`
  };

  const CheckCircleIcon = {
    template: `<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
    </svg>`
  };

  const AwardIcon = {
    template: `<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
    </svg>`
  };

  const ClipboardIcon = {
    template: `<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
    </svg>`
  };
  </script>

  <style>
  /* Add any additional global styles here */
  .dark {
    color-scheme: dark;
  }

  /* For better form controls in dark mode */
  .dark select, .dark input {
    color-scheme: dark;
  }
  </style>
