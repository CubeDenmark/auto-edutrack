<script setup lang="ts">
import { ref, computed, onMounted, watch, inject } from 'vue';
import AppLayout from "@/layouts/AppLayout.vue";
import { type BreadcrumbItem } from "@/types";
import { Head, usePage, Link } from "@inertiajs/vue3";

// Breadcrumbs
const breadcrumbs: BreadcrumbItem[] = [
  {
    title: "Dashboard",
    href: "/dashboard",
  },
];

// Get the page props to access any data passed from the server
const page = usePage();

// Inject the current item ID from the sidebar
const currentItemId = inject('currentItemId', ref(null));

// State variables
const currentDate = ref(new Date().toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }));
const currentSection = ref<any>(null);

// Student information - in a real app, this would come from authentication
const currentStudent = ref({
  id: '2023001',
  name: 'Emma Thompson',
  email: 'emma.t@school.edu',
  grade: '10th Grade',
  gpa: 3.7,
  behaviorStatus: 'Good',
  behaviorReports: 1,
  attendanceRate: 95,
  presentDays: 38,
  totalDays: 40
});

// Class sections data - same as in the attendance view
const classSections = ref([
  {
    id: 'sci101a',
    name: 'Science 101 - Section A',
    icon: '🧪',
    term: 'Fall Semester 2023',
    teacher: 'Dr. Johnson',
    room: 'SCI-201',
    grade: 'A-',
    gradeValue: 92,
    attendance: 96,
    pendingAssignments: 2,
    schedule: [
      { day: 'Monday', time: '9:00 AM - 10:30 AM', room: 'SCI-201', teacher: 'Dr. Johnson' },
      { day: 'Wednesday', time: '9:00 AM - 10:30 AM', room: 'SCI-201', teacher: 'Dr. Johnson' },
      { day: 'Friday', time: '9:00 AM - 10:30 AM', room: 'SCI-201', teacher: 'Dr. Johnson' }
    ],
    assignments: [
      {
        icon: '📝',
        iconBg: 'bg-blue-100 dark:bg-blue-900',
        iconColor: 'text-blue-500',
        title: 'Lab Report: Chemical Reactions',
        description: 'Complete the lab report for the chemical reactions experiment.',
        dueDate: 'Oct 25, 2023',
        status: 'In Progress',
        statusClass: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300'
      },
      {
        icon: '📚',
        iconBg: 'bg-green-100 dark:bg-green-900',
        iconColor: 'text-green-500',
        title: 'Chapter 5 Reading',
        description: 'Read Chapter 5 and answer the review questions.',
        dueDate: 'Oct 20, 2023',
        status: 'Not Started',
        statusClass: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300'
      }
    ],
    attendanceSummary: {
      present: 26,
      absent: 1,
      late: 1,
      excused: 0
    }
  },
  {
    id: 'sci101b',
    name: 'Science 101 - Section B',
    icon: '🧪',
    term: 'Fall Semester 2023',
    teacher: 'Dr. Smith',
    room: 'SCI-202',
    grade: 'B+',
    gradeValue: 88,
    attendance: 90,
    pendingAssignments: 1,
    schedule: [
      { day: 'Tuesday', time: '11:00 AM - 12:30 PM', room: 'SCI-202', teacher: 'Dr. Smith' },
      { day: 'Thursday', time: '11:00 AM - 12:30 PM', room: 'SCI-202', teacher: 'Dr. Smith' }
    ],
    assignments: [
      {
        icon: '🔬',
        iconBg: 'bg-purple-100 dark:bg-purple-900',
        iconColor: 'text-purple-500',
        title: 'Research Project',
        description: 'Complete your research project on renewable energy.',
        dueDate: 'Nov 5, 2023',
        status: 'In Progress',
        statusClass: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300'
      }
    ],
    attendanceSummary: {
      present: 23,
      absent: 1,
      late: 1,
      excused: 0
    }
  },
  {
    id: 'bio202',
    name: 'Biology 202',
    icon: '🧬',
    term: 'Fall Semester 2023',
    teacher: 'Prof. Williams',
    room: 'BIO-101',
    grade: 'A',
    gradeValue: 95,
    attendance: 98,
    pendingAssignments: 3,
    schedule: [
      { day: 'Monday', time: '1:00 PM - 2:30 PM', room: 'BIO-101', teacher: 'Prof. Williams' },
      { day: 'Wednesday', time: '1:00 PM - 2:30 PM', room: 'BIO-101', teacher: 'Prof. Williams' }
    ],
    assignments: [
      {
        icon: '🔬',
        iconBg: 'bg-blue-100 dark:bg-blue-900',
        iconColor: 'text-blue-500',
        title: 'Cell Structure Analysis',
        description: 'Complete the cell structure analysis worksheet.',
        dueDate: 'Oct 22, 2023',
        status: 'In Progress',
        statusClass: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300'
      },
      {
        icon: '📊',
        iconBg: 'bg-green-100 dark:bg-green-900',
        iconColor: 'text-green-500',
        title: 'Ecosystem Project',
        description: 'Prepare your presentation on local ecosystems.',
        dueDate: 'Nov 10, 2023',
        status: 'Not Started',
        statusClass: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300'
      },
      {
        icon: '📝',
        iconBg: 'bg-purple-100 dark:bg-purple-900',
        iconColor: 'text-purple-500',
        title: 'Genetics Quiz',
        description: 'Study for the upcoming genetics quiz.',
        dueDate: 'Oct 27, 2023',
        status: 'Not Started',
        statusClass: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300'
      }
    ],
    attendanceSummary: {
      present: 20,
      absent: 1,
      late: 0,
      excused: 1
    }
  },
  {
    id: 'chem101',
    name: 'Chemistry 101',
    icon: '⚗️',
    term: 'Fall Semester 2023',
    teacher: 'Dr. Brown',
    room: 'CHEM-305',
    grade: 'B',
    gradeValue: 85,
    attendance: 92,
    pendingAssignments: 2,
    schedule: [
      { day: 'Tuesday', time: '2:00 PM - 3:30 PM', room: 'CHEM-305', teacher: 'Dr. Brown' },
      { day: 'Thursday', time: '2:00 PM - 3:30 PM', room: 'CHEM-305', teacher: 'Dr. Brown' }
    ],
    assignments: [
      {
        icon: '🧪',
        iconBg: 'bg-yellow-100 dark:bg-yellow-900',
        iconColor: 'text-yellow-500',
        title: 'Chemical Equations',
        description: 'Complete the chemical equations worksheet.',
        dueDate: 'Oct 23, 2023',
        status: 'In Progress',
        statusClass: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300'
      },
      {
        icon: '📚',
        iconBg: 'bg-blue-100 dark:bg-blue-900',
        iconColor: 'text-blue-500',
        title: 'Periodic Table Quiz',
        description: 'Study for the periodic table quiz.',
        dueDate: 'Oct 30, 2023',
        status: 'Not Started',
        statusClass: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300'
      }
    ],
    attendanceSummary: {
      present: 22,
      absent: 1,
      late: 1,
      excused: 0
    }
  }
]);

