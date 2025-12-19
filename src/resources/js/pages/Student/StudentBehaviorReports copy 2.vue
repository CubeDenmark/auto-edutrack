<script setup lang="ts">
import { ref, computed, onMounted, watch, inject } from 'vue';
import AppLayout from "@/layouts/AppLayout.vue";
import { type BreadcrumbItem } from "@/types";
import { Head, usePage, Link } from "@inertiajs/vue3";

// Breadcrumbs
const breadcrumbs: BreadcrumbItem[] = [
  {
    title: "Behavior",
    href: "/behavior",
  },
];

// Get the page props to access any data passed from the server
const page = usePage();

// Inject the current item ID from the sidebar
const currentItemId = inject('currentItemId', ref(null));

// State variables
const currentSection = ref<any>(null);

// General behavior reports (used when no class is selected)
// const behaviorReports = [
//   {
//     icon: '🌟',
//     iconBg: 'bg-green-100 dark:bg-green-900',
//     iconColor: 'text-green-600 dark:text-green-400',
//     title: 'Outstanding Participation',
//     type: 'Positive',
//     typeClass: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
//     description: 'Alex has been consistently participating in class discussions and providing thoughtful insights.',
//     teacher: 'Mrs. Johnson',
//     date: 'Oct 10, 2023'
//   },
//   {
//     icon: '⚠️',
//     iconBg: 'bg-yellow-100 dark:bg-yellow-900',
//     iconColor: 'text-yellow-600 dark:text-yellow-400',
//     title: 'Homework Incomplete',
//     type: 'Warning',
//     typeClass: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
//     description: 'Alex did not complete the assigned homework for today\'s class.',
//     teacher: 'Dr. Smith',
//     date: 'Oct 5, 2023'
//   },
//   {
//     icon: '🏆',
//     iconBg: 'bg-blue-100 dark:bg-blue-900',
//     iconColor: 'text-blue-600 dark:text-blue-400',
//     title: 'Science Fair Recognition',
//     type: 'Positive',
//     typeClass: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
//     description: 'Alex\'s science project was selected as one of the top three in the class.',
//     teacher: 'Mrs. Johnson',
//     date: 'Sep 28, 2023'
//   },
//   {
//     icon: '👏',
//     iconBg: 'bg-purple-100 dark:bg-purple-900',
//     iconColor: 'text-purple-600 dark:text-purple-400',
//     title: 'Helping Peers',
//     type: 'Positive',
//     typeClass: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
//     description: 'Alex voluntarily helped classmates who were struggling with the math assignment.',
//     teacher: 'Dr. Smith',
//     date: 'Sep 22, 2023'
//   },
// ];

// const page = usePage();
const classSections = ref(page.props.classSections);

