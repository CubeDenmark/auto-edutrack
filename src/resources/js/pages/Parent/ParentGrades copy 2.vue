<script setup lang="ts">
import { ref, computed, onMounted, watch, inject } from 'vue';
import AppLayout from "@/layouts/AppLayout.vue";
import { type BreadcrumbItem } from "@/types";
import { Head, usePage, Link } from "@inertiajs/vue3";
import axios from 'axios';
import { ChevronLeft, User, BookOpen, BarChart3, Eye } from 'lucide-vue-next';

// Breadcrumbs
const breadcrumbs: BreadcrumbItem[] = [
  {
    title: "Grades",
    href: "/parent/grades",
  },
];

// Get the page props to access any data passed from the server
const page = usePage();

// Inject the current child ID from the parent component
const injectedChildId = inject('currentChildId', ref(null));
const injectedSectionId = inject('currentSectionId', ref(null));
// Inject the current item ID from the sidebar
const injectedItemId = inject('currentItemId', ref(null));

// State variables
const currentChild = ref<any>(null);
const selectedSubject = ref(null);
const selectedTerm = ref(null);
const isMobile = ref(false);
const loading = ref(false);
const error = ref(null);
const showMobileChildSelector = ref(false);

// Children data - initialize from props passed by the controller
const children = ref(page.props.initialChildren || []);

// Helper function for grade styling based on Philippine grading system (1.0-5.0)
// In Philippine system, 1.0 is highest (best) and 5.0 is lowest (worst)
const getGradeClass = (grade: number) => {
  if (typeof grade === 'string') {
    grade = parseFloat(grade);
  }

  if (isNaN(grade)) return '';

  // Adjusted Philippine grading system: 1.0 is best, 5.0 is worst
  if (grade <= 1.0) return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300'; // Excellent
  if (grade <= 1.5) return 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300'; // Very Good
  if (grade <= 2.0) return 'bg-blue-200 text-blue-900 dark:bg-blue-800 dark:text-blue-400'; // Good
  if (grade <= 2.5) return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300'; // Satisfactory
  if (grade <= 3.0) return 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-300'; // Passing
  return 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300'; // Failing
};

// Check if we're on mobile
const checkMobile = () => {
  isMobile.value = window.innerWidth < 768;
};

// Methods
function findChildById(id) {
  if (!id) return null;
  return children.value.find(child => child.id === id || child.id === Number(id)) || null;
}

// Fetch grades data for a specific child
async function fetchChildGrades(childId) {
  if (!childId) return;

  loading.value = true;
  error.value = null;

  try {
    const response = await axios.get(`/parent/grades/child/${childId}`);
    
    if (response.data && response.data.child) {
      // Update the current child with the fetched data
      currentChild.value = response.data.child;

      // Update the child in the children array to keep it in sync
      const childIndex = children.value.findIndex(c => c.id === childId || c.id === Number(childId));
      if (childIndex !== -1) {
        children.value[childIndex] = { ...children.value[childIndex], ...response.data.child };
      }
    } else {
      throw new Error('Invalid response format');
    }
  } catch (err) {
    console.error('Error fetching child grades:', err);
    error.value = 'Failed to load grades data. Please try again.';
  } finally {
    loading.value = false;
  }
}

// Select a child and notify other components
const selectChild = async (childId) => {
  if (childId === 'all') {
    currentChild.value = null;
    selectedSubject.value = null;
  } else {
    const child = findChildById(childId);
    if (child) {
      currentChild.value = child;
      selectedSubject.value = null;

      // Always fetch the latest grades data when selecting a child
      await fetchChildGrades(childId);
    }
  }

  // Close mobile selector after selection
  showMobileChildSelector.value = false;

  // Dispatch event for other components to listen to
  window.dispatchEvent(new CustomEvent('child-changed', {
    detail: {
      childId: childId,
      source: 'grades'
    }
  }));
};

