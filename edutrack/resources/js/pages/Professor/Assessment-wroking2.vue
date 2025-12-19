<script setup lang="ts">
import AppLayout from "@/layouts/AppLayout.vue";
import { type BreadcrumbItem } from "@/types";
import { Head, usePage } from "@inertiajs/vue3";
import { ref, computed, onMounted, inject, watch } from "vue";

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

// State variables
const currentSection = ref<any>(null);
const selectedAssessment = ref<string | null>(null);
const showAddAssignmentModal = ref(false);

// Assessment management state
const showModal = ref(false);
const isEditing = ref(false);
const editingId = ref(null);
const form = ref({
  name: '',
  type: '',
  weight: 0
});
const originalForm = ref({});
const assessments = ref([]);
const errors = ref({});
const loading = ref(false);
const message = ref({ text: '', type: '' });
const filters = ref({
  classId: '',
  type: '',
  search: ''
});

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

// Form states for new assignment
const newAssignment = ref({
  id: '',
  name: '',
  category: 'quiz',
  weight: 10,
  totalPoints: 100,
  dueDate: ''
});

// Fallback data for sections - simplified without student data
const fallbackSections = [
  {
    id: 'sci101a',
    name: 'Science 101 - Section A',
    icon: '🧪',
    term: 'Fall Semester 2023',
    studentCount: 28,
    pendingAssignments: 3,
    assignments: [
      {
        id: 'midterm',
        name: 'Midterm Exam',
        category: 'exam',
        weight: 30,
        totalPoints: 100,
        dueDate: '2023-10-15',
        term: 'midterm',
        items: [
          { id: 1, name: 'Multiple Choice', total_score: 50 },
          { id: 2, name: 'Essay Questions', total_score: 30 },
          { id: 3, name: 'Problem Solving', total_score: 20 }
        ]
      },
      {
        id: 'quiz1',
        name: 'Quiz 1',
        category: 'quiz',
        weight: 10,
        totalPoints: 50,
        dueDate: '2023-09-20',
        term: 'midterm',
        items: [
          { id: 4, name: 'Chapter 1 Questions', total_score: 25 },
          { id: 5, name: 'Chapter 2 Questions', total_score: 25 }
        ]
      },
      {
        id: 'lab',
        name: 'Lab Report',
        category: 'lab',
        weight: 15,
        totalPoints: 100,
        dueDate: '2023-10-05',
        term: 'midterm',
        items: [
          { id: 6, name: 'Procedure', total_score: 20 },
          { id: 7, name: 'Results', total_score: 40 },
          { id: 8, name: 'Discussion', total_score: 40 }
        ]
      },
      {
        id: 'final',
        name: 'Final Exam',
        category: 'exam',
        weight: 35,
        totalPoints: 100,
        dueDate: '2023-12-15',
        term: 'final',
        items: [
          { id: 9, name: 'Multiple Choice', total_score: 50 },
          { id: 10, name: 'Essay Questions', total_score: 30 },
          { id: 11, name: 'Problem Solving', total_score: 20 }
        ]
      },
      {
        id: 'project',
        name: 'Final Project',
        category: 'project',
        weight: 25,
        totalPoints: 100,
        dueDate: '2023-11-20',
        term: 'final',
        items: [
          { id: 12, name: 'Proposal', total_score: 10 },
          { id: 13, name: 'Implementation', total_score: 50 },
          { id: 14, name: 'Presentation', total_score: 40 }
        ]
      }
    ]
  }
];

// Get sections from page props or use fallback data
const sections = usePage().props.sections || [];

