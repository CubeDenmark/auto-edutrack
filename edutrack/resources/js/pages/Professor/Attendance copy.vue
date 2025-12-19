<script setup lang="ts">
import AppLayout from "@/layouts/AppLayout.vue";
import { type BreadcrumbItem } from "@/types";
import { Head, usePage } from "@inertiajs/vue3";
import { ref, computed, onMounted, reactive, watch, inject } from 'vue'

const breadcrumbs: BreadcrumbItem[] = [
{
  title: "Attendance",
  href: "/attendance",
}
];

// Get the page props to access any data passed from the server
const page = usePage();

defineProps<{
name?: string;
sectionId?: string; // Add prop to receive section ID from URL or parent
}>();

// Inject the current item ID from the parent component (from sidebar)
const currentItemId = inject('currentItemId', ref(null));

// State variables
const currentDate = ref(new Date().toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }))
const showSectionDropdown = ref(false)
const currentSection = ref<any>(null)
const showAddStudentModal = ref(false)

// Form states
const newStudent = reactive({
id: '',
name: '',
email: '',
status: 'present',
notes: ''
})

// Class sections data
const sections = usePage().props.sections;

const classSections = computed(() =>
  sections.map(section => ({
    id: section.id,
    name: section.name,
    icon: section.icon,
    term: section.term,
    studentCount: 0, // Static value
    presentCount: 0, // Static value
    attendanceSummary: {
      present: 0,
      absent: 0,
      late: 0,
      excused: 0
    },
    students: []
  }))
);