// Select a subject
const selectSubject = (subject) => {
  selectedSubject.value = subject;
  
  // Set default term if available
  if (subject && subject.assessments) {
    const terms = Object.keys(subject.assessments);
    if (terms.length > 0) {
      selectedTerm.value = terms[0];
    } else {
      selectedTerm.value = null;
    }
  } else {
    selectedTerm.value = null;
  }
};

// Select a term
const selectTerm = (term) => {
  selectedTerm.value = term;
};

// Handle item-changed event from sidebar
const handleItemChanged = async (event) => {
  if (event.detail) {
    const { itemId, itemType, source } = event.detail;

    if (source === 'sidebar') {
      if (itemId === 'all') {
        // Handle "All Children" selection
        currentChild.value = null;
        selectedSubject.value = null;
      } else {
        // Handle specific child selection
        await updateChildData(itemId);
      }
    }
  }
};

// Handle child-changed event from other components
const handleChildChanged = async (event) => {
  if (event.detail && event.detail.source !== 'grades') {
    const { childId } = event.detail;
    await updateChildData(childId);
  }
};

// Handle section-changed event (for backward compatibility)
const handleSectionChanged = async (event) => {
  if (event.detail) {
    const { sectionId, source } = event.detail;

    if (source === 'sidebar') {
      await updateChildData(sectionId);
    }
  }
};

const updateChildData = async (id) => {
  if (id && id !== 'all') {
    const child = findChildById(id);
    if (child) {
      currentChild.value = child;
      selectedSubject.value = null;
      
      // Always fetch the latest grades when updating child data
      await fetchChildGrades(id);
    }
  } else if (id === 'all') {
    currentChild.value = null;
    selectedSubject.value = null;
  }
};

// Toggle mobile child selector
const toggleMobileChildSelector = () => {
  showMobileChildSelector.value = !showMobileChildSelector.value;
};

// Lifecycle hooks
onMounted(() => {
  // Check if we're on mobile
  checkMobile();
  window.addEventListener('resize', checkMobile);

  // Add event listeners for various events
  window.addEventListener('item-changed', handleItemChanged);
  window.addEventListener('child-changed', handleChildChanged);
  window.addEventListener('section-changed', handleSectionChanged);

  // Initialize child selection based on URL parameters or props
  const initializeChildSelection = async () => {
    const urlParams = new URLSearchParams(window.location.search);
    const childIdFromUrl = urlParams.get('childId');
    const subject = urlParams.get('subject');
    const sectionId = urlParams.get('sectionId');
    const itemId = urlParams.get('itemId');
    const childIdFromProps = page.props.childId;

    let childIdToUse = null;

    if (childIdFromProps) {
      childIdToUse = childIdFromProps;
    } else if (childIdFromUrl) {
      childIdToUse = childIdFromUrl;
    } else if (sectionId || itemId) {
      childIdToUse = sectionId || itemId;
    } else if (children.value.length > 0) {
      // If no specific child is selected, default to the first child
      childIdToUse = children.value[0].id;
    }

    if (childIdToUse) {
      await updateChildData(childIdToUse);
      
      if (subject && currentChild.value && currentChild.value.grades) {
        selectedSubject.value = currentChild.value.grades.find(grade => grade.code === subject) || null;
      }
    }
  };

  initializeChildSelection();

  // Clean up event listeners on component unmount
  return () => {
    window.removeEventListener('resize', checkMobile);
    window.removeEventListener('item-changed', handleItemChanged);
    window.removeEventListener('child-changed', handleChildChanged);
    window.removeEventListener('section-changed', handleSectionChanged);
  };
});

// Watch for changes in the injected child ID
watch(injectedChildId, async (newChildId) => {
  if (newChildId) {
    await updateChildData(newChildId);
  }
}, { immediate: true });

// Watch for changes in the injected section ID
watch(injectedSectionId, async (newSectionId) => {
  if (newSectionId) {
    await updateChildData(newSectionId);
  }
}, { immediate: true });

