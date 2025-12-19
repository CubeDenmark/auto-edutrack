<script setup lang="ts">
import { ref, computed, onMounted, watch, inject } from 'vue';
import AppLayout from "@/layouts/AppLayout.vue";
import { type BreadcrumbItem } from "@/types";
import { Head, usePage, Link } from "@inertiajs/vue3";
import { format, parseISO, startOfMonth, endOfMonth, eachDayOfInterval, isSameDay } from 'date-fns';

// Define breadcrumbs
const breadcrumbs: BreadcrumbItem[] = [
  {
    title: "Student Portal",
    href: "/student",
  },
  {
    title: "Attendance",
    href: "/student/attendance",
  }
];

// Get the page props to access any data passed from the server
const page = usePage();

// Inject the current item ID from the parent component (from sidebar)
const currentItemId = inject('currentItemId', ref(null));

// State variables
const isMobile = ref(false);
const currentMonth = ref(new Date());
const currentPage = ref(1);
const itemsPerPage = ref(10);
const searchQuery = ref('');
const selectedStatus = ref('all');
const selectedDate = ref(null);
const currentClass = ref(null);

// Student data

// Class data
// Get the page props to access data passed from the controller

const student = computed(() => page.props.student || {});
const classes = computed(() => page.props.classes || []);

// Attendance records data - organized by class
const attendanceRecordsByClass = computed(() => {
    const records = usePage().props.attendanceRecordsByClass || {};

    if (!records || typeof records !== 'object') {
        console.error('Attendance records data is not available or not an object:', records);
        return {};
    }

    return records;
});

// Attendance policies
const attendancePolicies = ref(usePage().props.attendancePolicies || []);

// Helper methods
const getStatusClass = (status) => {
  switch (status) {
    case 'present':
      return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300';
    case 'absent':
      return 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300';
    case 'late':
      return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300';
    case 'excused':
      return 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300';
    default:
      return 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300';
  }
};

const getStatusIcon = (status) => {
  switch (status) {
    case 'present':
      return `<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
              </svg>`;
    case 'absent':
      return `<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
              </svg>`;
    case 'late':
      return `<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
              </svg>`;
    case 'excused':
      return `<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-8.707l-3-3a1 1 0 00-1.414 0l-3 3a1 1 0 001.414 1.414L9 9.414V13a1 1 0 102 0V9.414l1.293 1.293a1 1 0 001.414-1.414z" clip-rule="evenodd" />
              </svg>`;
    default:
      return `<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-3a1 1 0 00-.867.5 1 1 0 11-1.731-1A3 3 0 0113 8a3.001 3.001 0 01-2 2.83V11a1 1 0 11-2 0v-1a1 1 0 011-1 1 1 0 100-2zm0 8a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
              </svg>`;
  }
};

// Find class by ID
const findClassById = (id) => {
  if (!id) return null;
  return classes.value.find(cls => cls.id.toString() === id.toString()) || null;
};

// Get attendance records for the current class
const currentAttendanceRecords = computed(() => {
  if (!currentClass.value) {
    // If no class is selected, return all records combined
    return Object.values(attendanceRecordsByClass.value).flat();
  }

  return attendanceRecordsByClass.value[currentClass.value.id] || [];
});

// Computed properties
const filteredRecords = computed(() => {
  let records = [...currentAttendanceRecords.value];

  // Filter by search query
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase();
    records = records.filter(record =>
      record.date.toLowerCase().includes(query) ||
      record.day.toLowerCase().includes(query) ||
      record.status.toLowerCase().includes(query) ||
      (record.notes && record.notes.toLowerCase().includes(query)) ||
      record.class.toLowerCase().includes(query) ||
      record.teacher.toLowerCase().includes(query)
    );
  }

  // Filter by status
  if (selectedStatus.value !== 'all') {
    records = records.filter(record => record.status === selectedStatus.value);
  }

  // Filter by date
  if (selectedDate.value) {
    const selectedDateStr = format(selectedDate.value, 'yyyy-MM-dd');
    records = records.filter(record => record.date === selectedDateStr);
  }

  return records;
});

