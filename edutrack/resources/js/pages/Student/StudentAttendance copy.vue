<script setup lang="ts">
import { ref, computed, onMounted, watch, inject } from 'vue';
import AppLayout from "@/layouts/AppLayout.vue";
import { type BreadcrumbItem } from "@/types";
import { Head, usePage, Link } from "@inertiajs/vue3";

// Breadcrumbs for the attendance page
const breadcrumbs: BreadcrumbItem[] = [
  {
    title: "Dashboard",
    href: "/dashboard",
  },
  {
    title: "My Attendance",
    href: "/my-attendance",
  },
];

// Get the page props to access any data passed from the server
const page = usePage();

defineProps<{
  name?: string;
  sectionId?: string; // Add prop to receive section ID from URL or parent
}>();

// Inject the current item ID from the sidebar
const currentItemId = inject('currentItemId', ref(null));

// State variables
const currentDate = ref(new Date().toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }));
const showSectionDropdown = ref(false);
const currentSection = ref<any>(null);

// Student information - in a real app, this would come from authentication
const currentStudent = ref({
  id: '2023001',
  name: 'Emma Thompson',
  email: 'emma.t@school.edu'
});

// Class sections data - same as in the teacher view
const classSections = ref([
  {
    id: 'sci101a',
    name: 'Science 101 - Section A',
    icon: '🧪',
    term: 'Fall Semester 2023',
    alerts: 2,
    studentCount: 28,
    presentCount: 26,
    attendanceSummary: {
      present: 26,
      absent: 1,
      late: 1,
      excused: 0
    },
    // Other properties...
  },
  {
    id: 'sci101b',
    name: 'Science 101 - Section B',
    icon: '🧪',
    term: 'Fall Semester 2023',
    alerts: 0,
    studentCount: 25,
    presentCount: 23,
    attendanceSummary: {
      present: 23,
      absent: 1,
      late: 1,
      excused: 0
    },
    // Other properties...
  },
  {
    id: 'bio202',
    name: 'Biology 202',
    icon: '🧬',
    term: 'Fall Semester 2023',
    alerts: 1,
    studentCount: 22,
    presentCount: 20,
    attendanceSummary: {
      present: 20,
      absent: 1,
      late: 0,
      excused: 1
    },
    // Other properties...
  },
  {
    id: 'chem101',
    name: 'Chemistry 101',
    icon: '⚗️',
    term: 'Fall Semester 2023',
    alerts: 0,
    studentCount: 24,
    presentCount: 22,
    attendanceSummary: {
      present: 22,
      absent: 1,
      late: 1,
      excused: 0
    },
    // Other properties...
  }
]);

// Generate unique attendance records for each class section
const generateAttendanceRecords = (classSection) => {
  const records = [];
  const today = new Date();

  // Different patterns for different classes to make it visibly dynamic
  let pattern;
  switch(classSection.id) {
    case 'sci101a':
      pattern = [
        { status: 'Present', notes: '' },
        { status: 'Present', notes: '' },
        { status: 'Late', notes: 'Bus delay - 10 minutes late' },
        { status: 'Present', notes: '' },
        { status: 'Present', notes: '' },
        { status: 'Absent', notes: 'Medical appointment' },
        { status: 'Present', notes: '' },
        { status: 'Present', notes: '' },
        { status: 'Present', notes: '' },
        { status: 'Present', notes: '' }
      ];
      break;
    case 'sci101b':
      pattern = [
        { status: 'Present', notes: '' },
        { status: 'Absent', notes: 'Family emergency' },
        { status: 'Present', notes: '' },
        { status: 'Present', notes: '' },
        { status: 'Present', notes: '' },
        { status: 'Present', notes: '' },
        { status: 'Late', notes: 'Traffic delay - 5 minutes late' },
        { status: 'Present', notes: '' },
        { status: 'Present', notes: '' },
        { status: 'Present', notes: '' }
      ];
      break;
    case 'bio202':
      pattern = [
        { status: 'Present', notes: '' },
        { status: 'Present', notes: '' },
        { status: 'Present', notes: '' },
        { status: 'Excused', notes: 'School event' },
        { status: 'Present', notes: '' },
        { status: 'Present', notes: '' },
        { status: 'Present', notes: '' },
        { status: 'Late', notes: 'Car trouble - 15 minutes late' },
        { status: 'Present', notes: '' },
        { status: 'Present', notes: '' }
      ];
      break;
    case 'chem101':
      pattern = [
        { status: 'Late', notes: 'Alarm didn\'t go off - 20 minutes late' },
        { status: 'Present', notes: '' },
        { status: 'Present', notes: '' },
        { status: 'Present', notes: '' },
        { status: 'Absent', notes: 'Sick - flu' },
        { status: 'Present', notes: '' },
        { status: 'Present', notes: '' },
        { status: 'Present', notes: '' },
        { status: 'Present', notes: '' },
        { status: 'Present', notes: '' }
      ];
      break;
    default:
      pattern = Array(10).fill({ status: 'Present', notes: '' });
  }

  // Generate 10 days of attendance records
  for (let i = 0; i < 10; i++) {
    const date = new Date(today);
    date.setDate(date.getDate() - i);

    // Format date as "MMM DD, YYYY"
    const formattedDate = date.toLocaleDateString('en-US', {
      month: 'short',
      day: 'numeric',
      year: 'numeric'
    });

    // Use the pattern for this class
    const { status, notes } = pattern[i];

    // Determine status class based on status
    let statusClass = '';
    switch (status) {
      case 'Present':
        statusClass = 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300';
        break;
      case 'Absent':
        statusClass = 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300';
        break;
      case 'Late':
        statusClass = 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300';
        break;
      case 'Excused':
        statusClass = 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300';
        break;
    }

    records.push({
      date: formattedDate,
      status,
      statusClass,
      class: classSection.name,
      notes
    });
  }

  return records;
};