// Create class sections data from sections or fallback data
const classSections = computed(() => {
  // If sections from the server are available, use them with fallback properties
  if (sections && sections.length > 0) {
    return sections.map(section => {
      // Find a matching fallback section if available
      const fallbackSection = fallbackSections.find(fs => fs.id === section.id) || fallbackSections[0];

      // Ensure assignments array exists and has proper structure
      const assignments = section.assignments && Array.isArray(section.assignments)
        ? section.assignments.map(assignment => {
            // Add items array if not present
            const items = assignment.items || [];

            return {
              id: assignment.id || 'assignment',
              name: assignment.name || 'Assignment',
              category: assignment.category || 'exam',
              weight: assignment.weight || 10,
              totalPoints: assignment.totalPoints || 100,
              dueDate: assignment.dueDate || new Date().toISOString().split('T')[0],
              term: assignment.term || 'midterm',
              items: items
            };
          })
        : fallbackSection.assignments || [];

      return {
        id: section.id,
        name: section.name || fallbackSection.name || 'Unnamed Section',
        icon: section.icon || fallbackSection.icon || '📚',
        term: section.term || fallbackSection.term || 'Current Term',
        studentCount: section.studentCount || 0,
        pendingAssignments: section.pendingAssignments || fallbackSection.pendingAssignments || 0,
        assignments: assignments
      };
    });
  }
  // If no sections from server, use fallback data
  return fallbackSections;
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

  let sectionAssessments = currentSection.value.assignments.map(assignment => ({
    id: assignment.id,
    class_id: currentSection.value.id,
    name: assignment.name,
    type: assignment.category.charAt(0).toUpperCase() + assignment.category.slice(1), // Capitalize first letter
    weight: assignment.weight,
    dueDate: assignment.dueDate,
    items: assignment.items || []
  }));

  return sectionAssessments.filter(assessment => {
    const matchesType = !filters.value.type || assessment.type.toLowerCase() === filters.value.type.toLowerCase();
    const matchesSearch = !filters.value.search ||
      assessment.name.toLowerCase().includes(filters.value.search.toLowerCase());

    return matchesType && matchesSearch;
  });
});

// Get all assessment items for the current section
const allAssessmentItems = computed(() => {
  if (!currentSection.value) return [];

  let items = [];

  currentSection.value.assignments.forEach(assignment => {
    if (assignment.items && assignment.items.length > 0) {
      const assignmentItems = assignment.items.map(item => ({
        ...item,
        assessment_id: assignment.id,
        assessment_name: assignment.name
      }));

      items = [...items, ...assignmentItems];
    }
  });

  return items;
});

// Fixed calculation of class weights
const classWeights = computed(() => {
  const weights = {};

  classSections.value.forEach(section => {
    let totalWeight = 0;
    section.assignments.forEach(assignment => {
      // If we're editing, don't count the original weight of this assignment
      if (!(isEditing.value && assignment.id === editingId.value)) {
        totalWeight += parseFloat(assignment.weight) || 0;
      }
    });
    weights[section.id] = totalWeight;
  });

  return weights;
});