const paginatedRecords = computed(() => {
  const startIndex = (currentPage.value - 1) * itemsPerPage.value;
  const endIndex = startIndex + itemsPerPage.value;
  return filteredRecords.value.slice(startIndex, endIndex);
});

const totalPages = computed(() => {
  return Math.ceil(filteredRecords.value.length / itemsPerPage.value);
});

// Calendar data for the current month
const calendarDays = computed(() => {
  const start = startOfMonth(currentMonth.value);
  const end = endOfMonth(currentMonth.value);

  return eachDayOfInterval({ start, end }).map(date => {
    const dateStr = format(date, 'yyyy-MM-dd');
    const record = currentAttendanceRecords.value.find(r => r.date === dateStr);

    return {
      date,
      dateStr,
      day: format(date, 'd'),
      status: record ? record.status : 'no-record',
      record
    };
  });
});

// Current class attendance summary
const currentAttendanceSummary = computed(() => {
  if (!currentClass.value) {
    return student.value.attendanceSummary;
  }

  return currentClass.value.attendanceSummary;
});

// Methods
const changePage = (page) => {
  currentPage.value = page;
};

const changeMonth = (increment) => {
  const newMonth = new Date(currentMonth.value);
  newMonth.setMonth(newMonth.getMonth() + increment);
  currentMonth.value = newMonth;
};

const resetFilters = () => {
  searchQuery.value = '';
  selectedStatus.value = 'all';
  selectedDate.value = null;
  currentPage.value = 1;
};

const checkMobile = () => {
  isMobile.value = window.innerWidth < 768;
};

// Handle item changes from the sidebar
const handleItemChange = (event) => {
  if (event.detail) {
    const itemId = event.detail.itemId;
    console.log('Item changed event received:', event.detail);

    // Find the class with the matching ID
    const selectedClass = findClassById(itemId);
    if (selectedClass) {
      console.log('Selected class:', selectedClass);
      currentClass.value = selectedClass;
      // Reset filters and pagination when changing classes
      resetFilters();
    } else {
      console.log('Class not found for ID:', itemId);
    }
  }
};

// Watch for changes in the injected item ID
watch(currentItemId, (newItemId) => {
  console.log('currentItemId changed:', newItemId);
  if (newItemId) {
    const selectedClass = findClassById(newItemId);
    if (selectedClass) {
      console.log('Setting current class from injected ID:', selectedClass);
      currentClass.value = selectedClass;
      // Reset filters and pagination when changing classes
      resetFilters();
    } else {
      console.log('Class not found for injected ID:', newItemId);
    }
  }
}, { immediate: true });

// Watch for changes in the URL query parameters
watch(() => window.location.search, (newSearch) => {
  const params = new URLSearchParams(newSearch);
  const sectionId = params.get('sectionId');
  const itemId = params.get('itemId');
  const classId = params.get('classId');

  console.log('URL params changed:', { sectionId, itemId, classId });

  if (sectionId) {
    const selectedClass = findClassById(sectionId);
    if (selectedClass) {
      console.log('Setting current class from sectionId:', selectedClass);
      currentClass.value = selectedClass;
      resetFilters();
    }
  } else if (itemId) {
    const selectedClass = findClassById(itemId);
    if (selectedClass) {
      console.log('Setting current class from itemId:', selectedClass);
      currentClass.value = selectedClass;
      resetFilters();
    }
  } else if (classId) {
    const selectedClass = findClassById(classId);
    if (selectedClass) {
      console.log('Setting current class from classId:', selectedClass);
      currentClass.value = selectedClass;
      resetFilters();
    }
  }
}, { immediate: true });

