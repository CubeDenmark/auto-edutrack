<script setup lang="ts">
import AppLayout from "@/layouts/AppLayout.vue";
import { type BreadcrumbItem } from "@/types";
import { Head, usePage } from "@inertiajs/vue3";
import { ref, computed, onMounted, inject, watch } from "vue";

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: "Grades",
    href: "/grades",
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
const searchQuery = ref('');
const sortBy = ref('name');
const sortDirection = ref('asc');
const statusFilter = ref('all');
const gradeFilter = ref('all');
const selectedTerm = ref('Prelim'); // Default to midterm

// Term weight variables
const prelimWeight = ref(30); // Default 30%
const midtermWeight = ref(30); // Default 30%
const finalTermWeight = ref(40); // Default 40%

// Available terms
const terms = computed(() => {
  if (!currentSection.value || !currentSection.value.assessments) return [];

  // Extract unique terms from assessments
  const uniqueTerms = new Set();
  currentSection.value.assessments.forEach(assessment => {
    if (assessment.term && assessment.term !== 'Prelim') {
      uniqueTerms.add(assessment.term);
    }
  });

  // Convert to array of term objects with Prelim first
  return [
    { id: 'Prelim', name: 'Prelim' },
    ...Array.from(uniqueTerms).map(term => ({
      id: term,
      name: term === 'Midterm' ? 'Midterm' : term === 'Final Term' ? 'Final Term' : String(term)
    }))
  ];
});


// Get sections from page props
const sections = usePage().props.sections || [];

// Calculate letter grade based on numeric grade
function calculateLetterGrade(grade) {
  if (grade >= 90) return 'A';
  if (grade >= 80) return 'B';
  if (grade >= 70) return 'C';
  if (grade >= 60) return 'D';
  return 'F';
}

// Calculate the total points for an assessment by summing up its items' total_points
function calculateAssessmentTotalPoints(assessment) {
  if (!assessment || !assessment.items || !Array.isArray(assessment.items)) return 0;

  return assessment.items.reduce((total, item) => {
    return total + Number(item.total_score || 0); // Explicitly cast to number
  }, 0);
}

// Calculate grade for a specific term
function calculateTermGrade(student, assessments, termName) {
  if (!student || !assessments || !Array.isArray(assessments)) return 0;

  let totalWeightedScore = 0;
  let totalWeight = 0;

  if (student.assessment_grades) {
    Object.values(student.assessment_grades).forEach((assessmentGrade) => {
      const assessment = assessments.find(a => a.id === assessmentGrade.assessment_id);

      // Skip if not found or term doesn't match
      if (!assessment || (assessment.term && assessment.term !== termName)) return;

      const score = Number(assessmentGrade.total_score || 0);
      const totalPossible = calculateAssessmentTotalPoints(assessment);
      const weight = parseFloat(assessment.weight) || 0;

      if (totalPossible > 0) {
        const percentage = (score / totalPossible) * 100;
        const weightedScore = (percentage * weight) / 100; // Proper weighted formula
        totalWeightedScore += weightedScore;
        totalWeight += weight;
      }
    });
  }

  // If no weights were found, return 0
  if (totalWeight === 0) return 0;

  return parseFloat(totalWeightedScore.toFixed(2));
}

// Get assessment score for a student
function getAssessmentScore(student, assessmentId) {
  if (!student || !student.assessment_grades) return 0;
  
  // Check if student has this assessment grade
  const assessmentGrade = Object.values(student.assessment_grades).find(
    (grade: any) => grade.assessment_id === assessmentId
  );
  
  return assessmentGrade ? assessmentGrade.total_score : 0;
}

// Get assessment percentage score
function getAssessmentPercentage(score, totalPoints) {
  if (!totalPoints) return 0;
  return Math.round((score / totalPoints) * 100);
}

