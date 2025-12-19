<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue';
import AppLayout from "@/layouts/AppLayout.vue";
import { Head, usePage, Link } from "@inertiajs/vue3";

// Breadcrumbs for the attendance page
const breadcrumbs = [
  {
    title: "Dashboard",
    href: "/dashboard",
  },
  {
    title: "My Attendance",
    href: "/student/attendance",
  },
];

// Get the page props to access data passed from the controller
const page = usePage();
const student = computed(() => page.props.student || {});
const classes = computed(() => page.props.classes || []);
const attendanceRecordsByClass = computed(() => page.props.attendanceRecordsByClass || {});
const attendancePolicies = computed(() => page.props.attendancePolicies || []);

// State variables
const currentDate = ref(
  new Date().toLocaleDateString("en-US", { weekday: "long", year: "numeric", month: "long", day: "numeric" })
);
const showSectionDropdown = ref(false);
const currentClass = ref(null);
const activeTab = ref("records");

// URL parameters
const urlParams = new URLSearchParams(window.location.search);
const classIdFromUrl = ref(urlParams.get("classId"));

// Format date function
const formatDate = (dateString) => {
  if (!dateString) return '';
  const date = new Date(dateString);
  return date.toLocaleDateString("en-US", {
    month: "short",
    day: "numeric",
    year: "numeric",
  });
};

// Get status class based on attendance status
const getStatusClass = (status) => {
  if (!status) return "bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300";

  switch (status.toLowerCase()) {
    case "present":
      return "bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300";
    case "absent":
      return "bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300";
    case "late":
      return "bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300";
    case "excused":
      return "bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300";
    default:
      return "bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300";
  }
};

// Current class attendance records
const currentClassRecords = computed(() => {
  if (!currentClass.value || !attendanceRecordsByClass.value) return [];

  const classId = currentClass.value.id;
  return attendanceRecordsByClass.value[classId] || [];
});

// All attendance records combined (for when no class is selected)
const allAttendanceRecords = computed(() => {
  const records = [];

  if (!attendanceRecordsByClass.value) return records;

  Object.entries(attendanceRecordsByClass.value).forEach(([classId, classRecords]) => {
    if (!Array.isArray(classRecords)) return;

    classRecords.forEach((record) => {
      const foundClass = classes.value.find(c => c.id === parseInt(classId));
      records.push({
        ...record,
        classId,
        className: foundClass ? foundClass.name : 'Unknown Class'
      });
    });
  });

  // Sort by date (most recent first)
  return records.sort((a, b) => {
    if (!a.date || !b.date) return 0;
    const dateA = new Date(a.date);
    const dateB = new Date(b.date);
    return dateB - dateA;
  });
});

