<script setup lang="ts">
import { ref, computed, onMounted, watch, inject } from 'vue';
import AppLayout from "@/layouts/AppLayout.vue";
import { type BreadcrumbItem } from "@/types";
import { Head, usePage, Link } from "@inertiajs/vue3";

// Breadcrumbs
const breadcrumbs: BreadcrumbItem[] = [
  {
    title: "Attendance",
    href: "/parent/attendance",
  },
];

// Get the page props to access any data passed from the server
const page = usePage();

// Inject the current item ID from the parent component (from sidebar)
const currentItemId = inject('currentItemId', ref(null));

// State variables
const currentChild = ref<any>(null);
const selectedMonth = ref(new Date().getMonth());
const selectedYear = ref(new Date().getFullYear());

// Children data
const children = ref([
  {
    id: 'child1',
    name: 'Alex Johnson',
    grade: 'Grade 10',
    avatar: 'https://i.pravatar.cc/150?img=3',
    stats: {
      attendance: 95
    },
    attendanceSummary: {
      present: 38,
      absent: 1,
      late: 1,
      excused: 0,
      total: 40
    },
    attendanceRecords: [
      { date: '2023-10-16', status: 'present', class: 'All Classes', notes: '' },
      { date: '2023-10-15', status: 'present', class: 'All Classes', notes: '' },
      { date: '2023-10-14', status: 'present', class: 'All Classes', notes: '' },
      { date: '2023-10-13', status: 'late', class: 'Morning Session', notes: 'Arrived 15 minutes late' },
      { date: '2023-10-12', status: 'present', class: 'All Classes', notes: '' },
      { date: '2023-10-11', status: 'present', class: 'All Classes', notes: '' },
      { date: '2023-10-10', status: 'absent', class: 'All Classes', notes: 'Medical appointment' },
      { date: '2023-10-09', status: 'present', class: 'All Classes', notes: '' },
      { date: '2023-10-08', status: 'present', class: 'All Classes', notes: '' },
      { date: '2023-10-07', status: 'present', class: 'All Classes', notes: '' },
      { date: '2023-10-06', status: 'present', class: 'All Classes', notes: '' },
      { date: '2023-10-05', status: 'present', class: 'All Classes', notes: '' },
      { date: '2023-10-04', status: 'present', class: 'All Classes', notes: '' },
      { date: '2023-10-03', status: 'present', class: 'All Classes', notes: '' },
      { date: '2023-10-02', status: 'present', class: 'All Classes', notes: '' },
      { date: '2023-10-01', status: 'present', class: 'All Classes', notes: '' },
      { date: '2023-09-30', status: 'present', class: 'All Classes', notes: '' },
      { date: '2023-09-29', status: 'present', class: 'All Classes', notes: '' },
      { date: '2023-09-28', status: 'present', class: 'All Classes', notes: '' },
      { date: '2023-09-27', status: 'present', class: 'All Classes', notes: '' }
    ],
    monthlyAttendance: [
      { month: 'August', present: 10, absent: 0, late: 0, excused: 0, total: 10 },
      { month: 'September', present: 20, absent: 0, late: 0, excused: 0, total: 20 },
      { month: 'October', present: 8, absent: 1, late: 1, excused: 0, total: 10 }
    ]
  },
  {
    id: 'child2',
    name: 'Emma Johnson',
    grade: 'Grade 8',
    avatar: 'https://i.pravatar.cc/150?img=5',
    stats: {
      attendance: 98
    },
    attendanceSummary: {
      present: 39,
      absent: 0,
      late: 1,
      excused: 0,
      total: 40
    },
    attendanceRecords: [
      { date: '2023-10-16', status: 'present', class: 'All Classes', notes: '' },
      { date: '2023-10-15', status: 'present', class: 'All Classes', notes: '' },
      { date: '2023-10-14', status: 'present', class: 'All Classes', notes: '' },
      { date: '2023-10-13', status: 'present', class: 'All Classes', notes: '' },
      { date: '2023-10-12', status: 'present', class: 'All Classes', notes: '' },
      { date: '2023-10-11', status: 'present', class: 'All Classes', notes: '' },
      { date: '2023-10-10', status: 'present', class: 'All Classes', notes: '' },
      { date: '2023-10-09', status: 'present', class: 'All Classes', notes: '' },
      { date: '2023-10-08', status: 'present', class: 'All Classes', notes: '' },
      { date: '2023-10-07', status: 'present', class: 'All Classes', notes: '' },
      { date: '2023-10-06', status: 'present', class: 'All Classes', notes: '' },
      { date: '2023-10-05', status: 'present', class: 'All Classes', notes: '' },
      { date: '2023-10-04', status: 'present', class: 'All Classes', notes: '' },
      { date: '2023-10-03', status: 'present', class: 'All Classes', notes: '' },
      { date: '2023-10-02', status: 'present', class: 'All Classes', notes: '' },
      { date: '2023-10-01', status: 'present', class: 'All Classes', notes: '' },
      { date: '2023-09-30', status: 'present', class: 'All Classes', notes: '' },
      { date: '2023-09-29', status: 'present', class: 'All Classes', notes: '' },
      { date: '2023-09-28', status: 'present', class: 'All Classes', notes: '' },
      { date: '2023-09-27', status: 'late', class: 'Morning Session', notes: 'Bus delay' }
    ],
    monthlyAttendance: [
      { month: 'August', present: 10, absent: 0, late: 0, excused: 0, total: 10 },
      { month: 'September', present: 19, absent: 0, late: 1, excused: 0, total: 20 },
      { month: 'October', present: 10, absent: 0, late: 0, excused: 0, total: 10 }
    ]
  },
  {
    id: 'child3',
    name: 'Noah Johnson',
    grade: 'Grade 6',
    avatar: 'https://i.pravatar.cc/150?img=8',
    stats: {
      attendance: 92
    },
    attendanceSummary: {
      present: 36,
      absent: 2,
      late: 2,
      excused: 0,
      total: 40
    },
    attendanceRecords: [
      { date: '2023-10-16', status: 'present', class: 'All Classes', notes: '' },
      { date: '2023-10-15', status: 'present', class: 'All Classes', notes: '' },
      { date: '2023-10-14', status: 'present', class: 'All Classes', notes: '' },
      { date: '2023-10-13', status: 'present', class: 'All Classes', notes: '' },
      { date: '2023-10-12', status: 'present', class: 'All Classes', notes: '' },
      { date: '2023-10-11', status: 'late', class: 'Morning Session', notes: 'Traffic delay' },
      { date: '2023-10-10', status: 'present', class: 'All Classes', notes: '' },
      { date: '2023-10-09', status: 'present', class: 'All Classes', notes: '' },
      { date: '2023-10-08', status: 'present', class: 'All Classes', notes: '' },
      { date: '2023-10-07', status: 'present', class: 'All Classes', notes: '' },
      { date: '2023-10-06', status: 'present', class: 'All Classes', notes: '' },
      { date: '2023-10-05', status: 'absent', class: 'All Classes', notes: 'Sick' },
      { date: '2023-10-04', status: 'present', class: 'All Classes', notes: '' },
      { date: '2023-10-03', status: 'present', class: 'All Classes', notes: '' },
      { date: '2023-10-02', status: 'present', class: 'All Classes', notes: '' },
      { date: '2023-10-01', status: 'present', class: 'All Classes', notes: '' },
      { date: '2023-09-30', status: 'present', class: 'All Classes', notes: '' },
      { date: '2023-09-29', status: 'present', class: 'All Classes', notes: '' },
      { date: '2023-09-28', status: 'late', class: 'Morning Session', notes: 'Overslept' },
      { date: '2023-09-27', status: 'absent', class: 'All Classes', notes: 'Family emergency' }
    ],
    monthlyAttendance: [
      { month: 'August', present: 9, absent: 1, late: 0, excused: 0, total: 10 },
      { month: 'September', present: 18, absent: 1, late: 1, excused: 0, total: 20 },
      { month: 'October', present: 9, absent: 0, late: 1, excused: 0, total: 10 }
    ]
  }
]);

