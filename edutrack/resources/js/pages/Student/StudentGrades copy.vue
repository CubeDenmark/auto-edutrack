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
const isLoading = ref(false);
const currentTerm = ref('prelim'); // Default to prelim term

// Available terms
const terms = [
  { id: 'prelim', name: 'Preliminary' },
  { id: 'midterm', name: 'Midterm' },
  { id: 'final_term', name: 'Final Term' }
];

// Class sections data from props
const classSections = ref(page.props.classSections || []);

// Helper function for grade styling
const getGradeClass = (score: number, total: number = 100) => {
  const percentage = total > 0 ? (score / total) * 100 : 0;

  if (percentage >= 90) return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300';
  if (percentage >= 80) return 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300';
  if (percentage >= 70) return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300';
  if (percentage >= 60) return 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-300';
  return 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300';
};

// Student's enrolled classes
const enrolledClasses = computed(() => {
  // In a real app, this would be filtered based on the student's enrollment
  return classSections.value;
});

// Filtered assessments based on current term
const currentTermAssessments = computed(() => {
  if (!currentSection.value || !currentSection.value.assessments) {
    return [];
  }

  return currentSection.value.assessments.filter(assessment =>
    assessment.term === currentTerm.value
  );
});

// Final grade for the current term
const currentTermFinalGrade = computed(() => {
  if (!currentSection.value || !currentSection.value.finalGrade) {
    return null;
  }

  return currentSection.value.finalGrade[currentTerm.value];
});

// Add a computed property to track the selected section ID from URL or other sources
const selectedSectionId = computed(() => {
  const urlParams = new URLSearchParams(window.location.search);
  const sectionId = urlParams.get('sectionId');
  const itemId = urlParams.get('itemId');

  // Check URL parameters first
  if (sectionId) {
    return sectionId;
  }

  if (itemId) {
    return itemId;
  }

  // Then check injected ID
  if (currentItemId.value) {
    return currentItemId.value;
  }

  return null;
});

// Methods
function findSectionById(id) {
  return classSections.value.find(section => section.id === id) || null;
}

// Select a section
async function selectSection(section) {
  // Prevent re-selecting the same section to avoid infinite loops
  if (currentSection.value && currentSection.value.id === section.id) {
    return;
  }

  isLoading.value = true;

  try {
    // If the section doesn't have assessments yet, fetch them
    if (!section.assessments || section.assessments.length === 0) {
      await fetchSectionAssessments(section.id);
    }

    currentSection.value = section;

    // Update URL with the selected section ID without navigating
    const url = new URL(window.location.href);
    url.searchParams.set('sectionId', section.id);
    window.history.replaceState({}, '', url.toString());

    // Dispatch an event to notify the sidebar of the selection
    window.dispatchEvent(new CustomEvent('section-changed', {
      detail: {
        sectionId: section.id,
        source: 'grades'
      }
    }));

    // Also dispatch the newer item-changed event for better compatibility
    window.dispatchEvent(new CustomEvent('item-changed', {
      detail: {
        itemId: section.id,
        itemType: 'section',
        source: 'grades'
      }
    }));
  } catch (error) {
    console.error('Error selecting section:', error);
  } finally {
    isLoading.value = false;
  }
}

// Change the current term
function changeTerm(term) {
  currentTerm.value = term;
}

// Fetch assessments for a specific section
async function fetchSectionAssessments(sectionId) {
  try {
    // Make API call to fetch assessments for the section
    const response = await fetch(`/api/grades/section/${sectionId}/assessments`);

    if (!response.ok) {
      throw new Error(`Failed to fetch assessments: ${response.status}`);
    }

    const data = await response.json();

    // Find the section in our array and update its assessments
    const sectionIndex = classSections.value.findIndex(s => s.id === sectionId);
    if (sectionIndex !== -1) {
      classSections.value[sectionIndex].assessments = data.assessments || [];
      classSections.value[sectionIndex].finalGrade = data.finalGrade || {};
    }
  } catch (error) {
    console.error('Error fetching section assessments:', error);
    // Handle error state if needed
  }
}