// const classSections = ref([
// {
//   id: 'sci101a',
//   name: 'Science 101 - Section A',
//   icon: '🧪',
//   term: 'Fall Semester 2023',
//   studentCount: 28,
//   presentCount: 26,
//   attendanceSummary: {
//     present: 26,
//     absent: 1,
//     late: 1,
//     excused: 0
//   },
//   students: [
//     { id: '2023001', name: 'Emma Thompson', email: 'emma.t@school.edu', status: 'present', notes: '' },
//     { id: '2023002', name: 'Liam Johnson', email: 'liam.j@school.edu', status: 'present', notes: '' },
//     { id: '2023003', name: 'Olivia Davis', email: 'olivia.d@school.edu', status: 'present', notes: '' },
//     { id: '2023004', name: 'Noah Wilson', email: 'noah.w@school.edu', status: 'absent', notes: 'Medical appointment' },
//     { id: '2023005', name: 'Ava Martinez', email: 'ava.m@school.edu', status: 'present', notes: '' },
//     { id: '2023006', name: 'Ethan Brown', email: 'ethan.b@school.edu', status: 'present', notes: '' },
//     { id: '2023007', name: 'Sophia Taylor', email: 'sophia.t@school.edu', status: 'present', notes: '' },
//     { id: '2023008', name: 'Mason Anderson', email: 'mason.a@school.edu', status: 'late', notes: 'Arrived 15 minutes late' },
//     { id: '2023009', name: 'Isabella Thomas', email: 'isabella.t@school.edu', status: 'present', notes: '' },
//     { id: '2023010', name: 'James Jackson', email: 'james.j@school.edu', status: 'present', notes: '' },
//   ]
// },
// {
//   id: 'sci101b',
//   name: 'Science 101 - Section B',
//   icon: '🧪',
//   term: 'Fall Semester 2023',
//   studentCount: 25,
//   presentCount: 23,
//   attendanceSummary: {
//     present: 23,
//     absent: 1,
//     late: 1,
//     excused: 0
//   },
//   students: [
//     { id: '2023011', name: 'William Garcia', email: 'william.g@school.edu', status: 'present', notes: '' },
//     { id: '2023012', name: 'Charlotte Rodriguez', email: 'charlotte.r@school.edu', status: 'present', notes: '' },
//     { id: '2023013', name: 'Benjamin Lee', email: 'benjamin.l@school.edu', status: 'present', notes: '' },
//     { id: '2023014', name: 'Amelia Hernandez', email: 'amelia.h@school.edu', status: 'absent', notes: 'Family emergency' },
//     { id: '2023015', name: 'Lucas Gonzalez', email: 'lucas.g@school.edu', status: 'present', notes: '' },
//     { id: '2023016', name: 'Mia Wilson', email: 'mia.w@school.edu', status: 'present', notes: '' },
//     { id: '2023017', name: 'Henry Lopez', email: 'henry.l@school.edu', status: 'present', notes: '' },
//     { id: '2023018', name: 'Evelyn Clark', email: 'evelyn.c@school.edu', status: 'late', notes: 'Bus delay' },
//     { id: '2023019', name: 'Alexander Lewis', email: 'alexander.l@school.edu', status: 'present', notes: '' },
//     { id: '2023020', name: 'Harper Young', email: 'harper.y@school.edu', status: 'present', notes: '' },
//   ]
// },
// {
//   id: 'bio202',
//   name: 'Biology 202',
//   icon: '🧬',
//   term: 'Fall Semester 2023',
//   studentCount: 22,
//   presentCount: 20,
//   attendanceSummary: {
//     present: 20,
//     absent: 1,
//     late: 0,
//     excused: 1
//   },
//   students: [
//     { id: '2023021', name: 'Daniel Moore', email: 'daniel.m@school.edu', status: 'present', notes: '' },
//     { id: '2023022', name: 'Scarlett White', email: 'scarlett.w@school.edu', status: 'present', notes: '' },
//     { id: '2023023', name: 'Michael Harris', email: 'michael.h@school.edu', status: 'present', notes: '' },
//     { id: '2023024', name: 'Victoria Martin', email: 'victoria.m@school.edu', status: 'excused', notes: 'Doctor appointment' },
//     { id: '2023025', name: 'Joseph Thompson', email: 'joseph.t@school.edu', status: 'present', notes: '' },
//     { id: '2023026', name: 'Penelope Garcia', email: 'penelope.g@school.edu', status: 'present', notes: '' },
//     { id: '2023027', name: 'David Martinez', email: 'david.m@school.edu', status: 'present', notes: '' },
//     { id: '2023028', name: 'Elizabeth Robinson', email: 'elizabeth.r@school.edu', status: 'absent', notes: 'No notification' },
//   ]
// },
// {
//   id: 'chem101',
//   name: 'Chemistry 101',
//   icon: '⚗️',
//   term: 'Fall Semester 2023',
//   studentCount: 24,
//   presentCount: 22,
//   attendanceSummary: {
//     present: 22,
//     absent: 1,
//     late: 1,
//     excused: 0
//   },
//   students: [
//     { id: '2023031', name: 'Samuel Walker', email: 'samuel.w@school.edu', status: 'present', notes: '' },
//     { id: '2023032', name: 'Grace Allen', email: 'grace.a@school.edu', status: 'present', notes: '' },
//     { id: '2023033', name: 'Andrew Scott', email: 'andrew.s@school.edu', status: 'present', notes: '' },
//     { id: '2023034', name: 'Zoe Green', email: 'zoe.g@school.edu', status: 'late', notes: 'Traffic delay' },
//     { id: '2023035', name: 'Christopher Baker', email: 'christopher.b@school.edu', status: 'present', notes: '' },
//     { id: '2023036', name: 'Lily Nelson', email: 'lily.n@school.edu', status: 'present', notes: '' },
//     { id: '2023037', name: 'Matthew Carter', email: 'matthew.c@school.edu', status: 'present', notes: '' },
//     { id: '2023038', name: 'Chloe Mitchell', email: 'chloe.m@school.edu', status: 'absent', notes: 'Sick' },
//   ]
// }
// ])

// Calculate total students across all sections
const totalStudents = computed(() => {
return classSections.value.reduce((total, section) => total + section.studentCount, 0)
})

// Calculate total present students
const totalPresentStudents = computed(() => {
return classSections.value.reduce((total, section) => total + section.presentCount, 0)
})