// Watch for changes in the injected item ID from sidebar
watch(injectedItemId, async (newItemId) => {
  if (newItemId) {
    await updateChildData(newItemId);
  }
}, { immediate: true });

// Watch for changes in the URL query parameters
watch(() => window.location.search, async (newSearch) => {
  const params = new URLSearchParams(newSearch);
  const childId = params.get('childId');
  const subject = params.get('subject');
  const sectionId = params.get('sectionId');
  const itemId = params.get('itemId');

  let childIdToUse = null;

  if (childId) {
    childIdToUse = childId;
  } else if (sectionId || itemId) {
    childIdToUse = sectionId || itemId;
  }

  if (childIdToUse) {
    await updateChildData(childIdToUse);
    
    if (subject && currentChild.value && currentChild.value.grades) {
      selectedSubject.value = currentChild.value.grades.find(grade => grade.code === subject) || null;
    }
  }
}, { immediate: true });

// Computed property for grade history data
const gradeHistory = computed(() => {
  if (!currentChild.value || !currentChild.value.grades) return [];

  // Create a simple grade history visualization
  return currentChild.value.grades.map(grade => {
    // Calculate percentage from the Philippine grade (1.0-5.0)
    // 1.0 = 100%, 5.0 = 50%
    const gradeValue = parseFloat(grade.final_grade);
    const percentage = isNaN(gradeValue) ? 0 : Math.max(50, Math.min(100, 100 - (((gradeValue - 1.0) / 4.0) * 50)));
    
    return {
      subject: grade.subject,
      percentage: Math.round(percentage),
      color: 'blue' // Use a single color for all subjects
    };
  });
});

// Computed property to get available terms for the selected subject
const availableTerms = computed(() => {
  if (!selectedSubject.value || !selectedSubject.value.assessments) return [];
  return Object.keys(selectedSubject.value.assessments);
});

// Computed property to get assessment data for the selected subject and term
const currentAssessments = computed(() => {
  if (!selectedSubject.value || !selectedSubject.value.assessments || !selectedTerm.value) return {};
  
  // Get assessments for the selected term
  return selectedSubject.value.assessments[selectedTerm.value] || {};
});

// Calculate GPA for a child
const calculateGPA = (child) => {
  if (!child || !child.grades || child.grades.length === 0) return 'N/A';
  
  let totalGradePoints = 0;
  let totalClasses = 0;
  
  child.grades.forEach(grade => {
    const numericGrade = parseFloat(grade.final_grade);
    if (!isNaN(numericGrade)) {
      totalGradePoints += numericGrade;
      totalClasses++;
    }
  });
  
  if (totalClasses === 0) return 'N/A';
  
  return (totalGradePoints / totalClasses).toFixed(2);
};
</script>