const classSections = computed(() => {
  // If sections from the server are available, use them
  if (sections && sections.length > 0) {
    return sections.map(section => {
      // Process assessments to calculate total points
      const processedAssessments = section.assessments && Array.isArray(section.assessments)
        ? section.assessments.map(assessment => {
            const totalPoints = calculateAssessmentTotalPoints(assessment);
            return {
              ...assessment,
              totalPoints: totalPoints
            };
          })
        : [];
      
      // Process students to ensure they have all required properties
      const students = section.students && Array.isArray(section.students)
        ? section.students.map(student => {
            // Calculate grades for each term
            const prelimGrade = calculateTermGrade(student, processedAssessments, 'Prelim');
            const midtermGrade = calculateTermGrade(student, processedAssessments, 'Midterm');
            const finalGrade = calculateTermGrade(student, processedAssessments, 'Final Term');
            
            // Calculate letter grades for each term
            const prelimLetterGrade = calculateLetterGrade(prelimGrade);
            const midtermLetterGrade = calculateLetterGrade(midtermGrade);
            const finalLetterGrade = calculateLetterGrade(finalGrade);
            
            return {
              ...student,
              grades: student.grades || {},
              prelimGrade,
              midtermGrade,
              finalGrade,
              prelimLetterGrade,
              midtermLetterGrade,
              finalLetterGrade
            };
          })
        : [];

      return {
        ...section,
        assessments: processedAssessments,
        students: students
      };
    });
  }
  
  // If no sections from server, return empty array
  return [];
});

// Get the current term grade for a student
function getCurrentTermGrade(student) {
  if (!student) return 0;
  
  switch (selectedTerm.value) {
    case 'Prelim':
      return student.prelimGrade || 0;
    case 'Midterm':
      return student.midtermGrade || 0;
    case 'Final Term':
      return student.finalGrade || 0;
    case 'Final':
      // For Final Grading, we could return an average or just the final term grade
      return calculateFinalGrade(student);
    default:
      return student.midtermGrade || 0;
  }
}

// Get the current term letter grade for a student
function getCurrentTermLetterGrade(student) {
  if (!student) return 'F';
  
  switch (selectedTerm.value) {
    case 'Prelim':
      return student.prelimLetterGrade || 'F';
    case 'Midterm':
      return student.midtermLetterGrade || 'F';
    case 'Final Term':
      return student.finalLetterGrade || 'F';
    case 'Final':
      // For Final Grading, calculate the letter grade based on the weighted average
      return calculateLetterGrade(calculateFinalGrade(student));
    default:
      return student.midtermLetterGrade || 'F';
  }
}

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
  
  // If there are terms available, select the first one
  if (terms.value.length > 0) {
    selectedTerm.value = terms.value[0].id;
  }

  // Update URL with the selected section ID without navigating
  const url = new URL(window.location.href);
  url.searchParams.set('sectionId', section.id);
  window.history.replaceState({}, '', url.toString());

  // Dispatch an event to notify other components (like the sidebar)
  window.dispatchEvent(new CustomEvent('section-changed', {
    detail: {
      sectionId: section.id,
      source: 'grades'
    }
  }));

  // Also dispatch the item-changed event for newer sidebar implementations
  window.dispatchEvent(new CustomEvent('item-changed', {
    detail: {
      itemId: section.id,
      itemType: 'section',
      source: 'grades'
    }
  }));
}

// Get CSS class for grade display
function getGradeClass(grade) {
  if (typeof grade === 'string') {
    switch (grade) {
      case 'A': return 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900 dark:text-emerald-300';
      case 'B': return 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300';
      case 'C': return 'bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-300';
      case 'D': return 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-300';
      case 'F': return 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300';
      default: return 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300';
    }
  } else {
    if (grade >= 90) return 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900 dark:text-emerald-300';
    if (grade >= 80) return 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300';
    if (grade >= 70) return 'bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-300';
    if (grade >= 60) return 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-300';
    return 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300';
  }
}

// Get color for category badge
function getCategoryColor(category) {
  switch (category) {
    case 'quiz': return 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300';
    case 'exam': return 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300';
    case 'lab': return 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900 dark:text-emerald-300';
    case 'project': return 'bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-300';
    default: return 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300';
  }
}

// Update student grades when input changes
function updateStudentGrades(student) {
  if (!student || !currentSection.value) return;

  // Calculate grades for each term
  student.prelimGrade = calculateTermGrade(student, currentSection.value.assessments, 'Prelim');
  student.midtermGrade = calculateTermGrade(student, currentSection.value.assessments, 'Midterm');
  student.finalGrade = calculateTermGrade(student, currentSection.value.assessments, 'Final Term');
  
  // Update letter grades
  student.prelimLetterGrade = calculateLetterGrade(student.prelimGrade);
  student.midtermLetterGrade = calculateLetterGrade(student.midtermGrade);
  student.finalLetterGrade = calculateLetterGrade(student.finalGrade);
}

