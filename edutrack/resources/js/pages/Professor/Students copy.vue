<script setup lang="ts">
import AppLayout from "@/layouts/AppLayout.vue";
import { type BreadcrumbItem } from "@/types";
import { Head, usePage } from "@inertiajs/vue3";
import { ref, computed, onMounted, inject, watch } from "vue";

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: "Students",
    href: "/students",
  }
];

defineProps<{
  name?: string;
  sectionId?: string;
}>();

// Get the page props to access any data passed from the server
const page = usePage();

// Inject the current item ID from the parent component (from sidebar)
const currentItemId = inject('currentItemId', ref(null));

// State variables
const currentSection = ref<any>(null);
const showAddStudentModal = ref(false);
const searchQuery = ref('');
const sortBy = ref('name');
const sortOrder = ref('asc');

// Form states for new student
const newStudent = ref({
  id: '',
  name: '',
  email: '',
  status: 'active',
  notes: ''
});

// Class sections data
const classSections = ref([
  {
    id: 'sci101a',
    name: 'Science 101 - Section A',
    icon: '🧪',
    term: 'Fall Semester 2023',
    studentCount: 28,
    presentCount: 26,
    students: [
      { id: '2023001', name: 'Emma Thompson', email: 'emma.t@school.edu', status: 'present', notes: '', score: 92, gradeComment: 'Excellent work!' },
      { id: '2023002', name: 'Liam Johnson', email: 'liam.j@school.edu', status: 'present', notes: '', score: 85, gradeComment: 'Good effort' },
      { id: '2023003', name: 'Olivia Davis', email: 'olivia.d@school.edu', status: 'present', notes: '', score: 78, gradeComment: 'Needs improvement in section 3' },
      { id: '2023004', name: 'Noah Wilson', email: 'noah.w@school.edu', status: 'absent', notes: 'Medical appointment', score: 0, gradeComment: 'Absent - needs to make up exam' },
      { id: '2023005', name: 'Ava Martinez', email: 'ava.m@school.edu', status: 'present', notes: '', score: 95, gradeComment: 'Outstanding performance!' },
      { id: '2023006', name: 'Ethan Brown', email: 'ethan.b@school.edu', status: 'present', notes: '', score: 82, gradeComment: '' },
      { id: '2023007', name: 'Sophia Taylor', email: 'sophia.t@school.edu', status: 'present', notes: '', score: 88, gradeComment: '' },
      { id: '2023008', name: 'Mason Anderson', email: 'mason.a@school.edu', status: 'late', notes: 'Arrived 15 minutes late', score: 79, gradeComment: 'Good work but needs more detail' },
      { id: '2023009', name: 'Isabella Thomas', email: 'isabella.t@school.edu', status: 'present', notes: '', score: 91, gradeComment: '' },
      { id: '2023010', name: 'James Jackson', email: 'james.j@school.edu', status: 'present', notes: '', score: 84, gradeComment: '' },
    ]
  },
  {
    id: 'sci101b',
    name: 'Science 101 - Section B',
    icon: '🧪',
    term: 'Fall Semester 2023',
    studentCount: 25,
    presentCount: 23,
    students: [
      { id: '2023011', name: 'William Garcia', email: 'william.g@school.edu', status: 'present', notes: '', score: 88, gradeComment: 'Good work!' },
      { id: '2023012', name: 'Charlotte Rodriguez', email: 'charlotte.r@school.edu', status: 'present', notes: '', score: 79, gradeComment: 'Needs improvement' },
      { id: '2023013', name: 'Benjamin Lee', email: 'benjamin.l@school.edu', status: 'present', notes: '', score: 92, gradeComment: 'Excellent work!' },
      { id: '2023014', name: 'Amelia Hernandez', email: 'amelia.h@school.edu', status: 'absent', notes: 'Family emergency', score: 0, gradeComment: 'Absent - needs to make up exam' },
      { id: '2023015', name: 'Lucas Gonzalez', email: 'lucas.g@school.edu', status: 'present', notes: '', score: 85, gradeComment: 'Good effort' },
      { id: '2023016', name: 'Mia Wilson', email: 'mia.w@school.edu', status: 'present', notes: '', score: 77, gradeComment: 'Needs to focus more on section 2' },
      { id: '2023017', name: 'Henry Lopez', email: 'henry.l@school.edu', status: 'present', notes: '', score: 81, gradeComment: '' },
      { id: '2023018', name: 'Evelyn Clark', email: 'evelyn.c@school.edu', status: 'late', notes: 'Bus delay', score: 83, gradeComment: '' },
      { id: '2023019', name: 'Alexander Lewis', email: 'alexander.l@school.edu', status: 'present', notes: '', score: 90, gradeComment: 'Very thorough work' },
      { id: '2023020', name: 'Harper Young', email: 'harper.y@school.edu', status: 'present', notes: '', score: 76, gradeComment: 'Needs to improve lab technique' },
    ]
  },
  {
    id: 'bio202',
    name: 'Biology 202',
    icon: '🧬',
    term: 'Fall Semester 2023',
    studentCount: 22,
    presentCount: 20,
    students: [
      { id: '2023021', name: 'Daniel Moore', email: 'daniel.m@school.edu', status: 'present', notes: '', score: 88, gradeComment: 'Good work!' },
      { id: '2023022', name: 'Scarlett White', email: 'scarlett.w@school.edu', status: 'present', notes: '', score: 92, gradeComment: 'Excellent analysis' },
      { id: '2023023', name: 'Michael Harris', email: 'michael.h@school.edu', status: 'present', notes: '', score: 79, gradeComment: 'Needs more detail in responses' },
      { id: '2023024', name: 'Victoria Martin', email: 'victoria.m@school.edu', status: 'excused', notes: 'Doctor appointment', score: 0, gradeComment: 'Excused absence - will make up exam' },
      { id: '2023025', name: 'Joseph Thompson', email: 'joseph.t@school.edu', status: 'present', notes: '', score: 85, gradeComment: 'Good understanding of concepts' },
      { id: '2023026', name: 'Penelope Garcia', email: 'penelope.g@school.edu', status: 'present', notes: '', score: 90, gradeComment: 'Excellent work!' },
      { id: '2023027', name: 'David Martinez', email: 'david.m@school.edu', status: 'present', notes: '', score: 77, gradeComment: 'Needs improvement in section 3' },
      { id: '2023028', name: 'Elizabeth Robinson', email: 'elizabeth.r@school.edu', status: 'absent', notes: 'No notification', score: 0, gradeComment: 'Absent - needs to make up exam' },
    ]
  },
  {
    id: 'chem101',
    name: 'Chemistry 101',
    icon: '⚗️',
    term: 'Fall Semester 2023',
    studentCount: 24,
    presentCount: 22,
    students: [
      { id: '2023031', name: 'Samuel Walker', email: 'samuel.w@school.edu', status: 'present', notes: '', score: 83, gradeComment: 'Good work!' },
      { id: '2023032', name: 'Grace Allen', email: 'grace.a@school.edu', status: 'present', notes: '', score: 77, gradeComment: 'Needs improvement in calculations' },
      { id: '2023033', name: 'Andrew Scott', email: 'andrew.s@school.edu', status: 'present', notes: '', score: 91, gradeComment: 'Excellent lab work!' },
      { id: '2023034', name: 'Zoe Green', email: 'zoe.g@school.edu', status: 'late', notes: 'Traffic delay', score: 75, gradeComment: 'Needs to review chapter 4' },
      { id: '2023035', name: 'Christopher Baker', email: 'christopher.b@school.edu', status: 'present', notes: '', score: 88, gradeComment: 'Good understanding of concepts' },
      { id: '2023036', name: 'Lily Nelson', email: 'lily.n@school.edu', status: 'present', notes: '', score: 79, gradeComment: '' },
      { id: '2023037', name: 'Matthew Carter', email: 'matthew.c@school.edu', status: 'present', notes: '', score: 82, gradeComment: '' },
      { id: '2023038', name: 'Chloe Mitchell', email: 'chloe.m@school.edu', status: 'absent', notes: 'Sick', score: 0, gradeComment: 'Absent - needs to make up exam' },
    ]
  }
]);

