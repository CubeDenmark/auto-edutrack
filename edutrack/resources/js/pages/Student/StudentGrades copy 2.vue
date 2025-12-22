<script setup lang="ts">
import { ref, computed, onMounted, watch, inject } from 'vue';
import AppLayout from "@/layouts/AppLayout.vue";
import { type BreadcrumbItem } from "@/types";
import { Head, usePage, Link } from "@inertiajs/vue3";

// Breadcrumbs
const breadcrumbs: BreadcrumbItem[] = [
  {
    title: "Grades",
    href: "/grades",
  },
];

// Get the page props to access any data passed from the server
const page = usePage();

// Inject the current item ID from the sidebar
const currentItemId = inject('currentItemId', ref(null));

// State variables
const currentSection = ref<any>(null);

// Helper function for grade styling
const getGradeClass = (score: number) => {
  if (score >= 90) return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300';
  if (score >= 80) return 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300';
  if (score >= 70) return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300';
  if (score >= 60) return 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-300';
  return 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300';
};

// General grades data (used when no class is selected)
const gradesData = [
  {
    subject: 'Mathematics',
    code: 'MATH101',
    icon: '📐',
    iconBg: 'bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-400',
    teacher: 'Dr. Smith',
    grade: 'A',
    gradeClass: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
    percentage: 92,
    updated: 'Oct 12, 2023'
  },
  {
    subject: 'Science',
    code: 'SCI102',
    icon: '🔬',
    iconBg: 'bg-green-100 dark:bg-green-900 text-green-600 dark:text-green-400',
    teacher: 'Mrs. Johnson',
    grade: 'B+',
    gradeClass: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
    percentage: 88,
    updated: 'Oct 10, 2023'
  },
  {
    subject: 'English',
    code: 'ENG103',
    icon: '📚',
    iconBg: 'bg-yellow-100 dark:bg-yellow-900 text-yellow-600 dark:text-yellow-400',
    teacher: 'Mr. Williams',
    grade: 'C+',
    gradeClass: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
    percentage: 78,
    updated: 'Oct 14, 2023'
  },
  {
    subject: 'History',
    code: 'HIST104',
    icon: '🏛️',
    iconBg: 'bg-purple-100 dark:bg-purple-900 text-purple-600 dark:text-purple-400',
    teacher: 'Ms. Davis',
    grade: 'B',
    gradeClass: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
    percentage: 85,
    updated: 'Oct 8, 2023'
  },
  {
    subject: 'Physical Education',
    code: 'PE105',
    icon: '🏀',
    iconBg: 'bg-red-100 dark:bg-red-900 text-red-600 dark:text-red-400',
    teacher: 'Coach Brown',
    grade: 'A-',
    gradeClass: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
    percentage: 90,
    updated: 'Oct 5, 2023'
  },
];

// Class sections data - same as in the other views
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
    gradeItems: [
      {
        icon: '📝',
        iconBg: 'bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-400',
        title: 'Lab Report: Chemical Reactions',
        description: 'Analysis of chemical reaction experiments',
        type: 'Assignment',
        score: 94,
        date: 'Oct 12, 2023'
      },
      {
        icon: '📊',
        iconBg: 'bg-green-100 dark:bg-green-900 text-green-600 dark:text-green-400',
        title: 'Midterm Exam',
        description: 'Chapters 1-5 comprehensive exam',
        type: 'Exam',
        score: 88,
        date: 'Oct 5, 2023'
      },
      {
        icon: '🧪',
        iconBg: 'bg-purple-100 dark:bg-purple-900 text-purple-600 dark:text-purple-400',
        title: 'Lab Participation',
        description: 'In-class laboratory work and participation',
        type: 'Participation',
        score: 95,
        date: 'Sep 28, 2023'
      },
      {
        icon: '📚',
        iconBg: 'bg-yellow-100 dark:bg-yellow-900 text-yellow-600 dark:text-yellow-400',
        title: 'Chapter 3 Quiz',
        description: 'Atomic structure and periodic table',
        type: 'Quiz',
        score: 90,
        date: 'Sep 20, 2023'
      }
    ]
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
    gradeItems: [
      {
        icon: '🔬',
        iconBg: 'bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-400',
        title: 'Research Project Proposal',
        description: 'Renewable energy research proposal',
        type: 'Project',
        score: 92,
        date: 'Oct 10, 2023'
      },
      {
        icon: '📝',
        iconBg: 'bg-green-100 dark:bg-green-900 text-green-600 dark:text-green-400',
        title: 'Midterm Exam',
        description: 'Chapters 1-5 comprehensive exam',
        type: 'Exam',
        score: 85,
        date: 'Oct 3, 2023'
      },
      {
        icon: '📊',
        iconBg: 'bg-purple-100 dark:bg-purple-900 text-purple-600 dark:text-purple-400',
        title: 'Data Analysis Assignment',
        description: 'Statistical analysis of experimental data',
        type: 'Assignment',
        score: 88,
        date: 'Sep 25, 2023'
      }
    ]
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
    gradeItems: [
      {
        icon: '🧬',
        iconBg: 'bg-green-100 dark:bg-green-900 text-green-600 dark:text-green-400',
        title: 'DNA Model Project',
        description: '3D model of DNA structure and function',
        type: 'Project',
        score: 98,
        date: 'Oct 15, 2023'
      },
      {
        icon: '📊',
        iconBg: 'bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-400',
        title: 'Ecosystem Presentation',
        description: 'Group presentation on local ecosystems',
        type: 'Presentation',
        score: 95,
        date: 'Oct 8, 2023'
      },
      {
        icon: '📝',
        iconBg: 'bg-purple-100 dark:bg-purple-900 text-purple-600 dark:text-purple-400',
        title: 'Genetics Quiz',
        description: 'Mendelian genetics and inheritance patterns',
        type: 'Quiz',
        score: 92,
        date: 'Oct 1, 2023'
      },
      {
        icon: '🔬',
        iconBg: 'bg-yellow-100 dark:bg-yellow-900 text-yellow-600 dark:text-yellow-400',
        title: 'Cell Structure Analysis',
        description: 'Lab report on cell structure observation',
        type: 'Lab Report',
        score: 94,
        date: 'Sep 22, 2023'
      }
    ]
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
    gradeItems: [
      {
        icon: '⚗️',
        iconBg: 'bg-yellow-100 dark:bg-yellow-900 text-yellow-600 dark:text-yellow-400',
        title: 'Chemical Equations Assignment',
        description: 'Balancing chemical equations worksheet',
        type: 'Assignment',
        score: 88,
        date: 'Oct 14, 2023'
      },
      {
        icon: '📚',
        iconBg: 'bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-400',
        title: 'Periodic Table Quiz',
        description: 'Elements, groups, and periodic trends',
        type: 'Quiz',
        score: 82,
        date: 'Oct 7, 2023'
      },
      {
        icon: '🧪',
        iconBg: 'bg-green-100 dark:bg-green-900 text-green-600 dark:text-green-400',
        title: 'Lab Safety Certification',
        description: 'Laboratory safety procedures assessment',
        type: 'Certification',
        score: 90,
        date: 'Sep 30, 2023'
      }
    ]
  }
]);

