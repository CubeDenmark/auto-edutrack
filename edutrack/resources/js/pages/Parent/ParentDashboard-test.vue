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
              <h1 class="text-lg font-bold">Parent Portal</h1>
            </div>
            <button @click="toggleSidebar" class="md:hidden">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <!-- Parent Profile -->
          <div class="p-4 border-b dark:border-gray-700">
            <div class="flex items-center space-x-3">
              <img src="https://i.pravatar.cc/100" alt="Parent Avatar" class="w-10 h-10 rounded-full" />
              <div>
                <h3 class="font-medium">Sarah Johnson</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">Parent ID: P2023045</p>
              </div>
            </div>
          </div>

          <!-- Child Selector -->
          <div class="p-4 border-b dark:border-gray-700">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Your Children</label>
            <div class="space-y-2">
              <button
                @click="selectedChild = 'all'"
                :class="[
                  'w-full flex items-center p-2 rounded-md transition-colors',
                  selectedChild === 'all'
                    ? 'bg-primary-50 text-primary-600 dark:bg-gray-700 dark:text-primary-400 border-l-4 border-primary-600 dark:border-primary-400'
                    : 'hover:bg-gray-100 dark:hover:bg-gray-700'
                ]"
              >
                <div class="w-8 h-8 rounded-full bg-primary-100 dark:bg-primary-900 text-primary-600 dark:text-primary-400 flex items-center justify-center mr-3">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                  </svg>
                </div>
                <div class="text-left">
                  <p class="font-medium">All Children</p>
                  <p class="text-xs text-gray-500 dark:text-gray-400">Overview</p>
                </div>
              </button>

              <button
                v-for="child in children"
                :key="child.id"
                @click="selectedChild = child.id"
                :class="[
                  'w-full flex items-center p-2 rounded-md transition-colors',
                  selectedChild === child.id
                    ? 'bg-primary-50 text-primary-600 dark:bg-gray-700 dark:text-primary-400 border-l-4 border-primary-600 dark:border-primary-400'
                    : 'hover:bg-gray-100 dark:hover:bg-gray-700'
                ]"
              >
                <img :src="child.avatar" :alt="`${child.name} Avatar`" class="w-8 h-8 rounded-full mr-3" />
                <div class="text-left">
                  <p class="font-medium">{{ child.name }}</p>
                  <p class="text-xs text-gray-500 dark:text-gray-400">{{ child.grade }}</p>
                </div>
                <div v-if="child.alerts" class="ml-auto bg-red-500 text-white text-xs font-bold w-5 h-5 flex items-center justify-center rounded-full">
                  {{ child.alerts }}
                </div>
              </button>
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
              <div class="flex items-center">
                <h1 class="text-xl font-semibold">{{ currentPage.name }}</h1>
                <span v-if="selectedChild !== 'all'" class="ml-2 text-sm text-gray-500 dark:text-gray-400">{{ currentChildName }}</span>
                <span v-else class="ml-2 text-sm text-gray-500 dark:text-gray-400">All Children</span>
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
            <!-- All Children Overview (when "All Children" is selected) -->
            <div v-if="selectedChild === 'all' && activePage === 'dashboard'">
              <h2 class="text-xl font-semibold mb-4">Children Overview</h2>
              <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
                <div v-for="child in children" :key="child.id" class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
                  <div class="p-4 border-b dark:border-gray-700 flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                      <img :src="child.avatar" :alt="`${child.name} Avatar`" class="w-10 h-10 rounded-full" />
                      <div>
                        <h3 class="font-medium">{{ child.name }}</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ child.grade }}</p>
                      </div>
                    </div>
                    <button
                      @click="selectedChild = child.id"
                      class="text-sm text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300"
                    >
                      View Details
                    </button>
                  </div>
                  <div class="p-4">
                    <div class="grid grid-cols-3 gap-4 mb-4">
                      <div class="text-center">
                        <p class="text-sm text-gray-500 dark:text-gray-400">GPA</p>
                        <p class="text-lg font-bold" :class="getGpaColor(child.stats.gpa)">{{ child.stats.gpa }}</p>
                      </div>
                      <div class="text-center">
                        <p class="text-sm text-gray-500 dark:text-gray-400">Attendance</p>
                        <p class="text-lg font-bold" :class="getAttendanceColor(child.stats.attendance)">{{ child.stats.attendance }}%</p>
                      </div>
                      <div class="text-center">
                        <p class="text-sm text-gray-500 dark:text-gray-400">Behavior</p>
                        <p class="text-lg font-bold" :class="getBehaviorColor(child.stats.behavior)">{{ child.stats.behavior }}</p>
                      </div>
                    </div>
                    <div v-if="child.alerts" class="bg-red-50 dark:bg-red-900/20 p-2 rounded-md text-sm text-red-600 dark:text-red-400">
                      <div class="flex items-center space-x-1">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <span>{{ child.alerts }} alert{{ child.alerts > 1 ? 's' : '' }} requiring attention</span>
                      </div>
                    </div>
                  </div>
                  <div class="bg-gray-50 dark:bg-gray-700 p-4">
                    <h4 class="font-medium text-sm mb-2">Recent Activity</h4>
                    <div class="text-sm text-gray-600 dark:text-gray-300">
                      <p>{{ child.recentActivity }}</p>
                    </div>
                  </div>
                </div>
              </div>

              <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-6">
                <h3 class="font-medium mb-4">Upcoming Events</h3>
                <div class="space-y-4">
                  <div v-for="(event, index) in upcomingEvents" :key="index" class="flex items-start space-x-4 pb-4 border-b dark:border-gray-700 last:border-0 last:pb-0">
                    <div class="w-12 h-12 rounded-lg flex flex-col items-center justify-center bg-primary-100 dark:bg-primary-900 text-primary-600 dark:text-primary-400">
                      <span class="text-sm font-bold">{{ event.date.split(' ')[0] }}</span>
                      <span class="text-xs">{{ event.date.split(' ')[1] }}</span>
                    </div>
                    <div class="flex-1">
                      <p class="font-medium">{{ event.title }}</p>
                      <p class="text-sm text-gray-500 dark:text-gray-400">{{ event.description }}</p>
                    </div>
                    <button class="px-3 py-1 text-xs rounded-full bg-primary-50 text-primary-600 dark:bg-gray-700 dark:text-primary-400 hover:bg-primary-100 dark:hover:bg-gray-600">
                      {{ event.action }}
                    </button>
                  </div>
                </div>
              </div>

              <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center justify-between mb-4">
                  <h3 class="font-medium">Recent Notifications</h3>
                  <a href="#" @click.prevent="activePage = 'notifications'" class="text-sm text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300">
                    View All
                  </a>
                </div>
                <div class="space-y-4">
                  <div v-for="(notification, index) in notifications.slice(0, 3)" :key="index"
                    :class="[
                      'p-4 rounded-lg',
                      notification.unread ? 'bg-blue-50 dark:bg-blue-900/10' : 'bg-gray-50 dark:bg-gray-700/50'
                    ]"
                  >
                    <div class="flex items-start space-x-4">
                      <div :class="`w-10 h-10 rounded-full flex items-center justify-center ${notification.iconBg} ${notification.iconColor}`">
                        <span class="text-lg">{{ notification.icon }}</span>
                      </div>
                      <div class="flex-1">
                        <div class="flex items-start justify-between mb-1">
                          <h4 class="font-medium">{{ notification.title }}</h4>
                          <span class="text-xs text-gray-500 dark:text-gray-400">{{ notification.time }}</span>
                        </div>
                        <p class="text-sm text-gray-600 dark:text-gray-300">{{ notification.message }}</p>
                        <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                          For: {{ notification.forChild }}
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Individual Child Dashboard -->
            <div v-if="selectedChild !== 'all' && activePage === 'dashboard'">
              <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <!-- Attendance Card -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                  <div class="flex items-center justify-between mb-4">
                    <h3 class="font-medium">Attendance</h3>
                    <span class="text-green-500 bg-green-100 dark:bg-green-900 px-2 py-1 rounded-full text-xs font-medium">95%</span>
                  </div>
                  <div class="flex items-center space-x-4">
                    <div class="w-16 h-16 rounded-full flex items-center justify-center bg-green-100 dark:bg-green-900 text-green-500">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                      </svg>
                    </div>
                    <div>
                      <p class="text-sm text-gray-500 dark:text-gray-400">Present Days</p>
                      <p class="text-2xl font-bold">38/40</p>
                    </div>
                  </div>
                </div>

                <!-- Grades Card -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                  <div class="flex items-center justify-between mb-4">
                    <h3 class="font-medium">GPA</h3>
                    <span class="text-blue-500 bg-blue-100 dark:bg-blue-900 px-2 py-1 rounded-full text-xs font-medium">B+</span>
                  </div>
                  <div class="flex items-center space-x-4">
                    <div class="w-16 h-16 rounded-full flex items-center justify-center bg-blue-100 dark:bg-blue-900 text-blue-500">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path d="M12 14l9-5-9-5-9 5 9 5z" />
                        <path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998a12.078 12.078 0 01.665-6.479L12 14z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222" />
                      </svg>
                    </div>
                    <div>
                      <p class="text-sm text-gray-500 dark:text-gray-400">Current GPA</p>
                      <p class="text-2xl font-bold">3.7</p>
                    </div>
                  </div>
                </div>

                <!-- Behavior Card -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                  <div class="flex items-center justify-between mb-4">
                    <h3 class="font-medium">Behavior</h3>
                    <span class="text-purple-500 bg-purple-100 dark:bg-purple-900 px-2 py-1 rounded-full text-xs font-medium">Good</span>
                  </div>
                  <div class="flex items-center space-x-4">
                    <div class="w-16 h-16 rounded-full flex items-center justify-center bg-purple-100 dark:bg-purple-900 text-purple-500">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                      </svg>
                    </div>
                    <div>
                      <p class="text-sm text-gray-500 dark:text-gray-400">Reports</p>
                      <p class="text-2xl font-bold">1</p>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Upcoming Events -->
              <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-6">
                <h3 class="font-medium mb-4">Upcoming Events</h3>
                <div class="space-y-4">
                  <div v-for="(event, index) in upcomingEvents" :key="index" class="flex items-start space-x-4 pb-4 border-b dark:border-gray-700 last:border-0 last:pb-0">
                    <div class="w-12 h-12 rounded-lg flex flex-col items-center justify-center bg-primary-100 dark:bg-primary-900 text-primary-600 dark:text-primary-400">
                      <span class="text-sm font-bold">{{ event.date.split(' ')[0] }}</span>
                      <span class="text-xs">{{ event.date.split(' ')[1] }}</span>
                    </div>
                    <div class="flex-1">
                      <p class="font-medium">{{ event.title }}</p>
                      <p class="text-sm text-gray-500 dark:text-gray-400">{{ event.description }}</p>
                    </div>
                    <button class="px-3 py-1 text-xs rounded-full bg-primary-50 text-primary-600 dark:bg-gray-700 dark:text-primary-400 hover:bg-primary-100 dark:hover:bg-gray-600">
                      {{ event.action }}
                    </button>
                  </div>
                </div>
              </div>

              <!-- Performance Chart -->
              <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-6">
                <h3 class="font-medium mb-4">Academic Performance</h3>
                <div class="h-64 w-full">
                  <!-- Chart would be rendered here with a chart library -->
                  <div class="h-full w-full flex items-center justify-center bg-gray-100 dark:bg-gray-700 rounded-lg">
                    <div class="space-y-4 w-full px-8">
                      <!-- Math -->
                      <div>
                        <div class="flex justify-between mb-1">
                          <span class="text-sm font-medium">Mathematics</span>
                          <span class="text-sm font-medium">92%</span>
                        </div>
                        <div class="w-full bg-gray-200 dark:bg-gray-600 rounded-full h-2.5">
                          <div class="bg-blue-600 h-2.5 rounded-full" style="width: 92%"></div>
                        </div>
                      </div>

                      <!-- Science -->
                      <div>
                        <div class="flex justify-between mb-1">
                          <span class="text-sm font-medium">Science</span>
                          <span class="text-sm font-medium">88%</span>
                        </div>
                        <div class="w-full bg-gray-200 dark:bg-gray-600 rounded-full h-2.5">
                          <div class="bg-green-600 h-2.5 rounded-full" style="width: 88%"></div>
                        </div>
                      </div>

                      <!-- English -->
                      <div>
                        <div class="flex justify-between mb-1">
                          <span class="text-sm font-medium">English</span>
                          <span class="text-sm font-medium">78%</span>
                        </div>
                        <div class="w-full bg-gray-200 dark:bg-gray-600 rounded-full h-2.5">
                          <div class="bg-yellow-600 h-2.5 rounded-full" style="width: 78%"></div>
                        </div>
                      </div>

                      <!-- History -->
                      <div>
                        <div class="flex justify-between mb-1">
                          <span class="text-sm font-medium">History</span>
                          <span class="text-sm font-medium">85%</span>
                        </div>
                        <div class="w-full bg-gray-200 dark:bg-gray-600 rounded-full h-2.5">
                          <div class="bg-purple-600 h-2.5 rounded-full" style="width: 85%"></div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Teacher Messages -->
              <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center justify-between mb-4">
                  <h3 class="font-medium">Recent Teacher Messages</h3>
                  <a href="#" class="text-sm text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300">
                    View All
                  </a>
                </div>
                <div class="space-y-4">
                  <div v-for="(message, index) in teacherMessages" :key="index" class="flex items-start space-x-4 pb-4 border-b dark:border-gray-700 last:border-0 last:pb-0">
                    <img :src="message.teacherAvatar" alt="Teacher Avatar" class="w-10 h-10 rounded-full" />
                    <div class="flex-1">
                      <div class="flex items-center justify-between mb-1">
                        <p class="font-medium">{{ message.teacherName }} <span class="text-sm font-normal text-gray-500 dark:text-gray-400">({{ message.subject }})</span></p>
                        <span class="text-xs text-gray-500 dark:text-gray-400">{{ message.time }}</span>
                      </div>
                      <p class="text-sm text-gray-600 dark:text-gray-300">{{ message.content }}</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Grades Page -->
            <div v-if="activePage === 'grades'">
              <!-- Child Selector Tabs (only visible when viewing grades for all children) -->
              <div v-if="selectedChild === 'all'" class="mb-6">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
                  <div class="border-b dark:border-gray-700">
                    <nav class="flex overflow-x-auto">
                      <button
                        v-for="child in children"
                        :key="child.id"
                        @click="tempSelectedChild = child.id"
                        :class="[
                          'px-4 py-3 text-sm font-medium whitespace-nowrap',
                          tempSelectedChild === child.id
                            ? 'border-b-2 border-primary-600 text-primary-600 dark:text-primary-400 dark:border-primary-400'
                            : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300'
                        ]"
                      >
                        {{ child.name }}
                      </button>
                    </nav>
                  </div>
                  <div class="p-4 bg-gray-50 dark:bg-gray-700 flex items-center justify-between">
                    <div class="text-sm">
                      <span class="font-medium">{{ getCurrentChildData().name }}</span>
                      <span class="text-gray-500 dark:text-gray-400"> - {{ getCurrentChildData().grade }}</span>
                    </div>
                    <div class="flex space-x-2">
                      <button class="text-sm text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300">
                        Download Report
                      </button>
                      <button class="text-sm text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300">
                        Email Teacher
                      </button>
                    </div>
                  </div>
                </div>
              </div>

              <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden mb-6">
                <div class="p-6 border-b dark:border-gray-700">
                  <h3 class="font-medium">Current Grades</h3>
                  <p class="text-sm text-gray-500 dark:text-gray-400">Fall Semester 2023</p>
                </div>
                <div class="overflow-x-auto">
                  <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                      <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Subject</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Teacher</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Grade</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Last Updated</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Action</th>
                      </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                      <tr v-for="(grade, index) in gradesData" :key="index">
                        <td class="px-6 py-4 whitespace-nowrap">
                          <div class="flex items-center">
                            <div class="flex-shrink-0 h-8 w-8 rounded-full flex items-center justify-center" :class="grade.iconBg">
                              <span class="text-lg">{{ grade.icon }}</span>
                            </div>
                            <div class="ml-4">
                              <div class="text-sm font-medium">{{ grade.subject }}</div>
                              <div class="text-xs text-gray-500 dark:text-gray-400">{{ grade.code }}</div>
                            </div>
                          </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">{{ grade.teacher }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                          <div class="flex items-center">
                            <span :class="`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${grade.gradeClass}`">
                              {{ grade.grade }}
                            </span>
                            <span class="ml-2 text-sm text-gray-500 dark:text-gray-400">{{ grade.percentage }}%</span>
                          </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ grade.updated }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                          <button class="text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300">
                            View Details
                          </button>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>

              <!-- Grade History -->
              <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h3 class="font-medium mb-4">Grade History</h3>
                <div class="h-64 w-full">
                  <!-- Chart would be rendered here with a chart library -->
                  <div class="h-full w-full flex items-center justify-center bg-gray-100 dark:bg-gray-700 rounded-lg">
                    <p class="text-gray-500 dark:text-gray-400">Grade trend chart would be displayed here</p>
                  </div>
                </div>
              </div>
            </div>

            <!-- Attendance Page -->
            <div v-if="activePage === 'attendance'">
              <!-- Child Selector Tabs (only visible when viewing attendance for all children) -->
              <div v-if="selectedChild === 'all'" class="mb-6">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
                  <div class="border-b dark:border-gray-700">
                    <nav class="flex overflow-x-auto">
                      <button
                        v-for="child in children"
                        :key="child.id"
                        @click="tempSelectedChild = child.id"
                        :class="[
                          'px-4 py-3 text-sm font-medium whitespace-nowrap',
                          tempSelectedChild === child.id
                            ? 'border-b-2 border-primary-600 text-primary-600 dark:text-primary-400 dark:border-primary-400'
                            : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300'
                        ]"
                      >
                        {{ child.name }}
                      </button>
                    </nav>
                  </div>
                </div>
              </div>

              <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                  <h3 class="font-medium mb-4">Attendance Summary</h3>
                  <div class="flex items-center space-x-4">
                    <div class="w-20 h-20 rounded-full flex items-center justify-center bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-400 text-xl font-bold">
                      95%
                    </div>
                    <div class="flex-1">
                      <div class="grid grid-cols-2 gap-2">
                        <div>
                          <p class="text-xs text-gray-500 dark:text-gray-400">Present</p>
                          <p class="font-medium">38 days</p>
                        </div>
                        <div>
                          <p class="text-xs text-gray-500 dark:text-gray-400">Absent</p>
                          <p class="font-medium">2 days</p>
                        </div>
                        <div>
                          <p class="text-xs text-gray-500 dark:text-gray-400">Late</p>
                          <p class="font-medium">0 days</p>
                        </div>
                        <div>
                          <p class="text-xs text-gray-500 dark:text-gray-400">Total</p>
                          <p class="font-medium">40 days</p>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                  <h3 class="font-medium mb-4">Monthly Trend</h3>
                  <div class="h-40 w-full">
                    <!-- Chart would be rendered here with a chart library -->
                    <div class="h-full w-full flex items-center justify-center bg-gray-100 dark:bg-gray-700 rounded-lg">
                      <p class="text-gray-500 dark:text-gray-400">Attendance trend chart would be displayed here</p>
                    </div>
                  </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                  <h3 class="font-medium mb-4">Upcoming Events</h3>
                  <div class="space-y-3">
                    <div class="flex items-center justify-between text-sm">
                      <p>Parent-Teacher Conference</p>
                      <p class="text-gray-500 dark:text-gray-400">Nov 15</p>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                      <p>School Holiday</p>
                      <p class="text-gray-500 dark:text-gray-400">Nov 23-24</p>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                      <p>End of Term</p>
                      <p class="text-gray-500 dark:text-gray-400">Dec 15</p>
                    </div>
                  </div>
                </div>
              </div>

              <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
                <div class="p-6 border-b dark:border-gray-700">
                  <h3 class="font-medium">Attendance Records</h3>
                  <p class="text-sm text-gray-500 dark:text-gray-400">Current Semester: Fall 2023</p>
                </div>
                <div class="overflow-x-auto">
                  <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                      <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Date</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Class</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Notes</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Action</th>
                      </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                      <tr v-for="(record, index) in attendanceRecords" :key="index">
                        <td class="px-6 py-4 whitespace-nowrap text-sm">{{ record.date }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                          <span :class="`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${record.statusClass}`">
                            {{ record.status }}
                          </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">{{ record.class }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ record.notes }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                          <button v-if="record.status === 'Absent'" class="text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300">
                            Submit Excuse
                          </button>
                          <span v-else>-</span>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
                <div class="p-4 border-t dark:border-gray-700 flex items-center justify-between">
                  <div class="text-sm text-gray-500 dark:text-gray-400">
                    Showing <span class="font-medium">1</span> to <span class="font-medium">10</span> of <span class="font-medium">40</span> entries
                  </div>
                  <div class="flex space-x-2">
                    <button class="px-3 py-1 rounded border dark:border-gray-600 text-sm">Previous</button>
                    <button class="px-3 py-1 rounded bg-primary-600 text-white text-sm">Next</button>
                  </div>
                </div>
              </div>
            </div>

            <!-- Behavior Reports Page -->
            <div v-if="activePage === 'behavior'">
              <!-- Child Selector Tabs (only visible when viewing behavior for all children) -->
              <div v-if="selectedChild === 'all'" class="mb-6">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
                  <div class="border-b dark:border-gray-700">
                    <nav class="flex overflow-x-auto">
                      <button
                        v-for="child in children"
                        :key="child.id"
                        @click="tempSelectedChild = child.id"
                        :class="[
                          'px-4 py-3 text-sm font-medium whitespace-nowrap',
                          tempSelectedChild === child.id
                            ? 'border-b-2 border-primary-600 text-primary-600 dark:text-primary-400 dark:border-primary-400'
                            : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300'
                        ]"
                      >
                        {{ child.name }}
                      </button>
                    </nav>
                  </div>
                </div>
              </div>

              <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-6">
                <h3 class="font-medium mb-4">Behavior Summary</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                  <div class="p-4 bg-green-50 dark:bg-green-900/20 rounded-lg border border-green-100 dark:border-green-800">
                    <div class="flex items-center space-x-3">
                      <div class="bg-green-100 dark:bg-green-800 p-2 rounded-full">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                      </div>
                      <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Positive Reports</p>
                        <p class="text-xl font-bold">5</p>
                      </div>
                    </div>
                  </div>
                  <div class="p-4 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg border border-yellow-100 dark:border-yellow-800">
                    <div class="flex items-center space-x-3">
                      <div class="bg-yellow-100 dark:bg-yellow-800 p-2 rounded-full">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-yellow-600 dark:text-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                      </div>
                      <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Warnings</p>
                        <p class="text-xl font-bold">1</p>
                      </div>
                    </div>
                  </div>
                  <div class="p-4 bg-red-50 dark:bg-red-900/20 rounded-lg border border-red-100 dark:border-red-800">
                    <div class="flex items-center space-x-3">
                      <div class="bg-red-100 dark:bg-red-800 p-2 rounded-full">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                      </div>
                      <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Incidents</p>
                        <p class="text-xl font-bold">0</p>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
                <div class="p-6 border-b dark:border-gray-700">
                  <h3 class="font-medium">Behavior Reports</h3>
                  <p class="text-sm text-gray-500 dark:text-gray-400">Current Academic Year</p>
                </div>
                <div class="divide-y divide-gray-200 dark:divide-gray-700">
                  <div v-for="(report, index) in behaviorReports" :key="index" class="p-6">
                    <div class="flex items-start space-x-4">
                      <div :class="`w-10 h-10 rounded-full flex items-center justify-center ${report.iconBg} ${report.iconColor}`">
                        <span class="text-lg">{{ report.icon }}</span>
                      </div>
                      <div class="flex-1">
                        <div class="flex items-center justify-between mb-2">
                          <h4 class="font-medium">{{ report.title }}</h4>
                          <span :class="`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${report.typeClass}`">
                            {{ report.type }}
                          </span>
                        </div>
                        <p class="text-sm text-gray-600 dark:text-gray-300 mb-2">{{ report.description }}</p>
                        <div class="flex items-center justify-between">
                          <p class="text-sm text-gray-500 dark:text-gray-400">{{ report.teacher }} • {{ report.date }}</p>
                          <button class="text-sm text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300">
                            Respond
                          </button>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Notifications Page -->
            <div v-if="activePage === 'notifications'">
              <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
                <div class="p-6 border-b dark:border-gray-700">
                  <div class="flex items-center justify-between">
                    <h3 class="font-medium">Notifications</h3>
                    <div class="flex space-x-4">
                      <select
                        v-model="notificationFilter"
                        class="text-sm rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100"
                      >
                        <option value="all">All Children</option>
                        <option v-for="child in children" :key="child.id" :value="child.id">{{ child.name }}</option>
                      </select>
                      <button class="text-sm text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300">
                        Mark all as read
                      </button>
                    </div>
                  </div>
                </div>
                <div class="divide-y divide-gray-200 dark:divide-gray-700">
                  <div v-for="(notification, index) in filteredNotifications" :key="index"
                    :class="[
                      'p-6 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors',
                      notification.unread ? 'bg-blue-50 dark:bg-blue-900/10' : ''
                    ]"
                  >
                    <div class="flex items-start space-x-4">
                      <div :class="`w-10 h-10 rounded-full flex items-center justify-center ${notification.iconBg} ${notification.iconColor}`">
                        <span class="text-lg">{{ notification.icon }}</span>
                      </div>
                      <div class="flex-1">
                        <div class="flex items-start justify-between mb-1">
                          <h4 class="font-medium">{{ notification.title }}</h4>
                          <span class="text-xs text-gray-500 dark:text-gray-400">{{ notification.time }}</span>
                        </div>
                        <p class="text-sm text-gray-600 dark:text-gray-300">{{ notification.message }}</p>
                        <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                          For: {{ notification.forChild }}
                        </div>
                        <div class="mt-2 flex justify-end space-x-2">
                          <button v-if="notification.unread" class="text-xs text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
                            Mark as read
                          </button>
                          <button class="text-xs text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300">
                            View details
                          </button>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="p-6 border-t dark:border-gray-700 text-center">
                  <button class="text-sm text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300">
                    Load more notifications
                  </button>
                </div>
              </div>
            </div>
          </div>
        </main>
      </div>
    </div>
  </template>

  <script>
  export default {
    data() {
      return {
        sidebarOpen: false,
        darkMode: false,
        activePage: 'dashboard',
        selectedChild: 'all', // Default to "All Children" view
        tempSelectedChild: 'child1', // For tabs in individual pages when "all" is selected
        notificationFilter: 'all',
        children: [
          {
            id: 'child1',
            name: 'Alex Johnson',
            grade: 'Grade 10',
            avatar: 'https://i.pravatar.cc/150?img=3',
            alerts: 2,
            stats: {
              gpa: 3.7,
              attendance: 95,
              behavior: 'Good'
            },
            recentActivity: 'Received an A on Math quiz and submitted Science project.'
          },
          {
            id: 'child2',
            name: 'Emma Johnson',
            grade: 'Grade 8',
            avatar: 'https://i.pravatar.cc/150?img=5',
            alerts: 1,
            stats: {
              gpa: 3.9,
              attendance: 98,
              behavior: 'Excellent'
            },
            recentActivity: 'Won first place in the science fair and joined the debate team.'
          },
          {
            id: 'child3',
            name: 'Noah Johnson',
            grade: 'Grade 6',
            avatar: 'https://i.pravatar.cc/150?img=8',
            alerts: 0,
            stats: {
              gpa: 3.5,
              attendance: 92,
              behavior: 'Good'
            },
            recentActivity: 'Completed all homework assignments and participated in class discussions.'
          }
        ],
        navItems: [
          { id: 'dashboard', name: 'Dashboard', icon: '📊' },
          { id: 'grades', name: 'Grades', icon: '🎓' },
          { id: 'attendance', name: 'Attendance', icon: '📅' },
          { id: 'behavior', name: 'Behavior Reports', icon: '📜' },
          { id: 'notifications', name: 'Notifications', icon: '🔔', notifications: 3 },
            // { title: 'Dashboard', href: '/dashboard', icon: '📊' },
            // { title: 'My Children', href: '/my-children', icon: '👨‍👩‍👧‍👦' },
            // { title: 'Academic Progress', href: '/academic-progress', icon: '📈' },
            // { title: 'Attendance Records', href: '/attendance-records', icon: '✅' },
            // { title: 'Communication', href: '/messages', icon: '✉️' }
        ],
        upcomingEvents: [
          {
            date: 'Nov 15 2023',
            title: 'Parent-Teacher Conference',
            description: 'Schedule a meeting with your child\'s teachers to discuss academic progress.',
            action: 'Schedule'
          },
          {
            date: 'Nov 20 2023',
            title: 'Science Fair',
            description: 'Students will present their science projects to parents and judges.',
            action: 'RSVP'
          },
          {
            date: 'Dec 05 2023',
            title: 'Winter Concert',
            description: 'Annual winter concert featuring performances from the school band and choir.',
            action: 'Get Tickets'
          }
        ],
        teacherMessages: [
          {
            teacherName: 'Dr. Smith',
            teacherAvatar: 'https://i.pravatar.cc/150?img=1',
            subject: 'Mathematics',
            time: '2 hours ago',
            content: 'Alex has been doing exceptionally well in calculus. I recommend looking into advanced math programs for next semester.'
          },
          {
            teacherName: 'Mrs. Johnson',
            teacherAvatar: 'https://i.pravatar.cc/150?img=5',
            subject: 'Science',
            time: '1 day ago',
            content: 'Please remind Alex to bring safety goggles for tomorrow\'s lab experiment. All students must have proper safety equipment.'
          },
          {
            teacherName: 'Mr. Williams',
            teacherAvatar: 'https://i.pravatar.cc/150?img=8',
            subject: 'English',
            time: '3 days ago',
            content: 'Alex\'s essay on Shakespeare was very insightful. However, there are some citation issues we need to address.'
          }
        ],
        attendanceRecords: [
          { date: 'Oct 15, 2023', status: 'Present', statusClass: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300', class: 'All Classes', notes: '' },
          { date: 'Oct 14, 2023', status: 'Present', statusClass: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300', class: 'All Classes', notes: '' },
          { date: 'Oct 13, 2023', status: 'Late', statusClass: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300', class: 'Morning Session', notes: 'Arrived 15 minutes late' },
          { date: 'Oct 12, 2023', status: 'Present', statusClass: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300', class: 'All Classes', notes: '' },
          { date: 'Oct 11, 2023', status: 'Present', statusClass: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300', class: 'All Classes', notes: '' },
          { date: 'Oct 10, 2023', status: 'Absent', statusClass: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300', class: 'All Classes', notes: 'Medical appointment' },
          { date: 'Oct 9, 2023', status: 'Present', statusClass: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300', class: 'All Classes', notes: '' },
          { date: 'Oct 8, 2023', status: 'Present', statusClass: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300', class: 'All Classes', notes: '' },
          { date: 'Oct 7, 2023', status: 'Present', statusClass: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300', class: 'All Classes', notes: '' },
          { date: 'Oct 6, 2023', status: 'Present', statusClass: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300', class: 'All Classes', notes: '' },
        ],
        gradesData: [
          {
            subject: 'Mathematics',
            code: 'MATH101',
            icon: '📐',
            iconBg: 'bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-400',
            teacher: 'Dr. Smith',
            grade: 'A',
            gradeClass: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
            percentage: 92,
            updated: 'Oct 12, 2023'
          },
          {
            subject: 'Science',
            code: 'SCI102',
            icon: '🔬',
            iconBg: 'bg-green-100 dark:bg-green-900 text-green-600 dark:text-green-400',
            teacher: 'Mrs. Johnson',
            grade: 'B+',
            gradeClass: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
            percentage: 88,
            updated: 'Oct 10, 2023'
          },
          {
            subject: 'English',
            code: 'ENG103',
            icon: '📚',
            iconBg: 'bg-yellow-100 dark:bg-yellow-900 text-yellow-600 dark:text-yellow-400',
            teacher: 'Mr. Williams',
            grade: 'C+',
            gradeClass: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
            percentage: 78,
            updated: 'Oct 14, 2023'
          },
          {
            subject: 'History',
            code: 'HIST104',
            icon: '🏛️',
            iconBg: 'bg-purple-100 dark:bg-purple-900 text-purple-600 dark:text-purple-400',
            teacher: 'Ms. Davis',
            grade: 'B',
            gradeClass: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
            percentage: 85,
            updated: 'Oct 8, 2023'
          },
          {
            subject: 'Physical Education',
            code: 'PE105',
            icon: '🏀',
            iconBg: 'bg-red-100 dark:bg-red-900 text-red-600 dark:text-red-400',
            teacher: 'Coach Brown',
            grade: 'A-',
            gradeClass: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
            percentage: 90,
            updated: 'Oct 5, 2023'
          },
        ],
        behaviorReports: [
          {
            icon: '🌟',
            iconBg: 'bg-green-100 dark:bg-green-900',
            iconColor: 'text-green-600 dark:text-green-400',
            title: 'Outstanding Participation',
            type: 'Positive',
            typeClass: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
            description: 'Alex has been consistently participating in class discussions and providing thoughtful insights.',
            teacher: 'Mrs. Johnson',
            date: 'Oct 10, 2023'
          },
          {
            icon: '⚠️',
            iconBg: 'bg-yellow-100 dark:bg-yellow-900',
            iconColor: 'text-yellow-600 dark:text-yellow-400',
            title: 'Homework Incomplete',
            type: 'Warning',
            typeClass: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
            description: 'Alex did not complete the assigned homework for today\'s class.',
            teacher: 'Dr. Smith',
            date: 'Oct 5, 2023'
          },
          {
            icon: '🏆',
            iconBg: 'bg-blue-100 dark:bg-blue-900',
            iconColor: 'text-blue-600 dark:text-blue-400',
            title: 'Science Fair Recognition',
            type: 'Positive',
            typeClass: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
            description: 'Alex\'s science project was selected as one of the top three in the class.',
            teacher: 'Mrs. Johnson',
            date: 'Sep 28, 2023'
          },
          {
            icon: '👏',
            iconBg: 'bg-purple-100 dark:bg-purple-900',
            iconColor: 'text-purple-600 dark:text-purple-400',
            title: 'Helping Peers',
            type: 'Positive',
            typeClass: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
            description: 'Alex voluntarily helped classmates who were struggling with the math assignment.',
            teacher: 'Dr. Smith',
            date: 'Sep 22, 2023'
          },
        ],
        notifications: [
          {
            icon: '📝',
            iconBg: 'bg-blue-100 dark:bg-blue-900',
            iconColor: 'text-blue-600 dark:text-blue-400',
            title: 'New Assignment Posted',
            message: 'History research paper due in two weeks. Check the class portal for details.',
            time: '2 hours ago',
            unread: true,
            forChild: 'Alex Johnson'
          },
          {
            icon: '🎓',
            iconBg: 'bg-green-100 dark:bg-green-900',
            iconColor: 'text-green-600 dark:text-green-400',
            title: 'Grade Updated',
            message: 'Science quiz has been graded. Your child received an A-.',
            time: '5 hours ago',
            unread: true,
            forChild: 'Emma Johnson'
          },
          {
            icon: '📅',
            iconBg: 'bg-purple-100 dark:bg-purple-900',
            iconColor: 'text-purple-600 dark:text-purple-400',
            title: 'Event Reminder',
            message: 'Career Day is scheduled for next Friday. Parents are encouraged to participate.',
            time: '1 day ago',
            unread: true,
            forChild: 'All Children'
          },
          {
            icon: '📢',
            iconBg: 'bg-yellow-100 dark:bg-yellow-900',
            iconColor: 'text-yellow-600 dark:text-yellow-400',
            title: 'School Announcement',
            message: 'Parent-Teacher conferences will be held next week. Schedule your appointment online.',
            time: '2 days ago',
            unread: false,
            forChild: 'All Children'
          },
          {
            icon: '🏆',
            iconBg: 'bg-red-100 dark:bg-red-900',
            iconColor: 'text-red-600 dark:text-red-400',
            title: 'Academic Achievement',
            message: 'Congratulations! Your child has been nominated for the Student of the Month award.',
            time: '3 days ago',
            unread: false,
            forChild: 'Noah Johnson'
          },
        ]
      }
    },
    computed: {
      currentPage() {
        return this.navItems.find(item => item.id === this.activePage) || this.navItems[0];
      },
      currentChildName() {
        const child = this.children.find(c => c.id === this.selectedChild);
        return child ? child.name : '';
      },
      filteredNotifications() {
        if (this.notificationFilter === 'all') {
          return this.notifications;
        } else {
          return this.notifications.filter(notification =>
            notification.forChild === this.getCurrentChildData().name ||
            notification.forChild === 'All Children'
          );
        }
      }
    },
    methods: {
      toggleSidebar() {
        this.sidebarOpen = !this.sidebarOpen;
      },
      toggleDarkMode() {
        this.darkMode = !this.darkMode;
        if (this.darkMode) {
          document.documentElement.classList.add('dark');
        } else {
          document.documentElement.classList.remove('dark');
        }
      },
      getCurrentChildData() {
        const childId = this.selectedChild === 'all' ? this.tempSelectedChild : this.selectedChild;
        return this.children.find(c => c.id === childId) || this.children[0];
      },
      getGpaColor(gpa) {
        if (gpa >= 3.7) return 'text-green-600 dark:text-green-400';
        if (gpa >= 3.0) return 'text-blue-600 dark:text-blue-400';
        if (gpa >= 2.0) return 'text-yellow-600 dark:text-yellow-400';
        return 'text-red-600 dark:text-red-400';
      },
      getAttendanceColor(attendance) {
        if (attendance >= 95) return 'text-green-600 dark:text-green-400';
        if (attendance >= 90) return 'text-blue-600 dark:text-blue-400';
        if (attendance >= 80) return 'text-yellow-600 dark:text-yellow-400';
        return 'text-red-600 dark:text-red-400';
      },
      getBehaviorColor(behavior) {
        if (behavior === 'Excellent') return 'text-green-600 dark:text-green-400';
        if (behavior === 'Good') return 'text-blue-600 dark:text-blue-400';
        if (behavior === 'Fair') return 'text-yellow-600 dark:text-yellow-400';
        return 'text-red-600 dark:text-red-400';
      }
    },
    mounted() {
      // Check for user preference
      if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
        this.darkMode = true;
        document.documentElement.classList.add('dark');
      }

      // Listen for changes in color scheme preference
      window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', event => {
        this.darkMode = event.matches;
        if (this.darkMode) {
          document.documentElement.classList.add('dark');
        } else {
          document.documentElement.classList.remove('dark');
        }
      });
    }
  }
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