// Calculate final grade (weighted average of all terms)
function calculateFinalGrade(student) {
  if (!student) return 0;
  
  // Convert weights to decimal percentages
  const pWeight = prelimWeight.value / 100;
  const mWeight = midtermWeight.value / 100;
  const fWeight = finalTermWeight.value / 100;
  
  // Calculate weighted average
  const weightedGrade = 
    (student.prelimGrade || 0) * pWeight + 
    (student.midtermGrade || 0) * mWeight + 
    (student.finalGrade || 0) * fWeight;
  
  return parseFloat(weightedGrade.toFixed(2));
}

// Ensure weights always add up to 100%
function adjustWeights(changedWeight, value) {
  // Convert value to number
  const numValue = parseInt(value, 10);
  
  // Validate input
  if (isNaN(numValue) || numValue < 0) {
    return;
  }
  
  // Update the changed weight
  if (changedWeight === 'prelim') {
    prelimWeight.value = numValue;
  } else if (changedWeight === 'midterm') {
    midtermWeight.value = numValue;
  } else if (changedWeight === 'final') {
    finalTermWeight.value = numValue;
  }
  
  // Calculate remaining weight
  const totalWeight = prelimWeight.value + midtermWeight.value + finalTermWeight.value;
  
  // Adjust weights if they don't add up to 100%
  if (totalWeight !== 100) {
    // If the changed weight is not prelim, adjust prelim
    if (changedWeight !== 'prelim' && totalWeight > 0) {
      const remaining = 100 - midtermWeight.value - finalTermWeight.value;
      prelimWeight.value = Math.max(0, remaining);
    }
    // If the changed weight is not midterm, adjust midterm
    else if (changedWeight !== 'midterm' && totalWeight > 0) {
      const remaining = 100 - prelimWeight.value - finalTermWeight.value;
      midtermWeight.value = Math.max(0, remaining);
    }
    // If the changed weight is not final, adjust final
    else if (changedWeight !== 'final' && totalWeight > 0) {
      const remaining = 100 - prelimWeight.value - midtermWeight.value;
      finalTermWeight.value = Math.max(0, remaining);
    }
  }
}

// Sort students
function sortStudents(a, b) {
  const direction = sortDirection.value === 'asc' ? 1 : -1;

  if (sortBy.value === 'name') {
    return (a.name || '').localeCompare(b.name || '') * direction;
  } else if (sortBy.value === 'id') {
    // Fix for ID sorting - ensure we're comparing strings
    const idA = String(a.id || '');
    const idB = String(b.id || '');
    return idA.localeCompare(idB) * direction;
  } else if (sortBy.value === 'finalGrade') {
    // Sort by the current term's grade
    const gradeA = getCurrentTermGrade(a);
    const gradeB = getCurrentTermGrade(b);
    return (gradeA - gradeB) * direction;
  } else if (sortBy.value === 'letterGrade') {
    // Sort by the current term's letter grade
    const letterGradeA = getCurrentTermLetterGrade(a);
    const letterGradeB = getCurrentTermLetterGrade(b);
    return letterGradeA.localeCompare(letterGradeB) * direction;
  }

  return 0;
}

// Toggle sort direction
function toggleSort(field) {
  if (sortBy.value === field) {
    sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
  } else {
    sortBy.value = field;
    sortDirection.value = 'asc';
  }
}

// Filter students by search query, status, and grade
const filteredStudents = computed(() => {
  if (!currentSection.value || !currentSection.value.students) return [];

  return currentSection.value.students
    .filter(student => {
      // Filter by search query
      if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        const matchesSearch =
          (student.name || '').toLowerCase().includes(query) ||
          (student.id || '').toLowerCase().includes(query) ||
          (student.email || '').toLowerCase().includes(query);

        if (!matchesSearch) return false;
      }

      // Filter by status
      if (statusFilter.value !== 'all') {
        if (statusFilter.value !== (student.status || 'active')) return false;
      }

      // Filter by grade - use the current term's letter grade
      if (gradeFilter.value !== 'all') {
        const currentLetterGrade = getCurrentTermLetterGrade(student);
        if (gradeFilter.value !== currentLetterGrade) return false;
      }

      return true;
    })
    .sort(sortStudents);
});