// Student's enrolled classes
const enrolledClasses = computed(() => {
  // In a real app, this would be filtered based on the student's enrollment
  return classSections.value;
});

// Methods
function findSectionById(id) {
  return classSections.value.find(section => section.id === id) || null;
}

// Select a section
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
  <Head title="Student Grades" />

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
        <p class="text-gray-500 dark:text-gray-400 mb-6 max-w-md">Please select a class section from the sidebar to view your grades and assignments.</p>
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

      <!-- Class-specific grades content -->
      <div v-if="currentSection" class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
        <div class="p-6 border-b dark:border-gray-700">
          <h3 class="font-medium">{{ currentSection.name }} Grades</h3>
          <p class="text-sm text-gray-500 dark:text-gray-400">{{ currentSection.term }}</p>
        </div>
        <div class="p-6">
          <div class="flex items-center justify-between mb-6">
            <div>
              <h4 class="text-lg font-medium">Current Grade: {{ currentSection.grade }}</h4>
              <p class="text-sm text-gray-500 dark:text-gray-400">Overall: {{ currentSection.gradeValue }}%</p>
            </div>
            <span :class="`px-3 py-1 rounded-full text-sm font-medium ${getGradeClass(currentSection.gradeValue)}`">
              {{ currentSection.grade }}
            </span>
          </div>

          <h4 class="font-medium mb-4">Assignments and Assessments</h4>
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
              <thead class="bg-gray-50 dark:bg-gray-700">
                <tr>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Assignment</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Type</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Score</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Date</th>
                </tr>
              </thead>
              <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                <tr v-for="(assignment, index) in currentSection.gradeItems" :key="index">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                      <div class="flex-shrink-0 h-8 w-8 rounded-full flex items-center justify-center" :class="assignment.iconBg">
                        <span class="text-lg">{{ assignment.icon }}</span>
                      </div>
                      <div class="ml-4">
                        <div class="text-sm font-medium">{{ assignment.title }}</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ assignment.description }}</div>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm">{{ assignment.type }}</td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                      <span :class="`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${getGradeClass(assignment.score)}`">
                        {{ assignment.score }}%
                      </span>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ assignment.date }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- General grades content (when no class is selected) -->
      <div v-if="!currentSection" class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden mt-6">
        <div class="p-6 border-b dark:border-gray-700">
          <h3 class="font-medium">Current Grades</h3>
          <p class="text-sm text-gray-500 dark:text-gray-400">Fall Semester 2023</p>
        </div>
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-700">
              <tr>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Subject</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Teacher</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Grade</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Last Updated</th>
              </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
              <tr v-for="(grade, index) in gradesData" :key="index">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <div class="flex-shrink-0 h-8 w-8 rounded-full flex items-center justify-center" :class="grade.iconBg">
                      <span class="text-lg">{{ grade.icon }}</span>
                    </div>
                    <div class="ml-4">
                      <div class="text-sm font-medium">{{ grade.subject }}</div>
                      <div class="text-xs text-gray-500 dark:text-gray-400">{{ grade.code }}</div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm">{{ grade.teacher }}</td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <span :class="`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${grade.gradeClass}`">
                      {{ grade.grade }}
                    </span>
                    <span class="ml-2 text-sm text-gray-500 dark:text-gray-400">{{ grade.percentage }}%</span>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ grade.updated }}</td>
              </tr>
            </tbody>
          </table>
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