// // Class sections data - same as in the other views
// const classSections = ref([
//   {
//     id: 'sci101a',
//     name: 'Science 101 - Section A',
//     icon: '🧪',
//     term: 'Fall Semester 2023',
//     teacher: 'Dr. Johnson',
//     room: 'SCI-201',
//     behaviorSummary: {
//       positive: 3,
//       warnings: 1,
//       incidents: 0
//     },
//     behaviorReports: [
//       {
//         icon: '🌟',
//         iconBg: 'bg-green-100 dark:bg-green-900',
//         iconColor: 'text-green-600 dark:text-green-400',
//         title: 'Excellent Lab Work',
//         type: 'Positive',
//         typeClass: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
//         description: 'Emma demonstrated exceptional attention to detail during the chemical reactions lab.',
//         teacher: 'Dr. Johnson',
//         date: 'Oct 12, 2023'
//       },
//       {
//         icon: '⚠️',
//         iconBg: 'bg-yellow-100 dark:bg-yellow-900',
//         iconColor: 'text-yellow-600 dark:text-yellow-400',
//         title: 'Late Assignment',
//         type: 'Warning',
//         typeClass: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
//         description: 'Lab report was submitted one day after the deadline.',
//         teacher: 'Dr. Johnson',
//         date: 'Oct 5, 2023'
//       },
//       {
//         icon: '👏',
//         iconBg: 'bg-green-100 dark:bg-green-900',
//         iconColor: 'text-green-600 dark:text-green-400',
//         title: 'Helping Classmates',
//         type: 'Positive',
//         typeClass: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
//         description: 'Emma helped several classmates understand the complex chemical equations.',
//         teacher: 'Dr. Johnson',
//         date: 'Sep 28, 2023'
//       },
//       {
//         icon: '🔍',
//         iconBg: 'bg-green-100 dark:bg-green-900',
//         iconColor: 'text-green-600 dark:text-green-400',
//         title: 'Insightful Questions',
//         type: 'Positive',
//         typeClass: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
//         description: 'Emma asked thoughtful questions that enhanced the class discussion on atomic structure.',
//         teacher: 'Dr. Johnson',
//         date: 'Sep 20, 2023'
//       }
//     ]
//   },
//   {
//     id: 'sci101b',
//     name: 'Science 101 - Section B',
//     icon: '🧪',
//     term: 'Fall Semester 2023',
//     teacher: 'Dr. Smith',
//     room: 'SCI-202',
//     behaviorSummary: {
//       positive: 2,
//       warnings: 0,
//       incidents: 0
//     },
//     behaviorReports: [
//       {
//         icon: '🌟',
//         iconBg: 'bg-green-100 dark:bg-green-900',
//         iconColor: 'text-green-600 dark:text-green-400',
//         title: 'Outstanding Project Proposal',
//         type: 'Positive',
//         typeClass: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
//         description: 'Emma\'s research project proposal on renewable energy was exceptionally well-developed.',
//         teacher: 'Dr. Smith',
//         date: 'Oct 8, 2023'
//       },
//       {
//         icon: '👏',
//         iconBg: 'bg-green-100 dark:bg-green-900',
//         iconColor: 'text-green-600 dark:text-green-400',
//         title: 'Active Participation',
//         type: 'Positive',
//         typeClass: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
//         description: 'Emma consistently contributes to class discussions with valuable insights.',
//         teacher: 'Dr. Smith',
//         date: 'Sep 25, 2023'
//       }
//     ]
//   },
//   {
//     id: 'bio202',
//     name: 'Biology 202',
//     icon: '🧬',
//     term: 'Fall Semester 2023',
//     teacher: 'Prof. Williams',
//     room: 'BIO-101',
//     behaviorSummary: {
//       positive: 2,
//       warnings: 1,
//       incidents: 0
//     },
//     behaviorReports: [
//       {
//         icon: '🌟',
//         iconBg: 'bg-green-100 dark:bg-green-900',
//         iconColor: 'text-green-600 dark:text-green-400',
//         title: 'Excellent Presentation',
//         type: 'Positive',
//         typeClass: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
//         description: 'Emma\'s presentation on local ecosystems was informative and well-researched.',
//         teacher: 'Prof. Williams',
//         date: 'Oct 11, 2023'
//       },
//       {
//         icon: '⚠️',
//         iconBg: 'bg-yellow-100 dark:bg-yellow-900',
//         iconColor: 'text-yellow-600 dark:text-yellow-400',
//         title: 'Missing Lab Materials',
//         type: 'Warning',
//         typeClass: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
//         description: 'Emma came to lab without the required materials. Please ensure all materials are brought to class.',
//         teacher: 'Prof. Williams',
//         date: 'Oct 2, 2023'
//       },
//       {
//         icon: '👏',
//         iconBg: 'bg-green-100 dark:bg-green-900',
//         iconColor: 'text-green-600 dark:text-green-400',
//         title: 'Thoughtful Analysis',
//         type: 'Positive',
//         typeClass: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
//         description: 'Emma provided an insightful analysis of the cell structure experiment results.',
//         teacher: 'Prof. Williams',
//         date: 'Sep 18, 2023'
//       }
//     ]
//   },
//   {
//     id: 'chem101',
//     name: 'Chemistry 101',
//     icon: '⚗️',
//     term: 'Fall Semester 2023',
//     teacher: 'Dr. Brown',
//     room: 'CHEM-305',
//     behaviorSummary: {
//       positive: 1,
//       warnings: 0,
//       incidents: 0
//     },
//     behaviorReports: [
//       {
//         icon: '🌟',
//         iconBg: 'bg-green-100 dark:bg-green-900',
//         iconColor: 'text-green-600 dark:text-green-400',
//         title: 'Lab Safety Champion',
//         type: 'Positive',
//         typeClass: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
//         description: 'Emma consistently follows all lab safety protocols and helps remind others of proper procedures.',
//         teacher: 'Dr. Brown',
//         date: 'Oct 9, 2023'
//       }
//     ]
//   }
// ]);

// Student's enrolled classes
const enrolledClasses = computed(() => {
  // In a real app, this would be filtered based on the student's enrollment
  return classSections.value;
});

