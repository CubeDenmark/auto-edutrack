<script setup lang="ts">
import AppLayout from "@/layouts/AppLayout.vue";
import { type BreadcrumbItem } from "@/types";
import { Head, usePage } from "@inertiajs/vue3";
import { ref, computed, onMounted, inject, watch } from "vue";

const breadcrumbs: BreadcrumbItem[] = [
{
  title: "Dashboard",
  href: "/dashboard",
}
];

defineProps<{
name?: string;
sectionId?: string;
}>();

// Get the page props to access any data passed from the server
const page = usePage();

// Inject the current item ID from the parent component
const currentItemId = inject('currentItemId', ref(null));

// State variables
const currentSection = ref<any>(null);
const showSectionDropdown = ref(false);


const sections = usePage().props.sections || [];

const classSections = computed(() => {
  return sections.map(section => ({
    id: section.id,
    name: section.name,
    icon: section.icon || '📚',
    term: section.term || 'Current Term',
    studentCount: section.studentCount || 0,
    presentCount: section.presentCount || 0,
    pendingAssignments: section.pendingAssignments || 0,
    toGradeCount: section.toGradeCount || 0,
    averageScore: section.averageScore || 0,
    attendanceSummary: section.attendanceSummary || { present: 0, absent: 0, late: 0, excused: 0 },
    performance: section.performance || { quiz1: 0, midterm: 0, project: 0, quiz2: 0 },
  }));
});

// Calculate total students across all sections
const totalStudents = computed(() => {
return classSections.value.reduce((total, section) => total + section.studentCount, 0);
});

// Calculate total present students
const totalPresentStudents = computed(() => {
return classSections.value.reduce((total, section) => total + section.presentCount, 0);
});

// Calculate total pending assignments
const totalPendingAssignments = computed(() => {
return classSections.value.reduce((total, section) => total + section.pendingAssignments, 0);
});

// Calculate total items to grade
const totalToGradeCount = computed(() => {
return classSections.value.reduce((total, section) => total + section.toGradeCount, 0);
});

// Find section by ID
function findSectionById(id) {
return classSections.value.find(section => section.id === id) || null;
}

// Update the current section
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

  // Dispatch an event to notify other components (like the sidebar)
  // This is the key addition to make the sidebar highlight the selected section
  window.dispatchEvent(new CustomEvent('section-changed', {
    detail: {
      sectionId: section.id,
      source: 'dashboard'
    }
  }));

  // Also dispatch the item-changed event for newer sidebar implementations
  window.dispatchEvent(new CustomEvent('item-changed', {
    detail: {
      itemId: section.id,
      itemType: 'section',
      source: 'dashboard'
    }
  }));
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