// Add attendance records to each class section
classSections.value.forEach(section => {
  section.attendanceRecords = generateAttendanceRecords(section);
});

// Student's enrolled classes
const enrolledClasses = computed(() => {
  // In a real app, this would be filtered based on the student's enrollment
  return classSections.value;
});

// All attendance records combined (for when no class is selected)
const allAttendanceRecords = computed(() => {
  const records = [];
  enrolledClasses.value.forEach(cls => {
    cls.attendanceRecords.forEach(record => {
      records.push({
        ...record,
        class: cls.name,
        classId: cls.id
      });
    });
  });

  // Sort by date (most recent first)
  return records.sort((a, b) => {
    const dateA = new Date(a.date);
    const dateB = new Date(b.date);
    return dateB - dateA;
  });
});

// Calculate attendance summary for the current section
const sectionSummary = computed(() => {
  if (!currentSection.value) return null;

  const records = currentSection.value.attendanceRecords;
  let present = 0, absent = 0, late = 0, excused = 0;

  records.forEach(record => {
    if (record.status === 'Present') present++;
    else if (record.status === 'Absent') absent++;
    else if (record.status === 'Late') late++;
    else if (record.status === 'Excused') excused++;
  });

  const total = records.length;
  return {
    present,
    absent,
    late,
    excused,
    total,
    presentPercentage: total > 0 ? Math.round((present / total) * 100) : 0
  };
});

// Methods
function selectSection(section) {
  // Prevent re-selecting the same section to avoid infinite loops
  if (currentSection.value && currentSection.value.id === section.id) {
    return;
  }

  currentSection.value = section;

  // Update URL with the selected section ID without navigating
  const url = new URL(window.location.href);
  url.searchParams.set('sectionId', section.id);
  window.history.replaceState({}, '', url.toString());
}

// Find section by ID
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