// Upcoming events
const upcomingEvents = ref([
  {
    title: 'Parent-Teacher Conference',
    date: 'Nov 15, 2023'
  },
  {
    title: 'School Holiday',
    date: 'Nov 23-24, 2023'
  },
  {
    title: 'End of Term',
    date: 'Dec 15, 2023'
  }
]);

// Flag to track if we're showing all children overview
const showAllChildren = ref(true);

// Helper function for status styling
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
      return 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300';
  }
};

// Computed properties
const filteredAttendanceRecords = computed(() => {
  if (!currentChild.value) return [];

  return currentChild.value.attendanceRecords.filter(record => {
    const date = new Date(record.date);
    return date.getMonth() === selectedMonth.value && date.getFullYear() === selectedYear.value;
  });
});

const monthName = computed(() => {
  return new Date(selectedYear.value, selectedMonth.value).toLocaleString('default', { month: 'long', year: 'numeric' });
});

// Methods
function findChildById(id) {
  return children.value.find(child => child.id === id || child.id === Number(id)) || null;
}

// Listen for item changes from the sidebar
const handleItemChange = (event) => {
  if (event.detail && event.detail.source === 'sidebar') {
    const itemId = event.detail.itemId;
    const itemType = event.detail.itemType;

    if (itemType === 'child') {
      // Handle child selection
      const child = findChildById(itemId);
      if (child) {
        currentChild.value = child;
        showAllChildren.value = false;
      }
    } else if (itemType === 'overview' && itemId === 'all') {
      // Handle "All Children" selection
      currentChild.value = null;
      showAllChildren.value = true;
    }
  }
};