// Calculate attendance summary for the current class
const classSummary = computed(() => {
  if (!currentClass.value || !currentClassRecords.value || !currentClassRecords.value.length) return {
    present: 0,
    absent: 0,
    late: 0,
    excused: 0,
    total: 0,
    presentPercentage: 0
  };

  const records = currentClassRecords.value;
  let present = 0, absent = 0, late = 0, excused = 0;

  records.forEach(record => {
    if (!record.status) return;

    const status = record.status.toLowerCase();
    if (status === 'present') present++;
    else if (status === 'absent') absent++;
    else if (status === 'late') late++;
    else if (status === 'excused') excused++;
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

// Pagination for attendance records
const recordsPerPage = 10;
const currentPage = ref(1);

const paginatedRecords = computed(() => {
  const records = currentClass.value ? currentClassRecords.value : allAttendanceRecords.value;
  if (!records || !records.length) return [];

  const start = (currentPage.value - 1) * recordsPerPage;
  const end = start + recordsPerPage;
  return records.slice(start, end);
});

const totalPages = computed(() => {
  const records = currentClass.value ? currentClassRecords.value : allAttendanceRecords.value;
  if (!records || !records.length) return 1;

  return Math.ceil(records.length / recordsPerPage);
});

// Methods
function selectClass(classData) {
  if (!classData) return;

  currentClass.value = classData;
  currentPage.value = 1; // Reset pagination when changing class

  // Update URL with the selected class ID without navigating
  const url = new URL(window.location.href);
  url.searchParams.set("classId", classData.id.toString());
  window.history.replaceState({}, "", url.toString());
  classIdFromUrl.value = classData.id.toString();

  // Close dropdown after selection
  showSectionDropdown.value = false;
}

function clearClassSelection() {
  currentClass.value = null;
  currentPage.value = 1; // Reset pagination

  // Remove classId from URL
  const url = new URL(window.location.href);
  url.searchParams.delete("classId");
  window.history.replaceState({}, "", url.toString());
  classIdFromUrl.value = null;
}

// Find class by ID
function findClassById(id) {
  if (!id || !classes.value || !classes.value.length) return null;
  return classes.value.find((cls) => cls.id === parseInt(id)) || null;
}

// Pagination methods
function nextPage() {
  if (currentPage.value < totalPages.value) {
    currentPage.value++;
  }
}

function prevPage() {
  if (currentPage.value > 1) {
    currentPage.value--;
  }
}

// Lifecycle hooks
// Initialize with safe defaults
const initialClassSet = ref(false); // Flag to track if initial class has been set

// Function to set the initial class
const setInitialClass = () => {
  if (classIdFromUrl.value) {
    const classData = findClassById(classIdFromUrl.value);
    if (classData) {
      currentClass.value = classData;
      initialClassSet.value = true;
    }
  } else if (classes.value && classes.value.length > 0) {
    // Default to first class if none selected
    currentClass.value = classes.value[0];
    initialClassSet.value = true;
  }
};

onMounted(() => {
  if (!classes.value || !classes.value.length) return;

  setInitialClass(); // Call the function to set the initial class
});

// Watch for changes in classes.value and set initial class if not already set
watch(classes, (newClasses) => {
  if (!initialClassSet.value && newClasses && newClasses.length > 0) {
    setInitialClass();
  }
});
</script>

<template>
  <Head title="My Attendance" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-6 pt-20">
      <!-- Loading State -->
      <div v-if="!classes || classes.length === 0" class="flex flex-col items-center justify-center h-[calc(100vh-10rem)] text-center">
        <div class="bg-gray-100 dark:bg-gray-800 p-6 rounded-full mb-4">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-gray-400 animate-spin" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
          </svg>
        </div>
        <h2 class="text-2xl font-bold mb-2">Loading Attendance Data</h2>
        <p class="text-gray-500 dark:text-gray-400 mb-6 max-w-md">Please wait while we fetch your attendance information...</p>
      </div>

      <!-- No Class Selected Message -->
      <div v-else-if="!currentClass" class="flex flex-col items-center justify-center h-[calc(100vh-10rem)] text-center">
        <div class="bg-gray-100 dark:bg-gray-800 p-6 rounded-full mb-4">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z" />
          </svg>
        </div>
        <h2 class="text-2xl font-bold mb-2">Select a Class</h2>
        <p class="text-gray-500 dark:text-gray-400 mb-6 max-w-md">Please select a class from the dropdown menu to view class-specific attendance information.</p>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
          <button
            v-for="classItem in classes.slice(0, 4)"
            :key="classItem.id"
            @click="selectClass(classItem)"
            class="flex flex-col items-center p-4 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700"
          >
            <span class="text-3xl mb-2">📚</span>
            <span class="text-sm font-medium">{{ classItem.name }}</span>
          </button>
        </div>
      </div>

      <!-- Class Selection -->
      <div v-else class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-6">
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
              <span>{{ currentClass ? currentClass.name : 'Select Class' }}</span>
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
                  v-for="classItem in classes"
                  :key="classItem.id"
                  @click="selectClass(classItem)"
                  class="w-full flex items-center space-x-2 p-2 rounded-md text-left text-sm hover:bg-gray-100 dark:hover:bg-gray-700"
                  :class="{'bg-gray-100 dark:bg-gray-700': currentClass && currentClass.id === classItem.id}"
                >
                  <span class="text-lg">📚</span>
                  <span>{{ classItem.name }}</span>
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Class-specific attendance summary -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
          <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Present</p>
                <p class="text-2xl font-bold">{{ classSummary.present }}</p>
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
                <p class="text-2xl font-bold">{{ classSummary.absent }}</p>
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
                <p class="text-2xl font-bold">{{ classSummary.late }}</p>
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
                <p class="text-2xl font-bold">{{ classSummary.excused }}</p>
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
        <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
          <div class="flex items-center">
            <div class="w-12 h-12 rounded-full bg-primary-100 dark:bg-primary-900 flex items-center justify-center text-primary-600 dark:text-primary-400 mr-4">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
              </svg>
            </div>
            <div>
              <p class="text-sm text-gray-500 dark:text-gray-400">Attendance Rate</p>
              <p class="text-xl font-bold">{{ classSummary.presentPercentage }}% Present</p>
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
      <div v-if="classes && classes.length > 0" class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
        <div class="p-6 border-b dark:border-gray-700">
          <h3 class="font-medium">{{ currentClass ? currentClass.name : 'All Classes' }} Attendance Records</h3>
          <p class="text-sm text-gray-500 dark:text-gray-400">{{ currentClass && currentClass.term ? currentClass.term : 'Current Term' }}</p>
        </div>
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-700">
              <tr>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Date</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                <th v-if="!currentClass" scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Class</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Notes</th>
              </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
              <tr v-for="(record, index) in paginatedRecords" :key="index">
                <td class="px-6 py-4 whitespace-nowrap text-sm">{{ formatDate(record.date) }}</td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span :class="`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${getStatusClass(record.status)}`">
                    {{ record.status || 'Unknown' }}
                  </span>
                </td>
                <td v-if="!currentClass" class="px-6 py-4 whitespace-nowrap text-sm">
                  <a
                    href="#"
                    @click.prevent="selectClass(findClassById(record.classId))"
                    class="text-primary-600 hover:text-primary-800 dark:text-primary-400 dark:hover:text-primary-300"
                  >
                    {{ record.className || 'Unknown Class' }}
                  </a>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ record.notes || '-' }}</td>
              </tr>
              <tr v-if="!paginatedRecords || paginatedRecords.length === 0">
                <td :colspan="currentClass ? 3 : 4" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                  No attendance records found.
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <div class="p-4 border-t dark:border-gray-700 flex items-center justify-between">
          <div class="text-sm text-gray-500 dark:text-gray-400">
            Showing
            <span class="font-medium">{{ paginatedRecords && paginatedRecords.length ? (currentPage - 1) * recordsPerPage + 1 : 0 }}</span>
            to
            <span class="font-medium">{{ paginatedRecords && paginatedRecords.length ? Math.min(currentPage * recordsPerPage, (currentClass ? (currentClassRecords.value || []) : (allAttendanceRecords.value || [])).length) : 0 }}</span>
            of
            <span class="font-medium">{{ (currentClass ? (currentClassRecords.value || []) : (allAttendanceRecords.value || [])).length }}</span> entries
          </div>
          <div class="flex space-x-2">
            <button
              @click="prevPage"
              :disabled="currentPage === 1"
              :class="{'opacity-50 cursor-not-allowed': currentPage === 1}"
              class="px-3 py-1 rounded border dark:border-gray-600 text-sm">
              Previous
            </button>
            <button
              @click="nextPage"
              :disabled="currentPage === totalPages"
              :class="{'opacity-50 cursor-not-allowed': currentPage === totalPages}"
              class="px-3 py-1 rounded bg-primary-600 text-white text-sm">
              Next
            </button>
          </div>
        </div>
      </div>

      <!-- Attendance Policies -->
      <div v-if="attendancePolicies && attendancePolicies.length > 0" class="mt-6 bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <h3 class="text-lg font-medium mb-4">Attendance Policies</h3>
        <div class="space-y-4">
          <div v-for="(policy, index) in attendancePolicies" :key="index" class="p-4 border border-gray-200 dark:border-gray-700 rounded-lg">
            <h4 class="font-medium mb-2">{{ policy.title || 'Attendance Policy' }}</h4>
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ policy.description || 'No description available.' }}</p>
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