// Fetch all sections data
async function fetchAllSections() {
  isLoading.value = true;

  try {
    // Make API call to fetch all sections data
    const response = await fetch('/api/grades/sections');

    if (!response.ok) {
      throw new Error(`Failed to fetch sections: ${response.status}`);
    }

    const data = await response.json();

    // Update the class sections with the fetched data
    classSections.value = data.classSections || [];

    // If we have a current section selected, update it with the new data
    if (currentSection.value) {
      const updatedSection = classSections.value.find(s => s.id === currentSection.value.id);
      if (updatedSection) {
        currentSection.value = updatedSection;
      }
    }
  } catch (error) {
    console.error('Error fetching sections:', error);
    // Handle error state if needed
  } finally {
    isLoading.value = false;
  }
}

// Listen for item changes from the sidebar
const handleItemChange = async (event) => {
  if (event.detail && event.detail.source === 'sidebar') {
    const itemId = event.detail.itemId;
    const itemType = event.detail.itemType;

    // For students, the itemType will be 'section' or undefined
    if (itemType === 'section' || !itemType) {
      const section = findSectionById(itemId);
      if (section) {
        if (!section.assessments || section.assessments.length === 0) {
          await fetchSectionAssessments(section.id);
        }

        if (!currentSection.value || currentSection.value.id !== section.id) {
          // Only update if it's a different section to avoid infinite loops
          currentSection.value = section;
        }
      }
    }
  }
};

// For backward compatibility, also listen for section-changed events
const handleSectionChange = async (event) => {
  if (event.detail && event.detail.source !== 'grades') {
    const sectionId = event.detail.sectionId;
    if (sectionId) {
      const section = findSectionById(sectionId);
      if (section) {
        if (!section.assessments || section.assessments.length === 0) {
          await fetchSectionAssessments(section.id);
        }

        if (!currentSection.value || currentSection.value.id !== section.id) {
          currentSection.value = section;
        }
      }
    }
  }
};

// Watch for changes in the injected item ID
watch(currentItemId, async (newItemId) => {
  if (newItemId) {
    const section = findSectionById(newItemId);
    if (section) {
      if (!section.assessments || section.assessments.length === 0) {
        await fetchSectionAssessments(section.id);
      }

      if (!currentSection.value || currentSection.value.id !== section.id) {
        // Only update if it's a different section
        currentSection.value = section;
      }
    }
  }
});

// Watch for changes in the URL query parameters
watch(() => window.location.search, async (newSearch) => {
  const params = new URLSearchParams(newSearch);
  const sectionId = params.get('sectionId');
  const itemId = params.get('itemId');

  // First check for sectionId (legacy parameter)
  if (sectionId) {
    const section = findSectionById(sectionId);
    if (section) {
      if (!section.assessments || section.assessments.length === 0) {
        await fetchSectionAssessments(section.id);
      }

      if (!currentSection.value || currentSection.value.id !== section.id) {
        // Only update if it's a different section
        currentSection.value = section;
      }
    }
  }
  // Then check for itemId (new parameter from sidebar)
  else if (itemId) {
    const section = findSectionById(itemId);
    if (section) {
      if (!section.assessments || section.assessments.length === 0) {
        await fetchSectionAssessments(section.id);
      }

      if (!currentSection.value || currentSection.value.id !== section.id) {
        // Only update if it's a different section
        currentSection.value = section;
      }
    }
  }
});