// For backward compatibility, also listen for section-changed events
const handleSectionChange = (event) => {
  const sectionId = event.detail.sectionId;
  if (sectionId) {
    const section = findSectionById(sectionId);
    if (section && (!currentSection.value || currentSection.value.id !== section.id)) {
      currentSection.value = section;
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

// Lifecycle hooks
onMounted(() => {
  // Add event listener for item changes from sidebar
  window.addEventListener('item-changed', handleItemChange);

  // For backward compatibility, also listen for section-changed events
  window.addEventListener('section-changed', handleSectionChange);

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
  <Head title="My Attendance" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-6 pt-20">
      <!-- No Section Selected Message -->
      <div v-if="!currentSection" class="flex flex-col items-center justify-center h-[calc(100vh-10rem)] text-center">
        <div class="bg-gray-100 dark:bg-gray-800 p-6 rounded-full mb-4">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z" />
          </svg>
        </div>
        <h2 class="text-2xl font-bold mb-2">Select a Class Section</h2>
        <p class="text-gray-500 dark:text-gray-400 mb-6 max-w-md">Please select a class section from the sidebar or use the dropdown menu to view class-specific attendance information.</p>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
          <button
            v-for="section in classSections.slice(0, 4)"
            :key="section.id"
            @click="selectSection(section)"
            class="flex flex-col items-center p-4 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700"
          >
            <span class="text-3xl mb-2">{{ section.icon }}</span>
            <span class="text-sm font-medium">{{ section.name }}</span>
          </button>
        </div>
      </div>

      <!-- Class Selection -->
      <div v-if="currentSection" class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-6">
        <div class="flex justify-between items-center mb-4">
          <div>
            <h2 class="text-xl font-bold">My Attendance Records</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ currentDate }}</p>
          </div>
          <div class="relative">
            <button
              @click="showSectionDropdown = !showSectionDropdown"
              class="flex items-center space-x-1 px-3 py-1.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md text-sm"
            >
              <span>{{ currentSection ? currentSection.name : 'Select Class' }}</span>
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
              </svg>
            </button>
            <div
              v-if="showSectionDropdown"
              class="absolute right-0 mt-1 w-56 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-md shadow-lg z-10"
            >
              <div class="p-2">
                <div class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-2 px-2">MY CLASSES</div>
                <button
                  v-for="section in enrolledClasses"
                  :key="section.id"
                  @click="selectSection(section); showSectionDropdown = false"
                  class="w-full flex items-center space-x-2 p-2 rounded-md text-left text-sm hover:bg-gray-100 dark:hover:bg-gray-700"
                  :class="{'bg-gray-100 dark:bg-gray-700': currentSection && currentSection.id === section.id}"
                >
                  <span class="text-lg">{{ section.icon }}</span>
                  <span>{{ section.name }}</span>
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Class-specific attendance summary -->
        <div v-if="currentSection && sectionSummary" class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
          <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Present</p>
                <p class="text-2xl font-bold">{{ sectionSummary.present }}</p>
              </div>
              <div class="w-10 h-10 rounded-full bg-green-100 dark:bg-green-900 flex items-center justify-center text-green-600 dark:text-green-400">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
              </div>
            </div>
          </div>
          <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Absent</p>
                <p class="text-2xl font-bold">{{ sectionSummary.absent }}</p>
              </div>
              <div class="w-10 h-10 rounded-full bg-red-100 dark:bg-red-900 flex items-center justify-center text-red-600 dark:text-red-400">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </div>
            </div>
          </div>
          <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Late</p>
                <p class="text-2xl font-bold">{{ sectionSummary.late }}</p>
              </div>
              <div class="w-10 h-10 rounded-full bg-yellow-100 dark:bg-yellow-900 flex items-center justify-center text-yellow-600 dark:text-yellow-400">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
            </div>
          </div>
          <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Excused</p>
                <p class="text-2xl font-bold">{{ sectionSummary.excused }}</p>
              </div>
              <div class="w-10 h-10 rounded-full bg-blue-100 dark:bg-blue-900 flex items-center justify-center text-blue-600 dark:text-blue-400">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
            </div>
          </div>
        </div>

        <!-- Attendance percentage -->
        <div v-if="currentSection && sectionSummary" class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
          <div class="flex items-center">
            <div class="w-12 h-12 rounded-full bg-primary-100 dark:bg-primary-900 flex items-center justify-center text-primary-600 dark:text-primary-400 mr-4">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
              </svg>
            </div>
            <div>
              <p class="text-sm text-gray-500 dark:text-gray-400">Attendance Rate</p>
              <p class="text-xl font-bold">{{ sectionSummary.presentPercentage }}% Present</p>
            </div>
          </div>
          <div>
            <Link href="/dashboard" class="text-primary-600 hover:text-primary-800 dark:text-primary-400 dark:hover:text-primary-300 text-sm">
              View Dashboard
            </Link>
          </div>
        </div>
      </div>

      <!-- Attendance Records -->
      <div v-if="currentSection" class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
        <div class="p-6 border-b dark:border-gray-700">
          <h3 class="font-medium">{{ currentSection ? currentSection.name : 'All Classes' }} Attendance Records</h3>
          <p class="text-sm text-gray-500 dark:text-gray-400">{{ currentSection ? currentSection.term : 'Fall Semester 2023' }}</p>
        </div>
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-700">
              <tr>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Date</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                <th v-if="!currentSection" scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Class</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Notes</th>
              </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
              <tr v-for="(record, index) in currentSection ? currentSection.attendanceRecords : allAttendanceRecords.slice(0, 10)" :key="index">
                <td class="px-6 py-4 whitespace-nowrap text-sm">{{ record.date }}</td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span :class="`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${record.statusClass}`">
                    {{ record.status }}
                  </span>
                </td>
                <td v-if="!currentSection" class="px-6 py-4 whitespace-nowrap text-sm">
                  <a
                    href="#"
                    @click.prevent="selectSection(findSectionById(record.classId))"
                    class="text-primary-600 hover:text-primary-800 dark:text-primary-400 dark:hover:text-primary-300"
                  >
                    {{ record.class }}
                  </a>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ record.notes }}</td>
              </tr>
            </tbody>
          </table>
        </div>
        <div class="p-4 border-t dark:border-gray-700 flex items-center justify-between">
          <div class="text-sm text-gray-500 dark:text-gray-400">
            Showing
            <span class="font-medium">1</span>
            to
            <span class="font-medium">{{ currentSection ? currentSection.attendanceRecords.length : 10 }}</span>
            of
            <span class="font-medium">{{ currentSection ? currentSection.attendanceRecords.length : allAttendanceRecords.length }}</span> entries
          </div>
          <div class="flex space-x-2">
            <button class="px-3 py-1 rounded border dark:border-gray-600 text-sm">Previous</button>
            <button class="px-3 py-1 rounded bg-primary-600 text-white text-sm">Next</button>
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