// Overall behavior summary
const overallBehaviorSummary = computed(() => {
  let positive = 0, warnings = 0, incidents = 0;

  enrolledClasses.value.forEach(cls => {
    if (cls.behaviorSummary) {
      positive += cls.behaviorSummary.positive || 0;
      warnings += cls.behaviorSummary.warnings || 0;
      incidents += cls.behaviorSummary.incidents || 0;
    }
  });

  return { positive, warnings, incidents };
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
  <Head title="Student Behavior" />

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
        <p class="text-gray-500 dark:text-gray-400 mb-6 max-w-md">Please select a class section from the sidebar to view behavior reports and feedback.</p>
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

      <!-- Class-specific behavior content -->
      <div v-if="currentSection">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-6">
          <h3 class="font-medium mb-2">{{ currentSection.name }} Behavior Summary</h3>
          <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Teacher: {{ currentSection.teacher }}</p>

          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="p-4 bg-green-50 dark:bg-green-900/20 rounded-lg border border-green-100 dark:border-green-800">
              <div class="flex items-center space-x-3">
                <div class="bg-green-100 dark:bg-green-800 p-2 rounded-full">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                  </svg>
                </div>
                <div>
                  <p class="text-sm text-gray-500 dark:text-gray-400">Positive Reports</p>
                  <p class="text-xl font-bold">{{ currentSection.behaviorSummary?.positive || 0 }}</p>
                </div>
              </div>
            </div>
            <div class="p-4 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg border border-yellow-100 dark:border-yellow-800">
              <div class="flex items-center space-x-3">
                <div class="bg-yellow-100 dark:bg-yellow-800 p-2 rounded-full">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-yellow-600 dark:text-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                  </svg>
                </div>
                <div>
                  <p class="text-sm text-gray-500 dark:text-gray-400">Warnings</p>
                  <p class="text-xl font-bold">{{ currentSection.behaviorSummary?.warnings || 0 }}</p>
                </div>
              </div>
            </div>
            <div class="p-4 bg-red-50 dark:bg-red-900/20 rounded-lg border border-red-100 dark:border-red-800">
              <div class="flex items-center space-x-3">
                <div class="bg-red-100 dark:bg-red-800 p-2 rounded-full">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                  </svg>
                </div>
                <div>
                  <p class="text-sm text-gray-500 dark:text-gray-400">Incidents</p>
                  <p class="text-xl font-bold">{{ currentSection.behaviorSummary?.incidents || 0 }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
          <div class="p-6 border-b dark:border-gray-700">
            <h3 class="font-medium">Class Behavior Reports</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400">Current Academic Year</p>
          </div>
          <div class="divide-y divide-gray-200 dark:divide-gray-700">
            <div v-for="(report, index) in currentSection.behaviorReports" :key="index" class="p-6">
              <div class="flex items-start space-x-4">
                <div :class="`w-10 h-10 rounded-full flex items-center justify-center ${report.iconBg} ${report.iconColor}`">
                  <span class="text-lg">{{ report.icon }}</span>
                </div>
                <div class="flex-1">
                  <div class="flex items-center justify-between mb-2">
                    <h4 class="font-medium">{{ report.title }}</h4>
                    <span :class="`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${report.typeClass}`">
                      {{ report.type }}
                    </span>
                  </div>
                  <p class="text-sm text-gray-600 dark:text-gray-300 mb-2">{{ report.description }}</p>
                  <div class="flex items-center text-sm text-gray-500 dark:text-gray-400">
                    <p>{{ report.teacher }} • {{ report.date }}</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- General behavior content (when no class is selected) -->
      <div v-if="!currentSection" class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-6 mt-6">
        <h3 class="font-medium mb-4">Behavior Summary</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div class="p-4 bg-green-50 dark:bg-green-900/20 rounded-lg border border-green-100 dark:border-green-800">
            <div class="flex items-center space-x-3">
              <div class="bg-green-100 dark:bg-green-800 p-2 rounded-full">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
              </div>
              <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Positive Reports</p>
                <p class="text-xl font-bold">{{ overallBehaviorSummary.positive }}</p>
              </div>
            </div>
          </div>
          <div class="p-4 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg border border-yellow-100 dark:border-yellow-800">
            <div class="flex items-center space-x-3">
              <div class="bg-yellow-100 dark:bg-yellow-800 p-2 rounded-full">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-yellow-600 dark:text-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
              </div>
              <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Warnings</p>
                <p class="text-xl font-bold">{{ overallBehaviorSummary.warnings }}</p>
              </div>
            </div>
          </div>
          <div class="p-4 bg-red-50 dark:bg-red-900/20 rounded-lg border border-red-100 dark:border-red-800">
            <div class="flex items-center space-x-3">
              <div class="bg-red-100 dark:bg-red-800 p-2 rounded-full">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </div>
              <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Incidents</p>
                <p class="text-xl font-bold">{{ overallBehaviorSummary.incidents }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div v-if="!currentSection" class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden mt-6">
        <div class="p-6 border-b dark:border-gray-700">
          <h3 class="font-medium">Behavior Reports</h3>
          <p class="text-sm text-gray-500 dark:text-gray-400">Current Academic Year</p>
        </div>
        <div class="divide-y divide-gray-200 dark:divide-gray-700">
          <div v-for="(report, index) in behaviorReports" :key="index" class="p-6">
            <div class="flex items-start space-x-4">
              <div :class="`w-10 h-10 rounded-full flex items-center justify-center ${report.iconBg} ${report.iconColor}`">
                <span class="text-lg">{{ report.icon }}</span>
              </div>
              <div class="flex-1">
                <div class="flex items-center justify-between mb-2">
                  <h4 class="font-medium">{{ report.title }}</h4>
                  <span :class="`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${report.typeClass}`">
                    {{ report.type }}
                  </span>
                </div>
                <p class="text-sm text-gray-600 dark:text-gray-300 mb-2">{{ report.description }}</p>
                <div class="flex items-center text-sm text-gray-500 dark:text-gray-400">
                  <p>{{ report.teacher }} • {{ report.date }}</p>
                </div>
              </div>
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
