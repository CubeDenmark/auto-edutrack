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
          sidebarOpen ? 'w-64 translate-x-0' : 'w-20 -translate-x-full md:translate-x-0',
          'md:relative fixed h-full'
        ]"
      >
        <div class="flex items-center justify-between h-16 px-4 border-b dark:border-gray-700">
          <div class="flex items-center">
            <div class="text-xl font-bold text-primary dark:text-primary-foreground flex items-center">
              <graduation-cap-icon class="h-8 w-8" />
              <span :class="[sidebarOpen ? 'ml-2 block' : 'hidden md:block md:ml-2']">EduTrack</span>
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

        <div class="py-4">
          <nav>
            <ul>
              <li v-for="(item, index) in menuItems" :key="index" class="mb-2">
                <a
                  href="#"
                  @click.prevent="activeMenuItem = item.name"
                  :class="[
                    'flex items-center px-4 py-3 text-sm transition-colors duration-200',
                    activeMenuItem === item.name
                      ? 'bg-primary/10 text-primary dark:bg-primary/20 dark:text-primary-foreground border-l-4 border-primary'
                      : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700',
                  ]"
                >
                  <component :is="item.icon" class="h-5 w-5" />
                  <span :class="[sidebarOpen ? 'ml-3 block' : 'hidden md:ml-3 md:block']">{{ item.name }}</span>
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
      <div class="flex-1 flex flex-col overflow-hidden">
        <!-- Top Navigation -->
        <header class="bg-white dark:bg-gray-800 shadow-sm z-10">
          <div class="flex items-center justify-between h-16 px-6">
            <div class="flex items-center">
              <button
                @click="toggleSidebar"
                class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 mr-4"
              >
                <menu-icon class="h-6 w-6" />
              </button>
              <h1 class="text-xl font-semibold text-gray-800 dark:text-gray-200">
                {{ activeMenuItem }}
              </h1>
            </div>
            <div class="flex items-center space-x-4">
              <div class="relative">
                <button
                  @click="notificationsOpen = !notificationsOpen"
                  class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 relative"
                >
                  <bell-icon class="h-6 w-6" />
                  <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-4 w-4 flex items-center justify-center">3</span>
                </button>
                <div
                  v-if="notificationsOpen"
                  class="absolute right-0 mt-2 w-80 bg-white dark:bg-gray-800 rounded-lg shadow-lg z-50 border dark:border-gray-700"
                >
                  <div class="p-3 border-b dark:border-gray-700">
                    <h3 class="font-medium text-gray-800 dark:text-gray-200">Notifications</h3>
                  </div>
                  <div class="max-h-64 overflow-y-auto">
                    <div v-for="(notification, index) in notifications" :key="index" class="p-3 border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700">
                      <div class="flex items-start">
                        <div class="flex-shrink-0">
                          <div class="w-8 h-8 rounded-full bg-primary/20 flex items-center justify-center text-primary">
                            <component :is="notification.icon" class="h-4 w-4" />
                          </div>
                        </div>
                        <div class="ml-3 flex-1">
                          <p class="text-sm font-medium text-gray-800 dark:text-gray-200">{{ notification.title }}</p>
                          <p class="text-xs text-gray-500 dark:text-gray-400">{{ notification.time }}</p>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="p-3 text-center">
                    <button class="text-sm text-primary hover:text-primary/80">View all notifications</button>
                  </div>
                </div>
              </div>
              <button @click="toggleDarkMode" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                <sun-icon v-if="darkMode" class="h-6 w-6" />
                <moon-icon v-else class="h-6 w-6" />
              </button>
            </div>
          </div>
        </header>

        <!-- Content Area -->
        <main class="flex-1 overflow-y-auto p-4 md:p-6 bg-gray-100 dark:bg-gray-900">
          <!-- Dashboard -->
          <div v-if="activeMenuItem === 'Dashboard'" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6">
              <div
                v-for="(stat, index) in stats"
                :key="index"
                class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 md:p-6 flex items-center"
              >
                <div
                  class="rounded-full p-3 mr-4"
                  :class="`bg-${stat.color}-100 dark:bg-${stat.color}-900/30 text-${stat.color}-600 dark:text-${stat.color}-400`"
                >
                  <component :is="stat.icon" class="h-6 w-6" />
                </div>
                <div>
                  <p class="text-sm text-gray-500 dark:text-gray-400">{{ stat.name }}</p>
                  <p class="text-2xl font-semibold text-gray-800 dark:text-gray-200">
                    {{ stat.value }}
                  </p>
                </div>
              </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
              <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">
                  Attendance Overview
                </h2>
                <div class="h-64 flex items-center justify-center">
                  <div class="w-full h-full flex items-center justify-center">
                    <!-- Placeholder for chart -->
                    <div class="relative w-40 h-40">
                      <div class="absolute inset-0 flex items-center justify-center">
                        <p class="text-2xl font-bold text-gray-800 dark:text-gray-200">85%</p>
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
                <div class="grid grid-cols-3 gap-4 mt-4">
                  <div class="text-center">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Present</p>
                    <p class="text-lg font-semibold text-gray-800 dark:text-gray-200">85%</p>
                  </div>
                  <div class="text-center">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Absent</p>
                    <p class="text-lg font-semibold text-gray-800 dark:text-gray-200">10%</p>
                  </div>
                  <div class="text-center">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Late</p>
                    <p class="text-lg font-semibold text-gray-800 dark:text-gray-200">5%</p>
                  </div>
                </div>
              </div>

              <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">
                  Grade Distribution
                </h2>
                <div class="h-64 flex items-center justify-center">
                  <!-- Placeholder for chart -->
                  <div class="w-full h-full flex flex-col space-y-2">
                    <div v-for="(grade, index) in gradeDistribution" :key="index" class="flex items-center">
                      <span class="w-8 text-sm text-gray-600 dark:text-gray-400">{{ grade.label }}</span>
                      <div class="flex-1 h-6 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden ml-2">
                        <div
                          class="h-full rounded-full bg-primary"
                          :style="{ width: `${grade.percentage}%` }"
                        ></div>
                      </div>
                      <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">{{ grade.percentage }}%</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
              <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">
                Recent Activities
              </h2>
              <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                  <thead>
                    <tr>
                      <th
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"
                      >
                        Student
                      </th>
                      <th
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"
                      >
                        Activity
                      </th>
                      <th
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"
                      >
                        Date
                      </th>
                      <th
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"
                      >
                        Status
                      </th>
                    </tr>
                  </thead>
                  <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    <tr v-for="(activity, index) in recentActivities" :key="index">
                      <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                          <div class="flex-shrink-0 h-10 w-10">
                            <img
                              :src="`/placeholder.svg?height=40&width=40&text=${activity.student.charAt(0)}`"
                              alt=""
                              class="h-10 w-10 rounded-full"
                            />
                          </div>
                          <div class="ml-4">
                            <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                              {{ activity.student }}
                            </div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">
                              {{ activity.class }}
                            </div>
                          </div>
                        </div>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900 dark:text-gray-100">
                          {{ activity.activity }}
                        </div>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-500 dark:text-gray-400">
                          {{ activity.date }}
                        </div>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap">
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

          <!-- Students -->
          <div v-else-if="activeMenuItem === 'Students'" class="space-y-6">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 md:gap-0">
              <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">
                Student List
              </h2>
              <div class="flex flex-col sm:flex-row w-full md:w-auto space-y-2 sm:space-y-0 sm:space-x-2">
                <div class="relative w-full sm:w-auto">
                  <search-icon class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 h-5 w-5" />
                  <input
                    type="text"
                    placeholder="Search students..."
                    class="w-full pl-10 pr-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-primary"
                  />
                </div>
                <button class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors">
                  Add Student
                </button>
              </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden">
              <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                  <thead>
                    <tr>
                      <th
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"
                      >
                        Name
                      </th>
                      <th
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"
                      >
                        ID
                      </th>
                      <th
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"
                      >
                        Class
                      </th>
                      <th
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"
                      >
                        Attendance
                      </th>
                      <th
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"
                      >
                        Average Grade
                      </th>
                      <th
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"
                      >
                        Actions
                      </th>
                    </tr>
                  </thead>
                  <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    <tr v-for="(student, index) in students" :key="index">
                      <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                          <div class="flex-shrink-0 h-10 w-10">
                            <img
                              :src="`/placeholder.svg?height=40&width=40&text=${student.name.charAt(0)}`"
                              alt=""
                              class="h-10 w-10 rounded-full"
                            />
                          </div>
                          <div class="ml-4">
                            <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                              {{ student.name }}
                            </div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">
                              {{ student.email }}
                            </div>
                          </div>
                        </div>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900 dark:text-gray-100">
                          {{ student.id }}
                        </div>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900 dark:text-gray-100">
                          {{ student.class }}
                        </div>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                          <div class="w-16 bg-gray-200 dark:bg-gray-700 rounded-full h-2.5 mr-2">
                            <div
                              class="bg-primary h-2.5 rounded-full"
                              :style="{ width: `${student.attendance}%` }"
                            ></div>
                          </div>
                          <span class="text-sm text-gray-900 dark:text-gray-100">
                            {{ student.attendance }}%
                          </span>
                        </div>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900 dark:text-gray-100">
                          {{ student.averageGrade }}
                        </div>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                        <div class="flex space-x-2">
                          <button class="text-primary hover:text-primary/80">
                            <eye-icon class="h-5 w-5" />
                          </button>
                          <button class="text-yellow-500 hover:text-yellow-400">
                            <edit-icon class="h-5 w-5" />
                          </button>
                          <button class="text-red-500 hover:text-red-400">
                            <trash-icon class="h-5 w-5" />
                          </button>
                        </div>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <div class="px-6 py-4 flex flex-col sm:flex-row items-center justify-between border-t dark:border-gray-700">
                <div class="text-sm text-gray-500 dark:text-gray-400 mb-2 sm:mb-0">
                  Showing <span class="font-medium">1</span> to
                  <span class="font-medium">10</span> of
                  <span class="font-medium">{{ students.length }}</span> results
                </div>
                <div class="flex space-x-2">
                  <button
                    class="px-3 py-1 border border-gray-300 dark:border-gray-600 rounded-md text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700"
                  >
                    Previous
                  </button>
                  <button
                    class="px-3 py-1 border border-gray-300 dark:border-gray-600 rounded-md text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700"
                  >
                    Next
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Attendance -->
          <div v-else-if="activeMenuItem === 'Attendance'" class="space-y-6">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 md:gap-0">
              <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">
                Attendance Management
              </h2>
              <div class="flex flex-col sm:flex-row w-full md:w-auto space-y-2 sm:space-y-0 sm:space-x-2">
                <select
                  v-model="selectedAttendanceClass"
                  class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-primary"
                >
                  <option value="">All Classes</option>
                  <option value="10A">Class 10A</option>
                  <option value="10B">Class 10B</option>
                  <option value="11A">Class 11A</option>
                  <option value="11B">Class 11B</option>
                </select>
                <input
                  type="date"
                  v-model="selectedDate"
                  class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-primary"
                />
                <button class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors">
                  Take Attendance
                </button>
              </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
              <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                  <thead>
                    <tr>
                      <th
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"
                      >
                        Student
                      </th>
                      <th
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"
                      >
                        Class
                      </th>
                      <th
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"
                      >
                        Status
                      </th>
                      <th
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"
                      >
                        Date
                      </th>
                      <th
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"
                      >
                        Time
                      </th>
                      <th
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"
                      >
                        Actions
                      </th>
                    </tr>
                  </thead>
                  <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    <tr v-for="(record, index) in filteredAttendance" :key="index">
                      <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                          <div class="flex-shrink-0 h-10 w-10">
                            <img
                              :src="`/placeholder.svg?height=40&width=40&text=${record.student.charAt(0)}`"
                              alt=""
                              class="h-10 w-10 rounded-full"
                            />
                          </div>
                          <div class="ml-4">
                            <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                              {{ record.student }}
                            </div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">
                              {{ record.id }}
                            </div>
                          </div>
                        </div>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900 dark:text-gray-100">
                          {{ record.class }}
                        </div>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap">
                        <span
                          class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                          :class="{
                            'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400': record.status === 'Present',
                            'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400': record.status === 'Late',
                            'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400': record.status === 'Absent'
                          }"
                        >
                          {{ record.status }}
                        </span>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-500 dark:text-gray-400">
                          {{ record.date }}
                        </div>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-500 dark:text-gray-400">
                          {{ record.time }}
                        </div>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                        <div class="flex space-x-2">
                          <button class="text-yellow-500 hover:text-yellow-400">
                            <edit-icon class="h-5 w-5" />
                          </button>
                          <button class="text-red-500 hover:text-red-400">
                            <trash-icon class="h-5 w-5" />
                          </button>
                        </div>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
              <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">
                  Monthly Attendance Overview
                </h2>
                <div class="h-64 flex items-center justify-center">
                  <!-- Placeholder for chart -->
                  <div class="w-full h-full flex items-end justify-around">
                    <div v-for="(month, index) in monthlyAttendance" :key="index" class="flex flex-col items-center">
                      <div class="flex h-48 items-end space-x-1">
                        <div
                          class="w-6 rounded-t-sm bg-green-500"
                          :style="{ height: `${month.present * 1.5}px` }"
                        ></div>
                        <div
                          class="w-6 rounded-t-sm bg-yellow-500"
                          :style="{ height: `${month.late * 1.5}px` }"
                        ></div>
                        <div
                          class="w-6 rounded-t-sm bg-red-500"
                          :style="{ height: `${month.absent * 1.5}px` }"
                        ></div>
                      </div>
                      <div class="mt-2 text-sm text-gray-600 dark:text-gray-400">{{ month.name }}</div>
                    </div>
                  </div>
                </div>
                <div class="flex justify-center mt-4 space-x-4">
                  <div class="flex items-center">
                    <div class="w-3 h-3 bg-green-500 rounded-sm mr-1"></div>
                    <span class="text-xs text-gray-600 dark:text-gray-400">Present</span>
                  </div>
                  <div class="flex items-center">
                    <div class="w-3 h-3 bg-yellow-500 rounded-sm mr-1"></div>
                    <span class="text-xs text-gray-600 dark:text-gray-400">Late</span>
                  </div>
                  <div class="flex items-center">
                    <div class="w-3 h-3 bg-red-500 rounded-sm mr-1"></div>
                    <span class="text-xs text-gray-600 dark:text-gray-400">Absent</span>
                  </div>
                </div>
              </div>

              <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">
                  Class Attendance Comparison
                </h2>
                <div class="h-64 flex items-center justify-center">
                  <!-- Placeholder for chart -->
                  <div class="w-full h-full flex flex-col space-y-4">
                    <div v-for="(classData, index) in classAttendance" :key="index" class="flex items-center">
                      <span class="w-16 text-sm text-gray-600 dark:text-gray-400">{{ classData.name }}</span>
                      <div class="flex-1 h-8 bg-gray-200 dark:bg-gray-700 rounded-lg overflow-hidden ml-2">
                        <div class="flex h-full">
                          <div
                            class="h-full bg-green-500"
                            :style="{ width: `${classData.present}%` }"
                          ></div>
                          <div
                            class="h-full bg-yellow-500"
                            :style="{ width: `${classData.late}%` }"
                          ></div>
                          <div
                            class="h-full bg-red-500"
                            :style="{ width: `${classData.absent}%` }"
                          ></div>
                        </div>
                      </div>
                      <div class="ml-2 flex space-x-2 text-xs">
                        <span class="text-green-600 dark:text-green-400">{{ classData.present }}%</span>
                        <span class="text-yellow-600 dark:text-yellow-400">{{ classData.late }}%</span>
                        <span class="text-red-600 dark:text-red-400">{{ classData.absent }}%</span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Grades -->
          <div v-else-if="activeMenuItem === 'Grades'" class="space-y-6">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 md:gap-0">
              <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">
                Grade Management
              </h2>
              <div class="flex flex-col sm:flex-row w-full md:w-auto space-y-2 sm:space-y-0 sm:space-x-2">
                <select
                  v-model="selectedClass"
                  class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-primary"
                >
                  <option value="">All Classes</option>
                  <option value="10A">Class 10A</option>
                  <option value="10B">Class 10B</option>
                  <option value="11A">Class 11A</option>
                  <option value="11B">Class 11B</option>
                </select>
                <select
                  v-model="selectedSubject"
                  class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-primary"
                >
                  <option value="">All Subjects</option>
                  <option v-for="(subject, index) in subjects" :key="index" :value="subject">
                    {{ subject }}
                  </option>
                </select>
                <button class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors">
                  Add Grade
                </button>
              </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
              <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                  <thead>
                    <tr>
                      <th
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"
                      >
                        Student
                      </th>
                      <th
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"
                      >
                        Subject
                      </th>
                      <th
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"
                      >
                        Test Type
                      </th>
                      <th
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"
                      >
                        Grade
                      </th>
                      <th
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"
                      >
                        Date
                      </th>
                      <th
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"
                      >
                        Actions
                      </th>
                    </tr>
                  </thead>
                  <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    <tr v-for="(grade, index) in filteredGrades" :key="index">
                      <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                          <div class="flex-shrink-0 h-10 w-10">
                            <img
                              :src="`/placeholder.svg?height=40&width=40&text=${grade.student.charAt(0)}`"
                              alt=""
                              class="h-10 w-10 rounded-full"
                            />
                          </div>
                          <div class="ml-4">
                            <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                              {{ grade.student }}
                            </div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">
                              {{ grade.class }}
                            </div>
                          </div>
                        </div>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900 dark:text-gray-100">
                          {{ grade.subject }}
                        </div>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900 dark:text-gray-100">
                          {{ grade.testType }}
                        </div>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap">
                        <span
                          class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full"
                          :class="{
                            'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400': grade.grade >= 80,
                            'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400': grade.grade >= 60 && grade.grade < 80,
                            'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400': grade.grade < 60
                          }"
                        >
                          {{ grade.grade }}%
                        </span>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-500 dark:text-gray-400">
                          {{ grade.date }}
                        </div>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                        <div class="flex space-x-2">
                          <button class="text-yellow-500 hover:text-yellow-400">
                            <edit-icon class="h-5 w-5" />
                          </button>
                          <button class="text-red-500 hover:text-red-400">
                            <trash-icon class="h-5 w-5" />
                          </button>
                        </div>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
              <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">
                  Subject Performance
                </h2>
                <div class="h-64 flex items-center justify-center">
                  <!-- Placeholder for chart -->
                  <div class="w-full h-full flex flex-col space-y-4">
                    <div v-for="(subject, index) in subjectPerformance" :key="index" class="flex items-center">
                      <span class="w-24 text-sm text-gray-600 dark:text-gray-400">{{ subject.name }}</span>
                      <div class="flex-1 h-6 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden ml-2">
                        <div
                          class="h-full rounded-full"
                          :class="{
                            'bg-green-500': subject.average >= 80,
                            'bg-yellow-500': subject.average >= 60 && subject.average < 80,
                            'bg-red-500': subject.average < 60
                          }"
                          :style="{ width: `${subject.average}%` }"
                        ></div>
                      </div>
                      <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">{{ subject.average }}%</span>
                    </div>
                  </div>
                </div>
              </div>

              <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">
                  Class Average Comparison
                </h2>
                <div class="h-64 flex items-center justify-center">
                  <!-- Placeholder for chart -->
                  <div class="w-full h-full flex items-end justify-around">
                    <div v-for="(classData, index) in classAverages" :key="index" class="flex flex-col items-center">
                      <div
                        class="w-16 rounded-t-lg"
                        :class="{
                          'bg-green-500': classData.average >= 80,
                          'bg-yellow-500': classData.average >= 60 && classData.average < 80,
                          'bg-red-500': classData.average < 60
                        }"
                        :style="{ height: `${classData.average * 2}px` }"
                      ></div>
                      <div class="mt-2 text-sm text-gray-600 dark:text-gray-400">{{ classData.name }}</div>
                      <div class="text-sm font-medium text-gray-800 dark:text-gray-200">{{ classData.average }}%</div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Subjects -->
          <div v-else-if="activeMenuItem === 'Subjects'" class="space-y-6">
            <div class="flex justify-between items-center">
              <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">
                Subject Management
              </h2>
              <button class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors">
                Add Subject
              </button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
              <div v-for="(subject, index) in subjectDetails" :key="index" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden">
                <div class="p-6">
                  <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center">
                      <div
                        class="w-10 h-10 rounded-full flex items-center justify-center"
                        :class="`bg-${subject.color}-100 dark:bg-${subject.color}-900/30 text-${subject.color}-600 dark:text-${subject.color}-400`"
                      >
                        <component :is="subject.icon" class="h-5 w-5" />
                      </div>
                      <h3 class="ml-3 text-lg font-semibold text-gray-800 dark:text-gray-200">
                        {{ subject.name }}
                      </h3>
                    </div>
                    <div class="flex space-x-1">
                      <button class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                        <edit-icon class="h-5 w-5" />
                      </button>
                      <button class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                        <more-horizontal-icon class="h-5 w-5" />
                      </button>
                    </div>
                  </div>
                  <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                    {{ subject.description }}
                  </p>
                  <div class="flex justify-between text-sm">
                    <div>
                      <p class="text-gray-500 dark:text-gray-400">Teacher</p>
                      <p class="font-medium text-gray-800 dark:text-gray-200">{{ subject.teacher }}</p>
                    </div>
                    <div>
                      <p class="text-gray-500 dark:text-gray-400">Classes</p>
                      <p class="font-medium text-gray-800 dark:text-gray-200">{{ subject.classes.join(", ") }}</p>
                    </div>
                    <div>
                      <p class="text-gray-500 dark:text-gray-400">Students</p>
                      <p class="font-medium text-gray-800 dark:text-gray-200">{{ subject.students }}</p>
                    </div>
                  </div>
                </div>
                <div class="px-6 py-3 bg-gray-50 dark:bg-gray-700/50 flex justify-between items-center">
                  <div class="flex items-center">
                    <div class="w-full bg-gray-200 dark:bg-gray-600 rounded-full h-2.5 w-24">
                      <div
                        class="h-2.5 rounded-full"
                        :class="{
                          'bg-green-500': subject.averageGrade >= 80,
                          'bg-yellow-500': subject.averageGrade >= 60 && subject.averageGrade < 80,
                          'bg-red-500': subject.averageGrade < 60
                        }"
                        :style="{ width: `${subject.averageGrade}%` }"
                      ></div>
                    </div>
                    <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">{{ subject.averageGrade }}%</span>
                  </div>
                  <button class="text-primary hover:text-primary/80 text-sm font-medium">
                    View Details
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Reports -->
          <div v-else-if="activeMenuItem === 'Reports'" class="space-y-6">
            <div class="flex justify-between items-center">
              <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">
                Reports & Analytics
              </h2>
              <div class="flex space-x-2">
                <button class="px-4 py-2 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors flex items-center">
                  <download-icon class="h-4 w-4 mr-2" />
                  Export
                </button>
                <button class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors flex items-center">
                  <printer-icon class="h-4 w-4 mr-2" />
                  Print
                </button>
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
              <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">
                  Performance Trends
                </h3>
                <div class="h-48 flex items-center justify-center">
                  <!-- Placeholder for chart -->
                  <div class="w-full h-full flex flex-col justify-center">
                    <div class="relative h-40">
                      <div class="absolute inset-0 flex items-end">
                        <div v-for="(point, index) in performanceTrend" :key="index" class="flex-1 flex flex-col items-center">
                          <div class="w-2 h-2 rounded-full bg-primary"></div>
                          <div
                            class="w-1 bg-primary"
                            :style="{ height: `${index === 0 ? 0 : Math.abs(point - performanceTrend[index-1]) * 2}px` }"
                          ></div>
                          <div
                            class="w-2 h-2 rounded-full bg-primary"
                            :style="{ marginTop: `${(100 - point) * 0.4}px` }"
                          ></div>
                        </div>
                      </div>
                    </div>
                    <div class="flex justify-between text-xs text-gray-500 dark:text-gray-400 mt-2">
                      <span>Jan</span>
                      <span>Feb</span>
                      <span>Mar</span>
                      <span>Apr</span>
                      <span>May</span>
                      <span>Jun</span>
                    </div>
                  </div>
                </div>
                <div class="mt-4">
                  <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-500 dark:text-gray-400">Current Average</span>
                    <span class="text-lg font-semibold text-gray-800 dark:text-gray-200">78.5%</span>
                  </div>
                  <div class="flex justify-between items-center mt-2">
                    <span class="text-sm text-gray-500 dark:text-gray-400">Trend</span>
                    <span class="text-sm font-medium text-green-600 dark:text-green-400 flex items-center">
                      <arrow-up-icon class="h-3 w-3 mr-1" />
                      3.2%
                    </span>
                  </div>
                </div>
              </div>

              <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">
                  Student Distribution
                </h3>
                <div class="h-48 flex items-center justify-center">
                  <!-- Placeholder for chart -->
                  <div class="relative w-40 h-40">
                    <svg class="w-full h-full" viewBox="0 0 100 100">
                      <!-- Background circle -->
                      <circle
                        class="text-gray-200 dark:text-gray-700 stroke-current"
                        stroke-width="10"
                        cx="50"
                        cy="50"
                        r="40"
                        fill="transparent"
                      ></circle>
                      <!-- Class 10A - 35% -->
                      <circle
                        class="text-blue-500 stroke-current"
                        stroke-width="10"
                        stroke-dasharray="251.2"
                        stroke-dashoffset="163.28"
                        cx="50"
                        cy="50"
                        r="40"
                        fill="transparent"
                        transform="rotate(-90 50 50)"
                      ></circle>
                      <!-- Class 10B - 25% -->
                      <circle
                        class="text-green-500 stroke-current"
                        stroke-width="10"
                        stroke-dasharray="251.2"
                        stroke-dashoffset="188.4"
                        cx="50"
                        cy="50"
                        r="40"
                        fill="transparent"
                        transform="rotate(0 50 50)"
                      ></circle>
                      <!-- Class 11A - 20% -->
                      <circle
                        class="text-yellow-500 stroke-current"
                        stroke-width="10"
                        stroke-dasharray="251.2"
                        stroke-dashoffset="200.96"
                        cx="50"
                        cy="50"
                        r="40"
                        fill="transparent"
                        transform="rotate(90 50 50)"
                      ></circle>
                      <!-- Class 11B - 20% -->
                      <circle
                        class="text-red-500 stroke-current"
                        stroke-width="10"
                        stroke-dasharray="251.2"
                        stroke-dashoffset="200.96"
                        cx="50"
                        cy="50"
                        r="40"
                        fill="transparent"
                        transform="rotate(180 50 50)"
                      ></circle>
                    </svg>
                  </div>
                </div>
                <div class="grid grid-cols-2 gap-2 mt-4">
                  <div class="flex items-center">
                    <div class="w-3 h-3 bg-blue-500 rounded-sm mr-2"></div>
                    <span class="text-sm text-gray-600 dark:text-gray-400">Class 10A (35%)</span>
                  </div>
                  <div class="flex items-center">
                    <div class="w-3 h-3 bg-green-500 rounded-sm mr-2"></div>
                    <span class="text-sm text-gray-600 dark:text-gray-400">Class 10B (25%)</span>
                  </div>
                  <div class="flex items-center">
                    <div class="w-3 h-3 bg-yellow-500 rounded-sm mr-2"></div>
                    <span class="text-sm text-gray-600 dark:text-gray-400">Class 11A (20%)</span>
                  </div>
                  <div class="flex items-center">
                    <div class="w-3 h-3 bg-red-500 rounded-sm mr-2"></div>
                    <span class="text-sm text-gray-600 dark:text-gray-400">Class 11B (20%)</span>
                  </div>
                </div>
              </div>

              <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">
                  Gender Distribution
                </h3>
                <div class="h-48 flex items-center justify-center">
                  <!-- Placeholder for chart -->
                  <div class="flex items-end space-x-8">
                    <div class="flex flex-col items-center">
                      <div class="w-20 bg-blue-500 rounded-t-lg" style="height: 120px"></div>
                      <div class="mt-2 text-sm text-gray-600 dark:text-gray-400">Male</div>
                      <div class="text-sm font-medium text-gray-800 dark:text-gray-200">52%</div>
                    </div>
                    <div class="flex flex-col items-center">
                      <div class="w-20 bg-pink-500 rounded-t-lg" style="height: 110px"></div>
                      <div class="mt-2 text-sm text-gray-600 dark:text-gray-400">Female</div>
                      <div class="text-sm font-medium text-gray-800 dark:text-gray-200">48%</div>
                    </div>
                  </div>
                </div>
                <div class="mt-4 text-center">
                  <p class="text-sm text-gray-500 dark:text-gray-400">
                    Total Students: 1,234
                  </p>
                </div>
              </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
              <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">
                Top Performing Students
              </h3>
              <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                  <thead>
                    <tr>
                      <th
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"
                      >
                        Rank
                      </th>
                      <th
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"
                      >
                        Student
                      </th>
                      <th
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"
                      >
                        Class
                      </th>
                      <th
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"
                      >
                        Average Grade
                      </th>
                      <th
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"
                      >
                        Attendance
                      </th>
                    </tr>
                  </thead>
                  <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    <tr v-for="(student, index) in topStudents" :key="index">
                      <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                          #{{ index + 1 }}
                        </div>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                          <div class="flex-shrink-0 h-10 w-10">
                            <img
                              :src="`/placeholder.svg?height=40&width=40&text=${student.name.charAt(0)}`"
                              alt=""
                              class="h-10 w-10 rounded-full"
                            />
                          </div>
                          <div class="ml-4">
                            <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                              {{ student.name }}
                            </div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">
                              {{ student.id }}
                            </div>
                          </div>
                        </div>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900 dark:text-gray-100">
                          {{ student.class }}
                        </div>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap">
                        <span
                          class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400"
                        >
                          {{ student.averageGrade }}
                        </span>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                          <div class="w-16 bg-gray-200 dark:bg-gray-700 rounded-full h-2.5 mr-2">
                            <div
                              class="bg-primary h-2.5 rounded-full"
                              :style="{ width: `${student.attendance}%` }"
                            ></div>
                          </div>
                          <span class="text-sm text-gray-900 dark:text-gray-100">
                            {{ student.attendance }}%
                          </span>
                        </div>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>

          <!-- Calendar -->
          <div v-else-if="activeMenuItem === 'Calendar'" class="space-y-6">
            <div class="flex justify-between items-center">
              <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">
                School Calendar
              </h2>
              <div class="flex space-x-2">
                <button class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors">
                  Add Event
                </button>
              </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
              <div class="lg:col-span-3 bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                <div class="flex justify-between items-center mb-6">
                  <button class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                    <chevron-left-icon class="h-5 w-5" />
                  </button>
                  <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200">
                    March 2023
                  </h3>
                  <button class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                    <chevron-right-icon class="h-5 w-5" />
                  </button>
                </div>
                <div class="grid grid-cols-7 gap-2">
                  <div v-for="day in ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat']" :key="day" class="text-center text-sm font-medium text-gray-500 dark:text-gray-400 py-2">
                    {{ day }}
                  </div>
                  <div v-for="i in 35" :key="i"
                    :class="[
                      'h-24 border border-gray-200 dark:border-gray-700 rounded-lg p-1 overflow-hidden',
                      i >= 3 && i <= 31 ? 'bg-white dark:bg-gray-800' : 'bg-gray-50 dark:bg-gray-700/50 text-gray-400 dark:text-gray-500'
                    ]"
                  >
                    <div class="flex justify-between items-start">
                      <span class="text-sm font-medium">{{ i >= 3 && i <= 31 ? i - 2 : (i < 3 ? 28 + i : i - 31) }}</span>
                      <div v-if="calendarEvents[i]" class="w-2 h-2 rounded-full bg-primary"></div>
                    </div>
                    <div v-if="calendarEvents[i]" class="mt-1">
                      <div v-for="(event, index) in calendarEvents[i]" :key="index"
                        class="text-xs p-1 mb-1 rounded truncate"
                        :class="`bg-${event.color}-100 dark:bg-${event.color}-900/30 text-${event.color}-800 dark:text-${event.color}-400`"
                      >
                        {{ event.title }}
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">
                  Upcoming Events
                </h3>
                <div class="space-y-4">
                  <div v-for="(event, index) in upcomingEvents" :key="index" class="border-l-4 pl-4 py-2"
                    :class="`border-${event.color}-500`"
                  >
                    <div class="flex justify-between">
                      <h4 class="text-sm font-medium text-gray-800 dark:text-gray-200">{{ event.title }}</h4>
                      <span class="text-xs text-gray-500 dark:text-gray-400">{{ event.time }}</span>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ event.date }}</p>
                    <div class="flex items-center mt-2">
                      <div class="flex -space-x-2">
                        <img v-for="i in 3" :key="i" :src="`/placeholder.svg?height=24&width=24&text=${i}`" class="w-6 h-6 rounded-full border-2 border-white dark:border-gray-800" />
                      </div>
                      <span class="text-xs text-gray-500 dark:text-gray-400 ml-2">+{{ event.attendees }} attendees</span>
                    </div>
                  </div>
                </div>
                <button class="w-full mt-4 text-center text-sm text-primary hover:text-primary/80">
                  View All Events
                </button>
              </div>
            </div>
          </div>

          <!-- Analytics -->
          <div v-else-if="activeMenuItem === 'Analytics'" class="space-y-6">
            <div class="flex justify-between items-center">
              <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">
                Analytics Dashboard
              </h2>
              <div class="flex space-x-2">
                <select
                  v-model="analyticsTimeframe"
                  class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-primary"
                >
                  <option value="week">This Week</option>
                  <option value="month">This Month</option>
                  <option value="quarter">This Quarter</option>
                  <option value="year">This Year</option>
                </select>
                <button class="px-4 py-2 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors flex items-center">
                  <download-icon class="h-4 w-4 mr-2" />
                  Export
                </button>
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
              <div v-for="(metric, index) in analyticsMetrics" :key="index" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                <div class="flex justify-between items-start">
                  <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ metric.name }}</p>
                    <p class="text-2xl font-semibold text-gray-800 dark:text-gray-200 mt-1">
                      {{ metric.value }}
                    </p>
                  </div>
                  <div
                    class="rounded-full p-2"
                    :class="`bg-${metric.color}-100 dark:bg-${metric.color}-900/30 text-${metric.color}-600 dark:text-${metric.color}-400`"
                  >
                    <component :is="metric.icon" class="h-5 w-5" />
                  </div>
                </div>
                <div class="flex items-center mt-4">
                  <span
                    class="text-xs font-medium flex items-center"
                    :class="metric.trend > 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'"
                  >
                    <component :is="metric.trend > 0 ? 'arrow-up-icon' : 'arrow-down-icon'" class="h-3 w-3 mr-1" />
                    {{ Math.abs(metric.trend) }}%
                  </span>
                  <span class="text-xs text-gray-500 dark:text-gray-400 ml-2">vs previous {{ analyticsTimeframe }}</span>
                </div>
              </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
              <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">
                  Attendance Trends
                </h3>
                <div class="h-64 flex items-center justify-center">
                  <!-- Placeholder for chart -->
                  <div class="w-full h-full flex flex-col">
                    <div class="flex-1 flex items-end">
                      <div v-for="(point, index) in attendanceTrend" :key="index" class="flex-1 flex flex-col items-center justify-end h-full">
                        <div
                          class="w-8 rounded-t-sm bg-primary"
                          :style="{ height: `${point}%` }"
                        ></div>
                      </div>
                    </div>
                    <div class="flex justify-between text-xs text-gray-500 dark:text-gray-400 mt-2">
                      <span>Mon</span>
                      <span>Tue</span>
                      <span>Wed</span>
                      <span>Thu</span>
                      <span>Fri</span>
                    </div>
                  </div>
                </div>
              </div>

              <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">
                  Grade Distribution
                </h3>
                <div class="h-64 flex items-center justify-center">
                  <!-- Placeholder for chart -->
                  <div class="w-full h-full flex items-center justify-center">
                    <div class="grid grid-cols-5 gap-4 w-full">
                      <div v-for="(grade, index) in gradeAnalytics" :key="index" class="flex flex-col items-center">
                        <div class="text-sm font-medium text-gray-800 dark:text-gray-200 mb-2">{{ grade.label }}</div>
                        <div class="relative w-full">
                          <div class="h-40 bg-gray-200 dark:bg-gray-700 w-8 mx-auto rounded-t-lg"></div>
                          <div
                            class="absolute bottom-0 w-8 mx-auto rounded-t-lg left-0 right-0"
                            :class="{
                              'bg-green-500': index === 0 || index === 1,
                              'bg-yellow-500': index === 2,
                              'bg-red-500': index === 3 || index === 4
                            }"
                            :style="{ height: `${grade.percentage * 1.6}px` }"
                          ></div>
                        </div>
                        <div class="text-sm text-gray-500 dark:text-gray-400 mt-2">{{ grade.percentage }}%</div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
              <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">
                Performance by Subject
              </h3>
              <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                  <thead>
                    <tr>
                      <th
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"
                      >
                        Subject
                      </th>
                      <th
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"
                      >
                        Average Grade
                      </th>
                      <th
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"
                      >
                        Pass Rate
                      </th>
                      <th
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"
                      >
                        Trend
                      </th>
                    </tr>
                  </thead>
                  <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    <tr v-for="(subject, index) in subjectAnalytics" :key="index">
                      <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                          <div
                            class="w-8 h-8 rounded-full flex items-center justify-center mr-3"
                            :class="`bg-${subject.color}-100 dark:bg-${subject.color}-900/30 text-${subject.color}-600 dark:text-${subject.color}-400`"
                          >
                            <component :is="subject.icon" class="h-4 w-4" />
                          </div>
                          <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                            {{ subject.name }}
                          </div>
                        </div>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                          <div class="w-24 bg-gray-200 dark:bg-gray-700 rounded-full h-2.5 mr-2">
                            <div
                              class="h-2.5 rounded-full"
                              :class="{
                                'bg-green-500': subject.average >= 80,
                                'bg-yellow-500': subject.average >= 60 && subject.average < 80,
                                'bg-red-500': subject.average < 60
                              }"
                              :style="{ width: `${subject.average}%` }"
                            ></div>
                          </div>
                          <span class="text-sm text-gray-900 dark:text-gray-100">
                            {{ subject.average }}%
                          </span>
                        </div>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900 dark:text-gray-100">
                          {{ subject.passRate }}%
                        </div>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap">
                        <span
                          class="text-sm font-medium flex items-center"
                          :class="subject.trend > 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'"
                        >
                          <component :is="subject.trend > 0 ? 'arrow-up-icon' : 'arrow-down-icon'" class="h-3 w-3 mr-1" />
                          {{ Math.abs(subject.trend) }}%
                        </span>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>

          <!-- Settings -->
          <div v-else-if="activeMenuItem === 'Settings'" class="space-y-6">
            <div class="flex justify-between items-center">
              <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">
                System Settings
              </h2>
              <button class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors">
                Save Changes
              </button>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
              <div class="lg:col-span-2 space-y-6">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                  <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">
                    General Settings
                  </h3>
                  <div class="space-y-4">
                    <div>
                      <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        School Name
                      </label>
                      <input
                        type="text"
                        value="Springfield High School"
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-primary"
                      />
                    </div>
                    <div>
                      <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        School Address
                      </label>
                      <textarea
                        rows="3"
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-primary"
                      >123 Education St, Springfield, ST 12345</textarea>
                    </div>
                    <div>
                      <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Contact Email
                      </label>
                      <input
                        type="email"
                        value="info@springfieldhigh.edu"
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-primary"
                      />
                    </div>
                    <div>
                      <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Phone Number
                      </label>
                      <input
                        type="tel"
                        value="(555) 123-4567"
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-primary"
                      />
                    </div>
                  </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                  <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">
                    Notification Settings
                  </h3>
                  <div class="space-y-4">
                    <div class="flex items-center justify-between">
                      <div>
                        <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Email Notifications</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Receive email notifications for important updates</p>
                      </div>
                      <div class="relative inline-block w-10 mr-2 align-middle select-none">
                        <input type="checkbox" id="toggle1" checked class="sr-only" />
                        <label for="toggle1" class="block overflow-hidden h-6 rounded-full bg-gray-300 dark:bg-gray-600 cursor-pointer">
                          <span class="block h-6 w-6 rounded-full bg-white dark:bg-gray-200 transform translate-x-4"></span>
                        </label>
                      </div>
                    </div>
                    <div class="flex items-center justify-between">
                      <div>
                        <p class="text-sm font-medium text-gray-700 dark:text-gray-300">SMS Notifications</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Receive text messages for urgent alerts</p>
                      </div>
                      <div class="relative inline-block w-10 mr-2 align-middle select-none">
                        <input type="checkbox" id="toggle2" class="sr-only" />
                        <label for="toggle2" class="block overflow-hidden h-6 rounded-full bg-gray-300 dark:bg-gray-600 cursor-pointer">
                          <span class="block h-6 w-6 rounded-full bg-white dark:bg-gray-200 transform"></span>
                        </label>
                      </div>
                    </div>
                    <div class="flex items-center justify-between">
                      <div>
                        <p class="text-sm font-medium text-gray-700 dark:text-gray-300">System Notifications</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Receive in-app notifications</p>
                      </div>
                      <div class="relative inline-block w-10 mr-2 align-middle select-none">
                        <input type="checkbox" id="toggle3" checked class="sr-only" />
                        <label for="toggle3" class="block overflow-hidden h-6 rounded-full bg-gray-300 dark:bg-gray-600 cursor-pointer">
                          <span class="block h-6 w-6 rounded-full bg-white dark:bg-gray-200 transform translate-x-4"></span>
                        </label>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                  <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">
                    Appearance Settings
                  </h3>
                  <div class="space-y-4">
                    <div>
                      <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Theme
                      </label>
                      <div class="flex space-x-4">
                        <button
                          @click="darkMode = false"
                          :class="[
                            'px-4 py-2 rounded-lg border flex items-center',
                            !darkMode
                              ? 'border-primary bg-primary/10 text-primary'
                              : 'border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300'
                          ]"
                        >
                          <sun-icon class="h-4 w-4 mr-2" />
                          Light
                        </button>
                        <button
                          @click="darkMode = true"
                          :class="[
                            'px-4 py-2 rounded-lg border flex items-center',
                            darkMode
                              ? 'border-primary bg-primary/10 text-primary'
                              : 'border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300'
                          ]"
                        >
                          <moon-icon class="h-4 w-4 mr-2" />
                          Dark
                        </button>
                        <button
                          class="px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 flex items-center"
                        >
                          <laptop-icon class="h-4 w-4 mr-2" />
                          System
                        </button>
                      </div>
                    </div>
                    <div>
                      <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Primary Color
                      </label>
                      <div class="flex space-x-2">
                        <button class="w-8 h-8 rounded-full bg-blue-500 border-2 border-white dark:border-gray-800 shadow-sm"></button>
                        <button class="w-8 h-8 rounded-full bg-green-500 border-2 border-white dark:border-gray-800 shadow-sm"></button>
                        <button class="w-8 h-8 rounded-full bg-purple-500 border-2 border-white dark:border-gray-800 shadow-sm"></button>
                        <button class="w-8 h-8 rounded-full bg-red-500 border-2 border-white dark:border-gray-800 shadow-sm"></button>
                        <button class="w-8 h-8 rounded-full bg-yellow-500 border-2 border-white dark:border-gray-800 shadow-sm"></button>
                        <button class="w-8 h-8 rounded-full bg-pink-500 border-2 border-white dark:border-gray-800 shadow-sm"></button>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <div class="space-y-6">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                  <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">
                    Account Settings
                  </h3>
                  <div class="flex items-center mb-6">
                    <img
                      src="/placeholder.svg?height=80&width=80"
                      alt="User"
                      class="h-20 w-20 rounded-full"
                    />
                    <div class="ml-4">
                      <p class="text-sm font-medium text-gray-700 dark:text-gray-300">John Doe</p>
                      <p class="text-xs text-gray-500 dark:text-gray-400">Administrator</p>
                      <button class="mt-2 text-xs text-primary hover:text-primary/80">
                        Change Profile Picture
                      </button>
                    </div>
                  </div>
                  <div class="space-y-4">
                    <div>
                      <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Full Name
                      </label>
                      <input
                        type="text"
                        value="John Doe"
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-primary"
                      />
                    </div>
                    <div>
                      <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Email Address
                      </label>
                      <input
                        type="email"
                        value="john.doe@example.com"
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-primary"
                      />
                    </div>
                    <div>
                      <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Password
                      </label>
                      <input
                        type="password"
                        value="********"
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-primary"
                      />
                      <button class="mt-1 text-xs text-primary hover:text-primary/80">
                        Change Password
                      </button>
                    </div>
                  </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                  <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">
                    System Information
                  </h3>
                  <div class="space-y-2">
                    <div class="flex justify-between">
                      <span class="text-sm text-gray-500 dark:text-gray-400">Version</span>
                      <span class="text-sm text-gray-700 dark:text-gray-300">v1.0.0</span>
                    </div>
                    <div class="flex justify-between">
                      <span class="text-sm text-gray-500 dark:text-gray-400">Last Updated</span>
                      <span class="text-sm text-gray-700 dark:text-gray-300">March 8, 2023</span>
                    </div>
                    <div class="flex justify-between">
                      <span class="text-sm text-gray-500 dark:text-gray-400">Database</span>
                      <span class="text-sm text-gray-700 dark:text-gray-300">MySQL 8.0</span>
                    </div>
                    <div class="flex justify-between">
                      <span class="text-sm text-gray-500 dark:text-gray-400">Server</span>
                      <span class="text-sm text-gray-700 dark:text-gray-300">Node.js 16.14.0</span>
                    </div>
                  </div>
                  <div class="mt-4 pt-4 border-t dark:border-gray-700">
                    <button class="w-full px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                      Check for Updates
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Default Content -->
          <div v-else class="flex items-center justify-center h-full">
            <div class="text-center">
              <h2 class="text-2xl font-semibold text-gray-800 dark:text-gray-200 mb-2">
                Select an option from the sidebar
              </h2>
              <p class="text-gray-500 dark:text-gray-400">
                Choose a menu item to view the corresponding content.
              </p>
            </div>
          </div>
        </main>
      </div>
    </div>
  </template>

  <script setup>
  import { ref, computed, onMounted } from 'vue'
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
    Dumbbell,
    ArrowUp,
    ArrowDown,
    Download,
    Printer,
    Laptop
  } from 'lucide-vue-next'

  // State
  const sidebarOpen = ref(false)
  const darkMode = ref(false)
  const activeMenuItem = ref('Dashboard')
  const selectedClass = ref('')
  const selectedSubject = ref('')
  const selectedAttendanceClass = ref('')
  const selectedDate = ref('')
  const notificationsOpen = ref(false)
  const analyticsTimeframe = ref('month')

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

  // Initialize responsive sidebar
  onMounted(() => {
    // Check if screen is larger than mobile
    if (window.innerWidth >= 768) {
      sidebarOpen.value = true
    }

    // Initialize dark mode based on system preference
    if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
      darkMode.value = true
      document.documentElement.classList.add('dark')
    }

    // Add resize listener for responsive behavior
    window.addEventListener('resize', () => {
      if (window.innerWidth < 768) {
        sidebarOpen.value = false
      } else {
        sidebarOpen.value = true
      }
    })
  })

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
    },
    {
      name: 'Art',
      icon: Palette,
      color: 'pink',
      description: 'Creative expression through various mediums including drawing, painting, and sculpture.',
      teacher: 'Ms. Sarah Anderson',
      classes: ['10B', '11A'],
      students: 45,
      averageGrade: 88
    },
    {
      name: 'Music',
      icon: Music,
      color: 'indigo',
      description: 'Music theory, appreciation, and performance in various styles and instruments.',
      teacher: 'Mr. David Lee',
      classes: ['10A', '11B'],
      students: 38,
      averageGrade: 85
    },
    {
      name: 'Computer Science',
      icon: Layers,
      color: 'cyan',
      description: 'Programming fundamentals, algorithms, and software development principles.',
      teacher: 'Dr. Lisa Chen',
      classes: ['11A', '11B'],
      students: 52,
      averageGrade: 82
    }
  ]

  // Attendance data
  const attendanceRecords = [
    { student: 'John Smith', id: 'ST10001', class: '10A', status: 'Present', date: '2023-03-15', time: '08:30 AM' },
    { student: 'Emily Johnson', id: 'ST10002', class: '11B', status: 'Late', date: '2023-03-15', time: '09:15 AM' },
    { student: 'Michael Brown', id: 'ST10003', class: '10A', status: 'Present', date: '2023-03-15', time: '08:25 AM' },
    { student: 'Sarah Davis', id: 'ST10004', class: '11A', status: 'Absent', date: '2023-03-15', time: '-- --' },
    { student: 'David Wilson', id: 'ST10005', class: '10B', status: 'Present', date: '2023-03-15', time: '08:40 AM' },
    { student: 'Jessica Taylor', id: 'ST10006', class: '10A', status: 'Present', date: '2023-03-15', time: '08:30 AM' },
    { student: 'Robert Martinez', id: 'ST10007', class: '11B', status: 'Late', date: '2023-03-15', time: '09:05 AM' },
    { student: 'Jennifer Anderson', id: 'ST10008', class: '10B', status: 'Present', date: '2023-03-15', time: '08:35 AM' },
    { student: 'Christopher Thomas', id: 'ST10009', class: '11A', status: 'Absent', date: '2023-03-15', time: '-- --' },
    { student: 'Lisa Rodriguez', id: 'ST10010', class: '10A', status: 'Present', date: '2023-03-15', time: '08:28 AM' }
  ]

  // Filtered attendance based on selection
  const filteredAttendance = computed(() => {
    return attendanceRecords.filter(record => {
      return selectedAttendanceClass.value ? record.class === selectedAttendanceClass.value : true
    })
  })

  // Monthly attendance data
  const monthlyAttendance = [
    { name: 'Jan', present: 80, late: 12, absent: 8 },
    { name: 'Feb', present: 82, late: 10, absent: 8 },
    { name: 'Mar', present: 85, late: 8, absent: 7 },
    { name: 'Apr', present: 83, late: 9, absent: 8 },
    { name: 'May', present: 87, late: 7, absent: 6 }
  ]

  // Class attendance data
  const classAttendance = [
    { name: 'Class 10A', present: 88, late: 7, absent: 5 },
    { name: 'Class 10B', present: 82, late: 10, absent: 8 },
    { name: 'Class 11A', present: 85, late: 8, absent: 7 },
    { name: 'Class 11B', present: 80, late: 12, absent: 8 }
  ]

  // Notifications
  const notifications = [
    { title: 'New grade submitted', time: '5 minutes ago', icon: Award },
    { title: 'Student absence reported', time: '1 hour ago', icon: Clock },
    { title: 'System update available', time: '2 hours ago', icon: Settings }
  ]

  // Calendar events
  const calendarEvents = {
    8: [{ title: 'Math Test', color: 'red' }],
    12: [{ title: 'Science Fair', color: 'green' }, { title: 'Parent Meeting', color: 'blue' }],
    15: [{ title: 'Sports Day', color: 'orange' }],
    20: [{ title: 'English Exam', color: 'purple' }],
    25: [{ title: 'Holiday', color: 'green' }]
  }

  // Upcoming events
  const upcomingEvents = [
    { title: 'Math Test - Class 10A', date: 'March 8, 2023', time: '10:00 AM', attendees: 32, color: 'red' },
    { title: 'Science Fair', date: 'March 12, 2023', time: 'All Day', attendees: 120, color: 'green' },
    { title: 'Parent-Teacher Meeting', date: 'March 12, 2023', time: '4:00 PM', attendees: 45, color: 'blue' },
    { title: 'Sports Day', date: 'March 15, 2023', time: 'All Day', attendees: 200, color: 'orange' }
  ]

  // Analytics metrics
  const analyticsMetrics = [
    { name: 'Average Attendance', value: '85%', icon: Users, color: 'blue', trend: 2.5 },
    { name: 'Average Grade', value: 'B+', icon: Award, color: 'green', trend: 1.8 },
    { name: 'Pass Rate', value: '92%', icon: BarChart, color: 'purple', trend: 3.2 },
    { name: 'Dropout Rate', value: '2.1%', icon: PieChart, color: 'red', trend: -0.5 }
  ]

  // Performance trend
  const performanceTrend = [75, 78, 76, 80, 82, 85]

  // Attendance trend
  const attendanceTrend = [85, 82, 88, 90, 86]

  // Grade analytics
  const gradeAnalytics = [
    { label: 'A', percentage: 35 },
    { label: 'B', percentage: 40 },
    { label: 'C', percentage: 15 },
    { label: 'D', percentage: 7 },
    { label: 'F', percentage: 3 }
  ]

  // Subject analytics
  const subjectAnalytics = [
    { name: 'Mathematics', average: 85, passRate: 92, trend: 2.5, color: 'blue', icon: Calculator },
    { name: 'Science', average: 78, passRate: 88, trend: 1.2, color: 'green', icon: Microscope },
    { name: 'English', average: 82, passRate: 90, trend: 3.0, color: 'yellow', icon: Book },
    { name: 'History', average: 75, passRate: 85, trend: -1.5, color: 'red', icon: FileText },
    { name: 'Geography', average: 70, passRate: 80, trend: 0.8, color: 'purple', icon: Globe }
  ]

  // Top performing students
  const topStudents = [
    { name: 'Michael Brown', id: 'ST10003', class: '10A', averageGrade: 'A+', attendance: 95 },
    { name: 'Lisa Rodriguez', id: 'ST10010', class: '10A', averageGrade: 'A', attendance: 93 },
    { name: 'John Smith', id: 'ST10001', class: '10A', averageGrade: 'A', attendance: 92 },
    { name: 'Jennifer Anderson', id: 'ST10008', class: '10B', averageGrade: 'A-', attendance: 90 },
    { name: 'Jessica Taylor', id: 'ST10006', class: '10A', averageGrade: 'B+', attendance: 88 }
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

  /* Add smooth scrolling */
  html {
    scroll-behavior: smooth;
  }

  /* Improve mobile responsiveness */
  @media (max-width: 640px) {
    .table-responsive {
      display: block;
      width: 100%;
      overflow-x: auto;
    }
  }

  /* Add transitions for better UX */
  .transition-all {
    transition-property: all;
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
    transition-duration: 300ms;
  }
  </style>