// Navigation methods
const previousMonth = () => {
  if (selectedMonth.value === 0) {
    selectedMonth.value = 11;
    selectedYear.value--;
  } else {
    selectedMonth.value--;
  }
};

const nextMonth = () => {
  if (selectedMonth.value === 11) {
    selectedMonth.value = 0;
    selectedYear.value++;
  } else {
    selectedMonth.value++;
  }
};

// Watch for changes in the injected item ID
watch(currentItemId, (newItemId) => {
  if (newItemId) {
    const child = findChildById(newItemId);
    if (child) {
      currentChild.value = child;
      showAllChildren.value = false;
    }
  } else {
    // If itemId is null or undefined, show all children
    showAllChildren.value = true;
    currentChild.value = null;
  }
}, { immediate: true });

// Watch for changes in the URL query parameters
watch(() => window.location.search, (newSearch) => {
  const params = new URLSearchParams(newSearch);
  const childId = params.get('childId');
  const itemId = params.get('itemId');

  // First check for childId (legacy parameter)
  if (childId) {
    const child = findChildById(childId);
    if (child) {
      currentChild.value = child;
      showAllChildren.value = false;
    }
  }
  // Then check for itemId (new parameter from sidebar)
  else if (itemId) {
    const child = findChildById(itemId);
    if (child) {
      currentChild.value = child;
      showAllChildren.value = false;
    } else if (itemId === 'all') {
      currentChild.value = null;
      showAllChildren.value = true;
    }
  } else {
    // Default to showing all children
    currentChild.value = null;
    showAllChildren.value = true;
  }
}, { immediate: true });

// Lifecycle hooks
onMounted(() => {
  // Add event listener for item changes from sidebar
  window.addEventListener('item-changed', handleItemChange);

  // Check if there's a child ID or item ID in the URL or page props
  const urlParams = new URLSearchParams(window.location.search);
  const childIdFromUrl = urlParams.get('childId');
  const itemIdFromUrl = urlParams.get('itemId');
  const childIdFromProps = page.props.childId;

  // First check for childId from URL or props
  if (childIdFromUrl || childIdFromProps) {
    const childId = childIdFromProps || childIdFromUrl;
    const child = findChildById(childId);
    if (child) {
      currentChild.value = child;
      showAllChildren.value = false;
    }
  }
  // Then check for itemId (which could be a child ID from sidebar)
  else if (itemIdFromUrl) {
    const child = findChildById(itemIdFromUrl);
    if (child) {
      currentChild.value = child;
      showAllChildren.value = false;
    } else if (itemIdFromUrl === 'all') {
      currentChild.value = null;
      showAllChildren.value = true;
    }
  } else {
    // Default to showing all children
    currentChild.value = null;
    showAllChildren.value = true;
  }

  // Clean up event listeners on component unmount
  return () => {
    window.removeEventListener('item-changed', handleItemChange);
  };
});
</script>