// Student's enrolled classes
const enrolledClasses = computed(() => {
  // In a real app, this would be filtered based on the student's enrollment
  return classSections.value;
});

// Academic performance data
const academicPerformance = computed(() => {
  return enrolledClasses.value.map(cls => ({
    subject: cls.name,
    percentage: cls.gradeValue,
    color: getGradeColor(cls.gradeValue)
  }));
});

// Recent activities data
const recentActivities = [
  {
    icon: '📝',
    iconBg: 'bg-blue-100 dark:bg-blue-900',
    iconColor: 'text-blue-500',
    title: 'Math Quiz Graded',
    description: 'You received an A on your recent Math quiz.',
    time: '2h ago'
  },
  {
    icon: '📚',
    iconBg: 'bg-green-100 dark:bg-green-900',
    iconColor: 'text-green-500',
    title: 'New Assignment',
    description: 'Science project due in 2 weeks.',
    time: '5h ago'
  },
  {
    icon: '🏆',
    iconBg: 'bg-yellow-100 dark:bg-yellow-900',
    iconColor: 'text-yellow-500',
    title: 'Academic Achievement',
    description: 'You\'ve been nominated for the Student of the Month award.',
    time: '1d ago'
  },
  {
    icon: '📢',
    iconBg: 'bg-purple-100 dark:bg-purple-900',
    iconColor: 'text-purple-500',
    title: 'School Announcement',
    description: 'Parent-Teacher conferences scheduled for next week.',
    time: '2d ago'
  },
];