<template>
  <Head title="Parent Grades View" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <!-- Full width container with responsive padding -->
    <div class="w-full p-4 md:p-6 pt-16 md:pt-20">
      <!-- Loading state -->
      <div v-if="loading" class="flex justify-center items-center py-12 w-full">
        <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-primary-600"></div>
      </div>

      <!-- Error message -->
      <div v-else-if="error" class="bg-red-50 dark:bg-red-900 border border-red-200 dark:border-red-800 rounded-lg p-4 mb-6 w-full">
        <p class="text-red-800 dark:text-red-200">{{ error }}</p>
        <button
          @click="error = null"
          class="mt-2 px-4 py-2 bg-red-100 dark:bg-red-800 text-red-800 dark:text-red-200 rounded-md text-sm"
        >
          Dismiss
        </button>
      </div>

      <div v-else class="w-full">
        <!-- Mobile Child Selector Toggle -->
        <div v-if="currentChild && isMobile" class="mb-4 w-full">
          <button 
            @click="toggleMobileChildSelector" 
            class="flex items-center justify-between w-full p-3 bg-white dark:bg-gray-800 rounded-lg shadow"
          >
            <div class="flex items-center">
              <img :src="currentChild.avatar" :alt="`${currentChild.name} Avatar`" class="w-8 h-8 rounded-full mr-2" />
              <span class="font-medium">{{ currentChild.name }}</span>
            </div>
            <span class="text-primary-600 dark:text-primary-400">
              <ChevronLeft v-if="showMobileChildSelector" class="transform rotate-90" size="18" />
              <ChevronLeft v-else class="transform -rotate-90" size="18" />
            </span>
          </button>
        </div>

        <!-- Child selector (visible on mobile when toggled or when no child is selected) -->
        <div v-if="(!currentChild || (isMobile && showMobileChildSelector))" class="mb-6 w-full">
          <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 md:p-6 w-full">
            <h3 class="font-medium mb-4 flex items-center">
              <User size="18" class="mr-2" />
              My Children
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3 md:gap-4 w-full">
              <div
                v-for="(child, index) in children"
                :key="index"
                class="border border-gray-200 dark:border-gray-700 rounded-lg p-3 md:p-4 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                :class="{'ring-2 ring-primary-500 dark:ring-primary-400': currentChild && currentChild.id === child.id}"
                @click="selectChild(child.id)"
              >
                <div class="flex items-center">
                  <img :src="child.avatar" :alt="`${child.name} Avatar`" class="w-10 h-10 md:w-12 md:h-12 rounded-full mr-3" />
                  <div>
                    <h4 class="font-medium">{{ child.name }}</h4>
                    <p class="text-xs md:text-sm text-gray-500 dark:text-gray-400">{{ child.grade }}</p>
                    <p class="text-xs md:text-sm mt-1">
                      GPA: <span :class="`px-1.5 py-0.5 rounded text-xs ${getGradeClass(calculateGPA(child))}`">{{ calculateGPA(child) }}</span>
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Child-specific grades content -->
        <div v-else-if="currentChild" class="w-full">
          <!-- Child header -->
          <div class="flex flex-col md:flex-row md:items-center gap-4 mb-6 bg-white dark:bg-gray-800 rounded-lg shadow p-4 w-full">
            <div class="flex items-center">
              <img :src="currentChild.avatar" :alt="`${currentChild.name} Avatar`" class="w-12 h-12 md:w-16 md:h-16 rounded-full mr-4" />
              <div>
                <h2 class="text-lg md:text-xl font-semibold">{{ currentChild.name }}</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ currentChild.grade }}</p>
                <div class="flex items-center mt-1">
                  <span class="text-xs md:text-sm text-gray-500 dark:text-gray-400 mr-2">GPA:</span>
                  <span :class="`inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium ${getGradeClass(calculateGPA(currentChild))}`">
                    {{ calculateGPA(currentChild) }}
                  </span>
                </div>
              </div>
            </div>
            <button
              @click="selectChild('all')"
              class="md:ml-auto flex items-center text-sm text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300"
            >
              <ChevronLeft size="16" class="mr-1" />
              Back to Overview
            </button>
          </div>

          <!-- Subject-specific grade details -->
          <div v-if="selectedSubject" class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden mb-6 w-full">
            <div class="p-4 md:p-6 border-b dark:border-gray-700 flex flex-col md:flex-row md:justify-between md:items-center gap-3">
              <div>
                <h3 class="font-medium text-lg flex items-center">
                  <BookOpen size="18" class="mr-2" />
                  {{ selectedSubject.subject }} Grades
                </h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">Teacher: {{ selectedSubject.teacher }}</p>
              </div>
              <button
                @click="selectedSubject = null"
                class="flex items-center text-primary-600 hover:text-primary-800 dark:text-primary-400 dark:hover:text-primary-300 text-sm"
              >
                <ChevronLeft size="16" class="mr-1" />
                Back to All Subjects
              </button>
            </div>
            <div class="p-4 md:p-6">
              <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6 bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                <div>
                  <h4 class="text-base md:text-lg font-medium">Current Grade</h4>
                  <p class="text-xs md:text-sm text-gray-500 dark:text-gray-400">Last Updated: {{ selectedSubject.updated }}</p>
                </div>
                <div class="flex flex-col items-start md:items-end">
                  <span :class="`px-3 py-1 rounded-full text-sm font-medium ${getGradeClass(selectedSubject.final_grade)}`">
                    {{ selectedSubject.final_grade }}
                  </span>
                </div>
              </div>

              <!-- Term grades -->
              <div class="mb-6 w-full">
                <h4 class="font-medium mb-4">Term Grades</h4>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3 md:gap-4 w-full">
                  <div v-if="selectedSubject.terms && selectedSubject.terms.prelim" 
                       class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg cursor-pointer w-full"
                       :class="{'ring-2 ring-primary-500': selectedTerm === 'Prelim'}"
                       @click="selectTerm('Prelim')">
                    <h5 class="text-sm font-medium mb-1">Prelim</h5>
                    <p class="text-lg font-semibold">{{ selectedSubject.terms.prelim }}</p>
                  </div>
                  <div v-if="selectedSubject.terms && selectedSubject.terms.midterm" 
                       class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg cursor-pointer w-full"
                       :class="{'ring-2 ring-primary-500': selectedTerm === 'Midterm'}"
                       @click="selectTerm('Midterm')">
                    <h5 class="text-sm font-medium mb-1">Midterm</h5>
                    <p class="text-lg font-semibold">{{ selectedSubject.terms.midterm }}</p>
                  </div>
                  <div v-if="selectedSubject.terms && selectedSubject.terms.final_term" 
                       class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg cursor-pointer w-full"
                       :class="{'ring-2 ring-primary-500': selectedTerm === 'Final Term'}"
                       @click="selectTerm('Final Term')">
                    <h5 class="text-sm font-medium mb-1">Final</h5>
                    <p class="text-lg font-semibold">{{ selectedSubject.terms.final_term }}</p>
                  </div>
                </div>
              </div>

              <!-- Dynamic term selection based on available assessment terms -->
              <div v-if="availableTerms.length > 0" class="mb-6 w-full">
                <h4 class="font-medium mb-4">Assessment Terms</h4>
                <div class="flex flex-wrap gap-2">
                  <button 
                    v-for="term in availableTerms" 
                    :key="term"
                    @click="selectTerm(term)"
                    class="px-3 py-1 text-sm rounded-md bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors"
                    :class="{'bg-primary-100 dark:bg-primary-900 text-primary-800 dark:text-primary-300': selectedTerm === term}"
                  >
                    {{ term }}
                  </button>
                </div>
              </div>

              <!-- Assessment scores for the selected term -->
              <div v-if="selectedTerm && Object.keys(currentAssessments).length > 0" class="mb-6 w-full">
                <h4 class="font-medium mb-4">{{ selectedTerm }} Assessments</h4>
                
                <div class="grid grid-cols-1 gap-6 w-full">
                  <div v-for="(assessments, type) in currentAssessments" :key="type" class="w-full">
                    <h5 class="text-sm font-medium mb-3 capitalize bg-gray-50 dark:bg-gray-700 p-3 rounded-t-lg">{{ type }}</h5>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 auto-rows-fr gap-0">
                      <div 
                        v-for="(assessment, index) in assessments" 
                        :key="index"
                        class="border border-gray-100 dark:border-gray-600 p-4 flex flex-col justify-between"
                        :class="{
                          'sm:col-span-2': index === 0 && assessments.length > 1 && assessments.length % 2 !== 0,
                          'lg:col-span-3': index === 0 && assessments.length > 2 && assessments.length % 3 === 1,
                          'xl:col-span-2': index === 0 && assessments.length > 3 && assessments.length % 4 === 1,
                          'sm:col-span-2 lg:col-span-2 xl:col-span-2': index === 0 && assessments.length > 3 && assessments.length % 4 === 2,
                          'rounded-tl-lg': index === 0,
                          'rounded-tr-lg': (index === 1 && assessments.length === 2) || 
                                          (index === 2 && assessments.length === 3) || 
                                          (index === 3 && assessments.length === 4) ||
                                          (index === 0 && assessments.length === 1),
                          'rounded-bl-lg': index === assessments.length - assessments.length % 3 || 
                                          (assessments.length <= 3 && index === 0),
                          'rounded-br-lg': index === assessments.length - 1,
                          'bg-white dark:bg-gray-700': true
                        }"
                      >
                        <div class="flex justify-between items-start">
                          <h6 class="font-medium text-sm">{{ assessment.title }}</h6>
                          <span 
                            class="px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 dark:bg-gray-600"
                          >
                            {{ assessment.score }}
                          </span>
                        </div>
                        <div class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                          Assessment #{{ index + 1 }}
                        </div>
                      </div>
                    </div>
                    
                    <div v-if="assessments.length === 0" class="text-center py-4 bg-white dark:bg-gray-700 rounded-b-lg w-full border-t-0 border border-gray-100 dark:border-gray-600">
                      <p class="text-gray-500 dark:text-gray-400">No {{ type }} assessments found for this term.</p>
                    </div>
                  </div>
                </div>
                
                <div v-if="Object.keys(currentAssessments).length === 0" class="text-center py-4 bg-gray-50 dark:bg-gray-700 rounded-lg w-full">
                  <p class="text-gray-500 dark:text-gray-400">No assessments found for this term.</p>
                </div>
              </div>

              <h4 class="font-medium mb-4">Remarks</h4>
              <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg mb-6 w-full">
                <p class="text-sm">{{ selectedSubject.remarks || 'No remarks available.' }}</p>
              </div>
            </div>
          </div>

          <!-- Subjects overview (when no specific subject is selected) - GRID VERSION -->
          <div v-else class="bg-white dark:bg-gray-800 rounded-lg shadow mb-6 w-full">
            <div class="p-4 md:p-6 border-b dark:border-gray-700">
              <h3 class="font-medium flex items-center">
                <BookOpen size="18" class="mr-2" />
                Subjects Overview
              </h3>
              <p class="text-sm text-gray-500 dark:text-gray-400">Current Semester</p>
            </div>
            
            <div v-if="currentChild.grades && currentChild.grades.length > 0" class="p-4 md:p-6">
              <!-- Grid layout for subjects -->
              <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 w-full">
                <div 
                  v-for="(subject, index) in currentChild.grades" 
                  :key="index"
                  class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 border border-gray-100 dark:border-gray-600"
                >
                  <div class="flex items-center mb-3">
                    <div class="flex-shrink-0 h-10 w-10 rounded-full flex items-center justify-center mr-3" :class="subject.iconBg">
                      <span class="text-lg">{{ subject.icon }}</span>
                    </div>
                    <div>
                      <h4 class="font-medium">{{ subject.subject }}</h4>
                      <p class="text-xs text-gray-500 dark:text-gray-400">{{ subject.code }}</p>
                    </div>
                  </div>
                  
                  <div class="flex flex-wrap gap-2 mb-3">
                    <div v-if="subject.terms && subject.terms.prelim" class="flex flex-col items-center">
                      <span class="text-xs text-gray-500 dark:text-gray-400">Prelim</span>
                      <span class="font-medium">{{ subject.terms.prelim }}</span>
                    </div>
                    <div v-if="subject.terms && subject.terms.midterm" class="flex flex-col items-center ml-4">
                      <span class="text-xs text-gray-500 dark:text-gray-400">Midterm</span>
                      <span class="font-medium">{{ subject.terms.midterm }}</span>
                    </div>
                    <div v-if="subject.terms && subject.terms.final_term" class="flex flex-col items-center ml-4">
                      <span class="text-xs text-gray-500 dark:text-gray-400">Final</span>
                      <span class="font-medium">{{ subject.terms.final_term }}</span>
                    </div>
                  </div>
                  
                  <div class="flex items-center justify-between">
                    <span :class="`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${getGradeClass(subject.final_grade)}`">
                      {{ subject.final_grade }}
                    </span>
                    <button
                      @click="selectSubject(subject)"
                      class="flex items-center text-primary-600 hover:text-primary-800 dark:text-primary-400 dark:hover:text-primary-300 text-sm"
                    >
                      <Eye size="14" class="mr-1" />
                      View Details
                    </button>
                  </div>
                </div>
              </div>
            </div>
            
            <div v-else class="text-center py-8 text-gray-500 dark:text-gray-400 w-full">
              No subjects or grades found for this student.
            </div>
          </div>

          <!-- Grade History -->
          <div v-if="gradeHistory.length > 0" class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 md:p-6 w-full">
            <h3 class="font-medium mb-4 flex items-center">
              <BarChart3 size="18" class="mr-2" />
              Grade History
            </h3>
            <div class="h-64 w-full">
              <!-- Chart visualization -->
              <div class="h-full w-full flex items-center justify-center bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                <div class="space-y-4 w-full px-4 md:px-8">
                  <div v-for="(item, index) in gradeHistory" :key="index">
                    <div class="flex justify-between mb-1">
                      <span class="text-xs md:text-sm font-medium truncate max-w-[70%]">{{ item.subject }}</span>
                      <span class="text-xs md:text-sm font-medium">{{ item.percentage }}%</span>
                    </div>
                    <div class="w-full bg-gray-200 dark:bg-gray-600 rounded-full h-2.5">
                      <div
                        class="h-2.5 rounded-full bg-primary-600 dark:bg-primary-500"
                        :style="`width: ${item.percentage}%`"
                      ></div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- No child selected message -->
        <div v-else class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 md:p-6 text-center w-full">
          <h3 class="text-xl font-medium mb-4">Grades Overview</h3>
          <p class="text-gray-600 dark:text-gray-300 mb-6">
            Select a child to view their grades and academic performance. You can see detailed information about each subject, including assignments and assessments.
          </p>
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3 md:gap-4 w-full">
            <div
              v-for="(child, index) in children"
              :key="index"
              class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
              @click="selectChild(child.id)"
            >
              <div class="flex items-center">
                <img :src="child.avatar" :alt="`${child.name} Avatar`" class="w-10 h-10 md:w-12 md:h-12 rounded-full mr-3 md:mr-4" />
                <div>
                  <h4 class="font-medium">{{ child.name }}</h4>
                  <p class="text-xs md:text-sm text-gray-500 dark:text-gray-400">{{ child.grade }}</p>
                  <p class="text-xs md:text-sm mt-1">
                    GPA: <span :class="`px-1.5 py-0.5 rounded text-xs ${getGradeClass(calculateGPA(child))}`">{{ calculateGPA(child) }}</span>
                  </p>
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
.bg-primary-500 { background-color: rgb(var(--primary-500)); }
.bg-primary-600 { background-color: rgb(var(--primary-600)); }
.text-primary-600 { color: rgb(var(--primary-600)); }
.text-primary-400 { color: rgb(var(--primary-400)); }

/* Add Tailwind dark mode support */
.dark .dark\:bg-primary-900 { background-color: rgb(var(--primary-900)); }
.dark .dark\:bg-primary-500 { background-color: rgb(var(--primary-500)); }
.dark .dark\:text-primary-400 { color: rgb(var(--primary-400)); }
.dark .dark\:text-primary-300 { color: rgb(var(--primary-300)); }

/* Responsive table styles */
@media (max-width: 768px) {
  .overflow-x-auto {
    -webkit-overflow-scrolling: touch;
  }
}
</style>