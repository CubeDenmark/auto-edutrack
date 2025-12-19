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
                    <p class="text-2xl font-semibold dark:text-white">{{ totalStudents }}</p>
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
              <!-- Attendance Overview Chart -->
              <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold mb-4 dark:text-white">Attendance Overview</h3>
                <div class="flex items-center justify-between mb-4">
                  <select
                    v-model="selectedSectionForAttendanceChart"
                    class="block rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white text-sm"
                  >
                    <option value="all">All Sections</option>
                    <option v-for="section in sections" :key="section.id" :value="section.id">
                      {{ section.name }}
                    </option>
                  </select>
                  <div class="flex space-x-4">
                    <div class="flex items-center">
                      <div class="w-3 h-3 bg-green-500 rounded-full mr-1"></div>
                      <span class="text-xs text-gray-500 dark:text-gray-400">Present</span>
                    </div>
                    <div class="flex items-center">
                      <div class="w-3 h-3 bg-red-500 rounded-full mr-1"></div>
                      <span class="text-xs text-gray-500 dark:text-gray-400">Absent</span>
                    </div>
                    <div class="flex items-center">
                      <div class="w-3 h-3 bg-yellow-500 rounded-full mr-1"></div>
                      <span class="text-xs text-gray-500 dark:text-gray-400">Late</span>
                    </div>
                  </div>
                </div>
                <div class="h-64">
                  <div class="relative h-full">
                    <!-- X-axis labels -->
                    <div class="absolute bottom-0 left-0 right-0 flex justify-between px-2">
                      <div v-for="(day, index) in weekdays" :key="index" class="text-xs text-gray-500 dark:text-gray-400">
                        {{ day }}
                      </div>
                    </div>

                    <!-- Y-axis grid lines -->
                    <div class="absolute inset-0 flex flex-col justify-between pb-6">
                      <div v-for="i in 5" :key="i" class="w-full h-px bg-gray-200 dark:bg-gray-700"></div>
                    </div>

                    <!-- Y-axis labels -->
                    <div class="absolute top-0 bottom-6 left-0 flex flex-col justify-between items-start">
                      <div v-for="i in 6" :key="i" class="text-xs text-gray-500 dark:text-gray-400">
                        {{ (6-i) * 20 }}%
                      </div>
                    </div>

                    <!-- Bars -->
                    <div class="absolute inset-0 flex justify-between pt-4 pb-6 px-8">
                      <div v-for="(day, index) in weekdays" :key="index" class="relative w-12">
                        <!-- Present -->
                        <div
                          class="absolute bottom-0 left-0 right-0 bg-green-500 dark:bg-green-600 rounded-t"
                          :style="{ height: `${attendanceData[index].present}%` }"
                        ></div>

                        <!-- Late (stacked on top of present) -->
                        <div
                          class="absolute left-0 right-0 bg-yellow-500 dark:bg-yellow-600 rounded-t"
                          :style="{
                            height: `${attendanceData[index].late}%`,
                            bottom: `${attendanceData[index].present}%`
                          }"
                        ></div>

                        <!-- Absent (stacked on top of late) -->
                        <div
                          class="absolute left-0 right-0 bg-red-500 dark:bg-red-600 rounded-t"
                          :style="{
                            height: `${attendanceData[index].absent}%`,
                            bottom: `${attendanceData[index].present + attendanceData[index].late}%`
                          }"
                        ></div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Grade Distribution Chart -->
              <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold mb-4 dark:text-white">Grade Distribution</h3>
                <div class="flex items-center justify-between mb-4">
                  <select
                    v-model="selectedSectionForGradeChart"
                    class="block rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white text-sm"
                  >
                    <option value="all">All Sections</option>
                    <option v-for="section in sections" :key="section.id" :value="section.id">
                      {{ section.name }}
                    </option>
                  </select>
                  <select
                    v-model="selectedAssignmentForGradeChart"
                    class="block rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white text-sm"
                  >
                    <option value="all">All Assignments</option>
                    <option value="quiz1">Science Quiz #1</option>
                    <option value="midterm">Science Midterm</option>
                    <option value="lab2">Lab Report #2</option>
                    <option value="final">Final Project</option>
                  </select>
                </div>
                <div class="h-64">
                  <div class="relative h-full">
                    <!-- Pie chart -->
                    <svg viewBox="0 0 100 100" class="w-full h-full">
                      <!-- A pie chart with 5 segments for grades A-F -->
                      <circle cx="50" cy="50" r="40" fill="transparent" stroke="#e5e7eb" stroke-width="20" />

                      <!-- A grade (35%) -->
                      <circle
                        cx="50"
                        cy="50"
                        r="40"
                        fill="transparent"
                        :stroke="darkMode ? '#4ade80' : '#22c55e'"
                        stroke-width="20"
                        stroke-dasharray="251.2 628"
                        stroke-dashoffset="0"
                        transform="rotate(-90 50 50)"
                      />

                      <!-- B grade (40%) -->
                      <circle
                        cx="50"
                        cy="50"
                        r="40"
                        fill="transparent"
                        :stroke="darkMode ? '#60a5fa' : '#3b82f6'"
                        stroke-width="20"
                        stroke-dasharray="219.8 628"
                        stroke-dashoffset="-251.2"
                        transform="rotate(-90 50 50)"
                      />

                      <!-- C grade (15%) -->
                      <circle
                        cx="50"
                        cy="50"
                        r="40"
                        fill="transparent"
                        :stroke="darkMode ? '#facc15' : '#eab308'"
                        stroke-width="20"
                        stroke-dasharray="94.2 628"
                        stroke-dashoffset="-471"
                        transform="rotate(-90 50 50)"
                      />

                      <!-- D grade (7%) -->
                      <circle
                        cx="50"
                        cy="50"
                        r="40"
                        fill="transparent"
                        :stroke="darkMode ? '#fb923c' : '#f97316'"
                        stroke-width="20"
                        stroke-dasharray="44 628"
                        stroke-dashoffset="-565.2"
                        transform="rotate(-90 50 50)"
                      />

                      <!-- F grade (3%) -->
                      <circle
                        cx="50"
                        cy="50"
                        r="40"
                        fill="transparent"
                        :stroke="darkMode ? '#f87171' : '#ef4444'"
                        stroke-width="20"
                        stroke-dasharray="18.8 628"
                        stroke-dashoffset="-609.2"
                        transform="rotate(-90 50 50)"
                      />
                    </svg>

                    <!-- Legend -->
                    <div class="absolute bottom-0 left-0 right-0 flex justify-center space-x-4">
                      <div v-for="(grade, index) in grades" :key="index" class="flex items-center">
                        <div
                          class="w-3 h-3 rounded-full mr-1"
                          :class="gradeColorClasses[index]"
                        ></div>
                        <span class="text-xs text-gray-500 dark:text-gray-400">{{ grade }} ({{ gradeData[index] }}%)</span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Recent Activity -->
            <div class="mt-6">
              <h3 class="text-lg font-semibold mb-4 dark:text-white">Recent Activity</h3>
              <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
                <div class="p-4 border-b dark:border-gray-700">
                  <div class="flex items-center justify-between">
                    <span class="text-sm font-medium dark:text-white">Today</span>
                  </div>
                </div>
                <div class="divide-y divide-gray-200 dark:divide-gray-700">
                  <div v-for="(activity, index) in recentActivities" :key="index" class="p-4">
                    <div class="flex items-start">
                      <div class="flex-shrink-0">
                        <div
                          class="w-8 h-8 rounded-full flex items-center justify-center"
                          :class="activityTypeColors[activity.type]"
                        >
                          <component :is="activityTypeIcons[activity.type]" class="h-4 w-4 text-white" />
                        </div>
                      </div>
                      <div class="ml-3 flex-1">
                        <p class="text-sm font-medium dark:text-white">{{ activity.title }}</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ activity.description }}</p>
                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">{{ activity.time }}</p>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Attendance -->
          <div v-if="activeTab === 'attendance'">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6">
              <h2 class="text-2xl font-bold dark:text-white mb-4 md:mb-0">Attendance</h2>

              <div class="flex flex-col md:flex-row space-y-3 md:space-y-0 md:space-x-4">
                <select
                  v-model="selectedSection"
                  class="block rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white"
                >
                  <option v-for="section in sections" :key="section.id" :value="section.id">
                    {{ section.name }}
                  </option>
                </select>

                <input
                  type="date"
                  v-model="selectedDate"
                  class="block rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white"
                />
              </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
              <div class="flex items-center justify-between p-4 border-b dark:border-gray-700">
                <div class="flex items-center">
                  <span class="text-gray-700 dark:text-gray-300 mr-2">Section:</span>
                  <span class="font-medium dark:text-white">{{ getSectionName(selectedSection) }}</span>
                  <span class="text-gray-700 dark:text-gray-300 mx-2">|</span>
                  <span class="text-gray-700 dark:text-gray-300 mr-2">Date:</span>
                  <span class="font-medium dark:text-white">{{ formatDate(selectedDate) }}</span>
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
                    <tr v-for="(student, index) in getStudentsBySection(selectedSection)" :key="index">
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
            <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6">
              <h2 class="text-2xl font-bold dark:text-white mb-4 md:mb-0">Grades</h2>

              <div class="flex flex-col md:flex-row space-y-3 md:space-y-0 md:space-x-4">
                <select
                  v-model="selectedGradeSection"
                  class="block rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white"
                >
                  <option v-for="section in sections" :key="section.id" :value="section.id">
                    {{ section.name }}
                  </option>
                </select>
              </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
              <div class="flex items-center justify-between p-4 border-b dark:border-gray-700">
                <div>
                  <select
                    v-model="selectedAssignment"
                    class="block rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white"
                  >
                    <option value="quiz1">Science Quiz #1</option>
                    <option value="midterm">Science Midterm</option>
                    <option value="lab2">Lab Report #2</option>
                    <option value="final">Final Project</option>
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
                    <tr v-for="(student, index) in getStudentsBySection(selectedGradeSection)" :key="index">
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
            <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6">
              <h2 class="text-2xl font-bold dark:text-white mb-4 md:mb-0">Behavior Reports</h2>

              <div class="flex flex-col md:flex-row space-y-3 md:space-y-0 md:space-x-4">
                <select
                  v-model="selectedBehaviorSection"
                  class="block rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white"
                >
                  <option v-for="section in sections" :key="section.id" :value="section.id">
                    {{ section.name }}
                  </option>
                </select>

                <button class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                  New Report
                </button>
              </div>
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
                    <tr v-for="(report, index) in getBehaviorReportsBySection(selectedBehaviorSection)" :key="index">
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
  const selectedSection = ref('section1');
  const selectedGradeSection = ref('section1');
  const selectedBehaviorSection = ref('section1');
  const selectedDate = ref(new Date().toISOString().split('T')[0]);
  const selectedAssignment = ref('quiz1');
  const selectedSectionForAttendanceChart = ref('all');
  const selectedSectionForGradeChart = ref('all');
  const selectedAssignmentForGradeChart = ref('all');

  // Navigation items
  const navItems = [
    { id: 'dashboard', name: 'Dashboard', icon: 'HomeIcon' },
    { id: 'attendance', name: 'Attendance', icon: 'CheckCircleIcon' },
    { id: 'grades', name: 'Grades', icon: 'AwardIcon' },
    { id: 'behavior', name: 'Behavior Reports', icon: 'ClipboardIcon' }
  ];

  // Sections data
  const sections = [
    { id: 'section1', name: 'Science 101 - Period 1', students: ['S001', 'S002', 'S003', 'S004', 'S005'] },
    { id: 'section2', name: 'Science 101 - Period 3', students: ['S006', 'S007', 'S008', 'S009', 'S010'] },
    { id: 'section3', name: 'Biology 202 - Period 5', students: ['S011', 'S012', 'S013', 'S014', 'S015'] }
  ];

  // Mock data
  const weekdays = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri'];
  const attendanceData = [
    { present: 70, absent: 20, late: 10 },
    { present: 80, absent: 10, late: 10 },
    { present: 65, absent: 25, late: 10 },
    { present: 85, absent: 10, late: 5 },
    { present: 75, absent: 15, late: 10 }
  ];

  const grades = ['A', 'B', 'C', 'D', 'F'];
  const gradeData = [35, 40, 15, 7, 3];
  const gradeColorClasses = [
    'bg-green-500 dark:bg-green-600',
    'bg-blue-500 dark:bg-blue-600',
    'bg-yellow-500 dark:bg-yellow-600',
    'bg-orange-500 dark:bg-orange-600',
    'bg-red-500 dark:bg-red-600'
  ];

  // All students data
  const allStudents = [
    // Section 1
    { id: 'S001', name: 'Emma Thompson', avatar: 'https://i.pravatar.cc/40?img=1', status: 'present', notes: '', grade: 'A', gradeComments: 'Excellent work!', section: 'section1' },
    { id: 'S002', name: 'James Wilson', avatar: 'https://i.pravatar.cc/40?img=2', status: 'present', notes: '', grade: 'B+', gradeComments: 'Good effort', section: 'section1' },
    { id: 'S003', name: 'Olivia Martinez', avatar: 'https://i.pravatar.cc/40?img=3', status: 'absent', notes: 'Doctor appointment', grade: 'A-', gradeComments: '', section: 'section1' },
    { id: 'S004', name: 'Noah Johnson', avatar: 'https://i.pravatar.cc/40?img=4', status: 'present', notes: '', grade: 'C', gradeComments: 'Needs improvement', section: 'section1' },
    { id: 'S005', name: 'Sophia Lee', avatar: 'https://i.pravatar.cc/40?img=5', status: 'late', notes: 'Bus delay', grade: 'B', gradeComments: '', section: 'section1' },

    // Section 2
    { id: 'S006', name: 'Liam Brown', avatar: 'https://i.pravatar.cc/40?img=6', status: 'present', notes: '', grade: 'B+', gradeComments: '', section: 'section2' },
    { id: 'S007', name: 'Ava Garcia', avatar: 'https://i.pravatar.cc/40?img=7', status: 'present', notes: '', grade: 'A', gradeComments: 'Outstanding work', section: 'section2' },
    { id: 'S008', name: 'Mason Davis', avatar: 'https://i.pravatar.cc/40?img=8', status: 'absent', notes: 'Sick', grade: 'C+', gradeComments: '', section: 'section2' },
    { id: 'S009', name: 'Isabella Rodriguez', avatar: 'https://i.pravatar.cc/40?img=9', status: 'present', notes: '', grade: 'B-', gradeComments: '', section: 'section2' },
    { id: 'S010', name: 'Ethan Wilson', avatar: 'https://i.pravatar.cc/40?img=10', status: 'late', notes: 'Traffic', grade: 'B', gradeComments: '', section: 'section2' },

    // Section 3
    { id: 'S011', name: 'Mia Clark', avatar: 'https://i.pravatar.cc/40?img=11', status: 'present', notes: '', grade: 'A-', gradeComments: '', section: 'section3' },
    { id: 'S012', name: 'Jacob Lewis', avatar: 'https://i.pravatar.cc/40?img=12', status: 'present', notes: '', grade: 'B+', gradeComments: '', section: 'section3' },
    { id: 'S013', name: 'Charlotte Walker', avatar: 'https://i.pravatar.cc/40?img=13', status: 'absent', notes: 'Family emergency', grade: 'B', gradeComments: '', section: 'section3' },
    { id: 'S014', name: 'Michael Hall', avatar: 'https://i.pravatar.cc/40?img=14', status: 'present', notes: '', grade: 'A', gradeComments: 'Excellent lab work', section: 'section3' },
    { id: 'S015', name: 'Amelia Young', avatar: 'https://i.pravatar.cc/40?img=15', status: 'present', notes: '', grade: 'C', gradeComments: 'Needs to focus more', section: 'section3' }
  ];

  // Behavior reports data
  const allBehaviorReports = [
    // Section 1
    { date: '2023-03-15', student: 'Emma Thompson', avatar: 'https://i.pravatar.cc/40?img=1', type: 'Positive', description: 'Helped another student with their assignment.', section: 'section1' },
    { date: '2023-03-14', student: 'James Wilson', avatar: 'https://i.pravatar.cc/40?img=2', type: 'Concern', description: 'Distracted during class discussion.', section: 'section1' },
    { date: '2023-03-12', student: 'Noah Johnson', avatar: 'https://i.pravatar.cc/40?img=4', type: 'Incident', description: 'Argument with another student during lunch break.', section: 'section1' },
    { date: '2023-03-10', student: 'Sophia Lee', avatar: 'https://i.pravatar.cc/40?img=5', type: 'Positive', description: 'Excellent presentation in class.', section: 'section1' },

    // Section 2
    { date: '2023-03-16', student: 'Liam Brown', avatar: 'https://i.pravatar.cc/40?img=6', type: 'Positive', description: 'Volunteered to help clean lab equipment.', section: 'section2' },
    { date: '2023-03-15', student: 'Ava Garcia', avatar: 'https://i.pravatar.cc/40?img=7', type: 'Positive', description: 'Excellent group leadership during project.', section: 'section2' },
    { date: '2023-03-13', student: 'Ethan Wilson', avatar: 'https://i.pravatar.cc/40?img=10', type: 'Concern', description: 'Using phone during class.', section: 'section2' },

    // Section 3
    { date: '2023-03-17', student: 'Mia Clark', avatar: 'https://i.pravatar.cc/40?img=11', type: 'Positive', description: 'Helped organize class materials.', section: 'section3' },
    { date: '2023-03-16', student: 'Jacob Lewis', avatar: 'https://i.pravatar.cc/40?img=12', type: 'Incident', description: 'Disruptive behavior during lab work.', section: 'section3' },
    { date: '2023-03-14', student: 'Michael Hall', avatar: 'https://i.pravatar.cc/40?img=14', type: 'Positive', description: 'Assisted new student with class procedures.', section: 'section3' }
  ];

  // Recent activities
  const recentActivities = [
    {
      type: 'attendance',
      title: 'Attendance Updated',
      description: 'You marked attendance for Science 101 - Period 1',
      time: '2 hours ago'
    },
    {
      type: 'grade',
      title: 'Grades Submitted',
      description: 'You submitted grades for Science Quiz #1 in Biology 202',
      time: '4 hours ago'
    },
    {
      type: 'behavior',
      title: 'Behavior Report Created',
      description: 'You created a positive behavior report for Mia Clark',
      time: '5 hours ago'
    },
    {
      type: 'assignment',
      title: 'Assignment Created',
      description: 'You created Lab Report #3 for Science 101',
      time: 'Yesterday'
    }
  ];

  // Activity type colors and icons
  const activityTypeColors = {
    attendance: 'bg-green-500',
    grade: 'bg-blue-500',
    behavior: 'bg-purple-500',
    assignment: 'bg-orange-500'
  };

  const activityTypeIcons = {
    attendance: {
      template: `<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
      </svg>`
    },
    grade: {
      template: `<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
      </svg>`
    },
    behavior: {
      template: `<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z" />
      </svg>`
    },
    assignment: {
      template: `<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
      </svg>`
    }
  };

  // Computed properties
  const currentDate = computed(() => {
    const date = new Date();
    return date.toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
  });

  const totalStudents = computed(() => {
    return allStudents.length;
  });

  // Helper functions
  function getStudentsBySection(sectionId) {
    return allStudents.filter(student => student.section === sectionId);
  }

  function getBehaviorReportsBySection(sectionId) {
    return allBehaviorReports.filter(report => report.section === sectionId);
  }

  function getSectionName(sectionId) {
    const section = sections.find(s => s.id === sectionId);
    return section ? section.name : '';
  }

  function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
  }

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