<template>
  <Head title="Parent Attendance View" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-6">
      <!-- Child-specific attendance content -->
      <div v-if="currentChild">
        <!-- Child header -->
        <div class="flex items-center mb-6">
          <img :src="currentChild.avatar" :alt="`${currentChild.name} Avatar`" class="w-16 h-16 rounded-full mr-4" />
          <div>
            <h2 class="text-xl font-semibold">{{ currentChild.name }}</h2>
            <p class="text-gray-500 dark:text-gray-400">{{ currentChild.grade }}</p>
            <div class="flex items-center mt-1">
              <span class="text-sm text-gray-500 dark:text-gray-400 mr-2">Attendance Rate:</span>
              <span class="text-sm font-medium">{{ currentChild.stats.attendance }}%</span>
            </div>
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
          <!-- Attendance Summary Card -->
          <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h3 class="font-medium mb-4">Attendance Summary</h3>
            <div class="flex items-center space-x-4">
              <div class="w-20 h-20 rounded-full flex items-center justify-center bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-400 text-xl font-bold">
                {{ currentChild.stats.attendance }}%
              </div>
              <div class="flex-1">
                <div class="grid grid-cols-2 gap-2">
                  <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Present</p>
                    <p class="font-medium">{{ currentChild.attendanceSummary.present }} days</p>
                  </div>
                  <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Absent</p>
                    <p class="font-medium">{{ currentChild.attendanceSummary.absent }} days</p>
                  </div>
                  <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Late</p>
                    <p class="font-medium">{{ currentChild.attendanceSummary.late }} days</p>
                  </div>
                  <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Total</p>
                    <p class="font-medium">{{ currentChild.attendanceSummary.total }} days</p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Monthly Trend Card -->
          <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h3 class="font-medium mb-4">Monthly Trend</h3>
            <div class="h-40 w-full">
              <!-- Chart would be rendered here with a chart library -->
              <div class="h-full w-full flex flex-col justify-center bg-gray-100 dark:bg-gray-700 rounded-lg p-4">
                <div v-for="(month, index) in currentChild.monthlyAttendance" :key="index" class="mb-2 last:mb-0">
                  <div class="flex justify-between mb-1">
                    <span class="text-xs font-medium">{{ month.month }}</span>
                    <span class="text-xs font-medium">{{ Math.round((month.present / month.total) * 100) }}%</span>
                  </div>
                  <div class="w-full bg-gray-200 dark:bg-gray-600 rounded-full h-2">
                    <div
                      class="bg-blue-600 h-2 rounded-full"
                      :style="`width: ${Math.round((month.present / month.total) * 100)}%`"
                    ></div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Upcoming Events Card -->
          <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h3 class="font-medium mb-4">Upcoming Events</h3>
            <div class="space-y-3">
              <div v-for="(event, index) in upcomingEvents" :key="index" class="flex items-center justify-between text-sm">
                <p>{{ event.title }}</p>
                <p class="text-gray-500 dark:text-gray-400">{{ event.date }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Attendance Records -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
          <div class="p-6 border-b dark:border-gray-700 flex justify-between items-center">
            <div>
              <h3 class="font-medium">Attendance Records</h3>
              <p class="text-sm text-gray-500 dark:text-gray-400">{{ monthName }}</p>
            </div>
            <div class="flex space-x-2">
              <button
                @click="previousMonth"
                class="p-2 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700"
              >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
              </button>
              <button
                @click="nextMonth"
                class="p-2 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700"
              >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
              </button>
            </div>
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
                <tr v-for="(record, index) in filteredAttendanceRecords" :key="index">
                  <td class="px-6 py-4 whitespace-nowrap text-sm">
                    {{ new Date(record.date).toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric', year: 'numeric' }) }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span :class="`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium capitalize ${getStatusClass(record.status)}`">
                      {{ record.status }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm">{{ record.class }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ record.notes || 'No notes' }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm">
                    <button v-if="record.status === 'absent'" class="text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300">
                      Submit Excuse
                    </button>
                    <span v-else>-</span>
                  </td>
                </tr>
                <tr v-if="filteredAttendanceRecords.length === 0">
                  <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                    No attendance records for this month.
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          <div class="p-4 border-t dark:border-gray-700 flex items-center justify-between">
            <div class="text-sm text-gray-500 dark:text-gray-400">
              Showing <span class="font-medium">{{ filteredAttendanceRecords.length }}</span> records
            </div>
            <div class="flex space-x-2">
              <button
                @click="previousMonth"
                class="px-3 py-1 rounded border dark:border-gray-600 text-sm"
              >
                Previous Month
              </button>
              <button
                @click="nextMonth"
                class="px-3 py-1 rounded bg-primary-600 text-white text-sm"
              >
                Next Month
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- All Children Overview (when no specific child is selected) -->
      <div v-else-if="showAllChildren" class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <h3 class="text-xl font-medium mb-6">Attendance Overview</h3>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
          <div v-for="child in children" :key="child.id" class="bg-white dark:bg-gray-700 border dark:border-gray-600 rounded-lg shadow-sm overflow-hidden">
            <div class="p-4 border-b dark:border-gray-600">
              <div class="flex items-center">
                <img :src="child.avatar" :alt="`${child.name} Avatar`" class="w-10 h-10 rounded-full mr-3" />
                <div>
                  <h4 class="font-medium">{{ child.name }}</h4>
                  <p class="text-sm text-gray-500 dark:text-gray-400">{{ child.grade }}</p>
                </div>
              </div>
            </div>
            <div class="p-4">
              <div class="flex justify-between items-center mb-2">
                <span class="text-sm text-gray-500 dark:text-gray-400">Attendance Rate</span>
                <span class="text-sm font-medium">{{ child.stats.attendance }}%</span>
              </div>
              <div class="w-full bg-gray-200 dark:bg-gray-600 rounded-full h-2 mb-4">
                <div
                  class="bg-blue-600 h-2 rounded-full"
                  :style="`width: ${child.stats.attendance}%`"
                ></div>
              </div>

              <div class="grid grid-cols-2 gap-2 text-sm">
                <div>
                  <p class="text-gray-500 dark:text-gray-400">Present</p>
                  <p class="font-medium">{{ child.attendanceSummary.present }} days</p>
                </div>
                <div>
                  <p class="text-gray-500 dark:text-gray-400">Absent</p>
                  <p class="font-medium">{{ child.attendanceSummary.absent }} days</p>
                </div>
                <div>
                  <p class="text-gray-500 dark:text-gray-400">Late</p>
                  <p class="font-medium">{{ child.attendanceSummary.late }} days</p>
                </div>
                <div>
                  <p class="text-gray-500 dark:text-gray-400">Total</p>
                  <p class="font-medium">{{ child.attendanceSummary.total }} days</p>
                </div>
              </div>
            </div>
            <div class="p-4 bg-gray-50 dark:bg-gray-800 border-t dark:border-gray-600">
              <button
                @click="currentChild = child; showAllChildren = false"
                class="w-full text-center text-sm text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300"
              >
                View Detailed Records
              </button>
            </div>
          </div>
        </div>

        <!-- Upcoming Events for All Children View -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
          <h3 class="font-medium mb-4">Upcoming Events</h3>
          <div class="space-y-3">
            <div v-for="(event, index) in upcomingEvents" :key="index" class="flex items-center justify-between text-sm">
              <p>{{ event.title }}</p>
              <p class="text-gray-500 dark:text-gray-400">{{ event.date }}</p>
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