// Calculate total absent students
const totalAbsentStudents = computed(() => {
return classSections.value.reduce((total, section) =>
  total + section.attendanceSummary.absent + section.attendanceSummary.excused, 0)
})

// Calculate total late students
const totalLateStudents = computed(() => {
return classSections.value.reduce((total, section) => total + section.attendanceSummary.late, 0)
})

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

// We'll no longer dispatch an event here to avoid the circular reference
// This was causing the infinite loop
}

// Find section by ID
function findSectionById(id) {
return classSections.value.find(section => section.id === id) || null;
}

function openAddStudentModal() {
if (!currentSection.value) return;

// Reset form
Object.assign(newStudent, {
  id: generateStudentId(),
  name: '',
  email: '',
  status: 'present',
  notes: ''
});
showAddStudentModal.value = true;
}

function addStudent() {
if (!currentSection.value) return;

// Add the new student to the current section
currentSection.value.students.push({...newStudent});
currentSection.value.studentCount = currentSection.value.students.length;
currentSection.value.presentCount = currentSection.value.students.filter(s => s.status === 'present').length;

// Update attendance summary
updateAttendanceSummary();

showAddStudentModal.value = false;
}

function updateAttendanceSummary() {
if (!currentSection.value) return;

const students = currentSection.value.students;
currentSection.value.attendanceSummary = {
  present: students.filter(s => s.status === 'present').length,
  absent: students.filter(s => s.status === 'absent').length,
  late: students.filter(s => s.status === 'late').length,
  excused: students.filter(s => s.status === 'excused').length
};

currentSection.value.presentCount = currentSection.value.attendanceSummary.present;
}

function updateStudentStatus(student, newStatus) {
student.status = newStatus;
updateAttendanceSummary();
}

function saveAttendance() {
// In a real application, this would send the data to a server
alert('Attendance saved successfully!');
}

function generateStudentId() {
const year = new Date().getFullYear();
const randomNum = Math.floor(Math.random() * 1000).toString().padStart(3, '0');
return `${year}${randomNum}`;
}