// Helper function for grade styling
const getGradeClass = (score: number) => {
  if (score >= 90) return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300';
  if (score >= 80) return 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300';
  if (score >= 70) return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300';
  if (score >= 60) return 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-300';
  return 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300';
};

// Helper function for grade colors
const getGradeColor = (score: number) => {
  if (score >= 90) return 'bg-green-600';
  if (score >= 80) return 'bg-blue-600';
  if (score >= 70) return 'bg-yellow-600';
  if (score >= 60) return 'bg-orange-600';
  return 'bg-red-600';
};

// Methods
function findSectionById(id) {
  return classSections.value.find(section => section.id === id) || null;
}

// Listen for item changes from the sidebar
const handleItemChange = (event) => {
  if (event.detail && event.detail.source === 'sidebar') {
    const itemId = event.detail.itemId;
    const itemType = event.detail.itemType;

    // For students, the itemType will be 'section' or undefined
    if (itemType === 'section' || !itemType) {
      const section = findSectionById(itemId);
      if (section && (!currentSection.value || currentSection.value.id !== section.id)) {
        // Only update if it's a different section to avoid infinite loops
        currentSection.value = section;
      }
    }
  }
};

// Watch for changes in the injected item ID
watch(currentItemId, (newItemId) => {
  if (newItemId) {
    const section = findSectionById(newItemId);
    if (section && (!currentSection.value || currentSection.value.id !== section.id)) {
      // Only update if it's a different section
      currentSection.value = section;
    }
  }
}, { immediate: true });

// Watch for changes in the URL query parameters
watch(() => window.location.search, (newSearch) => {
  const params = new URLSearchParams(newSearch);
  const sectionId = params.get('sectionId');
  const itemId = params.get('itemId');

  // First check for sectionId (legacy parameter)
  if (sectionId) {
    const section = findSectionById(sectionId);
    if (section && (!currentSection.value || currentSection.value.id !== section.id)) {
      // Only update if it's a different section
      currentSection.value = section;
    }
  }
  // Then check for itemId (new parameter from sidebar)
  else if (itemId) {
    const section = findSectionById(itemId);
    if (section && (!currentSection.value || currentSection.value.id !== section.id)) {
      // Only update if it's a different section
      currentSection.value = section;
    }
  }
}, { immediate: true });

onMounted(() => {
  // Add event listener for item changes from sidebar
  window.addEventListener('item-changed', handleItemChange);

  // For backward compatibility, also listen for section-changed events
  window.addEventListener('section-changed', (event) => {
    if (event.detail && event.detail.sectionId) {
      const section = findSectionById(event.detail.sectionId);
      if (section && (!currentSection.value || currentSection.value.id !== section.id)) {
        // Only update if it's a different section
        currentSection.value = section;
      }
    }
  });

  // Check if there's a section ID in the URL or page props
  const urlParams = new URLSearchParams(window.location.search);
  const sectionIdFromUrl = urlParams.get('sectionId');
  const itemIdFromUrl = urlParams.get('itemId');
  const sectionIdFromProps = page.props.sectionId;

  // First check for sectionId from URL or props
  if (sectionIdFromUrl || sectionIdFromProps) {
    const sectionId = sectionIdFromProps || sectionIdFromUrl;
    const section = findSectionById(sectionId);
    if (section) {
      currentSection.value = section;
      return;
    }
  }
  // Then check for itemId (which could be a section ID from sidebar)
  else if (itemIdFromUrl) {
    const section = findSectionById(itemIdFromUrl);
    if (section) {
      currentSection.value = section;
      return;
    }
  }
  // Finally check for injected item ID
  else if (currentItemId.value) {
    const section = findSectionById(currentItemId.value);
    if (section) {
      currentSection.value = section;
      return;
    }
  }

  // Clean up event listeners on component unmount
  return () => {
    window.removeEventListener('item-changed', handleItemChange);
    window.removeEventListener('section-changed', handleSectionChange);
  };
});
</script>