// Find section by ID
function findSectionById(id) {
  return classSections.value.find(section => section.id === id) || null;
}

// Update the current section
function updateCurrentSection(sectionId) {
  if (sectionId) {
    const section = findSectionById(sectionId);
    if (section) {
      currentSection.value = section;

      // Update URL with the selected section ID without navigating
      const url = new URL(window.location.href);
      url.searchParams.set('sectionId', sectionId);
      window.history.replaceState({}, '', url.toString());
    }
  }
}

// Generate a new student ID
function generateStudentId() {
  const year = new Date().getFullYear();
  const randomNum = Math.floor(Math.random() * 1000).toString().padStart(3, '0');
  return `${year}${randomNum}`;
}

// Open add student modal
function openAddStudentModal() {
  if (!currentSection.value) return;

  // Reset form
  newStudent.value = {
    id: generateStudentId(),
    name: '',
    email: '',
    status: 'active',
    notes: ''
  };

  showAddStudentModal.value = true;
}

// Add a new student
function addStudent() {
  if (!currentSection.value) return;

  // Add the new student to the current section
  currentSection.value.students.push({
    ...newStudent.value,
    score: 0,
    gradeComment: ''
  });

  // Update student count
  currentSection.value.studentCount = currentSection.value.students.length;

  showAddStudentModal.value = false;
}

