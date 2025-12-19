<template>
    <div class="flex h-screen bg-gray-100 dark:bg-gray-900">
      <!-- Overlay for mobile when sidebar is open -->
      <div
        v-if="sidebarOpen"
        @click="toggleSidebar"
        class="fixed inset-0 bg-black bg-opacity-50 z-10 md:hidden"
      ></div>

      <!-- Sidebar -->
      <div
        :class="[
          'bg-white dark:bg-gray-800 shadow-lg transition-all duration-300 z-20',
          sidebarOpen ? 'w-64' : 'w-20',
          'md:relative fixed h-full',
          sidebarOpen ? 'left-0' : '-left-20 md:left-0'
        ]"
      >
        <div class="flex items-center justify-between h-16 px-4 border-b dark:border-gray-700">
          <div class="flex items-center">
            <div class="text-xl font-bold text-primary dark:text-primary-foreground flex items-center">
              <graduation-cap-icon class="h-8 w-8" />
              <span :class="[sidebarOpen ? 'ml-2 block' : 'hidden']">EduTrack</span>
            </div>
          </div>
          <button
            @click="toggleSidebar"
            class="md:hidden text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
          >
            <x-icon v-if="sidebarOpen" class="h-6 w-6" />
            <menu-icon v-else class="h-6 w-6" />
          </button>
        </div>

        <div class="py-4 overflow-y-auto max-h-[calc(100vh-8rem)]">
          <nav>
            <ul>
              <li v-for="(item, index) in menuItems" :key="index" class="mb-2">
                <a
                  href="#"
                  @click.prevent="activeMenuItem = item.name; isMobile && toggleSidebar()"
                  :class="[
                    'flex items-center px-4 py-3 text-sm transition-colors duration-200',
                    activeMenuItem === item.name
                      ? 'bg-primary/10 text-primary dark:bg-primary/20 dark:text-primary-foreground border-l-4 border-primary'
                      : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700',
                  ]"
                >
                  <component :is="item.icon" class="h-5 w-5 flex-shrink-0" />
                  <span :class="[sidebarOpen ? 'ml-3 block' : 'hidden']">{{ item.name }}</span>
                </a>
              </li>
            </ul>
          </nav>
        </div>

        <div class="absolute bottom-0 w-full border-t dark:border-gray-700 p-4">
          <div class="flex items-center" :class="[sidebarOpen ? 'justify-between' : 'justify-center']">
            <div v-if="sidebarOpen" class="flex items-center">
              <img
                src="/placeholder.svg?height=40&width=40"
                alt="User"
                class="h-10 w-10 rounded-full"
              />
              <div class="ml-3">
                <p class="text-sm font-medium text-gray-700 dark:text-gray-200">John Doe</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">Administrator</p>
              </div>
            </div>
            <button
              @click="toggleSidebar"
              class="hidden md:block text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
            >
              <chevron-left-icon v-if="sidebarOpen" class="h-5 w-5" />
              <chevron-right-icon v-else class="h-5 w-5" />
            </button>
          </div>
        </div>
      </div>

      <!-- Main Content -->
      <div class="flex-1 flex flex-col overflow-hidden w-full">
        <!-- Top Navigation -->
        <header class="bg-white dark:bg-gray-800 shadow-sm z-10">
          <div class="flex items-center justify-between h-16 px-4 md:px-6">
            <div class="flex items-center">
              <button
                @click="toggleSidebar"
                class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 mr-4"
              >
                <menu-icon class="h-6 w-6" />
              </button>
              <h1 class="text-lg md:text-xl font-semibold text-gray-800 dark:text-gray-200 truncate">
                {{ activeMenuItem }}
              </h1>
            </div>
            <div class="flex items-center space-x-2 md:space-x-4">
              <button class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                <bell-icon class="h-5 w-5 md:h-6 md:w-6" />
              </button>
              <button @click="toggleDarkMode" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                <sun-icon v-if="darkMode" class="h-5 w-5 md:h-6 md:w-6" />
                <moon-icon v-else class="h-5 w-5 md:h-6 md:w-6" />
              </button>
            </div>
          </div>
        </header>

        <!-- Content Area -->
        <main class="flex-1 overflow-y-auto p-4 md:p-6 bg-gray-100 dark:bg-gray-900">
          <!-- Dashboard -->
          <div v-if="activeMenuItem === 'Dashboard'" class="space-y-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6">
              <div
                v-for="(stat, index) in stats"
                :key="index"
                class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 md:p-6 flex items-center"
              >
                <div
                  class="rounded-full p-3 mr-4"
                  :class="`bg-${stat.color}-100 dark:bg-${stat.color}-900/30 text-${stat.color}-600 dark:text-${stat.color}-400`"
                >
                  <component :is="stat.icon" class="h-5 w-5 md:h-6 md:w-6" />
                </div>
                <div>
                  <p class="text-sm text-gray-500 dark:text-gray-400">{{ stat.name }}</p>
                  <p class="text-xl md:text-2xl font-semibold text-gray-800 dark:text-gray-200">
                    {{ stat.value }}
                  </p>
                </div>
              </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
              <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 md:p-6">
                <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">
                  Attendance Overview
                </h2>
                <div class="h-48 md:h-64 flex items-center justify-center">
                  <div class="w-full h-full flex items-center justify-center">
                    <!-- Placeholder for chart -->
                    <div class="relative w-32 md:w-40 h-32 md:h-40">
                      <div class="absolute inset-0 flex items-center justify-center">
                        <p class="text-xl md:text-2xl font-bold text-gray-800 dark:text-gray-200">85%</p>
                      </div>
                      <svg class="w-full h-full" viewBox="0 0 100 100">
                        <circle
                          class="text-gray-200 dark:text-gray-700 stroke-current"
                          stroke-width="10"
                          cx="50"
                          cy="50"
                          r="40"
                          fill="transparent"
                        ></circle>
                        <circle
                          class="text-primary stroke-current"
                          stroke-width="10"
                          stroke-linecap="round"
                          stroke-dasharray="251.2"
                          stroke-dashoffset="37.68"
                          cx="50"
                          cy="50"
                          r="40"
                          fill="transparent"
                          transform="rotate(-90 50 50)"
                        ></circle>
                      </svg>
                    </div>
                  </div>
                </div>
                <div class="grid grid-cols-3 gap-2 md:gap-4 mt-4">
                  <div class="text-center">
                    <p class="text-xs md:text-sm text-gray-500 dark:text-gray-400">Present</p>
                    <p class="text-base md:text-lg font-semibold text-gray-800 dark:text-gray-200">85%</p>
                  </div>
                  <div class="text-center">
                    <p class="text-xs md:text-sm text-gray-500 dark:text-gray-400">Absent</p>
                    <p class="text-base md:text-lg font-semibold text-gray-800 dark:text-gray-200">10%</p>
                  </div>
                  <div class="text-center">
                    <p class="text-xs md:text-sm text-gray-500 dark:text-gray-400">Late</p>
                    <p class="text-base md:text-lg font-semibold text-gray-800 dark:text-gray-200">5%</p>
                  </div>
                </div>
              </div>

              <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 md:p-6">
                <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">
                  Grade Distribution
                </h2>
                <div class="h-48 md:h-64 flex items-center justify-center">
                  <!-- Placeholder for chart -->
                  <div class="w-full h-full flex flex-col space-y-2">
                    <div v-for="(grade, index) in gradeDistribution" :key="index" class="flex items-center">
                      <span class="w-6 md:w-8 text-xs md:text-sm text-gray-600 dark:text-gray-400">{{ grade.label }}</span>
                      <div class="flex-1 h-5 md:h-6 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden ml-2">
                        <div
                          class="h-full rounded-full bg-primary"
                          :style="{ width: `${grade.percentage}%` }"
                        ></div>
                      </div>
                      <span class="ml-2 text-xs md:text-sm text-gray-600 dark:text-gray-400">{{ grade.percentage }}%</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 md:p-6">
              <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">
                Recent Activities
              </h2>
              <div class="overflow-x-auto -mx-4 md:mx-0">
                <div class="inline-block min-w-full align-middle px-4 md:px-0">
                  <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead>
                      <tr>
                        <th
                          class="px-3 md:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"
                        >
                          Student
                        </th>
                        <th
                          class="px-3 md:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"
                        >
                          Activity
                        </th>
                        <th
                          class="px-3 md:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"
                        >
                          Date
                        </th>
                        <th
                          class="px-3 md:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"
                        >
                          Status
                        </th>
                      </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                      <tr v-for="(activity, index) in recentActivities" :key="index">
                        <td class="px-3 md:px-6 py-4 whitespace-nowrap">
                          <div class="flex items-center">
                            <div class="flex-shrink-0 h-8 w-8 md:h-10 md:w-10">
                              <img
                                :src="`/placeholder.svg?height=40&width=40&text=${activity.student.charAt(0)}`"
                                alt=""
                                class="h-8 w-8 md:h-10 md:w-10 rounded-full"
                              />
                            </div>
                            <div class="ml-2 md:ml-4">
                              <div class="text-xs md:text-sm font-medium text-gray-900 dark:text-gray-100">
                                {{ activity.student }}
                              </div>
                              <div class="text-xs text-gray-500 dark:text-gray-400">
                                {{ activity.class }}
                              </div>
                            </div>
                          </div>
                        </td>
                        <td class="px-3 md:px-6 py-4 whitespace-nowrap">
                          <div class="text-xs md:text-sm text-gray-900 dark:text-gray-100">
                            {{ activity.activity }}
                          </div>
                        </td>
                        <td class="px-3 md:px-6 py-4 whitespace-nowrap">
                          <div class="text-xs md:text-sm text-gray-500 dark:text-gray-400">
                            {{ activity.date }}
                          </div>
                        </td>
                        <td class="px-3 md:px-6 py-4 whitespace-nowrap">
                          <span
                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                            :class="{
                              'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400': activity.status === 'Completed',
                              'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400': activity.status === 'Pending',
                              'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400': activity.status === 'Failed'
                            }"
                          >
                            {{ activity.status }}
                          </span>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>

          <!-- Students -->
          <div v-else-if="activeMenuItem === 'Students'" class="space-y-6">
            <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-4">
              <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">
                Student List
              </h2>
              <div class="flex flex-col sm:flex-row gap-2">
                <div class="relative">
                  <search-icon class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 h-4 w-4 md:h-5 md:w-5" />
                  <input
                    type="text"
                    placeholder="Search students..."
                    class="w-full pl-10 pr-4 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-primary"
                  />
                </div>
                <button class="px-4 py-2 bg-primary text-white text-sm rounded-lg hover:bg-primary/90 transition-colors">
                  Add Student
                </button>
              </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden">
              <div class="overflow-x-auto -mx-4 md:mx-0">
                <div class="inline-block min-w-full align-middle px-4 md:px-0">
                  <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead>
                      <tr>
                        <th
                          class="px-3 md:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"
                        >
                          Name
                        </th>
                        <th
                          class="px-3 md:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"
                        >
                          ID
                        </th>
                        <th
                          class="px-3 md:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"
                        >
                          Class
                        </th>
                        <th
                          class="px-3 md:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"
                        >
                          Attendance
                        </th>
                        <th
                          class="px-3 md:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"
                        >
                          Grade
                        </th>
                        <th
                          class="px-3 md:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"
                        >
                          Actions
                        </th>
                      </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                      <tr v-for="(student, index) in students" :key="index">
                        <td class="px-3 md:px-6 py-4 whitespace-nowrap">
                          <div class="flex items-center">
                            <div class="flex-shrink-0 h-8 w-8 md:h-10 md:w-10">
                              <img
                                :src="`/placeholder.svg?height=40&width=40&text=${student.name.charAt(0)}`"
                                alt=""
                                class="h-8 w-8 md:h-10 md:w-10 rounded-full"
                              />
                            </div>
                            <div class="ml-2 md:ml-4">
                              <div class="text-xs md:text-sm font-medium text-gray-900 dark:text-gray-100">
                                {{ student.name }}
                              </div>
                              <div class="text-xs text-gray-500 dark:text-gray-400 hidden sm:block">
                                {{ student.email }}
                              </div>
                            </div>
                          </div>
                        </td>
                        <td class="px-3 md:px-6 py-4 whitespace-nowrap">
                          <div class="text-xs md:text-sm text-gray-900 dark:text-gray-100">
                            {{ student.id }}
                          </div>
                        </td>
                        <td class="px-3 md:px-6 py-4 whitespace-nowrap">
                          <div class="text-xs md:text-sm text-gray-900 dark:text-gray-100">
                            {{ student.class }}
                          </div>
                        </td>
                        <td class="px-3 md:px-6 py-4 whitespace-nowrap">
                          <div class="flex items-center">
                            <div class="w-12 md:w-16 bg-gray-200 dark:bg-gray-700 rounded-full h-1.5 md:h-2.5 mr-2">
                              <div
                                class="bg-primary h-1.5 md:h-2.5 rounded-full"
                                :style="{ width: `${student.attendance}%` }"
                              ></div>
                            </div>
                            <span class="text-xs md:text-sm text-gray-900 dark:text-gray-100">
                              {{ student.attendance }}%
                            </span>
                          </div>
                        </td>
                        <td class="px-3 md:px-6 py-4 whitespace-nowrap">
                          <div class="text-xs md:text-sm text-gray-900 dark:text-gray-100">
                            {{ student.averageGrade }}
                          </div>
                        </td>
                        <td class="px-3 md:px-6 py-4 whitespace-nowrap text-xs md:text-sm text-gray-500 dark:text-gray-400">
                          <div class="flex space-x-1 md:space-x-2">
                            <button class="text-primary hover:text-primary/80">
                              <eye-icon class="h-4 w-4 md:h-5 md:w-5" />
                            </button>
                            <button class="text-yellow-500 hover:text-yellow-400">
                              <edit-icon class="h-4 w-4 md:h-5 md:w-5" />
                            </button>
                            <button class="text-red-500 hover:text-red-400">
                              <trash-icon class="h-4 w-4 md:h-5 md:w-5" />
                            </button>
                          </div>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
              <div class="px-4 md:px-6 py-3 md:py-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-t dark:border-gray-700">
                <div class="text-xs md:text-sm text-gray-500 dark:text-gray-400">
                  Showing <span class="font-medium">1</span> to
                  <span class="font-medium">10</span> of
                  <span class="font-medium">{{ students.length }}</span> results
                </div>
                <div class="flex space-x-2">
                  <button
                    class="px-2 md:px-3 py-1 border border-gray-300 dark:border-gray-600 rounded-md text-xs md:text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700"
                  >
                    Previous
                  </button>
                  <button
                    class="px-2 md:px-3 py-1 border border-gray-300 dark:border-gray-600 rounded-md text-xs md:text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700"
                  >
                    Next
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Other sections remain similar with responsive adjustments -->
          <!-- Default Content -->
          <div v-if="!['Dashboard', 'Students', 'Grades', 'Subjects'].includes(activeMenuItem)" class="flex items-center justify-center h-full">
            <div class="text-center p-4">
              <h2 class="text-xl md:text-2xl font-semibold text-gray-800 dark:text-gray-200 mb-2">
                Select an option from the sidebar
              </h2>
              <p class="text-sm md:text-base text-gray-500 dark:text-gray-400">
                Choose a menu item to view the corresponding content.
              </p>
            </div>
          </div>
        </main>
      </div>
    </div>
  </template>

  <script setup>
  import { ref, computed, onMounted, onUnmounted } from 'vue'
  import {
    Bell,
    ChevronLeft,
    ChevronRight,
    Edit,
    Eye,
    GraduationCap,
    Menu,
    Moon,
    MoreHorizontal,
    Search,
    Sun,
    Trash,
    Users,
    X,
    BookOpen,
    BarChart,
    Calendar,
    Home,
    Award,
    FileText,
    Settings,
    Clock,
    PieChart,
    Layers,
    Book,
    Calculator,
    Globe,
    Microscope,
    Music,
    Palette,
    Dumbbell
  } from 'lucide-vue-next'

  // State
  const sidebarOpen = ref(window.innerWidth >= 768) // Default open on desktop, closed on mobile
  const darkMode = ref(false)
  const activeMenuItem = ref('Dashboard')
  const selectedClass = ref('')
  const selectedSubject = ref('')
  const isMobile = ref(window.innerWidth < 768)

  // Responsive handler
  const handleResize = () => {
    isMobile.value = window.innerWidth < 768
    if (!isMobile.value && !sidebarOpen.value) {
      sidebarOpen.value = true
    }
  }

  // Lifecycle hooks for responsive behavior
  onMounted(() => {
    window.addEventListener('resize', handleResize)

    // Initialize dark mode based on system preference
    if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
      darkMode.value = true
      document.documentElement.classList.add('dark')
    }
  })

  onUnmounted(() => {
    window.removeEventListener('resize', handleResize)
  })

  // Toggle functions
  const toggleSidebar = () => {
    sidebarOpen.value = !sidebarOpen.value
  }

  const toggleDarkMode = () => {
    darkMode.value = !darkMode.value
    if (darkMode.value) {
      document.documentElement.classList.add('dark')
    } else {
      document.documentElement.classList.remove('dark')
    }
  }

  // Menu items
  const menuItems = [
    { name: 'Dashboard', icon: Home },
    { name: 'Students', icon: Users },
    { name: 'Attendance', icon: Clock },
    { name: 'Grades', icon: Award },
    { name: 'Subjects', icon: BookOpen },
    { name: 'Reports', icon: FileText },
    { name: 'Calendar', icon: Calendar },
    { name: 'Analytics', icon: BarChart },
    { name: 'Settings', icon: Settings }
  ]

  // Dashboard stats
  const stats = [
    { name: 'Total Students', value: '1,234', icon: Users, color: 'blue' },
    { name: 'Average Attendance', value: '85%', icon: Clock, color: 'green' },
    { name: 'Average Grade', value: 'B+', icon: Award, color: 'yellow' },
    { name: 'Active Courses', value: '24', icon: BookOpen, color: 'purple' }
  ]

  // Recent activities
  const recentActivities = [
    { student: 'John Smith', class: 'Class 10A', activity: 'Submitted Math Assignment', date: '2 hours ago', status: 'Completed' },
    { student: 'Emily Johnson', class: 'Class 11B', activity: 'Missed Science Test', date: '1 day ago', status: 'Failed' },
    { student: 'Michael Brown', class: 'Class 10A', activity: 'Joined History Class', date: '2 days ago', status: 'Completed' },
    { student: 'Sarah Davis', class: 'Class 11A', activity: 'English Project Due', date: 'Tomorrow', status: 'Pending' },
    { student: 'David Wilson', class: 'Class 10B', activity: 'Physics Lab Report', date: '3 days ago', status: 'Completed' }
  ]

  // Students data
  const students = [
    { name: 'John Smith', email: 'john.smith@example.com', id: 'ST10001', class: '10A', attendance: 92, averageGrade: 'A' },
    { name: 'Emily Johnson', email: 'emily.j@example.com', id: 'ST10002', class: '11B', attendance: 78, averageGrade: 'B+' },
    { name: 'Michael Brown', email: 'michael.b@example.com', id: 'ST10003', class: '10A', attendance: 95, averageGrade: 'A+' },
    { name: 'Sarah Davis', email: 'sarah.d@example.com', id: 'ST10004', class: '11A', attendance: 85, averageGrade: 'B' },
    { name: 'David Wilson', email: 'david.w@example.com', id: 'ST10005', class: '10B', attendance: 65, averageGrade: 'C+' },
    { name: 'Jessica Taylor', email: 'jessica.t@example.com', id: 'ST10006', class: '10A', attendance: 88, averageGrade: 'B+' },
    { name: 'Robert Martinez', email: 'robert.m@example.com', id: 'ST10007', class: '11B', attendance: 72, averageGrade: 'C' },
    { name: 'Jennifer Anderson', email: 'jennifer.a@example.com', id: 'ST10008', class: '10B', attendance: 90, averageGrade: 'A-' },
    { name: 'Christopher Thomas', email: 'chris.t@example.com', id: 'ST10009', class: '11A', attendance: 82, averageGrade: 'B' },
    { name: 'Lisa Rodriguez', email: 'lisa.r@example.com', id: 'ST10010', class: '10A', attendance: 93, averageGrade: 'A' }
  ]

  // Grade distribution
  const gradeDistribution = [
    { label: 'A', percentage: 35 },
    { label: 'B', percentage: 40 },
    { label: 'C', percentage: 15 },
    { label: 'D', percentage: 7 },
    { label: 'F', percentage: 3 }
  ]

  // Subjects
  const subjects = [
    'Mathematics', 'Science', 'English', 'History', 'Geography',
    'Physics', 'Chemistry', 'Biology', 'Computer Science', 'Physical Education',
    'Art', 'Music'
  ]

  // Grades data
  const grades = [
    { student: 'John Smith', class: '10A', subject: 'Mathematics', testType: 'Mid-term', grade: 92, date: '2023-03-15' },
    { student: 'Emily Johnson', class: '11B', subject: 'Science', testType: 'Quiz', grade: 78, date: '2023-03-10' },
    { student: 'Michael Brown', class: '10A', subject: 'English', testType: 'Assignment', grade: 95, date: '2023-03-08' },
    { student: 'Sarah Davis', class: '11A', subject: 'History', testType: 'Project', grade: 85, date: '2023-03-05' },
    { student: 'David Wilson', class: '10B', subject: 'Geography', testType: 'Final', grade: 65, date: '2023-03-01' },
    { student: 'Jessica Taylor', class: '10A', subject: 'Physics', testType: 'Lab Report', grade: 88, date: '2023-02-28' },
    { student: 'Robert Martinez', class: '11B', subject: 'Chemistry', testType: 'Mid-term', grade: 72, date: '2023-02-25' },
    { student: 'Jennifer Anderson', class: '10B', subject: 'Biology', testType: 'Quiz', grade: 90, date: '2023-02-20' },
    { student: 'Christopher Thomas', class: '11A', subject: 'Computer Science', testType: 'Project', grade: 82, date: '2023-02-18' },
    { student: 'Lisa Rodriguez', class: '10A', subject: 'Mathematics', testType: 'Assignment', grade: 93, date: '2023-02-15' }
  ]

  // Filtered grades based on selection
  const filteredGrades = computed(() => {
    return grades.filter(grade => {
      const classMatch = selectedClass.value ? grade.class === selectedClass.value : true
      const subjectMatch = selectedSubject.value ? grade.subject === selectedSubject.value : true
      return classMatch && subjectMatch
    })
  })

  // Subject performance
  const subjectPerformance = [
    { name: 'Mathematics', average: 85 },
    { name: 'Science', average: 78 },
    { name: 'English', average: 82 },
    { name: 'History', average: 75 },
    { name: 'Geography', average: 70 },
    { name: 'Physics', average: 65 }
  ]

  // Class averages
  const classAverages = [
    { name: '10A', average: 85 },
    { name: '10B', average: 75 },
    { name: '11A', average: 80 },
    { name: '11B', average: 72 }
  ]

  // Subject details
  const subjectDetails = [
    {
      name: 'Mathematics',
      icon: Calculator,
      color: 'blue',
      description: 'Core mathematics curriculum covering algebra, geometry, and calculus fundamentals.',
      teacher: 'Dr. Alan Smith',
      classes: ['10A', '10B', '11A'],
      students: 87,
      averageGrade: 85
    },
    {
      name: 'Science',
      icon: Microscope,
      color: 'green',
      description: 'General science covering basic principles of physics, chemistry, and biology.',
      teacher: 'Ms. Jessica Parker',
      classes: ['10A', '10B'],
      students: 62,
      averageGrade: 78
    },
    {
      name: 'English',
      icon: Book,
      color: 'yellow',
      description: 'Language arts focusing on literature, writing, and communication skills.',
      teacher: 'Mr. Robert Johnson',
      classes: ['10A', '11A', '11B'],
      students: 92,
      averageGrade: 82
    },
    {
      name: 'History',
      icon: FileText,
      color: 'red',
      description: 'Study of past events, civilizations, and their impact on modern society.',
      teacher: 'Mrs. Emily Davis',
      classes: ['10B', '11A'],
      students: 58,
      averageGrade: 75
    },
    {
      name: 'Geography',
      icon: Globe,
      color: 'purple',
      description: "Study of Earth's landscapes, environments, and the relationship between people and their environments.",
      teacher: 'Mr. Thomas Wilson',
      classes: ['10A', '11B'],
      students: 65,
      averageGrade: 70
    },
    {
      name: 'Physical Education',
      icon: Dumbbell,
      color: 'orange',
      description: 'Development of physical fitness, motor skills, and team sports participation.',
      teacher: 'Coach Michael Brown',
      classes: ['10A', '10B', '11A', '11B'],
      students: 120,
      averageGrade: 90
    }
  ]
  </script>

  <style>
  @tailwind base;
  @tailwind components;
  @tailwind utilities;

  :root {
    --primary: 222 47% 50%;
    --primary-foreground: 210 40% 98%;
  }

  .dark {
    --primary: 217 91% 60%;
    --primary-foreground: 210 40% 98%;
  }

  /* Custom scrollbar for better mobile experience */
  ::-webkit-scrollbar {
    width: 6px;
    height: 6px;
  }

  ::-webkit-scrollbar-track {
    background: transparent;
  }

  ::-webkit-scrollbar-thumb {
    background: rgba(156, 163, 175, 0.5);
    border-radius: 3px;
  }

  ::-webkit-scrollbar-thumb:hover {
    background: rgba(156, 163, 175, 0.8);
  }

  /* Improve table responsiveness */
  @media (max-width: 640px) {
    table {
      display: block;
      overflow-x: auto;
    }
  }
  </style>
