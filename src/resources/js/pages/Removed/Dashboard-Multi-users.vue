<template>
    <div class="h-screen flex flex-col" :class="{ 'dark': darkMode }">
      <div class="flex-1 flex overflow-hidden bg-background dark:bg-background">
        <!-- Sidebar -->
        <aside
          :class="[
            'w-64 border-r border-border dark:border-border transition-all duration-300 ease-in-out',
            isSidebarOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0 md:w-16'
          ]"
          class="bg-card dark:bg-card text-card-foreground dark:text-card-foreground flex flex-col h-full z-30"
        >
          <!-- Sidebar Header -->
          <div class="h-16 flex items-center px-4 border-b border-border dark:border-border">
            <div class="flex items-center gap-2 overflow-hidden">
              <div class="flex items-center justify-center w-8 h-8 rounded-md bg-primary text-primary-foreground">
                <GraduationCap v-if="!isSidebarOpen" class="h-5 w-5" />
                <GraduationCap v-else class="h-5 w-5" />
              </div>
              <h1 v-if="isSidebarOpen" class="text-lg font-semibold truncate">EduTrack</h1>
            </div>
          </div>

          <!-- Sidebar Content -->
          <div class="flex-1 overflow-y-auto py-2">
            <div class="px-3 py-2">
              <h2 v-if="isSidebarOpen" class="mb-2 px-2 text-xs font-semibold tracking-tight text-muted-foreground">
                {{ userRole.toUpperCase() }}
              </h2>
              <nav class="space-y-1">
                <template v-for="(item, index) in filteredNavigation" :key="index">
                  <button
                    @click="activeItem = item.name"
                    :class="[
                      'w-full flex items-center gap-3 rounded-md px-3 py-2 text-sm transition-colors',
                      activeItem === item.name
                        ? 'bg-accent text-accent-foreground'
                        : 'text-muted-foreground hover:bg-accent hover:text-accent-foreground'
                    ]"
                  >
                    <component :is="item.icon" class="h-4 w-4" />
                    <span v-if="isSidebarOpen" class="truncate">{{ item.name }}</span>
                  </button>
                </template>
              </nav>
            </div>
          </div>

          <!-- Sidebar Footer -->
          <div class="border-t border-border dark:border-border p-3">
            <button
              @click="toggleDarkMode"
              class="w-full flex items-center gap-3 rounded-md px-3 py-2 text-sm text-muted-foreground hover:bg-accent hover:text-accent-foreground transition-colors"
            >
              <Sun v-if="darkMode" class="h-4 w-4" />
              <Moon v-else class="h-4 w-4" />
              <span v-if="isSidebarOpen">{{ darkMode ? 'Light Mode' : 'Dark Mode' }}</span>
            </button>
          </div>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
          <!-- Top Navigation -->
          <header class="h-16 border-b border-border dark:border-border flex items-center justify-between px-4 bg-card dark:bg-card">
            <button
              @click="toggleSidebar"
              class="md:hidden rounded-md p-2 text-muted-foreground hover:bg-accent hover:text-accent-foreground"
            >
              <Menu class="h-5 w-5" />
            </button>
            <div class="md:hidden flex-1 text-center font-semibold">EduTrack</div>
            <div class="hidden md:block">
              <h1 class="text-xl font-semibold">{{ pageTitle }}</h1>
            </div>
            <div class="flex items-center gap-4">
              <button class="relative rounded-md p-2 text-muted-foreground hover:bg-accent hover:text-accent-foreground">
                <Bell class="h-5 w-5" />
                <span class="absolute top-1 right-1 w-2 h-2 bg-destructive rounded-full"></span>
              </button>
              <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-full bg-muted flex items-center justify-center overflow-hidden">
                  <User class="h-5 w-5 text-muted-foreground" />
                </div>
                <div class="hidden md:block">
                  <div class="text-sm font-medium">John Doe</div>
                  <div class="text-xs text-muted-foreground">{{ userRole }}</div>
                </div>
              </div>
            </div>
          </header>

          <!-- Page Content -->
          <main class="flex-1 overflow-y-auto p-4 bg-background dark:bg-background">
            <!-- Dashboard Content -->
            <div v-if="activeItem === 'Dashboard'">
              <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <div v-for="(card, index) in dashboardCards" :key="index"
                  class="bg-card dark:bg-card text-card-foreground dark:text-card-foreground rounded-lg border border-border dark:border-border p-6 shadow-sm">
                  <div class="flex items-center gap-4">
                    <div class="p-2 rounded-md bg-primary/10 text-primary">
                      <component :is="card.icon" class="h-5 w-5" />
                    </div>
                    <div>
                      <div class="text-sm font-medium text-muted-foreground">{{ card.title }}</div>
                      <div class="text-2xl font-bold">{{ card.value }}</div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Charts Section -->
              <div class="mt-8 grid gap-4 md:grid-cols-2">
                <div class="bg-card dark:bg-card text-card-foreground dark:text-card-foreground rounded-lg border border-border dark:border-border p-6 shadow-sm">
                  <h3 class="text-lg font-medium mb-4">{{ chartTitles[0] }}</h3>
                  <div class="h-64 flex items-center justify-center">
                    <div class="w-full h-full flex space-x-2">
                      <div v-for="(bar, i) in chartData" :key="i" class="flex-1 flex flex-col justify-end">
                        <div :style="`height: ${bar.value}%`" class="bg-primary rounded-t-md"></div>
                        <div class="text-xs text-center mt-2 text-muted-foreground">{{ bar.label }}</div>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="bg-card dark:bg-card text-card-foreground dark:text-card-foreground rounded-lg border border-border dark:border-border p-6 shadow-sm">
                  <h3 class="text-lg font-medium mb-4">{{ chartTitles[1] }}</h3>
                  <div class="h-64 flex items-center justify-center">
                    <div class="w-full h-full flex items-center justify-center">
                      <div class="relative w-40 h-40">
                        <!-- Simplified donut chart -->
                        <div class="absolute inset-0 rounded-full border-8 border-primary"></div>
                        <div class="absolute inset-0 rounded-full border-8 border-t-muted border-r-muted border-l-transparent border-b-transparent"></div>
                        <div class="absolute inset-0 flex items-center justify-center">
                          <span class="text-2xl font-bold">75%</span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Role-specific content -->
              <div v-if="userRole === 'admin'" class="mt-8">
                <div class="bg-card dark:bg-card text-card-foreground dark:text-card-foreground rounded-lg border border-border dark:border-border p-6 shadow-sm">
                  <h3 class="text-lg font-medium mb-4">User Management</h3>
                  <div class="flex items-center justify-between mb-4">
                    <div class="relative">
                      <Search class="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground" />
                      <input
                        type="search"
                        placeholder="Search users..."
                        class="pl-8 h-10 w-full md:w-[250px] rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                      />
                    </div>
                    <button class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4 py-2">
                      Add User
                    </button>
                  </div>
                  <div class="rounded-md border">
                    <table class="w-full caption-bottom text-sm">
                      <thead class="[&_tr]:border-b">
                        <tr class="border-b transition-colors hover:bg-muted/50 data-[state=selected]:bg-muted">
                          <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">Name</th>
                          <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">Role</th>
                          <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">Status</th>
                          <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">Actions</th>
                        </tr>
                      </thead>
                      <tbody class="[&_tr:last-child]:border-0">
                        <tr v-for="(user, i) in users" :key="i" class="border-b transition-colors hover:bg-muted/50 data-[state=selected]:bg-muted">
                          <td class="p-4 align-middle">{{ user.name }}</td>
                          <td class="p-4 align-middle">{{ user.role }}</td>
                          <td class="p-4 align-middle">
                            <span :class="[
                              'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold',
                              user.status === 'Active' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300' : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300'
                            ]">
                              {{ user.status }}
                            </span>
                          </td>
                          <td class="p-4 align-middle">
                            <div class="flex items-center gap-2">
                              <button class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-8 w-8 p-0">
                                <Pencil class="h-4 w-4" />
                              </button>
                              <button class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-8 w-8 p-0">
                                <Trash class="h-4 w-4" />
                              </button>
                            </div>
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>

              <div v-if="userRole === 'teacher'" class="mt-8">
                <div class="bg-card dark:bg-card text-card-foreground dark:text-card-foreground rounded-lg border border-border dark:border-border p-6 shadow-sm">
                  <h3 class="text-lg font-medium mb-4">Attendance Overview</h3>
                  <div class="rounded-md border">
                    <table class="w-full caption-bottom text-sm">
                      <thead class="[&_tr]:border-b">
                        <tr class="border-b transition-colors hover:bg-muted/50 data-[state=selected]:bg-muted">
                          <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">Student</th>
                          <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">Status</th>
                          <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">Date</th>
                          <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">Actions</th>
                        </tr>
                      </thead>
                      <tbody class="[&_tr:last-child]:border-0">
                        <tr v-for="(student, i) in students" :key="i" class="border-b transition-colors hover:bg-muted/50 data-[state=selected]:bg-muted">
                          <td class="p-4 align-middle">{{ student.name }}</td>
                          <td class="p-4 align-middle">
                            <select class="h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm shadow-sm transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring">
                              <option>Present</option>
                              <option>Absent</option>
                              <option>Late</option>
                            </select>
                          </td>
                          <td class="p-4 align-middle">{{ student.date }}</td>
                          <td class="p-4 align-middle">
                            <button class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-primary text-primary-foreground hover:bg-primary/90 h-8 px-3">
                              Save
                            </button>
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>

              <div v-if="userRole === 'student'" class="mt-8">
                <div class="bg-card dark:bg-card text-card-foreground dark:text-card-foreground rounded-lg border border-border dark:border-border p-6 shadow-sm">
                  <h3 class="text-lg font-medium mb-4">My Grades</h3>
                  <div class="rounded-md border">
                    <table class="w-full caption-bottom text-sm">
                      <thead class="[&_tr]:border-b">
                        <tr class="border-b transition-colors hover:bg-muted/50 data-[state=selected]:bg-muted">
                          <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">Subject</th>
                          <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">Grade</th>
                          <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">Teacher</th>
                          <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">Last Updated</th>
                        </tr>
                      </thead>
                      <tbody class="[&_tr:last-child]:border-0">
                        <tr v-for="(grade, i) in grades" :key="i" class="border-b transition-colors hover:bg-muted/50 data-[state=selected]:bg-muted">
                          <td class="p-4 align-middle">{{ grade.subject }}</td>
                          <td class="p-4 align-middle font-medium">{{ grade.grade }}</td>
                          <td class="p-4 align-middle">{{ grade.teacher }}</td>
                          <td class="p-4 align-middle text-muted-foreground">{{ grade.updated }}</td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>

              <div v-if="userRole === 'parent'" class="mt-8">
                <div class="bg-card dark:bg-card text-card-foreground dark:text-card-foreground rounded-lg border border-border dark:border-border p-6 shadow-sm">
                  <h3 class="text-lg font-medium mb-4">Child's Attendance</h3>
                  <div class="rounded-md border">
                    <table class="w-full caption-bottom text-sm">
                      <thead class="[&_tr]:border-b">
                        <tr class="border-b transition-colors hover:bg-muted/50 data-[state=selected]:bg-muted">
                          <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">Date</th>
                          <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">Status</th>
                          <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">Subject</th>
                          <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">Teacher</th>
                        </tr>
                      </thead>
                      <tbody class="[&_tr:last-child]:border-0">
                        <tr v-for="(record, i) in attendanceRecords" :key="i" class="border-b transition-colors hover:bg-muted/50 data-[state=selected]:bg-muted">
                          <td class="p-4 align-middle">{{ record.date }}</td>
                          <td class="p-4 align-middle">
                            <span :class="[
                              'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold',
                              record.status === 'Present' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300' :
                              record.status === 'Absent' ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300' :
                              'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300'
                            ]">
                              {{ record.status }}
                            </span>
                          </td>
                          <td class="p-4 align-middle">{{ record.subject }}</td>
                          <td class="p-4 align-middle">{{ record.teacher }}</td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            </div>

            <!-- Other content sections would go here based on activeItem -->
          </main>
        </div>
      </div>
    </div>
  </template>

  <script setup>
  import { ref, computed } from 'vue'
  import {
    GraduationCap,
    BarChart,
    Users,
    BookOpen,
    ClipboardCheck,
    Award,
    FileText,
    Bell,
    User,
    Menu,
    Sun,
    Moon,
    Search,
    Pencil,
    Trash
  } from 'lucide-vue-next'

  // State
  const darkMode = ref(false)
  const isSidebarOpen = ref(true)
  const userRole = ref('student') // Can be 'admin', 'teacher', 'student', or 'parent'
  const activeItem = ref('Dashboard')

  // Toggle functions
  const toggleDarkMode = () => {
    darkMode.value = !darkMode.value
  }

  const toggleSidebar = () => {
    isSidebarOpen.value = !isSidebarOpen.value
  }

  // Navigation items based on role
  const navigation = {
    admin: [
      { name: 'Dashboard', icon: BarChart },
      { name: 'Manage Users', icon: Users },
      { name: 'Manage Courses', icon: BookOpen },
      { name: 'Manage Sections', icon: ClipboardCheck }
    ],
    teacher: [
      { name: 'Dashboard', icon: BarChart },
      { name: 'Attendance', icon: ClipboardCheck },
      { name: 'Grades', icon: Award },
      { name: 'Behavior Reports', icon: FileText }
    ],
    student: [
      { name: 'Dashboard', icon: BarChart },
      { name: 'Attendance', icon: ClipboardCheck },
      { name: 'Grades', icon: Award },
      { name: 'Behavior Reports', icon: FileText },
      { name: 'Notifications', icon: Bell }
    ],
    parent: [
      { name: 'Dashboard', icon: BarChart },
      { name: 'Child\'s Grades', icon: Award },
      { name: 'Child\'s Attendance', icon: ClipboardCheck },
      { name: 'Behavior Reports', icon: FileText },
      { name: 'Notifications', icon: Bell }
    ]
  }

  // Computed properties
  const filteredNavigation = computed(() => {
    return navigation[userRole.value] || []
  })

  const pageTitle = computed(() => {
    return activeItem.value
  })

  // Sample data for dashboard cards
  const dashboardCards = computed(() => {
    if (userRole.value === 'admin') {
      return [
        { title: 'Total Students', value: '1,234', icon: Users },
        { title: 'Total Teachers', value: '56', icon: Users },
        { title: 'Total Courses', value: '32', icon: BookOpen },
        { title: 'Total Sections', value: '48', icon: ClipboardCheck }
      ]
    } else if (userRole.value === 'teacher') {
      return [
        { title: 'My Students', value: '87', icon: Users },
        { title: 'Attendance Rate', value: '92%', icon: ClipboardCheck },
        { title: 'Average Grade', value: 'B+', icon: Award },
        { title: 'Pending Reports', value: '5', icon: FileText }
      ]
    } else if (userRole.value === 'student') {
      return [
        { title: 'Attendance Rate', value: '95%', icon: ClipboardCheck },
        { title: 'Average Grade', value: 'A-', icon: Award },
        { title: 'Completed Courses', value: '8', icon: BookOpen },
        { title: 'New Notifications', value: '3', icon: Bell }
      ]
    } else if (userRole.value === 'parent') {
      return [
        { title: 'Child\'s Attendance', value: '95%', icon: ClipboardCheck },
        { title: 'Child\'s Average Grade', value: 'A-', icon: Award },
        { title: 'Behavior Reports', value: '2', icon: FileText },
        { title: 'New Notifications', value: '4', icon: Bell }
      ]
    }
    return []
  })

  // Chart titles based on role
  const chartTitles = computed(() => {
    if (userRole.value === 'admin') {
      return ['Students per Department', 'User Distribution']
    } else if (userRole.value === 'teacher') {
      return ['Class Attendance Trend', 'Grade Distribution']
    } else if (userRole.value === 'student') {
      return ['Attendance by Month', 'Grade Distribution']
    } else if (userRole.value === 'parent') {
      return ['Child\'s Attendance Trend', 'Child\'s Grade Distribution']
    }
    return ['Chart 1', 'Chart 2']
  })

  // Sample chart data
  const chartData = [
    { label: 'Jan', value: 65 },
    { label: 'Feb', value: 75 },
    { label: 'Mar', value: 85 },
    { label: 'Apr', value: 70 },
    { label: 'May', value: 90 },
    { label: 'Jun', value: 80 }
  ]

  // Sample users data for admin
  const users = [
    { name: 'Jane Smith', role: 'Teacher', status: 'Active' },
    { name: 'Mike Johnson', role: 'Student', status: 'Active' },
    { name: 'Sarah Williams', role: 'Parent', status: 'Inactive' },
    { name: 'Robert Brown', role: 'Admin', status: 'Active' }
  ]

  // Sample students data for teacher
  const students = [
    { name: 'Alex Johnson', status: 'Present', date: '2023-05-15' },
    { name: 'Emma Davis', status: 'Absent', date: '2023-05-15' },
    { name: 'Ryan Wilson', status: 'Late', date: '2023-05-15' },
    { name: 'Olivia Martin', status: 'Present', date: '2023-05-15' }
  ]

  // Sample grades data for student
  const grades = [
    { subject: 'Mathematics', grade: 'A', teacher: 'Dr. Smith', updated: '2023-05-10' },
    { subject: 'Science', grade: 'B+', teacher: 'Mrs. Johnson', updated: '2023-05-12' },
    { subject: 'History', grade: 'A-', teacher: 'Mr. Williams', updated: '2023-05-08' },
    { subject: 'English', grade: 'B', teacher: 'Ms. Davis', updated: '2023-05-14' }
  ]

  // Sample attendance records for parent
  const attendanceRecords = [
    { date: '2023-05-15', status: 'Present', subject: 'Mathematics', teacher: 'Dr. Smith' },
    { date: '2023-05-14', status: 'Present', subject: 'Science', teacher: 'Mrs. Johnson' },
    { date: '2023-05-13', status: 'Absent', subject: 'History', teacher: 'Mr. Williams' },
    { date: '2023-05-12', status: 'Late', subject: 'English', teacher: 'Ms. Davis' }
  ]
  </script>

  <style>
  @tailwind base;
  @tailwind components;
  @tailwind utilities;

  :root {
    --background: 0 0% 100%;
    --foreground: 222.2 84% 4.9%;
    --card: 0 0% 100%;
    --card-foreground: 222.2 84% 4.9%;
    --popover: 0 0% 100%;
    --popover-foreground: 222.2 84% 4.9%;
    --primary: 221.2 83.2% 53.3%;
    --primary-foreground: 210 40% 98%;
    --secondary: 210 40% 96.1%;
    --secondary-foreground: 222.2 47.4% 11.2%;
    --muted: 210 40% 96.1%;
    --muted-foreground: 215.4 16.3% 46.9%;
    --accent: 210 40% 96.1%;
    --accent-foreground: 222.2 47.4% 11.2%;
    --destructive: 0 84.2% 60.2%;
    --destructive-foreground: 210 40% 98%;
    --border: 214.3 31.8% 91.4%;
    --input: 214.3 31.8% 91.4%;
    --ring: 221.2 83.2% 53.3%;
    --radius: 0.5rem;
  }

  .dark {
    --background: 222.2 84% 4.9%;
    --foreground: 210 40% 98%;
    --card: 222.2 84% 4.9%;
    --card-foreground: 210 40% 98%;
    --popover: 222.2 84% 4.9%;
    --popover-foreground: 210 40% 98%;
    --primary: 217.2 91.2% 59.8%;
    --primary-foreground: 210 40% 98%;
    --secondary: 217.2 32.6% 17.5%;
    --secondary-foreground: 210 40% 98%;
    --muted: 217.2 32.6% 17.5%;
    --muted-foreground: 215 20.2% 65.1%;
    --accent: 217.2 32.6% 17.5%;
    --accent-foreground: 210 40% 98%;
    --destructive: 0 62.8% 30.6%;
    --destructive-foreground: 210 40% 98%;
    --border: 217.2 32.6% 17.5%;
    --input: 217.2 32.6% 17.5%;
    --ring: 224.3 76.3% 48%;
  }

  html, body {
    height: 100%;
    margin: 0;
    padding: 0;
  }

  * {
    box-sizing: border-box;
  }
  </style>
