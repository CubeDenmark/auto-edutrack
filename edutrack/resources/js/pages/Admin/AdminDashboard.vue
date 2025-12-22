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
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998a12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222" />
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
              <img src="https://i.pravatar.cc/100" alt="Admin Avatar" class="w-10 h-10 rounded-full" />
              <div>
                <h3 class="font-medium">Admin User</h3>
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
                </a>
              </li>
            </ul>
          </nav>

          <!-- Sidebar Footer -->
          <div class="absolute bottom-0 w-full p-4 border-t dark:border-gray-700">
            <div class="flex items-center justify-between">
              <button @click="logout" class="flex items-center space-x-2 text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">
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
          <!-- Fixed Header -->
          <header class="bg-white dark:bg-gray-800 shadow-sm fixed top-0 right-0 left-0 md:left-64 z-30">
            <div class="flex items-center justify-between p-4">
              <h1 class="text-xl font-semibold">{{ currentPage.name }}</h1>
              <div class="flex items-center space-x-4">
                <!-- Notifications Dropdown -->
                <div class="relative">
                  <button
                    @click="toggleNotifications"
                    class="relative p-1 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700"
                  >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                    <span class="absolute top-0 right-0 h-2 w-2 bg-red-500 rounded-full"></span>
                  </button>

                  <!-- Notifications Dropdown Content -->
                  <div
                    v-if="showNotifications"
                    class="absolute right-0 mt-2 w-80 bg-white dark:bg-gray-800 rounded-md shadow-lg overflow-hidden z-50"
                  >
                    <div class="p-4 border-b dark:border-gray-700">
                      <div class="flex items-center justify-between">
                        <h3 class="text-lg font-medium">Notifications</h3>
                        <span class="px-2 py-1 text-xs bg-primary-100 dark:bg-primary-900 text-primary-600 dark:text-primary-400 rounded-full">
                          {{ unreadNotifications }} new
                        </span>
                      </div>
                    </div>
                    <div class="max-h-96 overflow-y-auto">
                      <div v-for="(notification, index) in notifications" :key="index"
                        :class="[
                          'p-4 border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors',
                          notification.unread ? 'bg-blue-50 dark:bg-blue-900/20' : ''
                        ]"
                      >
                        <div class="flex items-start">
                          <div :class="`w-10 h-10 rounded-full flex items-center justify-center ${notification.iconBg} ${notification.iconColor} mr-3 flex-shrink-0`">
                            <span class="text-lg">{{ notification.icon }}</span>
                          </div>
                          <div class="flex-1">
                            <p class="font-medium text-sm">{{ notification.title }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ notification.description }}</p>
                            <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">{{ notification.time }}</p>
                          </div>
                          <button v-if="notification.unread" @click="markAsRead(index)" class="text-xs text-primary-600 dark:text-primary-400 hover:underline">
                            Mark as read
                          </button>
                        </div>
                      </div>
                    </div>
                    <div class="p-3 text-center border-t dark:border-gray-700">
                      <button class="text-sm text-primary-600 dark:text-primary-400 hover:underline">
                        View all notifications
                      </button>
                    </div>
                  </div>
                </div>

                <!-- Settings Dropdown -->
                <div class="relative">
                  <button
                    @click="toggleSettings"
                    class="p-1 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700"
                  >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                  </button>

                  <!-- Settings Dropdown Content -->
                  <div
                    v-if="showSettings"
                    class="absolute right-0 mt-2 w-56 bg-white dark:bg-gray-800 rounded-md shadow-lg overflow-hidden z-50"
                  >
                    <div class="p-3 border-b dark:border-gray-700">
                      <h3 class="text-lg font-medium">Settings</h3>
                    </div>
                    <div class="py-1">
                      <a href="#" class="block px-4 py-2 text-sm hover:bg-gray-100 dark:hover:bg-gray-700">
                        <div class="flex items-center">
                          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3 text-gray-500 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                          </svg>
                          Profile Settings
                        </div>
                      </a>
                      <a href="#" class="block px-4 py-2 text-sm hover:bg-gray-100 dark:hover:bg-gray-700">
                        <div class="flex items-center">
                          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3 text-gray-500 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                          </svg>
                          Security
                        </div>
                      </a>
                      <a href="#" class="block px-4 py-2 text-sm hover:bg-gray-100 dark:hover:bg-gray-700">
                        <div class="flex items-center">
                          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3 text-gray-500 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                          </svg>
                          Notification Preferences
                        </div>
                      </a>
                      <a href="#" class="block px-4 py-2 text-sm hover:bg-gray-100 dark:hover:bg-gray-700">
                        <div class="flex items-center">
                          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3 text-gray-500 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                          </svg>
                          Help & Support
                        </div>
                      </a>
                    </div>
                    <div class="p-3 border-t dark:border-gray-700">
                      <div class="flex items-center justify-between">
                        <span class="text-sm">Dark Mode</span>
                        <button @click.stop="toggleDarkMode" class="relative inline-flex items-center h-6 rounded-full w-11 transition-colors focus:outline-none">
                          <span
                            :class="[
                              'inline-block w-4 h-4 transform transition-transform rounded-full',
                              darkMode
                                ? 'translate-x-6 bg-white'
                                : 'translate-x-1 bg-gray-700'
                            ]"
                            :style="{
                              backgroundColor: darkMode ? 'white' : 'gray',
                              boxShadow: '0 1px 2px 0 rgba(0, 0, 0, 0.05)'
                            }"
                          ></span>
                          <span
                            :class="[
                              'absolute block w-full h-full rounded-full transition-colors',
                              darkMode
                                ? 'bg-primary-600'
                                : 'bg-gray-300'
                            ]"
                          ></span>
                        </button>
                      </div>
                    </div>
                  </div>
                </div>

                <button class="relative p-1 rounded-full border w-8 h-8 overflow-hidden">
                  <img src="https://i.pravatar.cc/150?img=1" width="32" height="32" class="rounded-full" alt="Avatar" />
                </button>
              </div>
            </div>
          </header>

          <!-- Page Content with top padding to account for fixed header -->
          <div class="p-6 pt-20">
            <!-- Dashboard Page -->
            <div v-if="activePage === 'dashboard'">
              <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                <!-- Users Card -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                  <div class="flex items-center justify-between mb-4">
                    <h3 class="font-medium">Total Users</h3>
                  </div>
                  <div class="flex items-center space-x-4">
                    <div class="w-16 h-16 rounded-full flex items-center justify-center bg-blue-100 dark:bg-blue-900 text-blue-500">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                      </svg>
                    </div>
                    <div>
                      <p class="text-sm text-gray-500 dark:text-gray-400">Registered Users</p>
                      <p class="text-2xl font-bold">1,254</p>
                    </div>
                  </div>
                </div>

                <!-- Departments Card -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                  <div class="flex items-center justify-between mb-4">
                    <h3 class="font-medium">Departments</h3>
                  </div>
                  <div class="flex items-center space-x-4">
                    <div class="w-16 h-16 rounded-full flex items-center justify-center bg-green-100 dark:bg-green-900 text-green-500">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                      </svg>
                    </div>
                    <div>
                      <p class="text-sm text-gray-500 dark:text-gray-400">Active Departments</p>
                      <p class="text-2xl font-bold">8</p>
                    </div>
                  </div>
                </div>

                <!-- Courses Card -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                  <div class="flex items-center justify-between mb-4">
                    <h3 class="font-medium">Courses</h3>
                  </div>
                  <div class="flex items-center space-x-4">
                    <div class="w-16 h-16 rounded-full flex items-center justify-center bg-purple-100 dark:bg-purple-900 text-purple-500">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path d="M12 14l9-5-9-5-9 5 9 5z" />
                        <path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998a12.078 12.078 0 01.665-6.479L12 14z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998a12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222" />
                      </svg>
                    </div>
                    <div>
                      <p class="text-sm text-gray-500 dark:text-gray-400">Total Courses</p>
                      <p class="text-2xl font-bold">24</p>
                    </div>
                  </div>
                </div>

                <!-- Sections Card -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                  <div class="flex items-center justify-between mb-4">
                    <h3 class="font-medium">Sections</h3>
                  </div>
                  <div class="flex items-center space-x-4">
                    <div class="w-16 h-16 rounded-full flex items-center justify-center bg-yellow-100 dark:bg-yellow-900 text-yellow-500">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                      </svg>
                    </div>
                    <div>
                      <p class="text-sm text-gray-500 dark:text-gray-400">Active Sections</p>
                      <p class="text-2xl font-bold">42</p>
                    </div>
                  </div>
                </div>
              </div>

              <!-- User Statistics Chart -->
              <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-6">
                <h3 class="font-medium mb-4">User Statistics</h3>
                <div class="h-80 w-full">
                  <!-- Chart would be rendered here with a chart library -->
                  <div class="h-full w-full flex items-center justify-center bg-gray-100 dark:bg-gray-700 rounded-lg">
                    <div class="space-y-6 w-full px-8">
                      <h4 class="text-center text-gray-500 dark:text-gray-400">Students per Department</h4>
                      <!-- College of Computer Studies -->
                      <div>
                        <div class="flex justify-between mb-1">
                          <span class="text-sm font-medium">College of Computer Studies</span>
                          <span class="text-sm font-medium">450</span>
                        </div>
                        <div class="w-full bg-gray-200 dark:bg-gray-600 rounded-full h-2.5">
                          <div class="bg-blue-600 h-2.5 rounded-full" style="width: 90%"></div>
                        </div>
                      </div>

                      <!-- College of Engineering -->
                      <div>
                        <div class="flex justify-between mb-1">
                          <span class="text-sm font-medium">College of Engineering</span>
                          <span class="text-sm font-medium">380</span>
                        </div>
                        <div class="w-full bg-gray-200 dark:bg-gray-600 rounded-full h-2.5">
                          <div class="bg-green-600 h-2.5 rounded-full" style="width: 76%"></div>
                        </div>
                      </div>

                      <!-- College of Business -->
                      <div>
                        <div class="flex justify-between mb-1">
                          <span class="text-sm font-medium">College of Business</span>
                          <span class="text-sm font-medium">320</span>
                        </div>
                        <div class="w-full bg-gray-200 dark:bg-gray-600 rounded-full h-2.5">
                          <div class="bg-yellow-600 h-2.5 rounded-full" style="width: 64%"></div>
                        </div>
                      </div>

                      <!-- College of Arts and Sciences -->
                      <div>
                        <div class="flex justify-between mb-1">
                          <span class="text-sm font-medium">College of Arts and Sciences</span>
                          <span class="text-sm font-medium">280</span>
                        </div>
                        <div class="w-full bg-gray-200 dark:bg-gray-600 rounded-full h-2.5">
                          <div class="bg-purple-600 h-2.5 rounded-full" style="width: 56%"></div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Recent Activity -->
              <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h3 class="font-medium mb-4">Recent System Activity</h3>
                <div class="space-y-4">
                  <div v-for="(activity, index) in recentActivities" :key="index" class="flex items-start space-x-4 pb-4 border-b dark:border-gray-700 last:border-0 last:pb-0">
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

            <!-- Manage Users Page -->
            <div v-if="activePage === 'users'">
              <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
                <div class="p-6 border-b dark:border-gray-700 flex justify-between items-center">
                  <div>
                    <h3 class="font-medium">Manage Users</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Add, edit, or remove users from the system</p>
                  </div>
                  <button @click="openModal('user')" class="bg-primary-600 hover:bg-primary-700 text-white px-4 py-2 rounded-lg flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    Add User
                  </button>
                </div>

                <!-- User Type Filter -->
                <div class="p-4 border-b dark:border-gray-700 bg-gray-50 dark:bg-gray-700">
                  <div class="flex flex-wrap gap-2">
                    <button
                      v-for="type in userTypes"
                      :key="type.id"
                      @click="selectedUserType = type.id"
                      :class="[
                        'px-3 py-1 rounded-full text-sm font-medium',
                        selectedUserType === type.id
                          ? 'bg-primary-600 text-white'
                          : 'bg-gray-200 text-gray-700 dark:bg-gray-600 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-500'
                      ]"
                    >
                      {{ type.name }}
                    </button>
                  </div>
                </div>

                <div class="overflow-x-auto">
                  <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                      <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Name</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Email</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Role</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                      </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                      <tr v-for="(user, index) in paginatedUsers" :key="index">
                        <td class="px-6 py-4 whitespace-nowrap">
                          <div class="flex items-center">
                            <div class="flex-shrink-0 h-10 w-10">
                              <img class="h-10 w-10 rounded-full" :src="user.avatar" alt="" />
                            </div>
                            <div class="ml-4">
                              <div class="text-sm font-medium">{{ user.name }}</div>
                              <div class="text-sm text-gray-500 dark:text-gray-400">ID: {{ user.id }}</div>
                            </div>
                          </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">{{ user.email }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">{{ user.role }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                          <span :class="`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${user.statusClass}`">
                            {{ user.status }}
                          </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                          <div class="flex space-x-2">
                            <button @click="editUser(user)" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">Edit</button>
                            <button @click="deleteUser(user)" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">Delete</button>
                          </div>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
                <div class="p-4 border-t dark:border-gray-700 flex items-center justify-between">
                  <div class="text-sm text-gray-500 dark:text-gray-400">
                    Showing <span class="font-medium">{{ pagination.users.startIndex + 1 }}</span> to
                    <span class="font-medium">{{ Math.min(pagination.users.endIndex + 1, filteredUsers.length) }}</span> of
                    <span class="font-medium">{{ filteredUsers.length }}</span> entries
                  </div>
                  <div class="flex space-x-2">
                    <button
                      @click="prevPage('users')"
                      :disabled="pagination.users.currentPage === 1"
                      :class="[
                        'px-3 py-1 rounded border dark:border-gray-600 text-sm',
                        pagination.users.currentPage === 1 ? 'opacity-50 cursor-not-allowed' : ''
                      ]"
                    >
                      Previous
                    </button>
                    <button
                      @click="nextPage('users')"
                      :disabled="pagination.users.endIndex >= filteredUsers.length - 1"
                      :class="[
                        'px-3 py-1 rounded text-sm',
                        pagination.users.endIndex >= filteredUsers.length - 1
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

            <!-- Manage Departments Page -->
            <div v-if="activePage === 'departments'">
              <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
                <div class="p-6 border-b dark:border-gray-700 flex justify-between items-center">
                  <div>
                    <h3 class="font-medium">Manage Departments</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Add, edit, or remove departments</p>
                  </div>
                  <button @click="openModal('department')" class="bg-primary-600 hover:bg-primary-700 text-white px-4 py-2 rounded-lg flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    Add Department
                  </button>
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
                          <div class="text-sm font-medium">{{ department.name }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">{{ department.code }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">{{ department.head }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">{{ department.courses }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                          <div class="flex space-x-2">
                            <button @click="editDepartment(department)" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">Edit</button>
                            <button @click="deleteDepartment(department)" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">Delete</button>
                          </div>
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

            <!-- Manage Courses Page -->
            <div v-if="activePage === 'courses'">
              <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
                <div class="p-6 border-b dark:border-gray-700 flex justify-between items-center">
                  <div>
                    <h3 class="font-medium">Manage Courses</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Add, edit, or remove courses and assign to departments</p>
                  </div>
                  <button @click="openModal('course')" class="bg-primary-600 hover:bg-primary-700 text-white px-4 py-2 rounded-lg flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    Add Course
                  </button>
                </div>
                <div class="overflow-x-auto">
                  <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                      <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Course Name</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Code</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Department</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Credits</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                      </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                      <tr v-for="(course, index) in paginatedCourses" :key="index">
                        <td class="px-6 py-4 whitespace-nowrap">
                          <div class="text-sm font-medium">{{ course.name }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">{{ course.code }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">{{ course.department }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">{{ course.credits }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                          <div class="flex space-x-2">
                            <button @click="editCourse(course)" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">Edit</button>
                            <button @click="deleteCourse(course)" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">Delete</button>
                          </div>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
                <div class="p-4 border-t dark:border-gray-700 flex items-center justify-between">
                  <div class="text-sm text-gray-500 dark:text-gray-400">
                    Showing <span class="font-medium">{{ pagination.courses.startIndex + 1 }}</span> to
                    <span class="font-medium">{{ Math.min(pagination.courses.endIndex + 1, courses.length) }}</span> of
                    <span class="font-medium">{{ courses.length }}</span> entries
                  </div>
                  <div class="flex space-x-2">
                    <button
                      @click="prevPage('courses')"
                      :disabled="pagination.courses.currentPage === 1"
                      :class="[
                        'px-3 py-1 rounded border dark:border-gray-600 text-sm',
                        pagination.courses.currentPage === 1 ? 'opacity-50 cursor-not-allowed' : ''
                      ]"
                    >
                      Previous
                    </button>
                    <button
                      @click="nextPage('courses')"
                      :disabled="pagination.courses.endIndex >= courses.length - 1"
                      :class="[
                        'px-3 py-1 rounded text-sm',
                        pagination.courses.endIndex >= courses.length - 1
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

            <!-- Manage Majors Page (NEW!) -->
            <div v-if="activePage === 'majors'">
              <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
                <div class="p-6 border-b dark:border-gray-700 flex justify-between items-center">
                  <div>
                    <h3 class="font-medium">Manage Majors</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Add, edit, or remove majors and assign to courses</p>
                  </div>
                  <button @click="openModal('major')" class="bg-primary-600 hover:bg-primary-700 text-white px-4 py-2 rounded-lg flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    Add Major
                  </button>
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
                          <div class="text-sm font-medium">{{ major.name }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">{{ major.code }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">{{ major.course }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">{{ major.students }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                          <div class="flex space-x-2">
                            <button @click="editMajor(major)" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">Edit</button>
                            <button @click="deleteMajor(major)" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">Delete</button>
                          </div>
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

            <!-- Manage Year Levels Page (NEW!) -->
            <div v-if="activePage === 'yearLevels'">
              <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
                <div class="p-6 border-b dark:border-gray-700 flex justify-between items-center">
                  <div>
                    <h3 class="font-medium">Manage Year Levels</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Add, edit, or remove year levels and assign students</p>
                  </div>
                  <button @click="openModal('yearLevel')" class="bg-primary-600 hover:bg-primary-700 text-white px-4 py-2 rounded-lg flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    Add Year Level
                  </button>
                </div>
                <div class="overflow-x-auto">
                  <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                      <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Year Level</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Description</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Students</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Sections</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                      </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                      <tr v-for="(yearLevel, index) in paginatedYearLevels" :key="index">
                        <td class="px-6 py-4 whitespace-nowrap">
                          <div class="text-sm font-medium">{{ yearLevel.name }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">{{ yearLevel.description }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">{{ yearLevel.students }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">{{ yearLevel.sections }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                          <div class="flex space-x-2">
                            <button @click="editYearLevel(yearLevel)" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">Edit</button>
                            <button @click="deleteYearLevel(yearLevel)" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">Delete</button>
                          </div>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
                <div class="p-4 border-t dark:border-gray-700 flex items-center justify-between">
                  <div class="text-sm text-gray-500 dark:text-gray-400">
                    Showing <span class="font-medium">{{ pagination.yearLevels.startIndex + 1 }}</span> to
                    <span class="font-medium">{{ Math.min(pagination.yearLevels.endIndex + 1, yearLevels.length) }}</span> of
                    <span class="font-medium">{{ yearLevels.length }}</span> entries
                  </div>
                  <div class="flex space-x-2">
                    <button
                      @click="prevPage('yearLevels')"
                      :disabled="pagination.yearLevels.currentPage === 1"
                      :class="[
                        'px-3 py-1 rounded border dark:border-gray-600 text-sm',
                        pagination.yearLevels.currentPage === 1 ? 'opacity-50 cursor-not-allowed' : ''
                      ]"
                    >
                      Previous
                    </button>
                    <button
                      @click="nextPage('yearLevels')"
                      :disabled="pagination.yearLevels.endIndex >= yearLevels.length - 1"
                      :class="[
                        'px-3 py-1 rounded text-sm',
                        pagination.yearLevels.endIndex >= yearLevels.length - 1
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

            <!-- Manage Sections Page -->
            <div v-if="activePage === 'sections'">
              <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
                <div class="p-6 border-b dark:border-gray-700 flex justify-between items-center">
                  <div>
                    <h3 class="font-medium">Manage Sections</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Add, edit, or remove sections and assign courses and teachers</p>
                  </div>
                  <button @click="openModal('section')" class="bg-primary-600 hover:bg-primary-700 text-white px-4 py-2 rounded-lg flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    Add Section
                  </button>
                </div>
                <div class="overflow-x-auto">
                  <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                      <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Section Name</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Course</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Major</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Year Level</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Teacher</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Students</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                      </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                      <tr v-for="(section, index) in paginatedSections" :key="index">
                        <td class="px-6 py-4 whitespace-nowrap">
                          <div class="text-sm font-medium">{{ section.name }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">{{ section.course }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">{{ section.major }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">{{ section.yearLevel }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">{{ section.teacher }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">{{ section.students }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                          <div class="flex space-x-2">
                            <button @click="editSection(section)" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">Edit</button>
                            <button @click="deleteSection(section)" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">Delete</button>
                          </div>
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
                    <button
                      @click="prevPage('sections')"
                      :disabled="pagination.sections.currentPage === 1"
                      :class="[
                        'px-3 py-1 rounded border dark:border-gray-600 text-sm',
                        pagination.sections.currentPage === 1 ? 'opacity-50 cursor-not-allowed' : ''
                      ]"
                    >
                      Previous
                    </button>
                    <button
                      @click="nextPage('sections')"
                      :disabled="pagination.sections.endIndex >= sections.length - 1"
                      :class="[
                        'px-3 py-1 rounded text-sm',
                        pagination.sections.endIndex >= sections.length - 1
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

            <!-- Reports Page -->
            <div v-if="activePage === 'reports'">
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
        </main>
      </div>

      <!-- Modal Component -->
      <div v-if="showModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
          <!-- Background overlay -->
          <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" @click="closeModal"></div>

          <!-- Modal panel -->
          <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
              <div class="sm:flex sm:items-start">
                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                  <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100" id="modal-title">
                    {{ modalTitle }}
                  </h3>
                  <div class="mt-4 space-y-4">
                    <!-- User Form -->
                    <div v-if="modalType === 'user'" class="space-y-4">
                      <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Name</label>
                        <input type="text" v-model="formData.name" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-500 focus:ring-opacity-50" />
                      </div>
                      <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email</label>
                        <input type="email" v-model="formData.email" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-500 focus:ring-opacity-50" />
                      </div>
                      <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Role</label>
                        <select v-model="formData.role" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-500 focus:ring-opacity-50">
                          <option value="Admin">Admin</option>
                          <option value="Teacher">Teacher</option>
                          <option value="Student">Student</option>
                          <option value="Parent">Parent</option>
                        </select>
                      </div>
                      <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
                        <select v-model="formData.status" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-500 focus:ring-opacity-50">
                          <option value="Active">Active</option>
                          <option value="Inactive">Inactive</option>
                        </select>
                      </div>
                    </div>

                    <!-- Department Form -->
                    <div v-if="modalType === 'department'" class="space-y-4">
                      <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Department Name</label>
                        <input type="text" v-model="formData.name" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-500 focus:ring-opacity-50" />
                      </div>
                      <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Code</label>
                        <input type="text" v-model="formData.code" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-500 focus:ring-opacity-50" />
                      </div>
                      <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Department Head</label>
                        <input type="text" v-model="formData.head" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-500 focus:ring-opacity-50" />
                      </div>
                    </div>

                    <!-- Course Form -->
                    <div v-if="modalType === 'course'" class="space-y-4">
                      <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Course Name</label>
                        <input type="text" v-model="formData.name" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-500 focus:ring-opacity-50" />
                      </div>
                      <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Code</label>
                        <input type="text" v-model="formData.code" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-500 focus:ring-opacity-50" />
                      </div>
                      <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Department</label>
                        <select v-model="formData.department" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-500 focus:ring-opacity-50">
                          <option v-for="dept in departments" :key="dept.code" :value="dept.name">{{ dept.name }}</option>
                        </select>
                      </div>
                      <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Credits</label>
                        <input type="number" v-model="formData.credits" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-500 focus:ring-opacity-50" />
                      </div>
                    </div>

                    <!-- Major Form (NEW!) -->
                    <div v-if="modalType === 'major'" class="space-y-4">
                      <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Major Name</label>
                        <input type="text" v-model="formData.name" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-500 focus:ring-opacity-50" />
                      </div>
                      <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Code</label>
                        <input type="text" v-model="formData.code" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-500 focus:ring-opacity-50" />
                      </div>
                      <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Course</label>
                        <select v-model="formData.course" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-500 focus:ring-opacity-50">
                          <option v-for="course in courses" :key="course.code" :value="course.name">{{ course.name }}</option>
                        </select>
                      </div>
                      <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description</label>
                        <textarea v-model="formData.description" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-500 focus:ring-opacity-50"></textarea>
                      </div>
                    </div>

                    <!-- Year Level Form (NEW!) -->
                    <div v-if="modalType === 'yearLevel'" class="space-y-4">
                      <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Year Level</label>
                        <input type="text" v-model="formData.name" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-500 focus:ring-opacity-50" />
                      </div>
                      <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description</label>
                        <input type="text" v-model="formData.description" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-500 focus:ring-opacity-50" />
                      </div>
                      <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Number of Students</label>
                        <input type="number" v-model="formData.students" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-500 focus:ring-opacity-50" />
                      </div>
                      <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Number of Sections</label>
                        <input type="number" v-model="formData.sections" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-500 focus:ring-opacity-50" />
                      </div>
                    </div>

                    <!-- Section Form -->
                    <div v-if="modalType === 'section'" class="space-y-4">
                      <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Section Name</label>
                        <input type="text" v-model="formData.name" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-500 focus:ring-opacity-50" />
                      </div>
                      <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Course</label>
                        <select v-model="formData.course" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-500 focus:ring-opacity-50">
                          <option v-for="course in courses" :key="course.code" :value="course.code">{{ course.name }}</option>
                        </select>
                      </div>
                      <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Major</label>
                        <select v-model="formData.major" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-500 focus:ring-opacity-50">
                          <option v-for="major in majors" :key="major.code" :value="major.name">{{ major.name }}</option>
                        </select>
                      </div>
                      <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Year Level</label>
                        <select v-model="formData.yearLevel" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-500 focus:ring-opacity-50">
                          <option v-for="yearLevel in yearLevels" :key="yearLevel.name" :value="yearLevel.name">{{ yearLevel.name }}</option>
                        </select>
                      </div>
                      <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Teacher</label>
                        <select v-model="formData.teacher" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-500 focus:ring-opacity-50">
                          <option v-for="user in users.filter(u => u.role === 'Teacher')" :key="user.id" :value="user.name">{{ user.name }}</option>
                        </select>
                      </div>
                      <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Number of Students</label>
                        <input type="number" v-model="formData.students" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-500 focus:ring-opacity-50" />
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
              <button
                @click="saveModal"
                type="button"
                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-primary-600 text-base font-medium text-white hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 sm:ml-3 sm:w-auto sm:text-sm"
              >
                Save
              </button>
              <button
                @click="closeModal"
                type="button"
                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-800 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
              >
                Cancel
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Delete Confirmation Modal -->
      <div v-if="showDeleteModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
          <!-- Background overlay -->
          <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" @click="closeDeleteModal"></div>

          <!-- Modal panel -->
          <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
              <div class="sm:flex sm:items-start">
                <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                  <svg class="h-6 w-6 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                  </svg>
                </div>
                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                  <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100" id="modal-title">
                    Confirm Delete
                  </h3>
                  <div class="mt-2">
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                      Are you sure you want to delete this {{ modalType }}? This action cannot be undone.
                    </p>
                  </div>
                </div>
              </div>
            </div>
            <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
              <button
                @click="confirmDelete"
                type="button"
                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm"
              >
                Delete
              </button>
              <button
                @click="closeDeleteModal"
                type="button"
                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-800 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
              >
                Cancel
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </template>

  <script setup lang="ts">
  import { ref, computed, onMounted, watch } from 'vue';

  // State
  const sidebarOpen = ref(false);
  const darkMode = ref(false);
  const activePage = ref('dashboard');
  const selectedUserType = ref('all');
  const showModal = ref(false);
  const showDeleteModal = ref(false);
  const showNotifications = ref(false);
  const showSettings = ref(false);
  const modalType = ref('');
  const modalTitle = ref('');
  const formData = ref<any>({});
  const itemToDelete = ref<any>(null);

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

  // Navigation items
  const navItems = ref([
    { id: 'dashboard', name: 'Dashboard', icon: '📊' },
    { id: 'users', name: 'Manage Users', icon: '👤' },
    { id: 'departments', name: 'Manage Departments', icon: '🏫' },
    { id: 'courses', name: 'Manage Courses', icon: '📚' },
    { id: 'majors', name: 'Manage Majors', icon: '🎓' },
    { id: 'yearLevels', name: 'Manage Year Levels', icon: '📅' },
    { id: 'sections', name: 'Manage Sections', icon: '🏫' },
    { id: 'reports', name: 'Reports', icon: '📜' },
  ]);

  // User types for filtering
  const userTypes = ref([
    { id: 'all', name: 'All Users' },
    { id: 'admin', name: 'Admins' },
    { id: 'teacher', name: 'Teachers' },
    { id: 'student', name: 'Students' },
    { id: 'parent', name: 'Parents' },
  ]);

  // Sample data
  const notifications = ref([
    {
      icon: '👤',
      iconBg: 'bg-blue-100 dark:bg-blue-900',
      iconColor: 'text-blue-500',
      title: 'New User Registration',
      description: 'John Smith registered as a new student.',
      time: '2h ago',
      unread: true
    },
    {
      icon: '🏫',
      iconBg: 'bg-green-100 dark:bg-green-900',
      iconColor: 'text-green-500',
      title: 'Department Added',
      description: 'College of Fine Arts was added to the system.',
      time: '5h ago',
      unread: true
    },
    {
      icon: '📚',
      iconBg: 'bg-yellow-100 dark:bg-yellow-900',
      iconColor: 'text-yellow-500',
      title: 'Course Updated',
      description: 'BSIT curriculum was updated with new subjects.',
      time: '1d ago',
      unread: true
    },
    {
      icon: '📜',
      iconBg: 'bg-purple-100 dark:bg-purple-900',
      iconColor: 'text-purple-500',
      title: 'Report Generated',
      description: 'Performance report for College of Engineering was generated.',
      time: '2d ago',
      unread: false
    },
    {
      icon: '⚠️',
      iconBg: 'bg-red-100 dark:bg-red-900',
      iconColor: 'text-red-500',
      title: 'System Alert',
      description: 'Database backup completed successfully.',
      time: '3d ago',
      unread: false
    },
  ]);

  const recentActivities = ref([
    {
      icon: '👤',
      iconBg: 'bg-blue-100 dark:bg-blue-900',
      iconColor: 'text-blue-500',
      title: 'New User Registration',
      description: 'John Smith registered as a new student.',
      time: '2h ago'
    },
    {
      icon: '🏫',
      iconBg: 'bg-green-100 dark:bg-green-900',
      iconColor: 'text-green-500',
      title: 'Department Added',
      description: 'College of Fine Arts was added to the system.',
      time: '5h ago'
    },
    {
      icon: '📚',
      iconBg: 'bg-yellow-100 dark:bg-yellow-900',
      iconColor: 'text-yellow-500',
      title: 'Course Updated',
      description: 'BSIT curriculum was updated with new subjects.',
      time: '1d ago'
    },
    {
      icon: '📜',
      iconBg: 'bg-purple-100 dark:bg-purple-900',
      iconColor: 'text-purple-500',
      title: 'Report Generated',
      description: 'Performance report for College of Engineering was generated.',
      time: '2d ago'
    },
  ]);

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

  // Sample data for departments
  const departments = ref([
    {
      name: 'College of Computer Studies',
      code: 'CCS',
      head: 'Dr. James Wilson',
      courses: 4
    },
    {
      name: 'College of Engineering',
      code: 'COE',
      head: 'Dr. Maria Rodriguez',
      courses: 6
    },
    {
      name: 'College of Business',
      code: 'COB',
      head: 'Dr. Robert Chen',
      courses: 5
    },
    {
      name: 'College of Arts and Sciences',
      code: 'CAS',
      head: 'Dr. Elizabeth Taylor',
      courses: 8
    },
    {
      name: 'College of Education',
      code: 'COED',
      head: 'Dr. William Brown',
      courses: 6
    },
  ]);

  // Sample data for courses
  const courses = ref([
    {
      name: 'Bachelor of Science in Information Technology',
      code: 'BSIT',
      department: 'College of Computer Studies',
      credits: 120
    },
    {
      name: 'Bachelor of Science in Computer Science',
      code: 'BSCS',
      department: 'College of Computer Studies',
      credits: 130
    },
    {
      name: 'Bachelor of Science in Electronics Engineering',
      code: 'BSECE',
      department: 'College of Engineering',
      credits: 140
    },
    {
      name: 'Bachelor of Science in Mechanical Engineering',
      code: 'BSME',
      department: 'College of Engineering',
      credits: 145
    },
    {
      name: 'Bachelor of Science in Business Administration',
      code: 'BSBA',
      department: 'College of Business',
      credits: 120
    },
  ]);

  // Sample data for majors (NEW!)
  const majors = ref([
    {
      name: 'Web Development',
      code: 'WEBDEV',
      course: 'Bachelor of Science in Information Technology',
      description: 'Focus on web technologies and applications',
      students: 120
    },
    {
      name: 'Network Administration',
      code: 'NETADMIN',
      course: 'Bachelor of Science in Information Technology',
      description: 'Focus on network infrastructure and security',
      students: 85
    },
    {
      name: 'Software Engineering',
      code: 'SOFTENG',
      course: 'Bachelor of Science in Computer Science',
      description: 'Focus on software development methodologies',
      students: 110
    },
    {
      name: 'Artificial Intelligence',
      code: 'AI',
      course: 'Bachelor of Science in Computer Science',
      description: 'Focus on machine learning and AI applications',
      students: 75
    },
    {
      name: 'Power Systems',
      code: 'POWER',
      course: 'Bachelor of Science in Electronics Engineering',
      description: 'Focus on electrical power systems',
      students: 60
    },
  ]);

  // Sample data for year levels (NEW!)
  const yearLevels = ref([
    {
      name: '1st Year',
      description: 'Freshman',
      students: 450,
      sections: 12
    },
    {
      name: '2nd Year',
      description: 'Sophomore',
      students: 380,
      sections: 10
    },
    {
      name: '3rd Year',
      description: 'Junior',
      students: 320,
      sections: 8
    },
    {
      name: '4th Year',
      description: 'Senior',
      students: 280,
      sections: 7
    },
    {
      name: '5th Year',
      description: 'Extended',
      students: 50,
      sections: 2
    },
  ]);

  // Sample data for sections
  const sections = ref([
    {
      name: '1A',
      course: 'BSIT',
      major: 'Web Development',
      yearLevel: '1st Year',
      teacher: 'Jane Smith',
      students: 35
    },
    {
      name: '1B',
      course: 'BSIT',
      major: 'Network Administration',
      yearLevel: '1st Year',
      teacher: 'Robert Johnson',
      students: 32
    },
    {
      name: '2A',
      course: 'BSCS',
      major: 'Software Engineering',
      yearLevel: '2nd Year',
      teacher: 'Jane Smith',
      students: 28
    },
    {
      name: '1A',
      course: 'BSECE',
      major: 'Power Systems',
      yearLevel: '1st Year',
      teacher: 'David Lee',
      students: 30
    },
    {
      name: '1A',
      course: 'BSBA',
      major: 'Marketing',
      yearLevel: '1st Year',
      teacher: 'Maria Garcia',
      students: 40
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

  // Computed properties
  const currentPage = computed(() => {
    return navItems.value.find(item => item.id === activePage.value) || navItems.value[0];
  });

  const filteredUsers = computed(() => {
    if (selectedUserType.value === 'all') {
      return users.value;
    }
    return users.value.filter(user => user.type === selectedUserType.value);
  });

  const paginatedUsers = computed(() => {
    const { startIndex, endIndex } = pagination.value.users;
    return filteredUsers.value.slice(startIndex, endIndex + 1);
  });

  const paginatedDepartments = computed(() => {
    const { startIndex, endIndex } = pagination.value.departments;
    return departments.value.slice(startIndex, endIndex + 1);
  });

  const paginatedCourses = computed(() => {
    const { startIndex, endIndex } = pagination.value.courses;
    return courses.value.slice(startIndex, endIndex + 1);
  });

  const paginatedMajors = computed(() => {
    const { startIndex, endIndex } = pagination.value.majors;
    return majors.value.slice(startIndex, endIndex + 1);
  });

  const paginatedYearLevels = computed(() => {
    const { startIndex, endIndex } = pagination.value.yearLevels;
    return yearLevels.value.slice(startIndex, endIndex + 1);
  });

  const paginatedSections = computed(() => {
    const { startIndex, endIndex } = pagination.value.sections;
    return sections.value.slice(startIndex, endIndex + 1);
  });

  const paginatedReportStudents = computed(() => {
    const { startIndex, endIndex } = pagination.value.reportStudents;
    return reportStudents.value.slice(startIndex, endIndex + 1);
  });

  const unreadNotifications = computed(() => {
    return notifications.value.filter(notification => notification.unread).length;
  });

  // Methods
  const toggleSidebar = () => {
    sidebarOpen.value = !sidebarOpen.value;
  };

  const toggleDarkMode = () => {
    darkMode.value = !darkMode.value;
    if (darkMode.value) {
      document.documentElement.classList.add('dark');
    } else {
      document.documentElement.classList.remove('dark');
    }
  };

  const toggleNotifications = () => {
    showNotifications.value = !showNotifications.value;
    if (showNotifications.value) {
      showSettings.value = false;
    }
  };

  const toggleSettings = () => {
    showSettings.value = !showSettings.value;
    if (showSettings.value) {
      showNotifications.value = false;
    }
  };

  const markAsRead = (index: number) => {
    notifications.value[index].unread = false;
  };

  const openModal = (type: string) => {
    modalType.value = type;
    formData.value = {};

    switch(type) {
      case 'user':
        modalTitle.value = 'Add New User';
        formData.value = { status: 'Active' };
        break;
      case 'department':
        modalTitle.value = 'Add New Department';
        break;
      case 'course':
        modalTitle.value = 'Add New Course';
        break;
      case 'major':
        modalTitle.value = 'Add New Major';
        break;
      case 'yearLevel':
        modalTitle.value = 'Add New Year Level';
        break;
      case 'section':
        modalTitle.value = 'Add New Section';
        break;
    }

    showModal.value = true;
  };

  const closeModal = () => {
    showModal.value = false;
    formData.value = {};
  };

  const saveModal = () => {
    // Generate a unique ID for new items
    const generateId = (prefix: string) => {
      return `${prefix}${Math.floor(Math.random() * 1000).toString().padStart(3, '0')}`;
    };

    switch(modalType.value) {
      case 'user':
        if (formData.value.id) {
          // Update existing user
          const index = users.value.findIndex(u => u.id === formData.value.id);
          if (index !== -1) {
            users.value[index] = { ...users.value[index], ...formData.value };
          }
        } else {
          // Add new user
          const newUser = {
            id: generateId(formData.value.role === 'Admin' ? 'ADM' :
                           formData.value.role === 'Teacher' ? 'TCH' :
                           formData.value.role === 'Student' ? 'STD' : 'PAR'),
            name: formData.value.name,
            email: formData.value.email,
            role: formData.value.role,
            status: formData.value.status,
            statusClass: formData.value.status === 'Active' ?
                        'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300' :
                        'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
            avatar: `https://i.pravatar.cc/150?img=${Math.floor(Math.random() * 70)}`,
            type: formData.value.role.toLowerCase()
          };
          users.value.push(newUser);
        }
        break;

      case 'department':
        if (formData.value.code && departments.value.some(d => d.code === formData.value.code)) {
          // Update existing department
          const index = departments.value.findIndex(d => d.code === formData.value.code);
          if (index !== -1) {
            departments.value[index] = { ...departments.value[index], ...formData.value };
          }
        } else {
          // Add new department
          const newDepartment = {
            name: formData.value.name,
            code: formData.value.code,
            head: formData.value.head,
            courses: 0
          };
          departments.value.push(newDepartment);
        }
        break;

      case 'course':
        if (formData.value.code && courses.value.some(c => c.code === formData.value.code)) {
          // Update existing course
          const index = courses.value.findIndex(c => c.code === formData.value.code);
          if (index !== -1) {
            courses.value[index] = { ...courses.value[index], ...formData.value };
          }
        } else {
          // Add new course
          const newCourse = {
            name: formData.value.name,
            code: formData.value.code,
            department: formData.value.department,
            credits: formData.value.credits
          };
          courses.value.push(newCourse);

          // Update department course count
          const deptIndex = departments.value.findIndex(d => d.name === formData.value.department);
          if (deptIndex !== -1) {
            departments.value[deptIndex].courses++;
          }
        }
        break;

      case 'major':
        if (formData.value.code && majors.value.some(m => m.code === formData.value.code)) {
          // Update existing major
          const index = majors.value.findIndex(m => m.code === formData.value.code);
          if (index !== -1) {
            majors.value[index] = { ...majors.value[index], ...formData.value };
          }
        } else {
          // Add new major
          const newMajor = {
            name: formData.value.name,
            code: formData.value.code,
            course: formData.value.course,
            description: formData.value.description,
            students: 0
          };
          majors.value.push(newMajor);
        }
        break;

      case 'yearLevel':
        if (formData.value.name && yearLevels.value.some(y => y.name === formData.value.name)) {
          // Update existing year level
          const index = yearLevels.value.findIndex(y => y.name === formData.value.name);
          if (index !== -1) {
            yearLevels.value[index] = { ...yearLevels.value[index], ...formData.value };
          }
        } else {
          // Add new year level
          const newYearLevel = {
            name: formData.value.name,
            description: formData.value.description,
            students: formData.value.students || 0,
            sections: formData.value.sections || 0
          };
          yearLevels.value.push(newYearLevel);
        }
        break;

      case 'section':
        if (formData.value.name && formData.value.course &&
            sections.value.some(s => s.name === formData.value.name && s.course === formData.value.course)) {
          // Update existing section
          const index = sections.value.findIndex(s =>
            s.name === formData.value.name && s.course === formData.value.course
          );
          if (index !== -1) {
            sections.value[index] = { ...sections.value[index], ...formData.value };
          }
        } else {
          // Add new section
          const newSection = {
            name: formData.value.name,
            course: formData.value.course,
            major: formData.value.major,
            yearLevel: formData.value.yearLevel,
            teacher: formData.value.teacher,
            students: formData.value.students || 0
          };
          sections.value.push(newSection);
        }
        break;
    }

    closeModal();

    // Add to recent activities and notifications
    const activityData = {
      icon: modalType.value === 'user' ? '👤' :
            modalType.value === 'department' ? '🏫' :
            modalType.value === 'course' ? '📚' :
            modalType.value === 'major' ? '🎓' :
            modalType.value === 'yearLevel' ? '📅' : '🏫',
      iconBg: modalType.value === 'user' ? 'bg-blue-100 dark:bg-blue-900' :
              modalType.value === 'department' ? 'bg-green-100 dark:bg-green-900' :
              modalType.value === 'course' ? 'bg-yellow-100 dark:bg-yellow-900' :
              modalType.value === 'major' ? 'bg-purple-100 dark:bg-purple-900' :
              modalType.value === 'yearLevel' ? 'bg-indigo-100 dark:bg-indigo-900' : 'bg-pink-100 dark:bg-pink-900',
      iconColor: modalType.value === 'user' ? 'text-blue-500' :
                modalType.value === 'department' ? 'text-green-500' :
                modalType.value === 'course' ? 'text-yellow-500' :
                modalType.value === 'major' ? 'text-purple-500' :
                modalType.value === 'yearLevel' ? 'text-indigo-500' : 'text-pink-500',
      title: `${formData.value.id ? 'Updated' : 'Added'} ${modalType.value.charAt(0).toUpperCase() + modalType.value.slice(1)}`,
      description: `${formData.value.name} was ${formData.value.id ? 'updated' : 'added'} to the system.`,
      time: 'Just now'
    };

    recentActivities.value.unshift(activityData);

    // Add to notifications
    notifications.value.unshift({
      ...activityData,
      unread: true
    });

    // Trim activities to keep only the most recent 10
    if (recentActivities.value.length > 10) {
      recentActivities.value = recentActivities.value.slice(0, 10);
    }

    // Trim notifications to keep only the most recent 10
    if (notifications.value.length > 10) {
      notifications.value = notifications.value.slice(0, 10);
    }
  };

  const editUser = (user: any) => {
    modalType.value = 'user';
    modalTitle.value = 'Edit User';
    formData.value = { ...user };
    showModal.value = true;
  };

  const editDepartment = (department: any) => {
    modalType.value = 'department';
    modalTitle.value = 'Edit Department';
    formData.value = { ...department };
    showModal.value = true;
  };

  const editCourse = (course: any) => {
    modalType.value = 'course';
    modalTitle.value = 'Edit Course';
    formData.value = { ...course };
    showModal.value = true;
  };

  const editMajor = (major: any) => {
    modalType.value = 'major';
    modalTitle.value = 'Edit Major';
    formData.value = { ...major };
    showModal.value = true;
  };

  const editYearLevel = (yearLevel: any) => {
    modalType.value = 'yearLevel';
    modalTitle.value = 'Edit Year Level';
    formData.value = { ...yearLevel };
    showModal.value = true;
  };

  const editSection = (section: any) => {
    modalType.value = 'section';
    modalTitle.value = 'Edit Section';
    formData.value = { ...section };
    showModal.value = true;
  };

  const deleteUser = (user: any) => {
    modalType.value = 'user';
    itemToDelete.value = user;
    showDeleteModal.value = true;
  };

  const deleteDepartment = (department: any) => {
    modalType.value = 'department';
    itemToDelete.value = department;
    showDeleteModal.value = true;
  };

  const deleteCourse = (course: any) => {
    modalType.value = 'course';
    itemToDelete.value = course;
    showDeleteModal.value = true;
  };

  const deleteMajor = (major: any) => {
    modalType.value = 'major';
    itemToDelete.value = major;
    showDeleteModal.value = true;
  };

  const deleteYearLevel = (yearLevel: any) => {
    modalType.value = 'yearLevel';
    itemToDelete.value = yearLevel;
    showDeleteModal.value = true;
  };

  const deleteSection = (section: any) => {
    modalType.value = 'section';
    itemToDelete.value = section;
    showDeleteModal.value = true;
  };

  const closeDeleteModal = () => {
    showDeleteModal.value = false;
    itemToDelete.value = null;
  };

  const confirmDelete = () => {
    switch(modalType.value) {
      case 'user':
        users.value = users.value.filter(u => u.id !== itemToDelete.value.id);
        break;
      case 'department':
        departments.value = departments.value.filter(d => d.code !== itemToDelete.value.code);
        break;
      case 'course':
        courses.value = courses.value.filter(c => c.code !== itemToDelete.value.code);
        break;
      case 'major':
        majors.value = majors.value.filter(m => m.code !== itemToDelete.value.code);
        break;
      case 'yearLevel':
        yearLevels.value = yearLevels.value.filter(y => y.name !== itemToDelete.value.name);
        break;
      case 'section':
        sections.value = sections.value.filter(s =>
          !(s.name === itemToDelete.value.name && s.course === itemToDelete.value.course)
        );
        break;
    }

    // Add to recent activities and notifications
    const activityData = {
      icon: modalType.value === 'user' ? '👤' :
            modalType.value === 'department' ? '🏫' :
            modalType.value === 'course' ? '📚' :
            modalType.value === 'major' ? '🎓' :
            modalType.value === 'yearLevel' ? '📅' : '🏫',
      iconBg: 'bg-red-100 dark:bg-red-900',
      iconColor: 'text-red-500',
      title: `Deleted ${modalType.value.charAt(0).toUpperCase() + modalType.value.slice(1)}`,
      description: `${itemToDelete.value.name} was removed from the system.`,
      time: 'Just now'
    };

    recentActivities.value.unshift(activityData);

    // Add to notifications
    notifications.value.unshift({
      ...activityData,
      unread: true
    });

    closeDeleteModal();
  };

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

  const logout = () => {
    // Implement logout functionality
    console.log('Logging out...');
  };

  // Lifecycle hooks
  onMounted(() => {
    // Check for user preference
    if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
      darkMode.value = true;
      document.documentElement.classList.add('dark');
    }

    // Listen for changes in color scheme preference
    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', event => {
      darkMode.value = event.matches;
      if (darkMode.value) {
        document.documentElement.classList.add('dark');
      } else {
        document.documentElement.classList.remove('dark');
      }
    });

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

    // Close dropdowns when clicking outside
    document.addEventListener('click', (event) => {
      const target = event.target as HTMLElement;
      if (showNotifications.value && !target.closest('.notifications-dropdown')) {
        showNotifications.value = false;
      }
      if (showSettings.value && !target.closest('.settings-dropdown')) {
        showSettings.value = false;
      }
    });
  });
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
  .bg-primary-700 { background-color: rgb(var(--primary-700)); }
  .text-primary-600 { color: rgb(var(--primary-600)); }
  .text-primary-400 { color: rgb(var(--primary-400)); }

  /* Add Tailwind dark mode support */
  .dark .dark\:bg-primary-900 { background-color: rgb(var(--primary-900)); }
  .dark .dark\:text-primary-400 { color: rgb(var(--primary-400)); }
  .dark .dark\:text-primary-300 { color: rgb(var(--primary-300)); }
  </style>