// Computed property for filtered students
const filteredStudents = computed(() => {
  if (!currentSection.value) return [];

  let students = [...currentSection.value.students];

  // Apply search filter
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase();
    students = students.filter(student =>
      student.name.toLowerCase().includes(query) ||
      student.email.toLowerCase().includes(query) ||
      student.id.toLowerCase().includes(query)
    );
  }

  // Apply sorting
  students.sort((a, b) => {
    let comparison = 0;

    if (sortBy.value === 'name') {
      comparison = a.name.localeCompare(b.name);
    } else if (sortBy.value === 'id') {
      comparison = a.id.localeCompare(b.id);
    } else if (sortBy.value === 'score') {
      comparison = a.score - b.score;
    }

    return sortOrder.value === 'asc' ? comparison : -comparison;
  });

  return students;
});

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
  <Head title="Students Management" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-6 pt-20">
      <!-- No Section Selected Message -->
      <div v-if="!currentSection" class="flex flex-col items-center justify-center h-[calc(100vh-10rem)] text-center">
        <div class="bg-gray-100 dark:bg-gray-800 p-6 rounded-full mb-4">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
          </svg>
        </div>
        <h2 class="text-2xl font-bold mb-2">Select a Class Section</h2>
        <p class="text-gray-500 dark:text-gray-400 mb-6 max-w-md">Please select a class section from the sidebar to view and manage students.</p>
      </div>

      <!-- Students Management Page -->
      <div v-if="currentSection">
        <!-- Header with stats -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
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
                <p class="text-sm text-gray-500 dark:text-gray-400">Enrolled Students</p>
                <p class="text-2xl font-bold">{{ currentSection.studentCount }}</p>
              </div>
            </div>
          </div>

          <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex items-center justify-between mb-4">
              <h3 class="font-medium">Class Average</h3>
            </div>
            <div class="flex items-center space-x-4">
              <div class="w-16 h-16 rounded-full flex items-center justify-center bg-green-100 dark:bg-green-900 text-green-500">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
              </div>
              <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Average Score</p>
                <p class="text-2xl font-bold">
                  {{
                    Math.round(
                      currentSection.students
                        .filter(s => s.score > 0)
                        .reduce((sum, s) => sum + s.score, 0) /
                      currentSection.students.filter(s => s.score > 0).length
                    )
                  }}%
                </p>
              </div>
            </div>
          </div>

          <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex items-center justify-between mb-4">
              <h3 class="font-medium">Attendance</h3>
            </div>
            <div class="flex items-center space-x-4">
              <div class="w-16 h-16 rounded-full flex items-center justify-center bg-purple-100 dark:bg-purple-900 text-purple-500">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
              <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Present Today</p>
                <p class="text-2xl font-bold">{{ currentSection.presentCount }}/{{ currentSection.studentCount }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Student List -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden mb-6">
          <div class="p-6 border-b dark:border-gray-700 flex justify-between items-center">
            <div>
              <h3 class="font-medium">Student List</h3>
              <p class="text-sm text-gray-500 dark:text-gray-400">Class: {{ currentSection.name }} - {{ currentSection.term }}</p>
            </div>
            <div class="flex space-x-2">
              <button @click="openAddStudentModal" class="px-3 py-1 bg-primary-600 text-white rounded-md text-sm flex items-center gap-1">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Add Student
              </button>
              <button class="px-3 py-1 bg-gray-200 dark:bg-gray-700 rounded-md text-sm">Export List</button>
            </div>
          </div>

          <!-- Search and Filter -->
          <div class="p-4 bg-gray-50 dark:bg-gray-700 border-b dark:border-gray-600">
            <div class="flex flex-wrap items-center gap-4">
              <div class="flex-1">
                <input
                  type="text"
                  v-model="searchQuery"
                  placeholder="Search students..."
                  class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 text-sm focus:ring-primary-500 focus:border-primary-500 w-full"
                />
              </div>
              <div class="flex items-center gap-2">
                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Sort by:</label>
                <select
                  v-model="sortBy"
                  class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 text-sm focus:ring-primary-500 focus:border-primary-500"
                >
                  <option value="name">Name</option>
                  <option value="id">ID</option>
                  <option value="score">Score</option>
                </select>
                <button
                  @click="sortOrder = sortOrder === 'asc' ? 'desc' : 'asc'"
                  class="p-1 rounded-md hover:bg-gray-200 dark:hover:bg-gray-600"
                >
                  <svg v-if="sortOrder === 'asc'" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12" />
                  </svg>
                  <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h13M3 8h9m-9 4h9m5-4v12m0 0l-4-4m4 4l4-4" />
                  </svg>
                </button>
              </div>
            </div>
          </div>

          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
              <thead class="bg-gray-50 dark:bg-gray-700">
                <tr>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Student</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">ID</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Score</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                </tr>
              </thead>
              <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                <tr v-for="(student, index) in filteredStudents" :key="student.id">
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
                    <span
                      :class="{
                        'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300': student.status === 'present',
                        'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300': student.status === 'absent',
                        'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300': student.status === 'late',
                        'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300': student.status === 'excused'
                      }"
                      class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                    >
                      {{ student.status.charAt(0).toUpperCase() + student.status.slice(1) }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm">
                    {{ student.score > 0 ? `${student.score}%` : 'N/A' }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    <div class="flex space-x-2">
                      <button class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                      </button>
                      <button class="text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                      </button>
                      <button class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          <div class="p-4 border-t dark:border-gray-700 flex items-center justify-between">
            <div class="text-sm text-gray-500 dark:text-gray-400">
              Showing <span class="font-medium">{{ filteredStudents.length }}</span> of <span class="font-medium">{{ currentSection.studentCount }}</span> students
            </div>
            <div class="flex space-x-2">
              <button class="px-3 py-1 rounded border dark:border-gray-600 text-sm">Previous</button>
              <button class="px-3 py-1 rounded bg-primary-600 text-white text-sm">Next</button>
            </div>
          </div>
        </div>

        <!-- Student Performance Overview -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
          <h3 class="font-medium mb-4">Student Performance Overview</h3>
          <div class="h-64 w-full">
            <div class="h-full w-full flex items-center justify-center bg-gray-100 dark:bg-gray-700 rounded-lg">
              <div class="space-y-4 w-full px-8">
                <!-- Top Performers -->
                <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300">Top Performers</h4>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                  <div v-for="student in filteredStudents.filter(s => s.score > 0).sort((a, b) => b.score - a.score).slice(0, 3)" :key="student.id" class="bg-white dark:bg-gray-800 p-4 rounded-lg border border-gray-200 dark:border-gray-700">
                    <div class="flex items-center space-x-3">
                      <div class="flex-shrink-0 h-10 w-10">
                        <img class="h-10 w-10 rounded-full" :src="`https://i.pravatar.cc/100?img=${Math.floor(Math.random() * 50)}`" alt="" />
                      </div>
                      <div>
                        <div class="font-medium">{{ student.name }}</div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">Score: {{ student.score }}%</div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Students Needing Attention -->
                <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mt-6">Students Needing Attention</h4>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                  <div v-for="student in filteredStudents.filter(s => s.score > 0 && s.score < 70).slice(0, 3)" :key="student.id" class="bg-white dark:bg-gray-800 p-4 rounded-lg border border-gray-200 dark:border-gray-700">
                    <div class="flex items-center space-x-3">
                      <div class="flex-shrink-0 h-10 w-10">
                        <img class="h-10 w-10 rounded-full" :src="`https://i.pravatar.cc/100?img=${Math.floor(Math.random() * 50)}`" alt="" />
                      </div>
                      <div>
                        <div class="font-medium">{{ student.name }}</div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">Score: {{ student.score }}%</div>
                      </div>
                    </div>
                  </div>
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
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Student ID</label>
            <input
              type="text"
              v-model="newStudent.id"
              disabled
              class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm focus:ring-primary-500 focus:border-primary-500 w-full bg-gray-100 dark:bg-gray-600"
            />
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">ID is automatically generated</p>
          </div>
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
            <textarea
              v-model="newStudent.notes"
              placeholder="Add any notes about the student..."
              class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm focus:ring-primary-500 focus:border-primary-500 w-full"
              rows="3"
            ></textarea>
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
              class="px-4 py-2 bg-primary-600 text-white rounded-md text-sm"
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