// Function to handle section-changed events
const handleSectionChange = (event) => {
  if (event.detail && event.detail.sectionId && event.detail.source !== 'dashboard') {
    const section = findSectionById(event.detail.sectionId);
    if (section && (!currentSection.value || currentSection.value.id !== section.id)) {
      // Only update if it's a different section
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
<Head title="Professor Dashboard" />

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
              <p class="text-2xl font-bold">{{ currentSection.studentCount }}</p>
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
              <p class="text-2xl font-bold">{{ currentSection.presentCount }}</p>
            </div>
          </div>
        </div>

        <!-- Pending Assignments Card -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
          <div class="flex items-center justify-between mb-4">
            <h3 class="font-medium">Pending</h3>
          </div>
          <div class="flex items-center space-x-4">
            <div class="w-16 h-16 rounded-full flex items-center justify-center bg-yellow-100 dark:bg-yellow-900 text-yellow-500">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <div>
              <p class="text-sm text-gray-500 dark:text-gray-400">Pending Assignments</p>
              <p class="text-2xl font-bold">{{ currentSection.pendingAssignments }}</p>
            </div>
          </div>
        </div>

        <!-- To Grade Card -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
          <div class="flex items-center justify-between mb-4">
            <h3 class="font-medium">To Grade</h3>
          </div>
          <div class="flex items-center space-x-4">
            <div class="w-16 h-16 rounded-full flex items-center justify-center bg-purple-100 dark:bg-purple-900 text-purple-500">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
              </svg>
            </div>
            <div>
              <p class="text-sm text-gray-500 dark:text-gray-400">Items to Grade</p>
              <p class="text-2xl font-bold">{{ currentSection.toGradeCount }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Class Selection -->
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-6">
        <div class="flex justify-between items-center mb-4">
          <h3 class="font-medium">Class Information</h3>
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

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <div class="flex items-center mb-4">
              <span class="text-3xl mr-3">{{ currentSection.icon }}</span>
              <div>
                <h4 class="font-medium">{{ currentSection.name }}</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ currentSection.term }}</p>
              </div>
            </div>
            <div class="space-y-2">
              <div class="flex justify-between">
                <span class="text-sm text-gray-500 dark:text-gray-400">Total Students:</span>
                <span class="text-sm font-medium">{{ currentSection.studentCount }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-sm text-gray-500 dark:text-gray-400">Present Today:</span>
                <span class="text-sm font-medium">{{ currentSection.presentCount }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-sm text-gray-500 dark:text-gray-400">Pending Assignments:</span>
                <span class="text-sm font-medium">{{ currentSection.pendingAssignments }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-sm text-gray-500 dark:text-gray-400">Items to Grade:</span>
                <span class="text-sm font-medium">{{ currentSection.toGradeCount }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-sm text-gray-500 dark:text-gray-400">Average Score:</span>
                <span class="text-sm font-medium">{{ currentSection.averageScore }}%</span>
              </div>
            </div>
          </div>
          <div>
            <h4 class="font-medium mb-4">Attendance Summary</h4>
            <div class="space-y-4">
              <div class="relative pt-1">
                <div class="flex mb-2 items-center justify-between">
                  <div>
                    <span class="text-xs font-semibold inline-block py-1 px-2 uppercase rounded-full bg-green-200 text-green-800">
                      Present
                    </span>
                  </div>
                  <div class="text-right">
                    <span class="text-xs font-semibold inline-block text-green-800">
                      {{ Math.round(currentSection.attendanceSummary.present / currentSection.studentCount * 100) }}%
                    </span>
                  </div>
                </div>
                <div class="overflow-hidden h-2 mb-4 text-xs flex rounded bg-green-200">
                  <div :style="`width: ${currentSection.attendanceSummary.present / currentSection.studentCount * 100}%`" class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-green-500"></div>
                </div>
              </div>
              <div class="relative pt-1">
                <div class="flex mb-2 items-center justify-between">
                  <div>
                    <span class="text-xs font-semibold inline-block py-1 px-2 uppercase rounded-full bg-red-200 text-red-800">
                      Absent
                    </span>
                  </div>
                  <div class="text-right">
                    <span class="text-xs font-semibold inline-block text-red-800">
                      {{ Math.round(currentSection.attendanceSummary.absent / currentSection.studentCount * 100) }}%
                    </span>
                  </div>
                </div>
                <div class="overflow-hidden h-2 mb-4 text-xs flex rounded bg-red-200">
                  <div :style="`width: ${currentSection.attendanceSummary.absent / currentSection.studentCount * 100}%`" class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-red-500"></div>
                </div>
              </div>
              <div class="relative pt-1">
                <div class="flex mb-2 items-center justify-between">
                  <div>
                    <span class="text-xs font-semibold inline-block py-1 px-2 uppercase rounded-full bg-yellow-200 text-yellow-800">
                      Late
                    </span>
                  </div>
                  <div class="text-right">
                    <span class="text-xs font-semibold inline-block text-yellow-800">
                      {{ Math.round(currentSection.attendanceSummary.late / currentSection.studentCount * 100) }}%
                    </span>
                  </div>
                </div>
                <div class="overflow-hidden h-2 mb-4 text-xs flex rounded bg-yellow-200">
                  <div :style="`width: ${currentSection.attendanceSummary.late / currentSection.studentCount * 100}%`" class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-yellow-500"></div>
                </div>
              </div>
              <div class="relative pt-1">
                <div class="flex mb-2 items-center justify-between">
                  <div>
                    <span class="text-xs font-semibold inline-block py-1 px-2 uppercase rounded-full bg-blue-200 text-blue-800">
                      Excused
                    </span>
                  </div>
                  <div class="text-right">
                    <span class="text-xs font-semibold inline-block text-blue-800">
                      {{ Math.round(currentSection.attendanceSummary.excused / currentSection.studentCount * 100) }}%
                    </span>
                  </div>
                </div>
                <div class="overflow-hidden h-2 mb-4 text-xs flex rounded bg-blue-200">
                  <div :style="`width: ${currentSection.attendanceSummary.excused / currentSection.studentCount * 100}%`" class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-blue-500"></div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Performance Chart -->
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-6">
        <h3 class="font-medium mb-4">Class Performance</h3>
        <div class="h-64 w-full">
          <div class="h-full w-full flex items-center justify-center bg-gray-100 dark:bg-gray-700 rounded-lg">
            <div class="grid grid-cols-4 gap-4 w-full px-8">
              <div class="flex flex-col items-center">
                <div class="h-32 w-12 bg-blue-500 rounded-t-lg relative overflow-hidden">
                  <div class="absolute bottom-0 w-full bg-blue-600" :style="`height: ${currentSection.performance.quiz1}%`"></div>
                </div>
                <p class="mt-2 font-medium">Quiz 1</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ currentSection.performance.quiz1 }}%</p>
              </div>
              <div class="flex flex-col items-center">
                <div class="h-32 w-12 bg-purple-500 rounded-t-lg relative overflow-hidden">
                  <div class="absolute bottom-0 w-full bg-purple-600" :style="`height: ${currentSection.performance.midterm}%`"></div>
                </div>
                <p class="mt-2 font-medium">Midterm</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ currentSection.performance.midterm }}%</p>
              </div>
              <div class="flex flex-col items-center">
                <div class="h-32 w-12 bg-yellow-500 rounded-t-lg relative overflow-hidden">
                  <div class="absolute bottom-0 w-full bg-yellow-600" :style="`height: ${currentSection.performance.project}%`"></div>
                </div>
                <p class="mt-2 font-medium">Project</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ currentSection.performance.project }}%</p>
              </div>
              <div class="flex flex-col items-center">
                <div class="h-32 w-12 bg-green-500 rounded-t-lg relative overflow-hidden">
                  <div class="absolute bottom-0 w-full bg-green-600" :style="`height: ${currentSection.performance.quiz2}%`"></div>
                </div>
                <p class="mt-2 font-medium">Quiz 2</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ currentSection.performance.quiz2 }}%</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Quick Actions -->
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <h3 class="font-medium mb-4">Quick Actions</h3>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <a href="/attendance" class="flex flex-col items-center p-4 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700">
            <div class="w-12 h-12 rounded-full flex items-center justify-center bg-green-100 dark:bg-green-900 text-green-500 mb-2">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
              </svg>
            </div>
            <span class="text-sm font-medium">Take Attendance</span>
          </a>
          <a href="/grades" class="flex flex-col items-center p-4 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700">
            <div class="w-12 h-12 rounded-full flex items-center justify-center bg-purple-100 dark:bg-purple-900 text-purple-500 mb-2">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
              </svg>
            </div>
            <span class="text-sm font-medium">Grade Assignments</span>
          </a>
          <a href="/behavior-reports" class="flex flex-col items-center p-4 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700">
            <div class="w-12 h-12 rounded-full flex items-center justify-center bg-yellow-100 dark:bg-yellow-900 text-yellow-500 mb-2">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
              </svg>
            </div>
            <span class="text-sm font-medium">Behavior Reports</span>
          </a>
          <a href="/students" class="flex flex-col items-center p-4 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700">
            <div class="w-12 h-12 rounded-full flex items-center justify-center bg-blue-100 dark:bg-blue-900 text-blue-500 mb-2">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
              </svg>
            </div>
            <span class="text-sm font-medium">Student Profiles</span>
          </a>
        </div>
      </div>
    </div>
  </div>
</AppLayout>
</template>
<!--
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
</style> -->