// Listen for item changes from the sidebar
const handleItemChange = (event) => {
if (event.detail && event.detail.source === 'sidebar') {
  const itemId = event.detail.itemId;
  const itemType = event.detail.itemType;

  // For professors, the itemType will be 'section' or undefined
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

// Lifecycle hooks
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
<Head title="Attendance Management" />

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
      <p class="text-gray-500 dark:text-gray-400 mb-6 max-w-md">Please select a class section from the sidebar or use the dropdown menu to view class-specific information.</p>
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

    <!-- Dashboard Page -->
    <div v-if="currentSection">
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        <!-- Total Students Card -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
          <div class="flex items-center justify-between mb-4">
            <h3 class="font-medium">Total Students</h3>
          </div>
          <div class="flex items-center space-x-4">
            <div class="w-16 h-16 rounded-full flex items-center justify-center bg-blue-100 dark:bg-blue-900 text-blue-500">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
              </svg>
            </div>
            <div>
              <p class="text-sm text-gray-500 dark:text-gray-400">Registered Students</p>
              <p class="text-2xl font-bold">{{ totalStudents }}</p>
            </div>
          </div>
        </div>

        <!-- Present Students Card -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
          <div class="flex items-center justify-between mb-4">
            <h3 class="font-medium">Present</h3>
          </div>
          <div class="flex items-center space-x-4">
            <div class="w-16 h-16 rounded-full flex items-center justify-center bg-green-100 dark:bg-green-900 text-green-500">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
              </svg>
            </div>
            <div>
              <p class="text-sm text-gray-500 dark:text-gray-400">Present Students</p>
              <p class="text-2xl font-bold">{{ totalPresentStudents }}</p>
            </div>
          </div>
        </div>

        <!-- Absent Students Card -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
          <div class="flex items-center justify-between mb-4">
            <h3 class="font-medium">Absent</h3>
          </div>
          <div class="flex items-center space-x-4">
            <div class="w-16 h-16 rounded-full flex items-center justify-center bg-red-100 dark:bg-red-900 text-red-500">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </div>
            <div>
              <p class="text-sm text-gray-500 dark:text-gray-400">Absent Students</p>
              <p class="text-2xl font-bold">{{ totalAbsentStudents }}</p>
            </div>
          </div>
        </div>

        <!-- Late Students Card -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
          <div class="flex items-center justify-between mb-4">
            <h3 class="font-medium">Late</h3>
          </div>
          <div class="flex items-center space-x-4">
            <div class="w-16 h-16 rounded-full flex items-center justify-center bg-yellow-100 dark:bg-yellow-900 text-yellow-500">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <div>
              <p class="text-sm text-gray-500 dark:text-gray-400">Late Students</p>
              <p class="text-2xl font-bold">{{ totalLateStudents }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Class Selection -->
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-6">
        <div class="flex justify-between items-center mb-4">
          <h3 class="font-medium">Select Class Section</h3>
          <div class="relative">
            <button
              @click="showSectionDropdown = !showSectionDropdown"
              class="flex items-center space-x-1 px-3 py-1.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md text-sm"
            >
              <span>{{ currentSection ? currentSection.name : 'Select Section' }}</span>
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
              </svg>
            </button>
            <div
              v-if="showSectionDropdown"
              class="absolute right-0 mt-1 w-56 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-md shadow-lg z-10"
            >
              <div class="p-2">
                <div class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-2 px-2">SELECT CLASS</div>
                <button
                  v-for="section in classSections"
                  :key="section.id"
                  @click="selectSection(section); showSectionDropdown = false"
                  class="w-full flex items-center space-x-2 p-2 rounded-md text-left text-sm hover:bg-gray-100 dark:hover:bg-gray-700"
                >
                  <span class="text-lg">{{ section.icon }}</span>
                  <span>{{ section.name }}</span>
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Attendance Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
          <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Present</p>
                <p class="text-2xl font-bold">{{ currentSection.attendanceSummary.present }}</p>
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
                <p class="text-2xl font-bold">{{ currentSection.attendanceSummary.absent }}</p>
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
                <p class="text-2xl font-bold">{{ currentSection.attendanceSummary.late }}</p>
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
                <p class="text-2xl font-bold">{{ currentSection.attendanceSummary.excused }}</p>
              </div>
              <div class="w-10 h-10 rounded-full bg-blue-100 dark:bg-blue-900 flex items-center justify-center text-blue-600 dark:text-blue-400">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Attendance Records -->
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden mb-6">
        <div class="p-6 border-b dark:border-gray-700 flex justify-between items-center">
          <div>
            <h3 class="font-medium">Attendance Records</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400">Class: {{ currentSection.name }} - Today's Date: {{ currentDate }}</p>
          </div>
          <div class="flex space-x-2">
            <button @click="saveAttendance" class="px-3 py-1 bg-green-500 text-white rounded-md text-sm">Save Attendance</button>
            <button @click="openAddStudentModal" class="px-3 py-1 bg-blue-500 text-white rounded-md text-sm">Add Student</button>
          </div>
        </div>
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-700">
              <tr>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Student</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">ID</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Notes</th>
              </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
              <tr v-for="(student, index) in currentSection.students" :key="index">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <div class="flex-shrink-0 h-10 w-10">
                      <img class="h-10 w-10 rounded-full" :src="`https://i.pravatar.cc/100?img=${index + 10}`" alt="" />
                    </div>
                    <div class="ml-4">
                      <div class="text-sm font-medium">{{ student.name }}</div>
                      <div class="text-sm text-gray-500 dark:text-gray-400">{{ student.email }}</div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm">{{ student.id }}</td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <select
                    v-model="student.status"
                    @change="updateStudentStatus(student, student.status)"
                    class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm focus:ring-primary-500 focus:border-primary-500"
                  >
                    <option value="present">Present</option>
                    <option value="absent">Absent</option>
                    <option value="late">Late</option>
                    <option value="excused">Excused</option>
                  </select>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <input
                    type="text"
                    v-model="student.notes"
                    placeholder="Add notes..."
                    class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm focus:ring-primary-500 focus:border-primary-500 w-full"
                  />
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <div class="p-4 border-t dark:border-gray-700 flex items-center justify-between">
          <div class="text-sm text-gray-500 dark:text-gray-400">
            Showing <span class="font-medium">1</span> to <span class="font-medium">{{ currentSection.students.length }}</span> of <span class="font-medium">{{ currentSection.studentCount }}</span> students
          </div>
        </div>
      </div>

      <!-- Attendance Chart -->
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <h3 class="font-medium mb-4">Attendance Overview</h3>
        <div class="h-64 w-full">
          <div class="h-full w-full flex items-center justify-center bg-gray-100 dark:bg-gray-700 rounded-lg">
            <div class="grid grid-cols-4 gap-4 w-full px-8">
              <div class="flex flex-col items-center">
                <div class="h-32 w-12 bg-green-500 rounded-t-lg relative overflow-hidden">
                  <div class="absolute bottom-0 w-full bg-green-600" :style="`height: ${currentSection.attendanceSummary.present / currentSection.studentCount * 100}%`"></div>
                </div>
                <p class="mt-2 font-medium">Present</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ currentSection.attendanceSummary.present }} students</p>
              </div>
              <div class="flex flex-col items-center">
                <div class="h-32 w-12 bg-red-500 rounded-t-lg relative overflow-hidden">
                  <div class="absolute bottom-0 w-full bg-red-600" :style="`height: ${currentSection.attendanceSummary.absent / currentSection.studentCount * 100}%`"></div>
                </div>
                <p class="mt-2 font-medium">Absent</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ currentSection.attendanceSummary.absent }} students</p>
              </div>
              <div class="flex flex-col items-center">
                <div class="h-32 w-12 bg-yellow-500 rounded-t-lg relative overflow-hidden">
                  <div class="absolute bottom-0 w-full bg-yellow-600" :style="`height: ${currentSection.attendanceSummary.late / currentSection.studentCount * 100}%`"></div>
                </div>
                <p class="mt-2 font-medium">Late</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ currentSection.attendanceSummary.late }} students</p>
              </div>
              <div class="flex flex-col items-center">
                <div class="h-32 w-12 bg-blue-500 rounded-t-lg relative overflow-hidden">
                  <div class="absolute bottom-0 w-full bg-blue-600" :style="`height: ${currentSection.attendanceSummary.excused / currentSection.studentCount * 100}%`"></div>
                </div>
                <p class="mt-2 font-medium">Excused</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ currentSection.attendanceSummary.excused }} students</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Add Student Modal -->
  <div v-if="showAddStudentModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg w-full max-w-md p-6">
      <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-medium">Add New Student</h3>
        <button @click="showAddStudentModal = false" class="text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>
      <form @submit.prevent="addStudent" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Student Name</label>
          <input
            type="text"
            v-model="newStudent.name"
            required
            placeholder="e.g., John Smith"
            class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm focus:ring-primary-500 focus:border-primary-500 w-full"
          />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email</label>
          <input
            type="email"
            v-model="newStudent.email"
            required
            placeholder="e.g., john.smith@school.edu"
            class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm focus:ring-primary-500 focus:border-primary-500 w-full"
          />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
          <select
            v-model="newStudent.status"
            class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm focus:ring-primary-500 focus:border-primary-500 w-full"
          >
            <option value="present">Present</option>
            <option value="absent">Absent</option>
            <option value="late">Late</option>
            <option value="excused">Excused</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Notes</label>
          <input
            type="text"
            v-model="newStudent.notes"
            placeholder="Add any notes about the student..."
            class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm focus:ring-primary-500 focus:border-primary-500 w-full"
          />
        </div>
        <div class="flex justify-end space-x-2 pt-4">
          <button
            type="button"
            @click="showAddStudentModal = false"
            class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm"
          >
            Cancel
          </button>
          <button
            type="submit"
            class="px-4 py-2 bg-blue-600 text-white rounded-md text-sm"
          >
            Add Student
          </button>
        </div>
      </form>
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
