<template>
    <div class="min-h-screen bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100">
      <!-- Sidebar Toggle Button (Mobile) -->
      <button
        @click="toggleSidebar"
        class="fixed z-50 bottom-4 right-4 md:hidden bg-primary-600 hover:bg-primary-700 text-white p-3 rounded-full shadow-lg"
      >
        <svg v-if="sidebarOpen" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
        <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
        </svg>
      </button>

      <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <aside
          :class="[
            'w-64 bg-white dark:bg-gray-800 shadow-lg transition-all duration-300 ease-in-out',
            'fixed inset-y-0 left-0 z-40 md:relative',
            sidebarOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0'
          ]"
        >
          <!-- Sidebar Header -->
          <div class="flex items-center justify-between p-4 border-b dark:border-gray-700">
            <div class="flex items-center space-x-2">
              <div class="bg-primary-600 p-1.5 rounded">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path d="M12 14l9-5-9-5-9 5 9 5z" />
                  <path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222" />
                </svg>
              </div>
              <h1 class="text-lg font-bold">Admin Portal</h1>
            </div>
            <button @click="toggleSidebar" class="md:hidden">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <!-- Admin Profile -->
          <div class="p-4 border-b dark:border-gray-700">
            <div class="flex items-center space-x-3">
              <img src="https://i.pravatar.cc/100?img=68" alt="Admin Avatar" class="w-10 h-10 rounded-full" />
              <div>
                <h3 class="font-medium">John Anderson</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">System Administrator</p>
              </div>
            </div>
          </div>

          <!-- Navigation Menu -->
          <nav class="p-2">
            <ul class="space-y-1">
              <li v-for="(item, index) in navItems" :key="index">
                <a
                  href="#"
                  @click.prevent="activePage = item.id"
                  :class="[
                    'flex items-center space-x-3 p-3 rounded-lg transition-colors',
                    activePage === item.id
                      ? 'bg-primary-50 text-primary-600 dark:bg-gray-700 dark:text-primary-400'
                      : 'hover:bg-gray-100 dark:hover:bg-gray-700'
                  ]"
                >
                  <span class="text-xl">{{ item.icon }}</span>
                  <span>{{ item.name }}</span>
                  <span
                    v-if="item.notifications"
                    class="ml-auto bg-red-500 text-white text-xs font-bold px-2 py-0.5 rounded-full"
                  >
                    {{ item.notifications }}
                  </span>
                </a>
              </li>
            </ul>
          </nav>

          <!-- Sidebar Footer -->
          <div class="absolute bottom-0 w-full p-4 border-t dark:border-gray-700">
            <div class="flex items-center justify-between">
              <button class="flex items-center space-x-2 text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg>
                <span>Logout</span>
              </button>
              <button @click="toggleDarkMode" class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
                <svg v-if="darkMode" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
                <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                </svg>
              </button>
            </div>
          </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 overflow-y-auto bg-gray-50 dark:bg-gray-900">
          <!-- Header -->
          <header class="bg-white dark:bg-gray-800 shadow-sm">
            <div class="flex items-center justify-between p-4">
              <div class="flex items-center space-x-2">
                <h1 class="text-xl font-semibold">{{ currentPage.name }}</h1>
              </div>
              <div class="flex items-center space-x-4">
                <button class="relative p-1 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                  </svg>
                  <span class="absolute top-0 right-0 h-2 w-2 bg-red-500 rounded-full"></span>
                </button>
                <button class="p-1 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                  </svg>
                </button>
              </div>
            </div>
          </header>

          <!-- Page Content -->
          <div class="p-6">
            <!-- Dashboard Page -->
            <div v-if="activePage === 'dashboard'">
              <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                <!-- Total Users Card -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                  <div class="flex items-center justify-between mb-4">
                    <h3 class="font-medium">Total Users</h3>
                    <span class="text-green-500 bg-green-100 dark:bg-green-900 px-2 py-1 rounded-full text-xs font-medium">+12% this month</span>
                  </div>
                  <div class="flex items-center space-x-4">
                    <div class="w-16 h-16 rounded-full flex items-center justify-center bg-blue-100 dark:bg-blue-900 text-blue-500">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                      </svg>
                    </div>
                    <div>
                      <p class="text-sm text-gray-500 dark:text-gray-400">Total</p>
                      <p class="text-2xl font-bold">{{ dashboardStats.totalUsers }}</p>
                    </div>
                  </div>
                </div>

                <!-- Colleges Card -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                  <div class="flex items-center justify-between mb-4">
                    <h3 class="font-medium">Colleges</h3>
                    <span class="text-green-500 bg-green-100 dark:bg-green-900 px-2 py-1 rounded-full text-xs font-medium">+2 new</span>
                  </div>
                  <div class="flex items-center space-x-4">
                    <div class="w-16 h-16 rounded-full flex items-center justify-center bg-purple-100 dark:bg-purple-900 text-purple-500">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z" />
                      </svg>
                    </div>
                    <div>
                      <p class="text-sm text-gray-500 dark:text-gray-400">Total</p>
                      <p class="text-2xl font-bold">{{ dashboardStats.totalColleges }}</p>
                    </div>
                  </div>
                </div>

                <!-- Departments Card -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                  <div class="flex items-center justify-between mb-4">
                    <h3 class="font-medium">Departments</h3>
                    <span class="text-green-500 bg-green-100 dark:bg-green-900 px-2 py-1 rounded-full text-xs font-medium">+5 this month</span>
                  </div>
                  <div class="flex items-center space-x-4">
                    <div class="w-16 h-16 rounded-full flex items-center justify-center bg-green-100 dark:bg-green-900 text-green-500">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                      </svg>
                    </div>
                    <div>
                      <p class="text-sm text-gray-500 dark:text-gray-400">Total</p>
                      <p class="text-2xl font-bold">{{ dashboardStats.totalDepartments }}</p>
                    </div>
                  </div>
                </div>

                <!-- Courses Card -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                  <div class="flex items-center justify-between mb-4">
                    <h3 class="font-medium">Courses</h3>
                    <span class="text-green-500 bg-green-100 dark:bg-green-900 px-2 py-1 rounded-full text-xs font-medium">+8 this month</span>
                  </div>
                  <div class="flex items-center space-x-4">
                    <div class="w-16 h-16 rounded-full flex items-center justify-center bg-yellow-100 dark:bg-yellow-900 text-yellow-500">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                      </svg>
                    </div>
                    <div>
                      <p class="text-sm text-gray-500 dark:text-gray-400">Total</p>
                      <p class="text-2xl font-bold">{{ dashboardStats.totalCourses }}</p>
                    </div>
                  </div>
                </div>
              </div>

              <!-- User Distribution Chart -->
              <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-6">
                <h3 class="font-medium mb-4">User Distribution</h3>
                <div class="h-80 w-full">
                  <!-- Chart would be rendered here with a chart library -->
                  <div class="h-full w-full flex items-center justify-center bg-gray-100 dark:bg-gray-700 rounded-lg">
                    <div class="grid grid-cols-4 gap-8 w-full px-8">
                      <div class="flex flex-col items-center">
                        <div class="h-48 w-16 bg-blue-500 rounded-t-lg relative overflow-hidden">
                          <div class="absolute bottom-0 w-full bg-blue-600" :style="`height: ${dashboardStats.userDistribution.admins}%`"></div>
                        </div>
                        <p class="mt-2 font-medium">Admins</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ dashboardStats.userDistribution.admins }}%</p>
                      </div>
                      <div class="flex flex-col items-center">
                        <div class="h-48 w-16 bg-green-500 rounded-t-lg relative overflow-hidden">
                          <div class="absolute bottom-0 w-full bg-green-600" :style="`height: ${dashboardStats.userDistribution.teachers}%`"></div>
                        </div>
                        <p class="mt-2 font-medium">Teachers</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ dashboardStats.userDistribution.teachers }}%</p>
                      </div>
                      <div class="flex flex-col items-center">
                        <div class="h-48 w-16 bg-purple-500 rounded-t-lg relative overflow-hidden">
                          <div class="absolute bottom-0 w-full bg-purple-600" :style="`height: ${dashboardStats.userDistribution.students}%`"></div>
                        </div>
                        <p class="mt-2 font-medium">Students</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ dashboardStats.userDistribution.students }}%</p>
                      </div>
                      <div class="flex flex-col items-center">
                        <div class="h-48 w-16 bg-yellow-500 rounded-t-lg relative overflow-hidden">
                          <div class="absolute bottom-0 w-full bg-yellow-600" :style="`height: ${dashboardStats.userDistribution.parents}%`"></div>
                        </div>
                        <p class="mt-2 font-medium">Parents</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ dashboardStats.userDistribution.parents }}%</p>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Students per College Chart -->
              <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-6">
                <h3 class="font-medium mb-4">Students per College</h3>
                <div class="h-80 w-full">
                  <!-- Chart would be rendered here with a chart library -->
                  <div class="h-full w-full flex items-center justify-center bg-gray-100 dark:bg-gray-700 rounded-lg">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 w-full p-8">
                      <div v-for="(college, index) in dashboardStats.collegeStats" :key="index" class="flex items-center space-x-4">
                        <div class="w-12 h-12 rounded-full flex items-center justify-center" :class="college.bgColor">
                          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" :class="college.textColor" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z" />
                          </svg>
                        </div>
                        <div class="flex-1">
                          <p class="font-medium">{{ college.name }}</p>
                          <div class="w-full bg-gray-200 dark:bg-gray-600 rounded-full h-2.5 mt-2">
                            <div class="h-2.5 rounded-full" :class="college.barColor" :style="`width: ${college.percentage}%`"></div>
                          </div>
                          <div class="flex justify-between mt-1">
                            <span class="text-xs text-gray-500 dark:text-gray-400">{{ college.students }} students</span>
                            <span class="text-xs text-gray-500 dark:text-gray-400">{{ college.percentage }}%</span>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Recent Activity -->
              <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h3 class="font-medium mb-4">Recent Activity</h3>
                <div class="space-y-4">
                  <div v-for="(activity, index) in dashboardStats.recentActivities" :key="index" class="flex items-start space-x-4 pb-4 border-b dark:border-gray-700 last:border-0 last:pb-0">
                    <div :class="`w-10 h-10 rounded-full flex items-center justify-center ${activity.iconBg} ${activity.iconColor}`">
                      <span class="text-lg">{{ activity.icon }}</span>
                    </div>
                    <div class="flex-1">
                      <p class="font-medium">{{ activity.title }}</p>
                      <p class="text-sm text-gray-500 dark:text-gray-400">{{ activity.description }}</p>
                    </div>
                    <span class="text-xs text-gray-500 dark:text-gray-400">{{ activity.time }}</span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Users Page -->
            <div v-if="activePage === 'users'">
              <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden mb-6">
                <div class="p-6 border-b dark:border-gray-700 flex justify-between items-center">
                  <div>
                    <h3 class="font-medium">Manage Users</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Add, edit, or remove users from the system</p>
                  </div>
                  <div class="flex space-x-2">
                    <button @click="showAddUserModal = true" class="px-4 py-2 bg-primary-600 text-white rounded-md text-sm flex items-center gap-1">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                      </svg>
                      Add User
                    </button>
                    <button class="px-4 py-2 bg-gray-200 dark:bg-gray-700 rounded-md text-sm">Export</button>
                  </div>
                </div>

                <!-- Filter Controls -->
                <div class="p-4 bg-gray-50 dark:bg-gray-700 border-b dark:border-gray-600">
                  <div class="flex flex-wrap items-center gap-4">
                    <div class="flex-1">
                      <input
                        type="text"
                        placeholder="Search users..."
                        class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 text-sm focus:ring-primary-500 focus:border-primary-500"
                      />
                    </div>
                    <div>
                      <select class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 text-sm focus:ring-primary-500 focus:border-primary-500">
                        <option value="">All Roles</option>
                        <option value="admin">Admin</option>
                        <option value="teacher">Teacher</option>
                        <option value="student">Student</option>
                        <option value="parent">Parent</option>
                      </select>
                    </div>
                    <div>
                      <select class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 text-sm focus:ring-primary-500 focus:border-primary-500">
                        <option value="">All Status</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                      </select>
                    </div>
                    <button class="px-3 py-1 bg-primary-600 text-white rounded-md text-sm">Filter</button>
                  </div>
                </div>

                <div class="overflow-x-auto">
                  <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                      <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">User</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Role</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">College/Department</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                      </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                      <tr v-for="(user, index) in users" :key="index">
                        <td class="px-6 py-4 whitespace-nowrap">
                          <div class="flex items-center">
                            <div class="flex-shrink-0 h-10 w-10">
                              <img class="h-10 w-10 rounded-full" :src="user.avatar" alt="" />
                            </div>
                            <div class="ml-4">
                              <div class="text-sm font-medium">{{ user.name }}</div>
                              <div class="text-sm text-gray-500 dark:text-gray-400">{{ user.email }}</div>
                            </div>
                          </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                          <span :class="`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${user.roleClass}`">
                            {{ user.role }}
                          </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                          <span :class="`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${user.statusClass}`">
                            {{ user.status }}
                          </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                          {{ user.affiliation }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                          <div class="flex space-x-2">
                            <button @click="editUser(user)" class="text-primary-600 hover:text-primary-900 dark:hover:text-primary-400">Edit</button>
                            <button @click="deleteUser(user)" class="text-red-600 hover:text-red-900 dark:hover:text-red-400">Delete</button>
                          </div>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
                <div class="p-4 border-t dark:border-gray-700 flex items-center justify-between">
                  <div class="text-sm text-gray-500 dark:text-gray-400">
                    Showing <span class="font-medium">1</span> to <span class="font-medium">10</span> of <span class="font-medium">{{ users.length }}</span> users
                  </div>
                  <div class="flex space-x-2">
                    <button class="px-3 py-1 rounded border dark:border-gray-600 text-sm">Previous</button>
                    <button class="px-3 py-1 rounded bg-primary-600 text-white text-sm">Next</button>
                  </div>
                </div>
              </div>
            </div>

            <!-- Colleges Page -->
            <div v-if="activePage === 'colleges'">
              <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden mb-6">
                <div class="p-6 border-b dark:border-gray-700 flex justify-between items-center">
                  <div>
                    <h3 class="font-medium">Manage Colleges</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Add, edit, or remove colleges from the system</p>
                  </div>
                  <div class="flex space-x-2">
                    <button @click="showAddCollegeModal = true" class="px-4 py-2 bg-primary-600 text-white rounded-md text-sm flex items-center gap-1">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                      </svg>
                      Add College
                    </button>
                    <button class="px-4 py-2 bg-gray-200 dark:bg-gray-700 rounded-md text-sm">Export</button>
                  </div>
                </div>

                <div class="overflow-x-auto">
                  <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                      <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">College Name</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Code</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Departments</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Students</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Dean</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                      </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                      <tr v-for="(college, index) in colleges" :key="index">
                        <td class="px-6 py-4 whitespace-nowrap">
                          <div class="flex items-center">
                            <div class="flex-shrink-0 h-10 w-10 rounded-full flex items-center justify-center" :class="college.bgColor">
                              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" :class="college.textColor" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z" />
                              </svg>
                            </div>
                            <div class="ml-4">
                              <div class="text-sm font-medium">{{ college.name }}</div>
                              <div class="text-sm text-gray-500 dark:text-gray-400">Established: {{ college.established }}</div>
                            </div>
                          </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">{{ college.code }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">{{ college.departments }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">{{ college.students }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">{{ college.dean }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                          <div class="flex space-x-2">
                            <button @click="editCollege(college)" class="text-primary-600 hover:text-primary-900 dark:hover:text-primary-400">Edit</button>
                            <button @click="deleteCollege(college)" class="text-red-600 hover:text-red-900 dark:hover:text-red-400">Delete</button>
                          </div>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
                <div class="p-4 border-t dark:border-gray-700 flex items-center justify-between">
                  <div class="text-sm text-gray-500 dark:text-gray-400">
                    Showing <span class="font-medium">1</span> to <span class="font-medium">{{ colleges.length }}</span> of <span class="font-medium">{{ colleges.length }}</span> colleges
                  </div>
                  <div class="flex space-x-2">
                    <button class="px-3 py-1 rounded border dark:border-gray-600 text-sm">Previous</button>
                    <button class="px-3 py-1 rounded bg-primary-600 text-white text-sm">Next</button>
                  </div>
                </div>
              </div>
            </div>

            <!-- Departments Page -->
            <div v-if="activePage === 'departments'">
              <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden mb-6">
                <div class="p-6 border-b dark:border-gray-700 flex justify-between items-center">
                  <div>
                    <h3 class="font-medium">Manage Departments</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Add, edit, or remove departments from the system</p>
                  </div>
                  <div class="flex space-x-2">
                    <button @click="showAddDepartmentModal = true" class="px-4 py-2 bg-primary-600 text-white rounded-md text-sm flex items-center gap-1">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                      </svg>
                      Add Department
                    </button>
                    <button class="px-4 py-2 bg-gray-200 dark:bg-gray-700 rounded-md text-sm">Export</button>
                  </div>
                </div>

                <!-- Filter Controls -->
                <div class="p-4 bg-gray-50 dark:bg-gray-700 border-b dark:border-gray-600">
                  <div class="flex flex-wrap items-center gap-4">
                    <div>
                      <select class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 text-sm focus:ring-primary-500 focus:border-primary-500">
                        <option value="">All Colleges</option>
                        <option v-for="(college, index) in colleges" :key="index" :value="college.id">{{ college.name }}</option>
                      </select>
                    </div>
                    <button class="px-3 py-1 bg-primary-600 text-white rounded-md text-sm">Filter</button>
                  </div>
                </div>

                <div class="overflow-x-auto">
                  <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                      <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Department Name</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Code</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">College</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Courses</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Head</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                      </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                      <tr v-for="(department, index) in departments" :key="index">
                        <td class="px-6 py-4 whitespace-nowrap">
                          <div class="flex items-center">
                            <div class="flex-shrink-0 h-10 w-10 rounded-full flex items-center justify-center" :class="department.bgColor">
                              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" :class="department.textColor" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                              </svg>
                            </div>
                            <div class="ml-4">
                              <div class="text-sm font-medium">{{ department.name }}</div>
                              <div class="text-sm text-gray-500 dark:text-gray-400">Established: {{ department.established }}</div>
                            </div>
                          </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">{{ department.code }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">{{ department.college }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">{{ department.courses }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">{{ department.head }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                          <div class="flex space-x-2">
                            <button @click="editDepartment(department)" class="text-primary-600 hover:text-primary-900 dark:hover:text-primary-400">Edit</button>
                            <button @click="deleteDepartment(department)" class="text-red-600 hover:text-red-900 dark:hover:text-red-400">Delete</button>
                          </div>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
                <div class="p-4 border-t dark:border-gray-700 flex items-center justify-between">
                  <div class="text-sm text-gray-500 dark:text-gray-400">
                    Showing <span class="font-medium">1</span> to <span class="font-medium">{{ departments.length }}</span> of <span class="font-medium">{{ departments.length }}</span> departments
                  </div>
                  <div class="flex space-x-2">
                    <button class="px-3 py-1 rounded border dark:border-gray-600 text-sm">Previous</button>
                    <button class="px-3 py-1 rounded bg-primary-600 text-white text-sm">Next</button>
                  </div>
                </div>
              </div>
            </div>

            <!-- Add User Modal -->
            <div v-if="showAddUserModal" class="fixed inset-0 z-50 overflow-y-auto">
              <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                  <div class="absolute inset-0 bg-gray-500 dark:bg-gray-900 opacity-75"></div>
                </div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                  <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                      <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100">Add New User</h3>
                        <div class="mt-4 space-y-4">
                          <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Full Name</label>
                            <input type="text" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm focus:ring-primary-500 focus:border-primary-500" />
                          </div>
                          <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email</label>
                            <input type="email" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm focus:ring-primary-500 focus:border-primary-500" />
                          </div>
                          <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Role</label>
                            <select class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm focus:ring-primary-500 focus:border-primary-500">
                              <option value="admin">Admin</option>
                              <option value="teacher">Teacher</option>
                              <option value="student">Student</option>
                              <option value="parent">Parent</option>
                            </select>
                          </div>
                          <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">College/Department</label>
                            <select class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm focus:ring-primary-500 focus:border-primary-500">
                              <option value="">Select College/Department</option>
                              <option v-for="(college, index) in colleges" :key="`college-${index}`">{{ college.name }}</option>
                            </select>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-primary-600 text-base font-medium text-white hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 sm:ml-3 sm:w-auto sm:text-sm">
                      Add User
                    </button>
                    <button @click="showAddUserModal = false" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-800 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                      Cancel
                    </button>
                  </div>
                </div>
              </div>
            </div>

            <!-- Add College Modal -->
            <div v-if="showAddCollegeModal" class="fixed inset-0 z-50 overflow-y-auto">
              <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                  <div class="absolute inset-0 bg-gray-500 dark:bg-gray-900 opacity-75"></div>
                </div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                  <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                      <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100">Add New College</h3>
                        <div class="mt-4 space-y-4">
                          <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">College Name</label>
                            <input type="text" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm focus:ring-primary-500 focus:border-primary-500" />
                          </div>
                          <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">College Code</label>
                            <input type="text" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm focus:ring-primary-500 focus:border-primary-500" />
                          </div>
                          <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Dean</label>
                            <input type="text" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm focus:ring-primary-500 focus:border-primary-500" />
                          </div>
                          <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Established Year</label>
                            <input type="number" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm focus:ring-primary-500 focus:border-primary-500" />
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-primary-600 text-base font-medium text-white hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 sm:ml-3 sm:w-auto sm:text-sm">
                      Add College
                    </button>
                    <button @click="showAddCollegeModal = false" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-800 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                      Cancel
                    </button>
                  </div>
                </div>
              </div>
            </div>

            <!-- Add Department Modal -->
            <div v-if="showAddDepartmentModal" class="fixed inset-0 z-50 overflow-y-auto">
              <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                  <div class="absolute inset-0 bg-gray-500 dark:bg-gray-900 opacity-75"></div>
                </div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                  <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                      <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100">Add New Department</h3>
                        <div class="mt-4 space-y-4">
                          <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Department Name</label>
                            <input type="text" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm focus:ring-primary-500 focus:border-primary-500" />
                          </div>
                          <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Department Code</label>
                            <input type="text" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm focus:ring-primary-500 focus:border-primary-500" />
                          </div>
                          <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">College</label>
                            <select class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm focus:ring-primary-500 focus:border-primary-500">
                              <option value="">Select College</option>
                              <option v-for="(college, index) in colleges" :key="`college-select-${index}`">{{ college.name }}</option>
                            </select>
                          </div>
                          <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Department Head</label>
                            <input type="text" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm focus:ring-primary-500 focus:border-primary-500" />
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-primary-600 text-base font-medium text-white hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 sm:ml-3 sm:w-auto sm:text-sm">
                      Add Department
                    </button>
                    <button @click="showAddDepartmentModal = false" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-800 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                      Cancel
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </main>
      </div>
    </div>
  </template>

  <script setup lang="ts">
  import { ref, computed, onMounted } from 'vue'

  // State
  const sidebarOpen = ref(false)
  const darkMode = ref(false)
  const activePage = ref('dashboard')
  const showAddUserModal = ref(false)
  const showAddCollegeModal = ref(false)
  const showAddDepartmentModal = ref(false)

  // Navigation items
  const navItems = [
    { id: 'dashboard', name: 'Dashboard', icon: '📊' },
    { id: 'users', name: 'Manage Users', icon: '👤' },
    { id: 'colleges', name: 'Manage Colleges', icon: '🏫', notifications: 2 },
    { id: 'departments', name: 'Manage Departments', icon: '🏢' },
    { id: 'courses', name: 'Manage Courses', icon: '📚' },
    { id: 'majors', name: 'Manage Majors', icon: '🎓' },
    { id: 'yearlevels', name: 'Manage Year Levels', icon: '📅' },
    { id: 'sections', name: 'Manage Sections', icon: '🏫' },
    { id: 'reports', name: 'Reports', icon: '📜' },
  ]

  // Computed
  const currentPage = computed(() => {
    return navItems.find(item => item.id === activePage.value) || navItems[0]
  })

  // Dashboard stats
  const dashboardStats = {
    totalUsers: 2458,
    totalColleges: 8,
    totalDepartments: 32,
    totalCourses: 124,
    userDistribution: {
      admins: 5,
      teachers: 25,
      students: 60,
      parents: 10
    },
    collegeStats: [
      {
        name: 'College of Computer Studies',
        students: 850,
        percentage: 35,
        bgColor: 'bg-blue-100 dark:bg-blue-900',
        textColor: 'text-blue-500',
        barColor: 'bg-blue-500'
      },
      {
        name: 'College of Engineering',
        students: 720,
        percentage: 29,
        bgColor: 'bg-green-100 dark:bg-green-900',
        textColor: 'text-green-500',
        barColor: 'bg-green-500'
      },
      {
        name: 'College of Business',
        students: 520,
        percentage: 21,
        bgColor: 'bg-purple-100 dark:bg-purple-900',
        textColor: 'text-purple-500',
        barColor: 'bg-purple-500'
      },
      {
        name: 'College of Arts & Sciences',
        students: 368,
        percentage: 15,
        bgColor: 'bg-yellow-100 dark:bg-yellow-900',
        textColor: 'text-yellow-500',
        barColor: 'bg-yellow-500'
      }
    ],
    recentActivities: [
      {
        icon: '🏫',
        iconBg: 'bg-blue-100 dark:bg-blue-900',
        iconColor: 'text-blue-500',
        title: 'New College Added',
        description: 'College of Medicine has been added to the system.',
        time: '2h ago'
      },
      {
        icon: '👤',
        iconBg: 'bg-green-100 dark:bg-green-900',
        iconColor: 'text-green-500',
        title: 'New Users Registered',
        description: '15 new student accounts were created.',
        time: '5h ago'
      },
      {
        icon: '📚',
        iconBg: 'bg-yellow-100 dark:bg-yellow-900',
        iconColor: 'text-yellow-500',
        title: 'Course Updated',
        description: 'BSIT curriculum has been updated for the new semester.',
        time: '1d ago'
      },
      {
        icon: '📊',
        iconBg: 'bg-purple-100 dark:bg-purple-900',
        iconColor: 'text-purple-500',
        title: 'Reports Generated',
        description: 'Monthly enrollment statistics have been generated.',
        time: '2d ago'
      },
    ]
  }

  // Users data
  const users = [
    {
      name: 'John Anderson',
      email: 'john.anderson@university.edu',
      role: 'Admin',
      roleClass: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
      status: 'Active',
      statusClass: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
      affiliation: 'System Administration',
      avatar: 'https://i.pravatar.cc/100?img=68'
    },
    {
      name: 'Rebecca Wilson',
      email: 'rebecca.wilson@university.edu',
      role: 'Teacher',
      roleClass: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
      status: 'Active',
      statusClass: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
      affiliation: 'College of Computer Studies',
      avatar: 'https://i.pravatar.cc/100?img=29'
    },
    {
      name: 'Michael Brown',
      email: 'michael.brown@university.edu',
      role: 'Student',
      roleClass: 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300',
      status: 'Active',
      statusClass: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
      affiliation: 'College of Engineering - BSECE',
      avatar: 'https://i.pravatar.cc/100?img=12'
    },
    {
      name: 'Sarah Johnson',
      email: 'sarah.johnson@university.edu',
      role: 'Parent',
      roleClass: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
      status: 'Active',
      statusClass: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
      affiliation: 'Parent of Michael Johnson',
      avatar: 'https://i.pravatar.cc/100?img=32'
    },
    {
      name: 'David Lee',
      email: 'david.lee@university.edu',
      role: 'Teacher',
      roleClass: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
      status: 'Inactive',
      statusClass: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
      affiliation: 'College of Business',
      avatar: 'https://i.pravatar.cc/100?img=15'
    },
    {
      name: 'Jennifer Martinez',
      email: 'jennifer.martinez@university.edu',
      role: 'Admin',
      roleClass: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
      status: 'Active',
      statusClass: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
      affiliation: 'Registrar Office',
      avatar: 'https://i.pravatar.cc/100?img=25'
    },
    {
      name: 'Robert Taylor',
      email: 'robert.taylor@university.edu',
      role: 'Student',
      roleClass: 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300',
      status: 'Active',
      statusClass: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
      affiliation: 'College of Computer Studies - BSIT',
      avatar: 'https://i.pravatar.cc/100?img=53'
    },
    {
      name: 'Emily Davis',
      email: 'emily.davis@university.edu',
      role: 'Student',
      roleClass: 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300',
      status: 'Active',
      statusClass: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
      affiliation: 'College of Arts & Sciences - BA Psychology',
      avatar: 'https://i.pravatar.cc/100?img=44'
    },
    {
      name: 'James Wilson',
      email: 'james.wilson@university.edu',
      role: 'Teacher',
      roleClass: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
      status: 'Active',
      statusClass: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
      affiliation: 'College of Engineering',
      avatar: 'https://i.pravatar.cc/100?img=59'
    },
    {
      name: 'Lisa Thompson',
      email: 'lisa.thompson@university.edu',
      role: 'Parent',
      roleClass: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
      status: 'Inactive',
      statusClass: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
      affiliation: 'Parent of Emily Thompson',
      avatar: 'https://i.pravatar.cc/100?img=39'
    }
  ]

  // Colleges data
  const colleges = [
    {
      id: 1,
      name: 'College of Computer Studies',
      code: 'CCS',
      departments: 4,
      students: 850,
      dean: 'Dr. Alan Turing',
      established: '1985',
      bgColor: 'bg-blue-100 dark:bg-blue-900',
      textColor: 'text-blue-500'
    },
    {
      id: 2,
      name: 'College of Engineering',
      code: 'COE',
      departments: 6,
      students: 720,
      dean: 'Dr. Nikola Tesla',
      established: '1972',
      bgColor: 'bg-green-100 dark:bg-green-900',
      textColor: 'text-green-500'
    },
    {
      id: 3,
      name: 'College of Business',
      code: 'COB',
      departments: 5,
      students: 520,
      dean: 'Dr. Warren Buffett',
      established: '1980',
      bgColor: 'bg-purple-100 dark:bg-purple-900',
      textColor: 'text-purple-500'
    },
    {
      id: 4,
      name: 'College of Arts & Sciences',
      code: 'CAS',
      departments: 8,
      students: 368,
      dean: 'Dr. Marie Curie',
      established: '1965',
      bgColor: 'bg-yellow-100 dark:bg-yellow-900',
      textColor: 'text-yellow-500'
    },
    {
      id: 5,
      name: 'College of Education',
      code: 'COE',
      departments: 3,
      students: 290,
      dean: 'Dr. John Dewey',
      established: '1975',
      bgColor: 'bg-red-100 dark:bg-red-900',
      textColor: 'text-red-500'
    },
    {
      id: 6,
      name: 'College of Medicine',
      code: 'COM',
      departments: 4,
      students: 210,
      dean: 'Dr. Elizabeth Blackwell',
      established: '1990',
      bgColor: 'bg-indigo-100 dark:bg-indigo-900',
      textColor: 'text-indigo-500'
    }
  ]

  // Departments data
  const departments = [
    {
      id: 1,
      name: 'Information Technology Department',
      code: 'IT',
      college: 'College of Computer Studies',
      courses: 4,
      head: 'Dr. Tim Berners-Lee',
      established: '1990',
      bgColor: 'bg-blue-100 dark:bg-blue-900',
      textColor: 'text-blue-500'
    },
    {
      id: 2,
      name: 'Computer Science Department',
      code: 'CS',
      college: 'College of Computer Studies',
      courses: 3,
      head: 'Dr. Ada Lovelace',
      established: '1985',
      bgColor: 'bg-blue-100 dark:bg-blue-900',
      textColor: 'text-blue-500'
    },
    {
      id: 3,
      name: 'Electrical Engineering Department',
      code: 'EE',
      college: 'College of Engineering',
      courses: 5,
      head: 'Dr. Thomas Edison',
      established: '1972',
      bgColor: 'bg-green-100 dark:bg-green-900',
      textColor: 'text-green-500'
    },
    {
      id: 4,
      name: 'Civil Engineering Department',
      code: 'CE',
      college: 'College of Engineering',
      courses: 4,
      head: 'Dr. Isambard Brunel',
      established: '1975',
      bgColor: 'bg-green-100 dark:bg-green-900',
      textColor: 'text-green-500'
    },
    {
      id: 5,
      name: 'Business Administration Department',
      code: 'BA',
      college: 'College of Business',
      courses: 6,
      head: 'Dr. Peter Drucker',
      established: '1980',
      bgColor: 'bg-purple-100 dark:bg-purple-900',
      textColor: 'text-purple-500'
    },
    {
      id: 6,
      name: 'Psychology Department',
      code: 'PSY',
      college: 'College of Arts & Sciences',
      courses: 3,
      head: 'Dr. Sigmund Freud',
      established: '1968',
      bgColor: 'bg-yellow-100 dark:bg-yellow-900',
      textColor: 'text-yellow-500'
    }
  ]

  // Methods
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

  const editUser = (user: any) => {
    // Implement edit user functionality
    console.log('Edit user:', user)
  }

  const deleteUser = (user: any) => {
    // Implement delete user functionality
    console.log('Delete user:', user)
  }

  const editCollege = (college: any) => {
    // Implement edit college functionality
    console.log('Edit college:', college)
  }

  const deleteCollege = (college: any) => {
    // Implement delete college functionality
    console.log('Delete college:', college)
  }

  const editDepartment = (department: any) => {
    // Implement edit department functionality
    console.log('Edit department:', department)
  }

  const deleteDepartment = (department: any) => {
    // Implement delete department functionality
    console.log('Delete department:', department)
  }

  // Lifecycle hooks
  onMounted(() => {
    // Check for user preference
    if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
      darkMode.value = true
      document.documentElement.classList.add('dark')
    }

    // Listen for changes in color scheme preference
    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', event => {
      darkMode.value = event.matches
      if (darkMode.value) {
        document.documentElement.classList.add('dark')
      } else {
        document.documentElement.classList.remove('dark')
      }
    })
  })
  </script>

  <style>
  :root {
    --primary-50: 239, 246, 255;
    --primary-100: 219, 234, 254;
    --primary-200: 191, 219, 254;
    --primary-300: 147, 197, 253;
    --primary-400: 96, 165, 250;
    --primary-500: 59, 130, 246;
    --primary-600: 37, 99, 235;
    --primary-700: 29, 78, 216;
    --primary-800: 30, 64, 175;
    --primary-900: 30, 58, 138;
  }

  .bg-primary-50 { background-color: rgb(var(--primary-50)); }
  .bg-primary-100 { background-color: rgb(var(--primary-100)); }
  .bg-primary-600 { background-color: rgb(var(--primary-600)); }
  .text-primary-600 { color: rgb(var(--primary-600)); }
  .text-primary-400 { color: rgb(var(--primary-400)); }

  /* Add Tailwind dark mode support */
  .dark .dark\:bg-primary-900 { background-color: rgb(var(--primary-900)); }
  .dark .dark\:text-primary-400 { color: rgb(var(--primary-400)); }
  .dark .dark\:text-primary-300 { color: rgb(var(--primary-300)); }
  </style>