// Lifecycle hooks
onMounted(() => {
  // Check if we're on mobile
  checkMobile();
  window.addEventListener('resize', checkMobile);

  // Add event listener for item changes from sidebar
  window.addEventListener('item-changed', handleItemChange);
  window.addEventListener('section-changed', handleItemChange);

  // Check if there's a class ID in the URL
  const urlParams = new URLSearchParams(window.location.search);
  const sectionId = urlParams.get('sectionId');
  const itemId = urlParams.get('itemId');
  const classId = urlParams.get('classId');

  console.log('Initial URL params:', { sectionId, itemId, classId });
  console.log('Available classes:', classes.value);
  console.log('Current injected itemId:', currentItemId.value);

  if (sectionId) {
    const selectedClass = findClassById(sectionId);
    if (selectedClass) {
      console.log('Setting initial class from sectionId:', selectedClass);
      currentClass.value = selectedClass;
    }
  } else if (itemId) {
    const selectedClass = findClassById(itemId);
    if (selectedClass) {
      console.log('Setting initial class from itemId:', selectedClass);
      currentClass.value = selectedClass;
    }
  } else if (classId) {
    const selectedClass = findClassById(classId);
    if (selectedClass) {
      console.log('Setting initial class from classId:', selectedClass);
      currentClass.value = selectedClass;
    }
  } else if (currentItemId.value) {
    const selectedClass = findClassById(currentItemId.value);
    if (selectedClass) {
      console.log('Setting initial class from injected currentItemId:', selectedClass);
      currentClass.value = selectedClass;
    }
  }

  // If no class is selected yet, select the first one by default
  if (!currentClass.value && classes.value.length > 0) {
    console.log('No class selected, defaulting to first class:', classes.value[0]);
    currentClass.value = classes.value[0];
  }

  // Clean up event listeners on component unmount
  return () => {
    window.removeEventListener('resize', checkMobile);
    window.removeEventListener('item-changed', handleItemChange);
    window.removeEventListener('section-changed', handleItemChange);
  };
});

function selectSection(section) {
  // Prevent re-selecting the same section to avoid infinite loops
  if (currentClass.value && currentClass.value.id === section.id) {
    return;
  }

  currentClass.value = section;

  // Update URL with the selected section ID without navigating
  const url = new URL(window.location.href);
  url.searchParams.set('sectionId', section.id);
  window.history.replaceState({}, '', url.toString());
}
</script>

