<script setup lang="ts">
import AppLayout from "@/layouts/AppLayout.vue";
import { type BreadcrumbItem } from "@/types";
import { Head, usePage } from "@inertiajs/vue3";
import { ref, computed, onMounted, inject, watch, onUnmounted } from "vue";
import AddAssessmentModal from '@/components/Modals/AddAssessmentModal.vue';
import AddAssessmentItems from '@/components/Modals/AddAssessmentItemsModal.vue';
import { router, } from '@inertiajs/vue3'


const breadcrumbs: BreadcrumbItem[] = [
  {
    title: "Assessment",
    href: "/assessment",
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

const fetchAssessments = () => {
  router.reload({ only: ['prof.assessments'] }) // Reload only the assessments from the server
}

// State variables
const selectedAssessment = ref<string | null>(null);

// Assessment management state
const showModal = ref(false);
const isEditing = ref(false);

const openModal = (assessment = null) => {
  editingId.value = assessment;
  showModal.value = true;
}

const editingId = ref(null);
const loading = ref(false);

// Assessment Items state
const showItemModal = ref(false);
const selectedAssessmentForItems = ref<any>(null);
const assessmentItems = ref<any[]>([]);
const itemForm = ref({
  name: '',
  total_score: 100
});
const itemErrors = ref({});
const itemLoading = ref(false);

// Add the selectedItemsTerm state variable
const selectedItemsTerm = ref('Prelim');

// Fallback data for sections - simplified without student data
interface Assessment {
  id: string;
  assessment_name: string;
  term: string;
  weight: number;
  totalPoints: number;
  dueDate: string;
  items: any[];
}

interface Section {
  id: string;
  name: string;
  icon: string;
  term: string;
  studentCount: number;
  assessments: Assessment[];
}

interface Filters {
  type: string;
  search: string;
}

// Get sections from page props or use fallback data
const sections = usePage().props.sections || [];

// Sample fallback sections data (replace with your actual fallback data)
const fallbackSections = ref<Section[]>([]);

// Currently selected section for assessment filtering
const currentSection = ref<Section | null>(null);

// Filters for assessment management
const filters = ref<Filters>({
  type: '',
  search: ''
});

// Create class sections data from sections or fallback data
const classSections = computed<Section[]>(() => {
  if (sections && sections.length > 0) {
    return sections.map(section => {
      const fallbackSection = fallbackSections.value.find(fs => fs.id === section.id) || fallbackSections.value[0];
      const assessments: Assessment[] = section.assessments && Array.isArray(section.assessments)
        ? section.assessments.map(assessment => {
            const items = assessment.items || [];
            return {
              id: assessment.id || 'assessment',
              assessment_name: assessment.assessment_name || 'Assessment',
              term: assessment.term || 'midterm',
              weight: assessment.weight || 10,
              totalPoints: assessment.totalPoints || 100,
              dueDate: assessment.dueDate || new Date().toISOString().split('T')[0],
              items: items
            };
          })
        : fallbackSection.assessments || [];

      return {
        id: section.id,
        name: section.name || fallbackSection.name || 'Unnamed Section',
        icon: section.icon || fallbackSection.icon || '📚',
        term: section.term || fallbackSection.term || 'Current Term',
        studentCount: section.studentCount || 0,
        assessments: assessments
      };
    });
  }
  return fallbackSections.value;
});

// Convert class sections to format needed for assessment management
const classes = computed(() => {
  return classSections.value.map(section => ({
    id: section.id,
    name: section.name
  }));
});

// Computed properties for assessment management
const filteredAssessments = computed(() => {
  if (!currentSection.value) return [];

  let sectionAssessments = currentSection.value.assessments.map(assessment => ({
    id: assessment.id,
    class_id: currentSection.value.id,
    name: assessment.assessment_name,
    type: assessment.term.charAt(0).toUpperCase() + assessment.term.slice(1),
    weight: assessment.weight,
    term: assessment.term
  }));

  return sectionAssessments.filter(assessment => {
    const matchesType = !filters.value.type || assessment.type.toLowerCase() === filters.value.type.toLowerCase();
    const matchesSearch = !filters.value.search || assessment.name.toLowerCase().includes(filters.value.search.toLowerCase());
    return matchesType && matchesSearch;
  });
});

// Update the allAssessmentItems computed property to filter by term
const allAssessmentItems = computed(() => {
  if (!currentSection.value) return [];

  let items = [];
  currentSection.value.assessments
    .filter(assessment => assessment.term.toLowerCase() === selectedItemsTerm.value.toLowerCase())
    .forEach(assessment => {
      if (assessment.items && assessment.items.length > 0) {
        const assignmentItems = assessment.items.map(item => ({
          ...item,
          assessment_id: assessment.id,
          assessment_name: assessment.assessment_name
        }));
        items = [...items, ...assignmentItems];
      }
    });

  return items;
});

// Calculate term-specific weights
const termWeights = computed(() => {
  if (!currentSection.value) return { Prelim: 0, Midterm: 0, 'Final Term': 0 };

  const weights = {
    'Prelim': 0,
    'Midterm': 0,
    'Final Term': 0
  };

  currentSection.value.assessments.forEach(assessment => {
    const term = assessment.term;
    if (term && typeof term === 'string') {
      // Normalize the term name for consistent comparison
      const normalizedTerm = term.toLowerCase();

      if (normalizedTerm === 'prelim') {
        weights['Prelim'] += parseFloat(assessment.weight) || 0;
      } else if (normalizedTerm === 'midterm') {
        weights['Midterm'] += parseFloat(assessment.weight) || 0;
      } else if (normalizedTerm === 'final' || normalizedTerm === 'finalterm' || normalizedTerm === 'final term') {
        weights['Final Term'] += parseFloat(assessment.weight) || 0;
      }
    }
  });

  return weights;
});

// Get remaining weight for each term
const remainingTermWeights = computed(() => {
  return {
    'Prelim': Math.max(0, 100 - (termWeights.value['Prelim'] || 0)),
    'Midterm': Math.max(0, 100 - (termWeights.value['Midterm'] || 0)),
    'Final Term': Math.max(0, 100 - (termWeights.value['Final Term'] || 0))
  };
});

// Get CSS class for type display
function getTypeColor(type: string) {
  if (!type) return 'bg-gray-100 text-gray-800';
  switch (type.toLowerCase()) {
    case 'exam':
      return 'bg-red-100 text-red-800';
    case 'quiz':
      return 'bg-blue-100 text-blue-800';
    case 'assignment':
    case 'lab':
      return 'bg-green-100 text-green-800';
    case 'project':
      return 'bg-purple-100 text-purple-800';
    case 'participation':
      return 'bg-yellow-100 text-yellow-800';
    default:
      return 'bg-gray-100 text-gray-800';
  }
}

function findSectionById(id: string) {
  return classSections.value.find(section => section.id === id);
}

// Event handler for item changes (from sidebar or other parts)
function handleItemChange(event: CustomEvent) {
  const newItemId = event.detail.itemId;
  if (newItemId) {
    const section = findSectionById(newItemId);
    if (section && (!currentSection.value || currentSection.value.id !== section.id)) {
      selectSection(section);
    }
  }
}

// Event handler for section changes
function handleSectionChange(event: CustomEvent) {
  const newSectionId = event.detail.sectionId;
  if (newSectionId) {
    const section = findSectionById(newSectionId);
    if (section && (!currentSection.value || currentSection.value.id !== section.id)) {
      selectSection(section);
    }
  }
}

// Watch for changes in the injected item ID
watch(currentItemId, (newItemId) => {
  if (newItemId) {
    const section = findSectionById(newItemId);
    if (section && (!currentSection.value || currentSection.value.id !== section.id)) {
      selectSection(section);
    }
  }
}, { immediate: true });

// Watch for changes in the URL query parameters
watch(() => window.location.search, (newSearch) => {
  const params = new URLSearchParams(newSearch);
  const sectionId = params.get('sectionId');
  const itemId = params.get('itemId');

  if (sectionId) {
    const section = findSectionById(sectionId);
    if (section) {
      selectSection(section);
    }
  } else if (itemId) {
    const section = findSectionById(itemId);
    if (section) {
      selectSection(section);
    }
  }
}, { immediate: true });

// Lifecycle hooks
onMounted(() => {
  window.addEventListener('item-changed', handleItemChange);
  window.addEventListener('section-changed', handleSectionChange);

  const urlParams = new URLSearchParams(window.location.search);
  const sectionIdFromUrl = urlParams.get('sectionId');
  const itemIdFromUrl = urlParams.get('itemId');
  const sectionIdFromProps = page.props.sectionId;

  if (sectionIdFromUrl || sectionIdFromProps) {
    const sectionId = sectionIdFromProps || sectionIdFromUrl;
    const section = findSectionById(sectionId);
    if (section) {
      selectSection(section);
      return;
    }
  } else if (itemIdFromUrl) {
    const section = findSectionById(itemIdFromUrl);
    if (section) {
      selectSection(section);
      return;
    }
  } else if (currentItemId.value) {
    const section = findSectionById(currentItemId.value);
    if (section) {
      selectSection(section);
      return;
    }
  }
});

onUnmounted(() => {
  window.removeEventListener('item-changed', handleItemChange);
  window.removeEventListener('section-changed', handleSectionChange);
});

// Delete confirmation modals
const showDeleteAssessmentModal = ref(false);
const showDeleteItemModal = ref(false);
const assessmentToDelete = ref(null);
const itemToDelete = ref(null);
const assessmentIdForItem = ref(null);

function deleteAssessment(id) {
  assessmentToDelete.value = id;
  showDeleteAssessmentModal.value = true;
}

function confirmDeleteAssessment() {
  if (assessmentToDelete.value) {
    router.delete(route('assessments.destroy', assessmentToDelete.value), {
      onSuccess: () => {
        showDeleteAssessmentModal.value = false;
        assessmentToDelete.value = null;

        fetchAssessments();

        setTimeout(() => {
        window.location.reload();
      }, 1500);
      }
    });
  }
}

function cancelDeleteAssessment() {
  showDeleteAssessmentModal.value = false;
  assessmentToDelete.value = null;
}

function deleteAssessmentItem(itemId, assessmentId) {
  itemToDelete.value = itemId;
  assessmentIdForItem.value = assessmentId;
  showDeleteItemModal.value = true;
}

function confirmDeleteItem() {
  if (itemToDelete.value) {
    router.delete(route('assessment-items.destroy', itemToDelete.value), {
      onSuccess: () => {
        showDeleteItemModal.value = false;
        itemToDelete.value = null;
        assessmentIdForItem.value = null;

        // Optional: update local list before reloading
        fetchAssessments();

        // Reload the page after short delay (optional)
        setTimeout(() => {
        window.location.reload();
      }, 1500); // delay to allow UI cleanup
      }
    });
  }
}


function cancelDeleteItem() {
  showDeleteItemModal.value = false;
  itemToDelete.value = null;
  assessmentIdForItem.value = null;
}

function selectSection(section) {
  currentSection.value = section;
  selectedAssessment.value = section.assessments[0]?.id || null;
}

// Function to open the items modal
function openItemsModal(assessment = null) {
  // If an assessment is provided, use it
  if (assessment) {
    selectedAssessmentForItems.value = assessment;
  } else {
    // Otherwise, find the first assessment matching the selected term
    const termAssessments = currentSection.value?.assessments.filter(a =>
      a.term.toLowerCase() === selectedItemsTerm.value.toLowerCase()) || [];
    selectedAssessmentForItems.value = termAssessments.length > 0 ? termAssessments[0] : null;
  }

  showItemModal.value = true;

  // If an assessment is selected, load its items
  if (selectedAssessmentForItems.value) {
    const foundAssessment = currentSection.value?.assessments.find(a => a.id === selectedAssessmentForItems.value.id);
    if (foundAssessment && foundAssessment.items) {
      assessmentItems.value = foundAssessment.items;
    } else {
      assessmentItems.value = [];
    }
  } else {
    assessmentItems.value = [];
  }
}

// Function to close the items modal
function closeItemModal() {
  showItemModal.value = false;
  selectedAssessmentForItems.value = null;
  itemForm.value = { name: '', total_score: 100 };
  itemErrors.value = {};
}

// Function to handle term change in the assessment items component
function handleItemsTermChange(term) {
  selectedItemsTerm.value = term;

  // Find the first assessment matching the selected term
  const termAssessments = currentSection.value?.assessments.filter(a =>
    a.term.toLowerCase() === term.toLowerCase()) || [];
  selectedAssessmentForItems.value = termAssessments.length > 0 ? termAssessments[0] : null;

  // Update the items list
  if (selectedAssessmentForItems.value) {
    const foundAssessment = currentSection.value?.assessments.find(a => a.id === selectedAssessmentForItems.value.id);
    if (foundAssessment && foundAssessment.items) {
      assessmentItems.value = foundAssessment.items;
    } else {
      assessmentItems.value = [];
    }
  } else {
    assessmentItems.value = [];
  }
}

// Function to handle assessment selection in the assessment items component
function handleAssessmentSelected(assessment) {
  selectedAssessmentForItems.value = assessment;

  // Update the items list
  const foundAssessment = currentSection.value?.assessments.find(a => a.id === assessment.id);
  if (foundAssessment && foundAssessment.items) {
    assessmentItems.value = foundAssessment.items;
  } else {
    assessmentItems.value = [];
  }
}

// Function to handle item added event from the assessment items component
function handleItemAdded() {
  fetchAssessments();
}

// Function to handle item deleted event from the assessment items component
function handleItemDeleted() {
  fetchAssessments();
}

</script>

<template>
<Head title="Assessment Management" />

<AppLayout :breadcrumbs="breadcrumbs">
  <div class="p-6 pt-20">
    <!-- No Section Selected Message -->
    <div v-if="!currentSection" class="flex h-[calc(100vh-10rem)] flex-col items-center justify-center text-center">
        <div class="mb-4 rounded-full bg-gray-100 p-6 dark:bg-gray-800">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"
                />
            </svg>
        </div>
        <h2 class="mb-2 text-2xl font-bold">No Selected Class</h2>
        <p class="mb-6 max-w-md text-gray-500 dark:text-gray-400">
            Please select a class from the sidebar to view class-specific information. You can also add class from the sidebar, just click the '+' button.
        </p>
        <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
            <button
                v-for="section in classSections.slice(0, 4)"
                :key="section.id"
                @click="selectSection(section)"
                class="flex flex-col items-center rounded-lg border border-gray-200 bg-white p-4 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:hover:bg-gray-700"
            >
                <span class="mb-2 text-3xl">{{ section.icon }}</span>
                <span class="text-sm font-medium">{{ section.name }}</span>
            </button>
        </div>
    </div>

    <!-- Assessment Management Section -->
    <div v-if="currentSection" class="container mx-auto p-4">
      <!-- Header with Add Button -->
      <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Assessment Management - {{ currentSection.name }}</h1>
        <button
          @click="openModal()"
           class="px-4 py-2.5 bg-primary-600 text-white rounded-lg text-sm font-medium flex items-center gap-2 hover:bg-primary-700 transition-colors"
        >
          <span class="flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
            Add Assessment
          </span>
        </button>
      </div>

      <!-- Filter Controls -->
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-4 mb-6">
        <div class="flex flex-col md:flex-row gap-4">
          <div class="flex-1">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Filter by Term</label>
            <div class="flex items-center space-x-2">
              <button
                type="button"
                @click="filters.type = 'Prelim'"
                class="px-3 py-1 rounded-md text-xs font-medium transition-colors"
                :class="filters.type === 'Prelim' ? 'bg-indigo-600 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300'"
              >
                Prelim
              </button>
              <button
                type="button"
                @click="filters.type = 'Midterm'"
                class="px-3 py-1 rounded-md text-xs font-medium transition-colors"
                :class="filters.type === 'Midterm' ? 'bg-indigo-600 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300'"
              >
                Midterm
              </button>
              <button
                type="button"
                @click="filters.type = 'Final Term'"
                class="px-3 py-1 rounded-md text-xs font-medium transition-colors"
                :class="filters.type === 'Final Term' ? 'bg-indigo-600 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300'"
              >
                Final Term
              </button>
              <button
                type="button"
                @click="filters.type = ''"
                class="px-3 py-1 rounded-md text-xs font-medium transition-colors"
                :class="filters.type === '' ? 'bg-indigo-600 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300'"
              >
                All
              </button>
            </div>
          </div>
          <div class="flex-1">
            <label for="search" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Search</label>
            <input
              type="text"
              id="search"
              v-model="filters.search"
              placeholder="Search by name..."
              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white"
            />
          </div>
        </div>
      </div>

      <!-- Assessments Table -->
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden mb-8">
        <div class="p-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Assessments</h2>
        </div>

        <div v-if="loading" class="p-8 text-center">
          <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-solid border-indigo-600 border-r-transparent"></div>
          <p class="mt-2 text-gray-600 dark:text-gray-400">Loading assessments...</p>
        </div>

        <div v-else-if="filteredAssessments.length === 0" class="p-8 text-center">
          <p class="text-gray-600 dark:text-gray-400">No assessments found. Add your first assessment using the button above.</p>
        </div>

        <div v-else class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-700">
              <tr>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Name</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Term</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Weight</th>
                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
              </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
              <tr v-for="assessment in filteredAssessments" :key="assessment.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                  {{ assessment.name }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                  <span class="px-2 py-1 text-xs rounded-full" :class="getTypeColor(assessment.type)">
                    {{ assessment.term }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                  {{ assessment.weight }}%
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                  <button
                    @click="openModal(assessment)"
                    class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300 mr-3"
                  >
                    Edit
                  </button>
                  <button
                    @click="deleteAssessment(assessment.id)"
                    class="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300"
                  >
                    Delete
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Term Weight Summary -->
        <div class="p-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700">
          <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Weight Summary by Term</h3>

          <!-- Prelim Weight -->
          <div class="bg-white dark:bg-gray-800 p-3 rounded-md border border-gray-200 dark:border-gray-700 mb-3">
            <div class="flex justify-between items-center mb-1">
              <span class="font-medium text-gray-900 dark:text-white">Prelim</span>
              <span
                class="text-sm font-bold"
                :class="termWeights['Prelim'] > 100 ? 'text-red-600 dark:text-red-400' : 'text-gray-900 dark:text-white'"
              >
                {{ termWeights['Prelim'] || 0 }}%
              </span>
            </div>
            <div class="w-full bg-gray-200 dark:bg-gray-600 rounded-full h-2">
              <div
                class="h-2 rounded-full"
                :class="termWeights['Prelim'] > 100 ? 'bg-red-600' : 'bg-indigo-600'"
                :style="`width: ${Math.min(termWeights['Prelim'] || 0, 100)}%`"
              ></div>
            </div>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
              Remaining: {{ remainingTermWeights['Prelim'] }}%
            </p>
          </div>

          <!-- Midterm Weight -->
          <div class="bg-white dark:bg-gray-800 p-3 rounded-md border border-gray-200 dark:border-gray-700 mb-3">
            <div class="flex justify-between items-center mb-1">
              <span class="font-medium text-gray-900 dark:text-white">Midterm</span>
              <span
                class="text-sm font-bold"
                :class="termWeights['Midterm'] > 100 ? 'text-red-600 dark:text-red-400' : 'text-gray-900 dark:text-white'"
              >
                {{ termWeights['Midterm'] || 0 }}%
              </span>
            </div>
            <div class="w-full bg-gray-200 dark:bg-gray-600 rounded-full h-2">
              <div
                class="h-2 rounded-full"
                :class="termWeights['Midterm'] > 100 ? 'bg-red-600' : 'bg-indigo-600'"
                :style="`width: ${Math.min(termWeights['Midterm'] || 0, 100)}%`"
              ></div>
            </div>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
              Remaining: {{ remainingTermWeights['Midterm'] }}%
            </p>
          </div>

          <!-- Final Term Weight -->
          <div class="bg-white dark:bg-gray-800 p-3 rounded-md border border-gray-200 dark:border-gray-700">
            <div class="flex justify-between items-center mb-1">
              <span class="font-medium text-gray-900 dark:text-white">Final Term</span>
              <span
                class="text-sm font-bold"
                :class="termWeights['Final Term'] > 100 ? 'text-red-600 dark:text-red-400' : 'text-gray-900 dark:text-white'"
              >
                {{ termWeights['Final Term'] || 0 }}%
              </span>
            </div>
            <div class="w-full bg-gray-200 dark:bg-gray-600 rounded-full h-2">
              <div
                class="h-2 rounded-full"
                :class="termWeights['Final Term'] > 100 ? 'bg-red-600' : 'bg-indigo-600'"
                :style="`width: ${Math.min(termWeights['Final Term'] || 0, 100)}%`"
              ></div>
            </div>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
              Remaining: {{ remainingTermWeights['Final Term'] }}%
            </p>
          </div>
        </div>
      </div>

      <!-- Assessment Items Table -->
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden">
        <!-- Add a term filter switch to the Assessment Items Table header -->
        <div class="p-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
          <div class="flex items-center space-x-4">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Assessment Items</h2>
            <div class="flex items-center space-x-2 ml-4">
              <button
                type="button"
                @click="selectedItemsTerm = 'Prelim'"
                class="px-3 py-1 rounded-md text-xs font-medium transition-colors"
                :class="selectedItemsTerm === 'Prelim' ? 'bg-indigo-600 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300'"
              >
                Prelim
              </button>
              <button
                type="button"
                @click="selectedItemsTerm = 'Midterm'"
                class="px-3 py-1 rounded-md text-xs font-medium transition-colors"
                :class="selectedItemsTerm === 'Midterm' ? 'bg-indigo-600 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300'"
              >
                Midterm
              </button>
              <button
                type="button"
                @click="selectedItemsTerm = 'Final Term'"
                class="px-3 py-1 rounded-md text-xs font-medium transition-colors"
                :class="selectedItemsTerm === 'Final Term' ? 'bg-indigo-600 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300'"
              >
                Final Term
              </button>
            </div>
          </div>
          <button
            @click="openItemsModal()"
            class="px-4 py-2.5 bg-primary-600 text-white rounded-lg text-sm font-medium flex items-center gap-2 hover:bg-primary-700 transition-colors"
          >
            <span class="flex items-center">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
              Add Item
            </span>
          </button>
        </div>

        <div v-if="itemLoading && allAssessmentItems.length === 0" class="p-8 text-center">
          <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-solid border-indigo-600 border-r-transparent"></div>
          <p class="mt-2 text-gray-600 dark:text-gray-400">Loading assessment items...</p>
        </div>

        <div v-else-if="allAssessmentItems.length === 0" class="p-8 text-center">
          <p class="text-gray-600 dark:text-gray-400">No assessment items found. Add items to your assessments using the button above.</p>
        </div>

        <div v-else class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-700">
              <tr>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Assessment</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Item Name</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Total Score</th>
                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
              </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
              <tr v-for="item in allAssessmentItems" :key="item.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                  {{ item.assessment_name }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                  {{ item.name }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                  {{ item.total_score }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                  <button
                    @click="deleteAssessmentItem(item.id, item.assessment_id)"
                    class="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300"
                  >
                    Delete
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Assessment Modal -->
    <AddAssessmentModal
      :showModal="showModal"
      :assessment="editingId"
      :currentSection="currentSection"
      :assessments="currentSection ? currentSection.assessments : []"
      :class_id="currentSection ? currentSection.id : ''"
      @assessmentAdded="fetchAssessments"
      @closeModal="showModal = false"
    />

    <!-- Use the new AddAssessmentItems component -->
    <AddAssessmentItems
      :showModal="showItemModal"
      :selectedAssessment="selectedAssessmentForItems"
      :assessments="filteredAssessments"
      :items="assessmentItems"
      :selectedTerm="selectedItemsTerm"
      @close="closeItemModal"
      @itemAdded="handleItemAdded"
      @itemDeleted="handleItemDeleted"
      @termChanged="handleItemsTermChange"
      @assessmentSelected="handleAssessmentSelected"
    />

    <!-- Delete Assessment Confirmation Modal -->
    <div v-if="showDeleteAssessmentModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg w-full max-w-md mx-4 overflow-hidden">
        <div class="p-6">
          <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Confirm Deletion</h3>
          <p class="text-gray-600 dark:text-gray-400 mb-6">Are you sure you want to delete this assessment? This action cannot be undone.</p>
          <div class="flex justify-end space-x-3">
            <button
              @click="cancelDeleteAssessment"
              class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded-md hover:bg-gray-300 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-400"
            >
              Cancel
            </button>
            <button
              @click="confirmDeleteAssessment"
              class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500"
            >
              Delete
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Delete Assessment Item Confirmation Modal -->
    <div v-if="showDeleteItemModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg w-full max-w-md mx-4 overflow-hidden">
        <div class="p-6">
          <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Confirm Deletion</h3>
          <p class="text-gray-600 dark:text-gray-400 mb-6">Are you sure you want to delete this assessment item? This action cannot be undone.</p>
          <div class="flex justify-end space-x-3">
            <button
              @click="cancelDeleteItem"
              class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded-md hover:bg-gray-300 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-400"
            >
              Cancel
            </button>
            <button
              @click="confirmDeleteItem"
              class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500"
            >
              Delete
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</AppLayout>
</template>