<template>
  <Head title="Student Dashboard" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-6 pt-20">
      <!-- Class-specific dashboard content -->
      <div v-if="currentSection" class="mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-6">
          <h3 class="font-medium mb-4">{{ currentSection.name }} Overview</h3>
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-white dark:bg-gray-700 border dark:border-gray-600 rounded-lg p-4 shadow-sm">
              <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-full flex items-center justify-center bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-400">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                </div>
                <div>
                  <p class="text-sm text-gray-500 dark:text-gray-400">Current Grade</p>
                  <div class="flex items-center">
                    <p class="text-xl font-bold">{{ currentSection.grade }}</p>
                    <span :class="`ml-2 px-2 py-0.5 rounded-full text-xs font-medium ${getGradeClass(currentSection.gradeValue)}`">
                      {{ currentSection.gradeValue }}%
                    </span>
                  </div>
                </div>
              </div>
            </div>
            <div class="bg-white dark:bg-gray-700 border dark:border-gray-600 rounded-lg p-4 shadow-sm">
              <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-full flex items-center justify-center bg-green-100 dark:bg-green-900 text-green-600 dark:text-green-400">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                </div>
                <div>
                  <p class="text-sm text-gray-500 dark:text-gray-400">Attendance</p>
                  <p class="text-xl font-bold">{{ currentSection.attendance }}%</p>
                </div>
              </div>
            </div>
            <div class="bg-white dark:bg-gray-700 border dark:border-gray-600 rounded-lg p-4 shadow-sm">
              <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-full flex items-center justify-center bg-purple-100 dark:bg-purple-900 text-purple-600 dark:text-purple-400">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                  </svg>
                </div>
                <div>
                  <p class="text-sm text-gray-500 dark:text-gray-400">Pending Assignments</p>
                  <p class="text-xl font-bold">{{ currentSection.pendingAssignments }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Class Schedule -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-6">
          <h3 class="font-medium mb-4">Class Schedule</h3>
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
              <thead class="bg-gray-50 dark:bg-gray-700">
                <tr>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Day</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Time</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Room</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Teacher</th>
                </tr>
              </thead>
              <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                <tr v-for="(schedule, index) in currentSection.schedule" :key="index">
                  <td class="px-6 py-4 whitespace-nowrap text-sm">{{ schedule.day }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm">{{ schedule.time }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm">{{ schedule.room }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm">{{ schedule.teacher }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Upcoming Assignments -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
          <div class="flex justify-between items-center mb-4">
            <h3 class="font-medium">Upcoming Assignments</h3>
            <Link
              href="/my-attendance"
              class="text-primary-600 hover:text-primary-800 dark:text-primary-400 dark:hover:text-primary-300 text-sm"
            >
              View Attendance
            </Link>
          </div>
          <div class="space-y-4">
            <div v-for="(assignment, index) in currentSection.assignments" :key="index" class="flex items-start space-x-4 pb-4 border-b dark:border-gray-700 last:border-0 last:pb-0">
              <div :class="`w-10 h-10 rounded-full flex items-center justify-center ${assignment.iconBg} ${assignment.iconColor}`">
                <span class="text-lg">{{ assignment.icon }}</span>
              </div>
              <div class="flex-1">
                <div class="flex items-center justify-between">
                  <p class="font-medium">{{ assignment.title }}</p>
                  <span :class="`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${assignment.statusClass}`">
                    {{ assignment.status }}
                  </span>
                </div>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ assignment.description }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Due: {{ assignment.dueDate }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- General dashboard content (when no class is selected) -->
      <div v-else>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
          <!-- Attendance Card -->
          <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex items-center justify-between mb-4">
              <h3 class="font-medium">Attendance</h3>
              <span class="text-green-500 bg-green-100 dark:bg-green-900 px-2 py-1 rounded-full text-xs font-medium">{{ currentStudent.attendanceRate }}%</span>
            </div>
            <div class="flex items-center space-x-4">
              <div class="w-16 h-16 rounded-full flex items-center justify-center bg-green-100 dark:bg-green-900 text-green-500">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
              <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Present Days</p>
                <p class="text-2xl font-bold">{{ currentStudent.presentDays }}/{{ currentStudent.totalDays }}</p>
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
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998a12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222" />
                </svg>
              </div>
              <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Current GPA</p>
                <p class="text-2xl font-bold">{{ currentStudent.gpa }}</p>
              </div>
            </div>
          </div>

          <!-- Behavior Card -->
          <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex items-center justify-between mb-4">
              <h3 class="font-medium">Behavior</h3>
              <span class="text-purple-500 bg-purple-100 dark:bg-purple-900 px-2 py-1 rounded-full text-xs font-medium">{{ currentStudent.behaviorStatus }}</span>
            </div>
            <div class="flex items-center space-x-4">
              <div class="w-16 h-16 rounded-full flex items-center justify-center bg-purple-100 dark:bg-purple-900 text-purple-500">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
              <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Reports</p>
                <p class="text-2xl font-bold">{{ currentStudent.behaviorReports }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Performance Chart -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-6">
          <h3 class="font-medium mb-4">Academic Performance</h3>
          <div class="h-64 w-full">
            <div class="h-full w-full flex items-center justify-center bg-gray-100 dark:bg-gray-700 rounded-lg">
              <div class="space-y-4 w-full px-8">
                <div v-for="(subject, index) in academicPerformance" :key="index">
                  <div class="flex justify-between mb-1">
                    <span class="text-sm font-medium">{{ subject.subject }}</span>
                    <span class="text-sm font-medium">{{ subject.percentage }}%</span>
                  </div>
                  <div class="w-full bg-gray-200 dark:bg-gray-600 rounded-full h-2.5">
                    <div :class="subject.color" class="h-2.5 rounded-full" :style="`width: ${subject.percentage}%`"></div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- My Classes -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-6">
          <div class="flex justify-between items-center mb-4">
            <h3 class="font-medium">My Classes</h3>
            <Link
              href="/my-attendance"
              class="text-primary-600 hover:text-primary-800 dark:text-primary-400 dark:hover:text-primary-300 text-sm"
            >
              View All Attendance
            </Link>
          </div>
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div
              v-for="(cls, index) in enrolledClasses"
              :key="index"
              class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
              @click="currentSection = cls"
            >
              <div class="flex items-center mb-4">
                <div class="w-10 h-10 rounded-full bg-primary-100 dark:bg-primary-900 flex items-center justify-center text-primary-600 dark:text-primary-400 mr-3">
                  <span class="text-lg">{{ cls.icon }}</span>
                </div>
                <div>
                  <h4 class="font-medium">{{ cls.name }}</h4>
                  <p class="text-xs text-gray-500 dark:text-gray-400">{{ cls.teacher }}</p>
                </div>
              </div>
              <div class="flex justify-between items-center text-sm mb-2">
                <span class="text-gray-500 dark:text-gray-400">Grade:</span>
                <span :class="`px-2 py-0.5 rounded-full text-xs font-medium ${getGradeClass(cls.gradeValue)}`">
                  {{ cls.grade }} ({{ cls.gradeValue }}%)
                </span>
              </div>
              <div class="flex justify-between items-center text-sm mb-2">
                <span class="text-gray-500 dark:text-gray-400">Attendance:</span>
                <span class="font-medium">{{ cls.attendance }}%</span>
              </div>
              <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2 mb-4">
                <div class="bg-primary-600 h-2 rounded-full" :style="`width: ${cls.attendance}%`"></div>
              </div>
            </div>
          </div>
        </div>

        <!-- Recent Activity -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
          <h3 class="font-medium mb-4">Recent Activity</h3>
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
    </div>
  </AppLayout>
</template>

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