const classWeightSummary = computed(() => {
  const summary = [];

  for (const classId in classWeights.value) {
    const classItem = classes.value.find(c => c.id === classId);
    if (classItem) {
      summary.push({
        classId,
        className: classItem.name,
        totalWeight: classWeights.value[classId]
      });
    }
  }

  return summary;
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
  selectedAssessment.value = section.assignments[0]?.id || null;

  // Update URL with the selected section ID without navigating
  const url = new URL(window.location.href);
  url.searchParams.set('sectionId', section.id);
  window.history.replaceState({}, '', url.toString());

  // Dispatch an event to notify other components (like the sidebar)
  window.dispatchEvent(new CustomEvent('section-changed', {
    detail: {
      sectionId: section.id,
      source: 'assessment'
    }
  }));

  // Also dispatch the item-changed event for newer sidebar implementations
  window.dispatchEvent(new CustomEvent('item-changed', {
    detail: {
      itemId: section.id,
      itemType: 'section',
      source: 'assessment'
    }
  }));
}

// Generate a unique ID for new assignments
function generateAssignmentId() {
  return 'assignment_' + Date.now().toString(36);
}

// Generate a unique ID for new assessment items
function generateItemId() {
  return Date.now();
}

// Open add assignment modal
function openAddAssignmentModal() {
  if (!currentSection.value) return;

  // Reset form
  const today = new Date();
  const formattedDate = today.toISOString().split('T')[0];

  newAssignment.value = {
    id: generateAssignmentId(),
    name: '',
    category: 'quiz',
    weight: 10,
    totalPoints: 100,
    dueDate: formattedDate
  };

  showAddAssignmentModal.value = true;
}

// Add a new assignment
function addAssignment() {
  if (!currentSection.value) return;

  // Add the new assignment to the current section
  currentSection.value.assignments.push({...newAssignment.value, items: []});

  // Update pending assignments count
  currentSection.value.pendingAssignments += 1;

  // Set the new assignment as selected
  selectedAssessment.value = newAssignment.value.id;

  showAddAssignmentModal.value = false;
}

// Open assessment items modal
function openItemsModal(assessment = null) {
  if (assessment) {
    selectedAssessmentForItems.value = assessment;
    assessmentItems.value = assessment.items || [];
  } else {
    selectedAssessmentForItems.value = null;
    assessmentItems.value = [];
  }

  showItemModal.value = true;
  resetItemForm();
}

// Reset assessment item form
function resetItemForm() {
  itemForm.value = {
    name: '',
    total_score: 100
  };
  itemErrors.value = {};
}

// Close assessment items modal
function closeItemModal() {
  showItemModal.value = false;
  selectedAssessmentForItems.value = null;
  assessmentItems.value = [];
  resetItemForm();
}

// Add assessment item
async function addAssessmentItem() {
  // Reset errors
  itemErrors.value = {};

  // Validate form
  if (!itemForm.value.name) {
    itemErrors.value.name = 'Item name is required';
  } else if (itemForm.value.name.length > 255) {
    itemErrors.value.name = 'Name cannot exceed 255 characters';
  }

  if (!itemForm.value.total_score) {
    itemErrors.value.total_score = 'Total score is required';
  } else if (itemForm.value.total_score < 1) {
    itemErrors.value.total_score = 'Total score must be at least 1';
  }

  // Check if an assessment is selected
  if (!selectedAssessmentForItems.value) {
    itemErrors.value.assessment = 'Please select an assessment';
    return;
  }

  // If there are validation errors, stop submission
  if (Object.keys(itemErrors.value).length > 0) {
    return;
  }

  try {
    itemLoading.value = true;

    // Simulate API delay
    await new Promise(resolve => setTimeout(resolve, 500));

    // Add the new item to the assessment
    const newItem = {
      id: generateItemId(),
      name: itemForm.value.name,
      total_score: itemForm.value.total_score
    };

    // Add to the local array for display
    assessmentItems.value.push(newItem);

    // Also update the item in the assessment object
    if (selectedAssessmentForItems.value) {
      // Find the assessment in the section
      const section = currentSection.value;
      const assessmentIndex = section.assignments.findIndex(a => a.id === selectedAssessmentForItems.value.id);

      if (assessmentIndex !== -1) {
        if (!section.assignments[assessmentIndex].items) {
          section.assignments[assessmentIndex].items = [];
        }
        section.assignments[assessmentIndex].items.push(newItem);
      }
    }

    // Reset the form for the next item
    resetItemForm();

  } catch (error) {
    console.error('Error adding assessment item:', error);
    alert('Failed to add assessment item. Please try again.');
  } finally {
    itemLoading.value = false;
  }
}

// Delete assessment item
async function deleteAssessmentItem(itemId, assessmentId = null) {
  if (!confirm('Are you sure you want to delete this assessment item?')) {
    return;
  }

  try {
    itemLoading.value = true;

    // Simulate API delay
    await new Promise(resolve => setTimeout(resolve, 500));

    // If we're in the modal, update the local array
    if (showItemModal.value && selectedAssessmentForItems.value) {
      assessmentItems.value = assessmentItems.value.filter(item => item.id !== itemId);
    }

    // Find the assessment and remove the item
    const section = currentSection.value;
    if (!assessmentId) {
      // If assessmentId is not provided, we need to find it
      for (const assignment of section.assignments) {
        if (assignment.items) {
          const itemIndex = assignment.items.findIndex(item => item.id === itemId);
          if (itemIndex !== -1) {
            assignment.items.splice(itemIndex, 1);
            break;
          }
        }
      }
    } else {
      // If assessmentId is provided, we can directly find the assessment
      const assessmentIndex = section.assignments.findIndex(a => a.id === assessmentId);
      if (assessmentIndex !== -1 && section.assignments[assessmentIndex].items) {
        section.assignments[assessmentIndex].items = section.assignments[assessmentIndex].items.filter(item => item.id !== itemId);
      }
    }

  } catch (error) {
    console.error('Error deleting assessment item:', error);
    alert('Failed to delete assessment item. Please try again.');
  } finally {
    itemLoading.value = false;
  }
}

// Get CSS class for type display
function getTypeColor(type) {
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

// Calculate total score for an assessment
const getTotalAssessmentScore = (assessment) => {
  if (!assessment || !assessment.items || assessment.items.length === 0) {
    return assessment?.totalPoints || 0;
  }

  return assessment.items.reduce((total, item) => total + item.total_score, 0);
};

// Find assessment by ID
const findAssessmentById = (id) => {
  if (!currentSection.value) return null;

  return currentSection.value.assignments.find(a => a.id === id) || null;
};

// Get assessment name by ID
const getAssessmentName = (id) => {
  const assessment = findAssessmentById(id);
  return assessment ? assessment.name : 'Unknown';
};

// Assessment management methods
const openModal = (assessment = null) => {
  resetForm();

  if (assessment) {
    // Edit mode
    isEditing.value = true;
    editingId.value = assessment.id;
    form.value = { ...assessment };
    originalForm.value = { ...assessment };
  } else {
    // Create mode
    isEditing.value = false;
    editingId.value = null;
  }

  showModal.value = true;
};

const closeModal = () => {
  showModal.value = false;
  resetForm();
};

const resetForm = () => {
  form.value = {
    name: '',
    type: '',
    weight: 0
  };
  originalForm.value = {};
  errors.value = {};
  message.value = { text: '', type: '' };
};

// Fixed calculation for total weight
const getTotalWeight = () => {
  if (!currentSection.value) return 0;

  const sectionId = currentSection.value.id;
  const existingWeight = classWeights.value[sectionId] || 0;
  const formWeight = parseFloat(form.value.weight) || 0;

  // If editing, we need to calculate the difference
  if (isEditing.value && originalForm.value.id) {
    const originalWeight = parseFloat(originalForm.value.weight) || 0;
    return existingWeight - originalWeight + formWeight;
  }

  return existingWeight + formWeight;
};

const submitForm = async () => {
  // Reset errors and message
  errors.value = {};
  message.value = { text: '', type: '' };

  if (!currentSection.value) {
    message.value = { text: 'No section selected', type: 'error' };
    return;
  }

  // Validate form
  if (!form.value.name) {
    errors.value.name = 'Assessment name is required';
  } else if (form.value.name.length > 255) {
    errors.value.name = 'Name cannot exceed 255 characters';
  }

  if (!form.value.type) {
    errors.value.type = 'Please select an assessment type';
  }

  if (form.value.weight === null || form.value.weight === undefined) {
    errors.value.weight = 'Weight is required';
  } else if (form.value.weight < 0 || form.value.weight > 100) {
    errors.value.weight = 'Weight must be between 0 and 100';
  }

  // Check if total weight exceeds 100%
  const totalWeight = getTotalWeight();
  if (totalWeight > 100) {
    errors.value.weight = 'Total weight cannot exceed 100% for this class';
    message.value = { text: 'Total weight cannot exceed 100% for this class.', type: 'error' };
    return;
  }

  // If there are validation errors, stop submission
  if (Object.keys(errors.value).length > 0) {
    return;
  }

  // Submit form
  try {
    loading.value = true;

    // Simulate API call
    await new Promise(resolve => setTimeout(resolve, 1000));

    if (isEditing.value) {
      // Find the section and update the assignment
      const section = currentSection.value;
      const index = section.assignments.findIndex(a => a.id === editingId.value);
      if (index !== -1) {
        // Preserve the items when updating
        const items = section.assignments[index].items || [];

        section.assignments[index] = {
          id: editingId.value,
          name: form.value.name,
          category: form.value.type.toLowerCase(),
          weight: parseFloat(form.value.weight),
          totalPoints: section.assignments[index].totalPoints,
          dueDate: section.assignments[index].dueDate,
          items: items
        };
      }
      message.value = { text: 'Assessment updated successfully!', type: 'success' };
    } else {
      // Add the new assignment to the current section
      const newId = generateAssignmentId();
      const today = new Date();
      const formattedDate = today.toISOString().split('T')[0];

      currentSection.value.assignments.push({
        id: newId,
        name: form.value.name,
        category: form.value.type.toLowerCase(),
        weight: parseFloat(form.value.weight),
        totalPoints: 100,
        dueDate: formattedDate,
        items: []
      });

      // Update pending assignments count
      currentSection.value.pendingAssignments += 1;

      message.value = { text: 'Assessment added successfully!', type: 'success' };
    }

    // Close modal after a short delay to show the success message
    setTimeout(() => {
      closeModal();
    }, 1500);

  } catch (error) {
    console.error('Error submitting form:', error);
    message.value = { text: 'Failed to save assessment. Please try again.', type: 'error' };
  } finally {
    loading.value = false;
  }
};

const deleteAssessment = async (id) => {
  if (!confirm('Are you sure you want to delete this assessment?')) {
    return;
  }

  try {
    loading.value = true;

    // Simulate API call
    await new Promise(resolve => setTimeout(resolve, 800));

    // Find the section and remove the assignment
    for (const section of classSections.value) {
      const index = section.assignments.findIndex(a => a.id === id);
      if (index !== -1) {
        section.assignments.splice(index, 1);
        break;
      }
    }

  } catch (error) {
    console.error('Error deleting assessment:', error);
    alert('Failed to delete assessment. Please try again.');
  } finally {
    loading.value = false;
  }
};

const getClassName = (classId) => {
  const classItem = classes.value.find(c => c.id === classId);
  return classItem ? classItem.name : 'Unknown';
};

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
        selectedAssessment.value = section.assignments[0]?.id || null;
      }
    }
  }
};