<template>
  <Head title="Student Attendance" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-6 pt-20">
      <!-- Student Info -->
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-6">
        <div class="flex flex-col md:flex-row md:items-center">
          <div class="flex items-center mb-4 md:mb-0">
            <img :src="student.avatar" :alt="`${student.name} Avatar`" class="w-16 h-16 rounded-full mr-4" />
            <div>
              <h2 class="text-xl font-semibold">{{ student.name }}</h2>
              <p class="text-gray-500 dark:text-gray-400">{{ student.grade }} | Student ID: {{ student.studentId }}</p>
            </div>
          </div>
        </div>
      </div>

       <!-- No Section Selected Message -->
    <div v-if="!currentClass" class="flex flex-col items-center justify-center h-[calc(100vh-10rem)] text-center">
      <div class="bg-gray-100 dark:bg-gray-800 p-6 rounded-full mb-4">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z" />
        </svg>
      </div>
      <h2 class="text-2xl font-bold mb-2">Select a Class Section</h2>
      <p class="text-gray-500 dark:text-gray-400 mb-6 max-w-md">Please select a class section from the sidebar or use the dropdown menu to view class-specific information.</p>
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <button
          v-for="section in classes.slice(0, 4)"
          :key="section.id"
          @click="selectSection(section)"
          class="flex flex-col items-center p-4 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700"
        >
          <span class="text-3xl mb-2">{{ section.icon }}</span>
          <span class="text-sm font-medium">{{ section.name }}</span>
        </button>
      </div>
    </div>

      <!-- Class Selection (visible on mobile or when no class is selected from sidebar) -->
      <!-- <div v-if="isMobile" class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-6">
        <h3 class="font-medium mb-4">My Classes</h3>
        <div class="grid grid-cols-1 gap-3">
          <button
            v-for="cls in classes"
            :key="cls.id"
            @click="currentClass = cls; resetFilters();"
            :class="[
              'flex items-center p-3 rounded-lg border transition-colors',
              currentClass && currentClass.id === cls.id
                ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/20 dark:border-primary-400'
                : 'border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700'
            ]"
          >
            <div class="flex items-center">
              <span class="text-2xl mr-3">{{ cls.icon }}</span>
              <div>
                <p class="font-medium">{{ cls.name }}</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ cls.teacher }} | {{ cls.room }}</p>
              </div>
            </div>
          </button>
        </div>
      </div> -->

      <!-- Current Class Info (when a class is selected) -->
      <div v-if="currentClass" class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-6">
        <div class="flex items-center">
          <span class="text-3xl mr-4">{{ currentClass.icon }}</span>
          <div>
            <h3 class="text-lg font-medium">{{ currentClass.name }}</h3>
            <p class="text-gray-500 dark:text-gray-400">{{ currentClass.teacher }} | {{ currentClass.room }} | {{ currentClass.time }}</p>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
              Class Days: {{ currentClass.days && currentClass.days.length > 0 ? currentClass.days.join(', ') : 'Not specified' }}
            </p>
          </div>
        </div>

        <!-- Class-specific attendance summary -->
        <div class="flex-1 grid grid-cols-2 md:grid-cols-4 gap-4 mt-4">
          <div class="text-center p-3 bg-green-50 dark:bg-green-900/20 rounded-lg">
            <p class="text-sm text-gray-500 dark:text-gray-400">Present</p>
            <p class="text-2xl font-bold text-green-600 dark:text-green-400">{{ currentClass.attendanceSummary.present }}%</p>
          </div>
          <div class="text-center p-3 bg-red-50 dark:bg-red-900/20 rounded-lg">
            <p class="text-sm text-gray-500 dark:text-gray-400">Absent</p>
            <p class="text-2xl font-bold text-red-600 dark:text-red-400">{{ currentClass.attendanceSummary.absent }}%</p>
          </div>
          <div class="text-center p-3 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg">
            <p class="text-sm text-gray-500 dark:text-gray-400">Late</p>
            <p class="text-2xl font-bold text-yellow-600 dark:text-yellow-400">{{ currentClass.attendanceSummary.late }}%</p>
          </div>
          <div class="text-center p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
            <p class="text-sm text-gray-500 dark:text-gray-400">Excused</p>
            <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ currentClass.attendanceSummary.excused }}%</p>
          </div>
        </div>
      </div>

      <!-- All Classes Summary (when no class is selected) -->
      <div v-if="!currentClass" class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-6">
        <h3 class="text-lg font-medium mb-4">Overall Attendance Summary</h3>
        <div class="flex-1 grid grid-cols-2 md:grid-cols-4 gap-4">
          <div class="text-center p-3 bg-green-50 dark:bg-green-900/20 rounded-lg">
            <p class="text-sm text-gray-500 dark:text-gray-400">Present</p>
            <p class="text-2xl font-bold text-green-600 dark:text-green-400">{{ student.attendanceSummary.present }}%</p>
          </div>
          <div class="text-center p-3 bg-red-50 dark:bg-red-900/20 rounded-lg">
            <p class="text-sm text-gray-500 dark:text-gray-400">Absent</p>
            <p class="text-2xl font-bold text-red-600 dark:text-red-400">{{ student.attendanceSummary.absent }}%</p>
          </div>
          <div class="text-center p-3 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg">
            <p class="text-sm text-gray-500 dark:text-gray-400">Late</p>
            <p class="text-2xl font-bold text-yellow-600 dark:text-yellow-400">{{ student.attendanceSummary.late }}%</p>
          </div>
          <div class="text-center p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
            <p class="text-sm text-gray-500 dark:text-gray-400">Excused</p>
            <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ student.attendanceSummary.excused }}%</p>
          </div>
        </div>
      </div>

      <!-- Class-specific attendance summaries -->
      <div v-if="!currentClass && classes.length > 0" class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-6">
        <h3 class="font-medium mb-4">Attendance by Class</h3>
        <div class="space-y-6">
          <div v-for="cls in classes" :key="cls.id" class="border-b dark:border-gray-700 pb-6 last:border-0 last:pb-0">
            <div class="flex items-center mb-3">
              <span class="text-2xl mr-3">{{ cls.icon }}</span>
              <div>
                <h4 class="font-medium">{{ cls.name }}</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ cls.teacher }} | {{ cls.room }}</p>
              </div>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
              <div class="text-center p-3 bg-green-50 dark:bg-green-900/20 rounded-lg">
                <p class="text-sm text-gray-500 dark:text-gray-400">Present</p>
                <p class="text-xl font-bold text-green-600 dark:text-green-400">{{ cls.attendanceSummary.present }}%</p>
              </div>
              <div class="text-center p-3 bg-red-50 dark:bg-red-900/20 rounded-lg">
                <p class="text-sm text-gray-500 dark:text-gray-400">Absent</p>
                <p class="text-xl font-bold text-red-600 dark:text-red-400">{{ cls.attendanceSummary.absent }}%</p>
              </div>
              <div class="text-center p-3 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg">
                <p class="text-sm text-gray-500 dark:text-gray-400">Late</p>
                <p class="text-xl font-bold text-yellow-600 dark:text-yellow-400">{{ cls.attendanceSummary.late }}%</p>
              </div>
              <div class="text-center p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                <p class="text-sm text-gray-500 dark:text-gray-400">Excused</p>
                <p class="text-xl font-bold text-blue-600 dark:text-blue-400">{{ cls.attendanceSummary.excused }}%</p>
              </div>
            </div>

            <!-- Quick attendance stats for each class -->
            <div class="mt-4" v-if="attendanceRecordsByClass[cls.id] && attendanceRecordsByClass[cls.id].length > 0">
              <p class="text-sm font-medium mb-2">Recent Attendance:</p>
              <div class="flex flex-wrap gap-2">
                <span
                  v-for="(record, idx) in attendanceRecordsByClass[cls.id].slice(0, 5)"
                  :key="idx"
                  :class="`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${getStatusClass(record.status)}`"
                >
                  <span v-html="getStatusIcon(record.status)" class="mr-1"></span>
                  {{ format(parseISO(record.date), 'MMM dd') }}
                </span>
                <span v-if="attendanceRecordsByClass[cls.id].length > 5" class="text-xs text-gray-500 dark:text-gray-400 self-center">
                  + {{ attendanceRecordsByClass[cls.id].length - 5 }} more
                </span>
              </div>
            </div>

            <div class="mt-4">
              <button
                @click="currentClass = cls; resetFilters();"
                class="text-sm text-primary-600 dark:text-primary-400 hover:underline"
              >
                View details →
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Filters -->
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-4">
          <h3 class="font-medium mb-4 md:mb-0">
            {{ currentClass ? `${currentClass.name} Attendance Records` : 'All Attendance Records' }}
          </h3>
          <div class="flex flex-col md:flex-row space-y-3 md:space-y-0 md:space-x-3">
            <div class="relative">
              <input
                type="text"
                v-model="searchQuery"
                placeholder="Search records..."
                class="w-full md:w-64 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500 dark:bg-gray-700 dark:text-white"
              />
              <button
                v-if="searchQuery"
                @click="searchQuery = ''"
                class="absolute right-3 top-2.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
              >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                </svg>
              </button>
            </div>
            <select
              v-model="selectedStatus"
              class="w-full md:w-auto px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500 dark:bg-gray-700 dark:text-white"
            >
              <option value="all">All Statuses</option>
              <option value="present">Present</option>
              <option value="absent">Absent</option>
              <option value="late">Late</option>
              <option value="excused">Excused</option>
            </select>
            <input
              type="date"
              v-model="selectedDate"
              class="w-full md:w-auto px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500 dark:bg-gray-700 dark:text-white"
            />
            <button
              @click="resetFilters"
              class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-200 dark:hover:bg-gray-600"
            >
              Reset
            </button>
          </div>
        </div>

        <!-- Desktop View: Attendance Table -->
        <div v-if="!isMobile" class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-700">
              <tr>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Date</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Day</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Time</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Class</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Teacher</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Notes</th>
              </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
              <tr v-for="record in paginatedRecords" :key="record.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                <td class="px-6 py-4 whitespace-nowrap text-sm">{{ format(parseISO(record.date), 'MMM dd, yyyy') }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm">{{ record.day }}</td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span :class="`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${getStatusClass(record.status)}`">
                    <span v-html="getStatusIcon(record.status)" class="mr-1"></span>
                    {{ record.status.charAt(0).toUpperCase() + record.status.slice(1) }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm">{{ record.time || '-' }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm">{{ record.class }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm">{{ record.teacher }}</td>
                <td class="px-6 py-4 text-sm">{{ record.notes || '-' }}</td>
              </tr>
              <tr v-if="paginatedRecords.length === 0">
                <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                  No attendance records found.
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Mobile View: Attendance Cards -->
        <div v-else class="space-y-4">
          <div
            v-for="record in paginatedRecords"
            :key="record.id"
            class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 border-l-4"
            :class="{
              'border-green-500': record.status === 'present',
              'border-red-500': record.status === 'absent',
              'border-yellow-500': record.status === 'late',
              'border-blue-500': record.status === 'excused'
            }"
          >
            <div class="flex justify-between items-start mb-2">
              <div>
                <h4 class="font-medium">{{ format(parseISO(record.date), 'MMM dd, yyyy') }} ({{ record.day }})</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ record.class }}</p>
              </div>
              <span :class="`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${getStatusClass(record.status)}`">
                <span v-html="getStatusIcon(record.status)" class="mr-1"></span>
                {{ record.status.charAt(0).toUpperCase() + record.status.slice(1) }}
              </span>
            </div>
            <div class="grid grid-cols-2 gap-2 text-sm">
              <div>
                <span class="text-gray-500 dark:text-gray-400">Time:</span>
                <span>{{ record.time || '-' }}</span>
              </div>
              <div>
                <span class="text-gray-500 dark:text-gray-400">Teacher:</span>
                <span>{{ record.teacher }}</span>
              </div>
              <div class="col-span-2" v-if="record.notes">
                <span class="text-gray-500 dark:text-gray-400">Notes:</span>
                <span>{{ record.notes }}</span>
              </div>
            </div>
          </div>
          <div v-if="paginatedRecords.length === 0" class="text-center p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
            <p class="text-gray-500 dark:text-gray-400">No attendance records found.</p>
          </div>
        </div>

        <!-- Pagination -->
        <div class="mt-4 flex items-center justify-between">
          <div class="text-sm text-gray-500 dark:text-gray-400">
            Showing {{ filteredRecords.length > 0 ? (currentPage - 1) * itemsPerPage + 1 : 0 }} to {{ Math.min(currentPage * itemsPerPage, filteredRecords.length) }} of {{ filteredRecords.length }} records
          </div>
          <div class="flex space-x-2">
            <button
              @click="changePage(currentPage - 1)"
              :disabled="currentPage === 1"
              :class="[
                'px-3 py-1 rounded-md',
                currentPage === 1
                  ? 'bg-gray-100 text-gray-400 cursor-not-allowed dark:bg-gray-700 dark:text-gray-500'
                  : 'bg-gray-200 text-gray-700 hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600'
              ]"
            >
              Previous
            </button>
            <button
              v-for="page in totalPages"
              :key="page"
              @click="changePage(page)"
              :class="[
                'px-3 py-1 rounded-md',
                currentPage === page
                  ? 'bg-primary-600 text-white dark:bg-primary-700'
                  : 'bg-gray-200 text-gray-700 hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600'
              ]"
            >
              {{ page }}
            </button>
            <button
              @click="changePage(currentPage + 1)"
              :disabled="currentPage === totalPages || totalPages === 0"
              :class="[
                'px-3 py-1 rounded-md',
                currentPage === totalPages || totalPages === 0
                  ? 'bg-gray-100 text-gray-400 cursor-not-allowed dark:bg-gray-700 dark:text-gray-500'
                  : 'bg-gray-200 text-gray-700 hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600'
              ]"
            >
              Next
            </button>
          </div>
        </div>
      </div>

      <!-- Mobile View: Monthly Calendar -->
      <div v-if="isMobile" class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-6">
        <div class="flex items-center justify-between mb-4">
          <h3 class="font-medium">Monthly Overview</h3>
          <div class="flex space-x-2">
            <button
              @click="changeMonth(-1)"
              class="p-2 rounded-full bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
              </svg>
            </button>
            <span class="text-lg font-medium">{{ format(currentMonth, 'MMMM yyyy') }}</span>
            <button
              @click="changeMonth(1)"
              class="p-2 rounded-full bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
              </svg>
            </button>
          </div>
        </div>
        <div class="grid grid-cols-7 gap-1">
          <div v-for="day in ['S', 'M', 'T', 'W', 'T', 'F', 'S']" :key="day" class="text-center text-xs font-medium text-gray-500 dark:text-gray-400 p-2">
            {{ day }}
          </div>
          <!-- Empty cells for days before the first of the month -->
          <div
            v-for="_ in new Date(currentMonth.getFullYear(), currentMonth.getMonth(), 1).getDay()"
            :key="'empty-' + _"
            class="p-2"
          ></div>
          <!-- Calendar days -->
          <div
            v-for="calendarDay in calendarDays"
            :key="calendarDay.dateStr"
            class="p-1"
          >
            <div
              class="h-10 w-full rounded-full flex items-center justify-center cursor-pointer"
              :class="{
                'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300': calendarDay.status === 'present',
                'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300': calendarDay.status === 'absent',
                'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-300': calendarDay.status === 'late',
                'bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300': calendarDay.status === 'excused',
                'hover:bg-gray-100 dark:hover:bg-gray-700': calendarDay.status !== 'no-record',
                'text-gray-400 dark:text-gray-600': calendarDay.status === 'no-record'
              }"
              @click="calendarDay.record && (selectedDate = calendarDay.date)"
            >
              {{ calendarDay.day }}
            </div>
          </div>
        </div>
        <div class="mt-4 flex justify-center space-x-4 text-sm">
          <div class="flex items-center">
            <div class="w-3 h-3 rounded-full bg-green-500 mr-1"></div>
            <span>Present</span>
          </div>
          <div class="flex items-center">
            <div class="w-3 h-3 rounded-full bg-red-500 mr-1"></div>
            <span>Absent</span>
          </div>
          <div class="flex items-center">
            <div class="w-3 h-3 rounded-full bg-yellow-500 mr-1"></div>
            <span>Late</span>
          </div>
          <div class="flex items-center">
            <div class="w-3 h-3 rounded-full bg-blue-500 mr-1"></div>
            <span>Excused</span>
          </div>
        </div>
      </div>

      <!-- Class Schedule (for current class) -->
      <div v-if="currentClass && currentClass.days && currentClass.days.length > 0" class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-6">
        <h3 class="font-medium mb-4">Class Schedule</h3>
        <div class="space-y-3">
          <div v-for="day in currentClass.days" :key="day" class="p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
            <div class="flex justify-between items-center">
              <span class="font-medium">{{ day }}</span>
              <span class="text-sm text-gray-500 dark:text-gray-400">{{ currentClass.time }}</span>
            </div>
            <div class="mt-1 text-sm">
              <span>{{ currentClass.teacher }} | {{ currentClass.room }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Attendance Policies -->
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <h3 class="font-medium mb-4">Attendance Policies</h3>
        <div class="space-y-4">
          <div v-for="(policy, index) in attendancePolicies" :key="index" class="pb-4 border-b dark:border-gray-700 last:border-0 last:pb-0">
            <h4 class="font-medium mb-1">{{ policy.title }}</h4>
            <p class="text-sm text-gray-600 dark:text-gray-300">{{ policy.description }}</p>
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
