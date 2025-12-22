<template>
    <div class="min-h-screen bg-gray-50 dark:bg-gray-900" :class="{ 'dark': darkMode }">
      <!-- Mobile sidebar toggle -->
      <div class="fixed top-0 left-0 z-40 w-full bg-white dark:bg-gray-800 border-b dark:border-gray-700 lg:hidden">
        <div class="flex items-center justify-between p-4">
          <div class="flex items-center space-x-3">
            <img src="/placeholder.svg?height=40&width=40" alt="School Logo" class="h-10 w-10 rounded-full" />
            <h1 class="text-xl font-bold text-gray-800 dark:text-white">EduParent</h1>
          </div>
          <button @click="toggleSidebar" class="p-2 rounded-md text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">
            <Menu v-if="!sidebarOpen" class="h-6 w-6" />
            <X v-else class="h-6 w-6" />
          </button>
        </div>
      </div>

      <!-- Sidebar -->
      <aside
        class="fixed inset-y-0 left-0 z-30 w-64 bg-white dark:bg-gray-800 border-r dark:border-gray-700 transform transition-transform duration-300 ease-in-out lg:translate-x-0"
        :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
      >
        <div class="flex flex-col h-full">
          <!-- Sidebar header -->
          <div class="hidden lg:flex items-center space-x-3 p-5 border-b dark:border-gray-700">
            <img src="/placeholder.svg?height=40&width=40" alt="School Logo" class="h-10 w-10 rounded-full" />
            <h1 class="text-xl font-bold text-gray-800 dark:text-white">EduParent</h1>
          </div>

          <!-- Parent info -->
          <div class="p-5 border-b dark:border-gray-700">
            <div class="flex items-center space-x-3">
              <img src="/placeholder.svg?height=48&width=48" alt="Parent Avatar" class="h-12 w-12 rounded-full" />
              <div>
                <p class="text-sm font-medium text-gray-900 dark:text-white">Sarah Johnson</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">Parent of Emma Johnson</p>
              </div>
            </div>
          </div>

          <!-- Navigation -->
          <nav class="flex-1 overflow-y-auto p-4">
            <ul class="space-y-2">
              <li v-for="(item, index) in navItems" :key="index">
                <a
                  href="#"
                  @click.prevent="activeTab = item.id"
                  class="flex items-center p-3 text-sm font-medium rounded-lg transition-colors"
                  :class="activeTab === item.id ? 'bg-primary-50 text-primary-600 dark:bg-gray-700 dark:text-white' : 'text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700'"
                >
                  <component :is="item.icon" class="h-5 w-5 mr-3" />
                  {{ item.name }}
                  <span
                    v-if="item.notifications"
                    class="ml-auto bg-red-500 text-white text-xs font-medium px-2 py-0.5 rounded-full"
                  >
                    {{ item.notifications }}
                  </span>
                </a>
              </li>
            </ul>
          </nav>

          <!-- Sidebar footer -->
          <div class="p-4 border-t dark:border-gray-700">
            <div class="flex items-center justify-between">
              <button @click="toggleDarkMode" class="p-2 rounded-md text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">
                <Sun v-if="darkMode" class="h-5 w-5" />
                <Moon v-else class="h-5 w-5" />
              </button>
              <button class="p-2 rounded-md text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">
                <Settings class="h-5 w-5" />
              </button>
              <button class="p-2 rounded-md text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">
                <LogOut class="h-5 w-5" />
              </button>
            </div>
          </div>
        </div>
      </aside>

      <!-- Main content -->
      <main
        class="lg:ml-64 pt-16 lg:pt-0 min-h-screen transition-all duration-300"
        :class="{ 'ml-0': !sidebarOpen }"
      >
        <!-- Dashboard content -->
        <div class="p-6">
          <!-- Dashboard -->
          <div v-if="activeTab === 'dashboard'">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">Dashboard</h2>

            <!-- Quick stats -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
              <div v-for="(stat, index) in quickStats" :key="index" class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 border border-gray-200 dark:border-gray-700">
                <div class="flex items-center">
                  <div class="p-3 rounded-full" :class="stat.bgColor">
                    <component :is="stat.icon" class="h-6 w-6" :class="stat.iconColor" />
                  </div>
                  <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ stat.title }}</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ stat.value }}</p>
                  </div>
                </div>
              </div>
            </div>

            <!-- Charts section -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
              <!-- Grade trends -->
              <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 border border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Grade Trends</h3>
                <div class="h-64 flex items-end space-x-2">
                  <div v-for="(month, index) in gradeTrends" :key="index" class="flex-1 flex flex-col items-center">
                    <div class="w-full bg-primary-100 dark:bg-primary-900/30 rounded-t-sm" :style="`height: ${month.value}%`"></div>
                    <span class="text-xs text-gray-500 dark:text-gray-400 mt-2">{{ month.label }}</span>
                  </div>
                </div>
              </div>

              <!-- Attendance chart -->
              <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 border border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Attendance</h3>
                <div class="relative h-64 flex items-center justify-center">
                  <div class="w-48 h-48 rounded-full border-8 border-gray-200 dark:border-gray-700 flex items-center justify-center">
                    <div class="w-36 h-36 rounded-full border-8 border-primary-500 flex items-center justify-center">
                      <div class="text-center">
                        <p class="text-3xl font-bold text-gray-900 dark:text-white">95%</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Present</p>
                      </div>
                    </div>
                  </div>
                  <div class="absolute bottom-0 w-full flex justify-between text-xs text-gray-500 dark:text-gray-400">
                    <span>5% Absent</span>
                    <span>95% Present</span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Recent activities -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 border border-gray-200 dark:border-gray-700">
              <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Recent Activities</h3>
              <div class="space-y-4">
                <div v-for="(activity, index) in recentActivities" :key="index" class="flex items-start">
                  <div class="p-2 rounded-full" :class="activity.bgColor">
                    <component :is="activity.icon" class="h-5 w-5" :class="activity.iconColor" />
                  </div>
                  <div class="ml-4">
                    <p class="text-sm font-medium text-gray-900 dark:text-white">{{ activity.title }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ activity.time }}</p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Grades -->
          <div v-if="activeTab === 'grades'">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">Grades</h2>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow border border-gray-200 dark:border-gray-700 overflow-hidden">
              <div class="overflow-x-auto">
                <table class="w-full">
                  <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Subject</th>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Current Grade</th>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Last Test</th>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Progress</th>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Teacher</th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    <tr v-for="(subject, index) in subjects" :key="index" class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                      <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                          <div class="p-2 rounded-full" :class="subject.bgColor">
                            <component :is="subject.icon" class="h-5 w-5" :class="subject.iconColor" />
                          </div>
                          <div class="ml-3">
                            <p class="text-sm font-medium text-gray-900 dark:text-white">{{ subject.name }}</p>
                          </div>
                        </div>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 py-1 text-xs font-medium rounded-full" :class="getGradeClass(subject.grade)">
                          {{ subject.grade }}
                        </span>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ subject.lastTest }}</td>
                      <td class="px-6 py-4 whitespace-nowrap">
                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2.5">
                          <div class="h-2.5 rounded-full" :class="subject.progressColor" :style="`width: ${subject.progress}%`"></div>
                        </div>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ subject.teacher }}</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>

          <!-- Attendance -->
          <div v-if="activeTab === 'attendance'">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">Attendance</h2>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
              <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 border border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Attendance Summary</h3>
                <div class="space-y-4">
                  <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-500 dark:text-gray-400">Present</span>
                    <span class="text-sm font-medium text-gray-900 dark:text-white">95% (171 days)</span>
                  </div>
                  <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2.5">
                    <div class="bg-green-500 h-2.5 rounded-full" style="width: 95%"></div>
                  </div>

                  <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-500 dark:text-gray-400">Absent</span>
                    <span class="text-sm font-medium text-gray-900 dark:text-white">3% (5 days)</span>
                  </div>
                  <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2.5">
                    <div class="bg-red-500 h-2.5 rounded-full" style="width: 3%"></div>
                  </div>

                  <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-500 dark:text-gray-400">Late</span>
                    <span class="text-sm font-medium text-gray-900 dark:text-white">2% (4 days)</span>
                  </div>
                  <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2.5">
                    <div class="bg-yellow-500 h-2.5 rounded-full" style="width: 2%"></div>
                  </div>
                </div>
              </div>

              <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-lg shadow p-6 border border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Attendance History</h3>
                <div class="overflow-x-auto">
                  <table class="w-full">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                      <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Date</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Reason</th>
                      </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                      <tr v-for="(record, index) in attendanceRecords" :key="index" class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ record.date }}</td>
                        <td class="px-4 py-3 whitespace-nowrap">
                          <span class="px-2 py-1 text-xs font-medium rounded-full" :class="getStatusClass(record.status)">
                            {{ record.status }}
                          </span>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ record.reason || '-' }}</td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>

          <!-- Behavior -->
          <div v-if="activeTab === 'behavior'">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">Behavior Reports</h2>

            <div class="space-y-6">
              <div v-for="(report, index) in behaviorReports" :key="index" class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 border border-gray-200 dark:border-gray-700">
                <div class="flex items-start justify-between mb-4">
                  <div class="flex items-center">
                    <div class="p-2 rounded-full" :class="getReportTypeClass(report.type).bg">
                      <component :is="getReportTypeClass(report.type).icon" class="h-5 w-5" :class="getReportTypeClass(report.type).color" />
                    </div>
                    <div class="ml-3">
                      <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ report.title }}</h3>
                      <p class="text-sm text-gray-500 dark:text-gray-400">{{ report.date }} • {{ report.teacher }}</p>
                    </div>
                  </div>
                  <span class="px-2 py-1 text-xs font-medium rounded-full" :class="getReportTypeClass(report.type).badge">
                    {{ report.type }}
                  </span>
                </div>
                <p class="text-gray-700 dark:text-gray-300 mb-4">{{ report.description }}</p>
                <div class="flex justify-between items-center text-sm">
                  <span class="text-gray-500 dark:text-gray-400">{{ report.class }}</span>
                  <button class="text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300 font-medium">
                    Acknowledge
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Notifications -->
          <div v-if="activeTab === 'notifications'">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">Notifications</h2>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow border border-gray-200 dark:border-gray-700">
              <div class="p-4 border-b dark:border-gray-700 flex justify-between items-center">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Recent Messages</h3>
                <button class="text-sm text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300 font-medium">
                  Mark all as read
                </button>
              </div>

              <div class="divide-y divide-gray-200 dark:divide-gray-700">
                <div v-for="(notification, index) in notifications" :key="index"
                  class="p-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors"
                  :class="{ 'bg-blue-50 dark:bg-blue-900/10': !notification.read }"
                >
                  <div class="flex items-start space-x-3">
                    <img :src="notification.avatar || '/placeholder.svg?height=40&width=40'" alt="Avatar" class="h-10 w-10 rounded-full" />
                    <div class="flex-1 min-w-0">
                      <div class="flex items-center justify-between">
                        <p class="text-sm font-medium text-gray-900 dark:text-white">{{ notification.sender }}</p>
                        <span class="text-xs text-gray-500 dark:text-gray-400">{{ notification.time }}</span>
                      </div>
                      <p class="text-sm text-gray-500 dark:text-gray-400 truncate">{{ notification.subject }}</p>
                      <p class="mt-1 text-sm text-gray-700 dark:text-gray-300">{{ notification.message }}</p>

                      <div class="mt-2 flex space-x-2">
                        <button class="text-xs text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300 font-medium">
                          Reply
                        </button>
                        <button class="text-xs text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 font-medium">
                          Mark as {{ notification.read ? 'unread' : 'read' }}
                        </button>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </main>
    </div>
  </template>

  <script setup>
  import { ref } from 'vue';
  import {
    Menu, X, Sun, Moon, Settings, LogOut, BookOpen, Calendar, AlertTriangle,
    Bell, Home, TrendingUp, Award, Calculator, Beaker, Globe, BookMarked,
    Music, Dumbbell, ThumbsUp, AlertCircle, MessageSquare
  } from 'lucide-vue-next';

  // State
  const darkMode = ref(false);
  const sidebarOpen = ref(false);
  const activeTab = ref('dashboard');

  // Toggle functions
  const toggleDarkMode = () => {
    darkMode.value = !darkMode.value;
  };

  const toggleSidebar = () => {
    sidebarOpen.value = !sidebarOpen.value;
  };

  // Navigation items
  const navItems = [
    { id: 'dashboard', name: 'Dashboard', icon: Home },
    { id: 'grades', name: 'Grades', icon: BookOpen },
    { id: 'attendance', name: 'Attendance', icon: Calendar },
    { id: 'behavior', name: 'Behavior Reports', icon: AlertTriangle },
    { id: 'notifications', name: 'Notifications', icon: Bell, notifications: 3 },
  ];

  // Dashboard data
  const quickStats = [
    {
      title: 'GPA',
      value: '3.8',
      icon: Award,
      bgColor: 'bg-blue-100 dark:bg-blue-900/30',
      iconColor: 'text-blue-600 dark:text-blue-400'
    },
    {
      title: 'Attendance',
      value: '95%',
      icon: Calendar,
      bgColor: 'bg-green-100 dark:bg-green-900/30',
      iconColor: 'text-green-600 dark:text-green-400'
    },
    {
      title: 'Assignments',
      value: '2 Due',
      icon: BookOpen,
      bgColor: 'bg-yellow-100 dark:bg-yellow-900/30',
      iconColor: 'text-yellow-600 dark:text-yellow-400'
    },
    {
      title: 'Upcoming Tests',
      value: '1',
      icon: TrendingUp,
      bgColor: 'bg-purple-100 dark:bg-purple-900/30',
      iconColor: 'text-purple-600 dark:text-purple-400'
    },
  ];

  const gradeTrends = [
    { label: 'Jan', value: 85 },
    { label: 'Feb', value: 78 },
    { label: 'Mar', value: 92 },
    { label: 'Apr', value: 88 },
    { label: 'May', value: 90 },
    { label: 'Jun', value: 95 },
  ];

  const recentActivities = [
    {
      title: 'Math Quiz Graded - Score: A',
      time: 'Today, 10:30 AM',
      icon: Calculator,
      bgColor: 'bg-blue-100 dark:bg-blue-900/30',
      iconColor: 'text-blue-600 dark:text-blue-400'
    },
    {
      title: 'Science Project Submitted',
      time: 'Yesterday, 3:15 PM',
      icon: Beaker,
      bgColor: 'bg-green-100 dark:bg-green-900/30',
      iconColor: 'text-green-600 dark:text-green-400'
    },
    {
      title: 'Parent-Teacher Meeting Scheduled',
      time: 'Sep 15, 2023',
      icon: Calendar,
      bgColor: 'bg-purple-100 dark:bg-purple-900/30',
      iconColor: 'text-purple-600 dark:text-purple-400'
    },
  ];

  // Grades data
  const subjects = [
    {
      name: 'Mathematics',
      grade: 'A',
      lastTest: '95/100',
      progress: 95,
      progressColor: 'bg-green-500',
      teacher: 'Mr. Johnson',
      icon: Calculator,
      bgColor: 'bg-blue-100 dark:bg-blue-900/30',
      iconColor: 'text-blue-600 dark:text-blue-400'
    },
    {
      name: 'Science',
      grade: 'A-',
      lastTest: '88/100',
      progress: 88,
      progressColor: 'bg-green-500',
      teacher: 'Mrs. Smith',
      icon: Beaker,
      bgColor: 'bg-green-100 dark:bg-green-900/30',
      iconColor: 'text-green-600 dark:text-green-400'
    },
    {
      name: 'English',
      grade: 'B+',
      lastTest: '85/100',
      progress: 85,
      progressColor: 'bg-blue-500',
      teacher: 'Ms. Davis',
      icon: BookMarked,
      bgColor: 'bg-yellow-100 dark:bg-yellow-900/30',
      iconColor: 'text-yellow-600 dark:text-yellow-400'
    },
    {
      name: 'History',
      grade: 'B',
      lastTest: '82/100',
      progress: 82,
      progressColor: 'bg-blue-500',
      teacher: 'Mr. Wilson',
      icon: Globe,
      bgColor: 'bg-red-100 dark:bg-red-900/30',
      iconColor: 'text-red-600 dark:text-red-400'
    },
    {
      name: 'Music',
      grade: 'A',
      lastTest: '96/100',
      progress: 96,
      progressColor: 'bg-green-500',
      teacher: 'Mrs. Lee',
      icon: Music,
      bgColor: 'bg-purple-100 dark:bg-purple-900/30',
      iconColor: 'text-purple-600 dark:text-purple-400'
    },
    {
      name: 'Physical Education',
      grade: 'A',
      lastTest: '94/100',
      progress: 94,
      progressColor: 'bg-green-500',
      teacher: 'Mr. Brown',
      icon: Dumbbell,
      bgColor: 'bg-indigo-100 dark:bg-indigo-900/30',
      iconColor: 'text-indigo-600 dark:text-indigo-400'
    },
  ];

  // Attendance data
  const attendanceRecords = [
    { date: 'Sep 15, 2023', status: 'Present', reason: null },
    { date: 'Sep 14, 2023', status: 'Present', reason: null },
    { date: 'Sep 13, 2023', status: 'Absent', reason: 'Doctor appointment' },
    { date: 'Sep 12, 2023', status: 'Present', reason: null },
    { date: 'Sep 11, 2023', status: 'Late', reason: 'Traffic delay' },
    { date: 'Sep 10, 2023', status: 'Present', reason: null },
    { date: 'Sep 9, 2023', status: 'Present', reason: null },
    { date: 'Sep 8, 2023', status: 'Present', reason: null },
  ];

  // Behavior reports data
  const behaviorReports = [
    {
      title: 'Excellent Classroom Participation',
      date: 'Sep 14, 2023',
      teacher: 'Mrs. Smith',
      type: 'Positive',
      description: 'Emma has been actively participating in class discussions and helping other students understand difficult concepts. Her positive attitude creates a great learning environment.',
      class: 'Science Class'
    },
    {
      title: 'Homework Completion Issue',
      date: 'Sep 10, 2023',
      teacher: 'Mr. Johnson',
      type: 'Concern',
      description: 'Emma has missed submitting two homework assignments this week. Please ensure she completes her assignments on time as they are an important part of the learning process.',
      class: 'Mathematics'
    },
    {
      title: 'Leadership in Group Project',
      date: 'Sep 5, 2023',
      teacher: 'Ms. Davis',
      type: 'Positive',
      description: 'Emma took a leadership role in her group project, organizing tasks and ensuring everyone contributed. The project was completed ahead of schedule with excellent results.',
      class: 'English'
    }
  ];

  // Notifications data
  const notifications = [
    {
      sender: 'Principal Anderson',
      subject: 'School Fundraiser',
      message: 'Our annual school fundraiser will be held on October 15th. We encourage all parents to participate and support our school programs.',
      time: '2 hours ago',
      read: false,
      avatar: '/placeholder.svg?height=40&width=40'
    },
    {
      sender: 'Mrs. Smith (Science)',
      subject: 'Science Fair Project',
      message: 'Emma has chosen an excellent topic for her science fair project. Please ensure she has the materials needed to complete it by next Friday.',
      time: 'Yesterday',
      read: false,
      avatar: '/placeholder.svg?height=40&width=40'
    },
    {
      sender: 'School Nurse',
      subject: 'Vaccination Reminder',
      message: 'This is a reminder that all students must have their updated vaccination records submitted by the end of the month.',
      time: '3 days ago',
      read: true,
      avatar: '/placeholder.svg?height=40&width=40'
    },
    {
      sender: 'Athletics Department',
      subject: 'Sports Team Tryouts',
      message: 'Tryouts for the basketball team will be held next Tuesday after school. All interested students should bring appropriate athletic wear.',
      time: '1 week ago',
      read: true,
      avatar: '/placeholder.svg?height=40&width=40'
    }
  ];

  // Helper functions
  const getGradeClass = (grade) => {
    if (grade.startsWith('A')) return 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400';
    if (grade.startsWith('B')) return 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400';
    if (grade.startsWith('C')) return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400';
    if (grade.startsWith('D')) return 'bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-400';
    return 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400';
  };

  const getStatusClass = (status) => {
    if (status === 'Present') return 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400';
    if (status === 'Late') return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400';
    return 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400';
  };

  const getReportTypeClass = (type) => {
    if (type === 'Positive') {
      return {
        badge: 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
        bg: 'bg-green-100 dark:bg-green-900/30',
        color: 'text-green-600 dark:text-green-400',
        icon: ThumbsUp
      };
    }
    return {
      badge: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400',
      bg: 'bg-yellow-100 dark:bg-yellow-900/30',
      color: 'text-yellow-600 dark:text-yellow-400',
      icon: AlertCircle
    };
  };
  </script>

  <style>
  :root {
    --color-primary-50: 239 246 255;
    --color-primary-100: 219 234 254;
    --color-primary-500: 59 130 246;
    --color-primary-600: 37 99 235;
    --color-primary-700: 29 78 216;
    --color-primary-900: 30 58 138;
  }

  .dark {
    --color-primary-50: 30 58 138;
    --color-primary-100: 30 58 138;
    --color-primary-400: 96 165 250;
    --color-primary-500: 59 130 246;
    --color-primary-600: 37 99 235;
    --color-primary-700: 29 78 216;
    --color-primary-900: 30 58 138;
  }

  .bg-primary-50 {
    background-color: rgb(var(--color-primary-50) / 1);
  }

  .bg-primary-100 {
    background-color: rgb(var(--color-primary-100) / 1);
  }

  .bg-primary-500 {
    background-color: rgb(var(--color-primary-500) / 1);
  }

  .bg-primary-900 {
    background-color: rgb(var(--color-primary-900) / 1);
  }

  .text-primary-300 {
    color: rgb(var(--color-primary-400) / 1);
  }

  .text-primary-400 {
    color: rgb(var(--color-primary-400) / 1);
  }

  .text-primary-600 {
    color: rgb(var(--color-primary-600) / 1);
  }

  .text-primary-700 {
    color: rgb(var(--color-primary-700) / 1);
  }
  </style>