// Lifecycle hooks
onMounted(async () => {
  // Fetch all sections data if not provided in props
  if (!page.props.classSections || page.props.classSections.length === 0) {
    await fetchAllSections();
  }

  // Add event listener for item changes from sidebar
  window.addEventListener('item-changed', handleItemChange);

  // For backward compatibility, also listen for section-changed events
  window.addEventListener('section-changed', handleSectionChange);

  // Check if there's a section ID in the URL or page props
  const urlParams = new URLSearchParams(window.location.search);
  const sectionIdFromUrl = urlParams.get('sectionId');
  const itemIdFromUrl = urlParams.get('itemId');
  const sectionIdFromProps = page.props.sectionId;

  let initialSection;
  let initialSectionId;

  // First check for sectionId from URL or props
  if (sectionIdFromUrl || sectionIdFromProps) {
    initialSectionId = sectionIdFromProps || sectionIdFromUrl;
    initialSection = findSectionById(initialSectionId);
  }
  // Then check for itemId (which could be a section ID from sidebar)
  else if (itemIdFromUrl) {
    initialSectionId = itemIdFromUrl;
    initialSection = findSectionById(initialSectionId);
  }
  // Finally check for injected item ID
  else if (currentItemId.value) {
    initialSectionId = currentItemId.value;
    initialSection = findSectionById(initialSectionId);
  }

  if (initialSection) {
    if (!initialSection.assessments || initialSection.assessments.length === 0) {
      await fetchSectionAssessments(initialSection.id);
    }
    currentSection.value = initialSection;
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
      <!-- Loading State -->
      <div v-if="isLoading" class="flex justify-center items-center h-[calc(100vh-10rem)]">
        <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-primary-600"></div>
      </div>

      <!-- No Section Selected Message -->
      <div v-else-if="!currentSection" class="flex flex-col items-center justify-center h-[calc(100vh-10rem)] text-center">
        <div class="bg-gray-100 dark:bg-gray-800 p-6 rounded-full mb-4">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z" />
          </svg>
        </div>
        <h2 class="text-2xl font-bold mb-2">Select a Class Section</h2>
        <p class="text-gray-500 dark:text-gray-400 mb-6 max-w-md">Please select a class section from the sidebar to view your assessments and grades.</p>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
          <button
            v-for="section in classSections.slice(0, 4)"
            :key="section.id"
            @click="selectSection(section)"
            :class="[
              'flex flex-col items-center p-4 border rounded-lg transition-colors duration-200',
              selectedSectionId === section.id
                ? 'bg-primary-50 dark:bg-primary-900/20 border-primary-300 dark:border-primary-700 shadow-sm'
                : 'bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700'
            ]"
          >
            <span class="text-3xl mb-2">{{ section.icon }}</span>
            <span :class="[
              'text-sm font-medium',
              selectedSectionId === section.id ? 'text-primary-600 dark:text-primary-400' : ''
            ]">{{ section.name }}</span>
          </button>
        </div>
      </div>

      <!-- Class-specific assessments content -->
      <div v-else class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
        <div class="p-6 border-b dark:border-gray-700">
          <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
              <h3 class="font-medium">{{ currentSection.name }} Assessments</h3>
              <p class="text-sm text-gray-500 dark:text-gray-400">{{ currentSection.term }}</p>
            </div>

            <!-- Term Selector -->
            <div class="flex flex-wrap gap-2">
              <button
                v-for="term in terms"
                :key="term.id"
                @click="changeTerm(term.id)"
                :class="[
                  'px-3 py-1.5 text-sm font-medium rounded-md transition-colors',
                  currentTerm === term.id
                    ? 'bg-primary-100 text-primary-800 dark:bg-primary-900 dark:text-primary-100'
                    : 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600'
                ]"
              >
                {{ term.name }}
              </button>
            </div>
          </div>
        </div>

        <div class="p-6">
          <!-- Term Final Grade Summary -->
          <div v-if="currentTermFinalGrade" class="mb-6 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
              <div>
                <h4 class="text-lg font-medium">{{ terms.find(t => t.id === currentTerm)?.name }} Grade</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Overall: {{ currentTermFinalGrade }}%</p>
              </div>
              <span :class="`px-3 py-1 rounded-full text-sm font-medium ${getGradeClass(currentTermFinalGrade)}`">
                {{ currentTermFinalGrade >= 90 ? 'A' :
                   currentTermFinalGrade >= 80 ? 'B' :
                   currentTermFinalGrade >= 70 ? 'C' :
                   currentTermFinalGrade >= 60 ? 'D' : 'F' }}
              </span>
            </div>
          </div>

          <!-- Loading state for assessments -->
          <div v-if="isLoading" class="flex justify-center items-center py-12">
            <div class="animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-primary-600"></div>
          </div>

          <!-- No assessments message -->
          <div v-else-if="!currentTermAssessments || currentTermAssessments.length === 0" class="text-center py-12">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <h4 class="text-lg font-medium mb-2">No Assessments Available</h4>
            <p class="text-gray-500 dark:text-gray-400">There are no assessments available for this term yet.</p>
          </div>

          <!-- Assessments List -->
          <template v-else>
            <div v-for="(assessment, assessmentIndex) in currentTermAssessments" :key="assessmentIndex" class="mb-8">
              <div class="flex items-center justify-between mb-4">
                <h4 class="font-medium text-lg">{{ assessment.assessment_name }}</h4>
                <span class="text-sm text-gray-500 dark:text-gray-400">Weight: {{ assessment.weight }}%</span>
              </div>

              <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                  <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                      <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Assessment Item</th>
                      <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Score</th>
                      <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Total</th>
                      <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Percentage</th>
                    </tr>
                  </thead>
                  <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    <tr v-for="(item, itemIndex) in assessment.items" :key="itemIndex">
                      <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                          <div class="flex-shrink-0 h-8 w-8 rounded-full flex items-center justify-center"
                               :class="item.iconBg || 'bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-400'">
                            <span class="text-lg">{{ item.icon || '📝' }}</span>
                          </div>
                          <div class="ml-4">
                            <div class="text-sm font-medium">{{ item.name }}</div>
                            <div v-if="item.description" class="text-xs text-gray-500 dark:text-gray-400">{{ item.description }}</div>
                          </div>
                        </div>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap text-sm">{{ item.score }}</td>
                      <td class="px-6 py-4 whitespace-nowrap text-sm">{{ item.total_score }}</td>
                      <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                          <span :class="`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${getGradeClass(item.score, item.total_score)}`">
                            {{ ((item.score / item.total_score) * 100).toFixed(1) }}%
                          </span>
                        </div>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>

              <!-- Assessment Summary -->
              <div class="mt-4 p-3 bg-gray-50 dark:bg-gray-700 rounded-lg flex flex-col sm:flex-row sm:items-center justify-between">
                <span class="text-sm font-medium">Assessment Total</span>
                <div class="flex items-center gap-4 mt-2 sm:mt-0">
                  <span class="text-sm">
                    {{ assessment.totalScore || 0 }} / {{ assessment.totalPossible || 0 }}
                  </span>
                  <span :class="`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${getGradeClass(assessment.totalScore || 0, assessment.totalPossible || 100)}`">
                    {{ assessment.totalPossible ? ((assessment.totalScore / assessment.totalPossible) * 100).toFixed(1) : 0 }}%
                  </span>
                </div>
              </div>
            </div>
          </template>
        </div>
      </div>

      <!-- General sections list (when no class is selected) -->
      <div v-if="!currentSection && !isLoading && classSections.length > 0" class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden mt-6">
        <div class="p-6 border-b dark:border-gray-700">
          <h3 class="font-medium">Current Classes</h3>
          <p class="text-sm text-gray-500 dark:text-gray-400">Select a class to view assessments</p>
        </div>
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-700">
              <tr>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Subject</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Teacher</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
              </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
              <tr v-for="(section, index) in classSections" :key="section.id">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <div class="flex-shrink-0 h-8 w-8 rounded-full flex items-center justify-center" :class="section.iconBg || 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400'">
                      <span class="text-lg">{{ section.icon }}</span>
                    </div>
                    <div class="ml-4">
                      <div class="text-sm font-medium">{{ section.name }}</div>
                      <div class="text-xs text-gray-500 dark:text-gray-400">{{ section.room || 'No room assigned' }}</div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm">{{ section.teacher }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm">
                  <button
                    @click="selectSection(section)"
                    class="text-primary-600 hover:text-primary-800 dark:text-primary-400 dark:hover:text-primary-300 font-medium"
                  >
                    View Assessments
                  </button>
                </td>
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
.text-primary-800 { color: rgb(var(--primary-800)); }
.text-primary-100 { color: rgb(var(--primary-100)); }

/* Add Tailwind dark mode support */
.dark .dark\:bg-primary-900 { background-color: rgb(var(--primary-900)); }
.dark .dark\:text-primary-400 { color: rgb(var(--primary-400)); }
.dark .dark\:text-primary-300 { color: rgb(var(--primary-300)); }
.dark .dark\:text-primary-100 { color: rgb(var(--primary-100)); }
</style>