// Function to handle section-changed events
const handleSectionChange = (event) => {
  if (event.detail && event.detail.sectionId && event.detail.source !== 'assessment') {
    const section = findSectionById(event.detail.sectionId);
    if (section && (!currentSection.value || currentSection.value.id !== section.id)) {
      // Only update if it's a different section
      currentSection.value = section;
      selectedAssessment.value = section.assignments[0]?.id || null;
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
      selectedAssessment.value = section.assignments[0]?.id || null;
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
      selectedAssessment.value = section.assignments[0]?.id || null;
    }
  }
  // Then check for itemId (new parameter from sidebar)
  else if (itemId) {
    const section = findSectionById(itemId);
    if (section && (!currentSection.value || currentSection.value.id !== section.id)) {
      // Only update if it's a different section
      currentSection.value = section;
      selectedAssessment.value = section.assignments[0]?.id || null;
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
      selectedAssessment.value = section.assignments[0]?.id || null;
      return;
    }
  }
  // Then check for itemId (which could be a section ID from sidebar)
  else if (itemIdFromUrl) {
    const section = findSectionById(itemIdFromUrl);
    if (section) {
      currentSection.value = section;
      selectedAssessment.value = section.assignments[0]?.id || null;
      return;
    }
  }
  // Finally check for injected item ID
  else if (currentItemId.value) {
    const section = findSectionById(currentItemId.value);
    if (section) {
      currentSection.value = section;
      selectedAssessment.value = section.assignments[0]?.id || null;
      return;
    }
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
<Head title="Assessment Management" />

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

    <!-- Assessment Management Section -->
    <div v-if="currentSection" class="container mx-auto p-4">
      <!-- Header with Add Button -->
      <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Assessment Management - {{ currentSection.name }}</h1>
        <button
          @click="openModal()"
          class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500"
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
            <label for="filter-type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Filter by Type</label>
            <select
              id="filter-type"
              v-model="filters.type"
              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white"
            >
              <option value="">All Types</option>
              <option value="Exam">Exam</option>
              <option value="Quiz">Quiz</option>
              <option value="Lab">Lab</option>
              <option value="Assignment">Assignment</option>
              <option value="Project">Project</option>
              <option value="Participation">Participation</option>
              <option value="Other">Other</option>
            </select>
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
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Type</th>
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
                    {{ assessment.type }}
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

        <!-- Class Weight Summary -->
        <div class="p-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700">
          <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Weight Summary</h3>
          <div class="bg-white dark:bg-gray-800 p-3 rounded-md border border-gray-200 dark:border-gray-700">
            <div class="flex justify-between items-center mb-1">
              <span class="font-medium text-gray-900 dark:text-white">{{ currentSection.name }}</span>
              <span
                class="text-sm font-bold"
                :class="classWeights[currentSection.id] > 100 ? 'text-red-600 dark:text-red-400' : 'text-gray-900 dark:text-white'"
              >
                {{ classWeights[currentSection.id] || 0 }}%
              </span>
            </div>
            <div class="w-full bg-gray-200 dark:bg-gray-600 rounded-full h-2">
              <div
                class="h-2 rounded-full"
                :class="classWeights[currentSection.id] > 100 ? 'bg-red-600' : 'bg-indigo-600'"
                :style="`width: ${Math.min(classWeights[currentSection.id] || 0, 100)}%`"
              ></div>
            </div>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
              Remaining: {{ Math.max(0, 100 - (classWeights[currentSection.id] || 0)) }}%
            </p>
          </div>
        </div>
      </div>

      <!-- Assessment Items Table -->
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden">
        <div class="p-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Assessment Items</h2>
          <button
            @click="openItemsModal()"
            class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500"
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
    <div v-if="showModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl w-full max-w-md max-h-[90vh] overflow-y-auto" @click.stop>
        <div class="p-6">
          <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white">{{ isEditing ? 'Edit Assessment' : 'Add Assessment' }}</h2>
            <button @click="closeModal" class="text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <!-- Alert Messages -->
          <div v-if="message.text" :class="`mb-4 p-3 rounded-md text-sm ${message.type === 'error' ? 'bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-300' : 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300'}`">
            {{ message.text }}
          </div>

          <form @submit.prevent="submitForm">
            <!-- Assessment Name -->
            <div class="mb-4">
              <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Assessment Name</label>
              <input
                type="text"
                id="name"
                v-model="form.name"
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white"
                :class="{'border-red-500': errors.name}"
                placeholder="e.g., Midterm Exam, Final Project"
                maxlength="255"
                required
              />
              <p v-if="errors.name" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ errors.name }}</p>
            </div>

            <!-- Assessment Type -->
            <div class="mb-4">
              <label for="type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Assessment Type</label>
              <select
                id="type"
                v-model="form.type"
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white"
                :class="{'border-red-500': errors.type}"
                required
              >
                <option value="" disabled>Select type</option>
                <option value="Exam">Exam</option>
                <option value="Quiz">Quiz</option>
                <option value="Lab">Lab</option>
                <option value="Assignment">Assignment</option>
                <option value="Project">Project</option>
                <option value="Participation">Participation</option>
                <option value="Other">Other</option>
              </select>
              <p v-if="errors.type" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ errors.type }}</p>
            </div>

            <!-- Weight -->
            <div class="mb-6">
              <label for="weight" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Weight (%)
                <span class="text-sm text-gray-500 dark:text-gray-400">
                  Current total: {{ getTotalWeight() }}%
                </span>
              </label>
              <input
                type="number"
                id="weight"
                v-model.number="form.weight"
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white"
                :class="{'border-red-500': errors.weight}"
                min="0"
                max="100"
                step="0.1"
                required
              />
              <p v-if="errors.weight" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ errors.weight }}</p>

              <!-- Weight Visualization -->
              <div class="mt-2 bg-gray-200 dark:bg-gray-600 rounded-full h-2.5">
                <div
                  class="h-2.5 rounded-full"
                  :class="getTotalWeight() > 100 ? 'bg-red-600' : 'bg-indigo-600'"
                  :style="`width: ${Math.min(getTotalWeight(), 100)}%`"
                ></div>
              </div>
              <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                Remaining: {{ Math.max(0, 100 - getTotalWeight()) }}%
              </p>
            </div>

            <!-- Submit Button -->
            <div class="flex justify-end gap-2">
              <button
                type="button"
                @click="closeModal"
                class="px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
              >
                Cancel
              </button>
              <button
                type="submit"
                class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
              >
                <span v-if="loading" class="flex items-center">
                  <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  Processing...
                </span>
                <span v-else>{{ isEditing ? 'Update' : 'Create' }}</span>
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Assessment Items Modal -->
    <div v-if="showItemModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl w-full max-w-3xl max-h-[90vh] overflow-y-auto" @click.stop>
        <div class="p-6">
          <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white">
              {{ selectedAssessmentForItems ? `Add Items to ${selectedAssessmentForItems.name}` : 'Add Assessment Item' }}
            </h2>
            <button @click="closeItemModal" class="text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <!-- Add Item Form -->
          <div class="mb-6 bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-3">Add New Item</h3>
            <form @submit.prevent="addAssessmentItem" class="space-y-4">
              <!-- Assessment Selection (only shown if no assessment is pre-selected) -->
              <div v-if="!selectedAssessmentForItems">
                <label for="assessment-select" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Select Assessment</label>
                <select
                  id="assessment-select"
                  v-model="selectedAssessmentForItems"
                  class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white"
                  :class="{'border-red-500': itemErrors.assessment}"
                  required
                >
                  <option :value="null" disabled>Select an assessment</option>
                  <option v-for="assessment in filteredAssessments" :key="assessment.id" :value="assessment">
                    {{ assessment.name }}
                  </option>
                </select>
                <p v-if="itemErrors.assessment" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ itemErrors.assessment }}</p>
              </div>

              <div>
                <label for="item-name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Item Name</label>
                <input
                  type="text"
                  id="item-name"
                  v-model="itemForm.name"
                  class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white"
                  :class="{'border-red-500': itemErrors.name}"
                  placeholder="e.g., Multiple Choice Section, Essay Question"
                  maxlength="255"
                  required
                />
                <p v-if="itemErrors.name" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ itemErrors.name }}</p>
              </div>

              <div>
                <label for="total-score" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Total Score</label>
                <input
                  type="number"
                  id="total-score"
                  v-model.number="itemForm.total_score"
                  class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white"
                  :class="{'border-red-500': itemErrors.total_score}"
                  min="1"
                  step="1"
                  required
                />
                <p v-if="itemErrors.total_score" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ itemErrors.total_score }}</p>
              </div>

              <div class="flex justify-end">
                <button
                    type="submit"
                    class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                    :disabled="itemLoading"
                >
                    <span v-if="itemLoading" class="flex items-center">
                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Adding...
                    </span>
                    <span v-else>Add Item</span>
                </button>
                </div>

            </form>
          </div>

          <!-- Items Table -->
          <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div v-if="itemLoading && assessmentItems.length === 0" class="p-8 text-center">
              <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-solid border-indigo-600 border-r-transparent"></div>
              <p class="mt-2 text-gray-600 dark:text-gray-400">Loading assessment items...</p>
            </div>

            <div v-else-if="assessmentItems.length === 0" class="p-8 text-center">
              <p class="text-gray-600 dark:text-gray-400">No items found. Add your first assessment item using the form above.</p>
            </div>

            <div v-else>
              <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                  <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Name</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Total Score</th>
                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                  </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                  <tr v-for="item in assessmentItems" :key="item.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                      {{ item.name }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                      {{ item.total_score }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                      <button
                        @click="deleteAssessmentItem(item.id)"
                        class="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300"
                      >
                        Delete
                      </button>
                    </td>
                  </tr>
                </tbody>
                <tfoot class="bg-gray-50 dark:bg-gray-700">
                  <tr>
                    <td class="px-6 py-3 text-sm font-medium text-gray-900 dark:text-white">Total</td>
                    <td class="px-6 py-3 text-sm font-medium text-gray-900 dark:text-white">
                      {{ assessmentItems.reduce((sum, item) => sum + item.total_score, 0) }}
                    </td>
                    <td></td>
                  </tr>
                </tfoot>
              </table>
            </div>
          </div>

          <div class="mt-6 flex justify-end">
            <button
              @click="closeItemModal"
              class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2"
            >
              Close
            </button>
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