// Filter assessments by selected term
const filteredAssessments = computed(() => {
  if (!currentSection.value || !currentSection.value.assessments) return [];

  // If Final Grading is selected, return empty array (we'll show different columns)
  if (selectedTerm.value === 'Final') return [];

  return (currentSection.value.assessments || []).filter(assessment =>
    !assessment.term || assessment.term === selectedTerm.value
  );
});

// Group assessments by term
const assessmentsByTerm = computed(() => {
  if (!currentSection.value || !currentSection.value.assessments) return {};
  
  const grouped = {};
  
  currentSection.value.assessments.forEach(assessment => {
    const term = assessment.term || 'other';
    if (!grouped[term]) {
      grouped[term] = [];
    }
    grouped[term].push(assessment);
  });
  
  return grouped;
});




// Computed property for class average - based on current term
const classAverage = computed(() => {
  if (!currentSection.value || !currentSection.value.students) return 0;

  const activeStudents = (currentSection.value.students || []).filter(student => (student.status || 'active') === 'active');
  if (activeStudents.length === 0) return 0;

  // Sum the grades for the current term
  const sum = activeStudents.reduce((total, student) => {
    return total + getCurrentTermGrade(student);
  }, 0);
  
  return Math.round(sum / activeStudents.length);
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

// Function to handle section-changed events
const handleSectionChange = (event) => {
  if (event.detail && event.detail.sectionId && event.detail.source !== 'grades') {
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
  
  // If no section is selected and we have sections, select the first one
  if (!currentSection.value && classSections.value.length > 0) {
    currentSection.value = classSections.value[0];
  }
});

// Clean up event listeners when component is unmounted
onMounted(() => {
  return () => {
    window.removeEventListener('item-changed', handleItemChange);
    window.removeEventListener('section-changed', handleSectionChange);
  };
});
</script>

<template>
<Head title="Grades" />

<AppLayout :breadcrumbs="breadcrumbs">
  <div class="p-6 pt-20 w-full">
    <!-- No Section Selected Message -->
    <div v-if="!currentSection" class="flex flex-col items-center justify-center h-[calc(100vh-10rem)] text-center w-full">
      <div class="bg-gradient-to-br from-indigo-50 to-purple-50 dark:from-indigo-900/30 dark:to-purple-900/30 p-8 rounded-full mb-6 shadow-inner">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-20 w-20 text-indigo-500 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
        </svg>
      </div>
      <h2 class="text-3xl font-bold mb-3 text-gray-900 dark:text-white">Select a Class Section</h2>
      <p class="text-gray-600 dark:text-gray-400 mb-8 text-lg">Please select a class section to view and manage grades.</p>
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 w-full">
        <button
          v-for="section in classSections.slice(0, 4)"
          :key="section.id"
          @click="selectSection(section)"
          class="flex flex-col items-center p-6 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200 shadow-sm hover:shadow-md group"
        >
          <span class="text-4xl mb-3 transform group-hover:scale-110 transition-transform duration-200">{{ section.icon }}</span>
          <span class="text-base font-medium text-gray-900 dark:text-white">{{ section.name }}</span>
          <span class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ section.studentCount }} Students</span>
        </button>
      </div>
    </div>

    <!-- Grades Page -->
    <div v-if="currentSection" class="space-y-8 w-full">
      <!-- Header with Class Info -->
      <div class="bg-gradient-to-br from-indigo-600 to-violet-600 dark:from-indigo-800 dark:to-violet-800 rounded-2xl shadow-lg overflow-hidden w-full">
        <div class="p-8 text-white relative">
          <div class="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full -mt-20 -mr-20 blur-3xl"></div>
          <div class="absolute bottom-0 left-0 w-40 h-40 bg-white/10 rounded-full -mb-10 -ml-10 blur-2xl"></div>
          <div class="relative z-10">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 w-full">
              <div>
                <div class="flex items-center gap-3">
                  <span class="text-4xl">{{ currentSection.icon }}</span>
                  <div>
                    <h1 class="text-2xl md:text-3xl font-bold">{{ currentSection.name }}</h1>
                    <p class="mt-1 text-indigo-100 dark:text-indigo-200">{{ currentSection.term || 'Current Term' }} • {{ currentSection.studentCount || 0 }} Students</p>
                  </div>
                </div>
              </div>
              <div class="flex items-center gap-3">
                <div class="bg-white/20 backdrop-blur-sm rounded-lg px-4 py-2 flex items-center gap-2">
                  <span class="text-sm font-medium">Class Average:</span>
                  <span class="text-xl font-bold">{{ classAverage }}%</span>
                  <span :class="`text-xs font-medium px-2 py-0.5 rounded-full ml-1 ${getGradeClass(classAverage)}`">
                    {{ classAverage >= 90 ? 'A' : classAverage >= 80 ? 'B' : classAverage >= 70 ? 'C' : classAverage >= 60 ? 'D' : 'F' }}
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Term Selection -->
      <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-md overflow-hidden border border-gray-100 dark:border-gray-700 w-full">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
          <div class="flex justify-between items-center w-full">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Term Selection</h2>
          </div>
        </div>
        <div class="p-6">
          <div class="flex flex-col sm:flex-row gap-4 w-full">
            <div class="w-full">
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Select Term</label>
              <div class="grid grid-cols-4 gap-4 w-full">
                <button
                  v-for="term in terms"
                  :key="term.id"
                  @click="selectedTerm = term.id"
                  :class="[
                    'px-4 py-3 rounded-lg text-center font-medium transition-colors duration-200 w-full',
                    selectedTerm === term.id
                      ? 'bg-indigo-600 text-white shadow-md'
                      : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600'
                  ]"
                >
                  {{ term.name }}
                </button>
                <button
                  @click="selectedTerm = 'Final'"
                  :class="[
                    'px-4 py-3 rounded-lg text-center font-medium transition-colors duration-200 w-full',
                    selectedTerm === 'Final'
                      ? 'bg-indigo-600 text-white shadow-md'
                      : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600'
                  ]"
                >
                  Final Grading
                </button>
              </div>
              <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                {{ selectedTerm === 'Final' ? 'Viewing final grades for all terms' : `Viewing grades for ${selectedTerm} assessments` }}
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- Grades Content -->
      <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-md overflow-hidden border border-gray-100 dark:border-gray-700 w-full">
        <!-- Search and Filter -->
        <div class="p-6 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 w-full">
          <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between w-full">
            <div class="relative w-full sm:w-1/2">
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
              </div>
              <input
                type="text"
                v-model="searchQuery"
                placeholder="Search students..."
                class="pl-10 w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm focus:ring-indigo-500 focus:border-indigo-500 shadow-sm py-2.5"
              />
            </div>
          </div>
        </div>

        <!-- Student Grades Table -->
        <div class="overflow-x-auto w-full">
          <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 w-full">
            <thead class="bg-indigo-50 dark:bg-indigo-900/30">
              <tr>
                <th scope="col" class="px-6 py-4 text-left text-sm font-semibold text-indigo-700 dark:text-indigo-300 uppercase tracking-wide cursor-pointer" @click="toggleSort('name')">
                  <div class="flex items-center">
                    Student
                    <svg v-if="sortBy === 'name'" xmlns="http://www.w3.org/2000/svg" class="ml-2 h-5 w-5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="sortDirection === 'asc' ? 'M5 15l7-7 7 7' : 'M19 9l-7 7-7-7'" />
                    </svg>
                  </div>
                </th>
                <th scope="col" class="px-6 py-4 text-left text-sm font-semibold text-indigo-700 dark:text-indigo-300 uppercase tracking-wide cursor-pointer" @click="toggleSort('id')">
                  <div class="flex items-center">
                    ID
                    <svg v-if="sortBy === 'id'" xmlns="http://www.w3.org/2000/svg" class="ml-2 h-5 w-5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="sortDirection === 'asc' ? 'M5 15l7-7 7 7' : 'M19 9l-7 7-7-7'" />
                    </svg>
                  </div>
                </th>
                <!-- Show assessment columns only when not in Final Grading mode -->
                <template v-if="selectedTerm !== 'Final'">
                  <th v-for="assessment in filteredAssessments" :key="assessment.id" scope="col" class="px-6 py-4 text-left text-sm font-semibold text-indigo-700 dark:text-indigo-300 uppercase tracking-wide">
                    {{ assessment.assessment_name }}
                    <div class="text-xs font-normal text-indigo-500 dark:text-indigo-400 normal-case">
                    Weight: {{ assessment.weight }}%
                    </div>
                  </th>
                </template>
                <!-- Show term grade columns when in Final Grading mode -->
                <template v-if="selectedTerm === 'Final'">
                  <th scope="col" class="px-6 py-4 text-left text-sm font-semibold text-indigo-700 dark:text-indigo-300 uppercase tracking-wide">
                    Prelim Grade
                    <div class="text-xs font-normal text-indigo-500 dark:text-indigo-400 normal-case flex items-center mt-1">
                      Weight: 
                      <input 
                        type="number" 
                        v-model="prelimWeight" 
                        @input="adjustWeights('prelim', $event.target.value)"
                        min="0" 
                        max="100" 
                        class="ml-1 w-12 px-1 py-0.5 rounded border border-indigo-300 dark:border-indigo-700 bg-white dark:bg-gray-800 text-center"
                      />%
                    </div>
                  </th>
                  <th scope="col" class="px-6 py-4 text-left text-sm font-semibold text-indigo-700 dark:text-indigo-300 uppercase tracking-wide">
                    Midterm Grade
                    <div class="text-xs font-normal text-indigo-500 dark:text-indigo-400 normal-case flex items-center mt-1">
                      Weight: 
                      <input 
                        type="number" 
                        v-model="midtermWeight" 
                        @input="adjustWeights('midterm', $event.target.value)"
                        min="0" 
                        max="100" 
                        class="ml-1 w-12 px-1 py-0.5 rounded border border-indigo-300 dark:border-indigo-700 bg-white dark:bg-gray-800 text-center"
                      />%
                    </div>
                  </th>
                  <th scope="col" class="px-6 py-4 text-left text-sm font-semibold text-indigo-700 dark:text-indigo-300 uppercase tracking-wide">
                    Final Term Grade
                    <div class="text-xs font-normal text-indigo-500 dark:text-indigo-400 normal-case flex items-center mt-1">
                      Weight: 
                      <input 
                        type="number" 
                        v-model="finalTermWeight" 
                        @input="adjustWeights('final', $event.target.value)"
                        min="0" 
                        max="100" 
                        class="ml-1 w-12 px-1 py-0.5 rounded border border-indigo-300 dark:border-indigo-700 bg-white dark:bg-gray-800 text-center"
                      />%
                    </div>
                  </th>
                </template>
                <th
                  scope="col"
                  class="px-6 py-4 text-left text-sm font-semibold text-indigo-700 dark:text-indigo-300 uppercase tracking-wide cursor-pointer"
                  @click="toggleSort('finalGrade')"
                >
                  <div class="flex items-center">
                    {{ selectedTerm === 'Final' ? 'Final Grade' : `${selectedTerm} Grade` }}
                    <svg v-if="sortBy === 'finalGrade'" xmlns="http://www.w3.org/2000/svg" class="ml-2 h-5 w-5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        :d="sortDirection === 'asc' ? 'M5 15l7-7 7 7' : 'M19 9l-7 7-7-7'"
                      />
                    </svg>
                  </div>
                </th>
                <th scope="col" class="px-6 py-4 text-left text-sm font-semibold text-indigo-700 dark:text-indigo-300 uppercase tracking-wide cursor-pointer" @click="toggleSort('letterGrade')">
                  <div class="flex items-center">
                    Letter
                    <svg v-if="sortBy === 'letterGrade'" xmlns="http://www.w3.org/2000/svg" class="ml-2 h-5 w-5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="sortDirection === 'asc' ? 'M5 15l7-7 7 7' : 'M19 9l-7 7-7-7'" />
                    </svg>
                  </div>
                </th>
                <th scope="col" class="px-6 py-4 text-left text-sm font-semibold text-indigo-700 dark:text-indigo-300 uppercase tracking-wide">Status</th>
              </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
              <tr v-for="(student, index) in filteredStudents" :key="index" class="hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                <td class="px-6 py-4 flex items-center space-x-3">
                  <img class="h-10 w-10 rounded-full shadow-md" :src="`https://i.pravatar.cc/100?img=${index + 10}`" alt="" />
                  <div>
                    <p class="text-sm font-medium text-gray-900 dark:text-white">{{ student.name || 'Unnamed Student' }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ student.email || 'No email' }}</p>
                  </div>
                </td>
                <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">{{ student.id || 'No ID' }}</td>
                
                <!-- Show assessment columns only when not in Final Grading mode -->
                <template v-if="selectedTerm !== 'Final'">
                  <td v-for="assessment in filteredAssessments" :key="assessment.id" class="px-6 py-4">
                    <div class="space-y-2">
                      <!-- Find the matching assessment grade for this student and assessment -->
                      <div v-if="student.assessment_grades && Object.values(student.assessment_grades).find(g => g.assessment_id === assessment.id)" class="font-medium text-sm">
                        {{ Object.values(student.assessment_grades).find(g => g.assessment_id === assessment.id).total_score || 0 }} / {{ calculateAssessmentTotalPoints(assessment) }}
                        <span class="text-xs text-gray-500 dark:text-gray-400 ml-1">
                          ({{ calculateAssessmentTotalPoints(assessment) > 0 ? Math.round(((Object.values(student.assessment_grades).find(g => g.assessment_id === assessment.id).total_score || 0) / calculateAssessmentTotalPoints(assessment)) * 100) : 0 }}%)
                        </span>
                      </div>
                      <!-- If no grade exists for this assessment -->
                      <div v-else class="font-medium text-sm text-gray-400 dark:text-gray-500">
                        0 / {{ calculateAssessmentTotalPoints(assessment) }}
                        <span class="text-xs text-gray-400 dark:text-gray-500 ml-1">(0%)</span>
                      </div>
                    </div>
                  </td>
                </template>
                
                <!-- Show term grade columns when in Final Grading mode -->
                <template v-if="selectedTerm === 'Final'">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span class="text-sm font-medium text-gray-900 dark:text-white">
                      {{ isFinite(student.prelimGrade) ? student.prelimGrade.toFixed(2) : '0.00' }}%
                    </span>
                    <span :class="`ml-2 inline-flex items-center px-2 py-0.5 rounded-lg text-xs font-medium ${getGradeClass(student.prelimLetterGrade)}`">
                      {{ student.prelimLetterGrade }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span class="text-sm font-medium text-gray-900 dark:text-white">
                      {{ isFinite(student.midtermGrade) ? student.midtermGrade.toFixed(2) : '0.00' }}%
                    </span>
                    <span :class="`ml-2 inline-flex items-center px-2 py-0.5 rounded-lg text-xs font-medium ${getGradeClass(student.midtermLetterGrade)}`">
                      {{ student.midtermLetterGrade }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span class="text-sm font-medium text-gray-900 dark:text-white">
                      {{ isFinite(student.finalGrade) ? student.finalGrade.toFixed(2) : '0.00' }}%
                    </span>
                    <span :class="`ml-2 inline-flex items-center px-2 py-0.5 rounded-lg text-xs font-medium ${getGradeClass(student.finalLetterGrade)}`">
                      {{ student.finalLetterGrade }}
                    </span>
                  </td>
                </template>
                
                <td class="px-6 py-4 whitespace-nowrap">
                  <span class="text-sm font-medium text-gray-900 dark:text-white">
                    {{ selectedTerm === 'Final' 
                      ? isFinite(calculateFinalGrade(student)) 
                        ? calculateFinalGrade(student).toFixed(2) 
                        : '0.00'
                      : isFinite(getCurrentTermGrade(student)) 
                        ? getCurrentTermGrade(student).toFixed(2) 
                        : '0.00' }}%
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span :class="`inline-flex items-center px-3 py-1 rounded-lg text-xs font-medium ${
                    selectedTerm === 'Final' 
                      ? getGradeClass(calculateLetterGrade(calculateFinalGrade(student)))
                      : getGradeClass(getCurrentTermLetterGrade(student))
                  }`">
                    {{ selectedTerm === 'Final' 
                      ? calculateLetterGrade(calculateFinalGrade(student))
                      : getCurrentTermLetterGrade(student) }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span :class="{
                    'bg-emerald-100 text-emerald-800 dark:bg-emerald-900 dark:text-emerald-300': (student.status || 'active') === 'active',
                    'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300': (student.status || 'active') === 'inactive'
                  }" class="px-3 py-1 rounded-full text-xs font-medium capitalize">
                    {{ student.status || 'active' }}
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Empty State -->
        <div v-if="filteredStudents.length === 0" class="p-12 text-center w-full">
          <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 dark:bg-gray-700 mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
          <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-1">No students found</h3>
          <p class="text-gray-500 dark:text-gray-400">Try adjusting your search or filter criteria</p>
        </div>

        <!-- Footer - Save button removed as requested -->
        <div class="p-8 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 flex justify-end w-full">
          <!-- Save button removed as per request -->
        </div>
      </div>
    </div>
  </div>

</AppLayout>
</template>