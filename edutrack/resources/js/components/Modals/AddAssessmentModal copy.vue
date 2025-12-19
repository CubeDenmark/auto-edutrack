<template>
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
            <label for="assessment_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Assessment Name</label>
            <input
              type="text"
              id="assessment_name"
              v-model="form.assessment_name"
              class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white dark:border-gray-600"
              :class="{'border-red-500': errors.assessment_name}"
              placeholder="e.g., Midterm Exam, Final Project"
              maxlength="255"
              required
            />
            <p v-if="errors.assessment_name" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ errors.assessment_name }}</p>
          </div>

          <!-- Assessment Term -->
          <div class="mb-4">
            <label for="term" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Assessment Term</label>
            <select
              id="term"
              v-model="form.term"
              class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white dark:border-gray-600"
              :class="{'border-red-500': errors.term}"
              required
            >
              <option value="" disabled>Select Term</option>
              <option value="Prelim">Prelim</option>
              <option value="Midterm">Midterm</option>
              <option value="Final Term">Final Term</option>
            </select>
            <p v-if="errors.term" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ errors.term }}</p>
          </div>

          <!-- Weight -->
          <div class="mb-6">
            <label for="weight" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
              Weight (%)
              <span class="text-sm text-gray-500 dark:text-gray-400">
                Current total for {{ form.term || 'selected term' }}: {{ getTotalWeightForTerm(form.term) }}%
              </span>
            </label>
            <input
              type="number"
              id="weight"
              v-model.number="form.weight"
              class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white dark:border-gray-600"
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
                :class="getTotalWeightForTerm(form.term) > 100 ? 'bg-red-600' : 'bg-indigo-600'"
                :style="`width: ${Math.min(getTotalWeightForTerm(form.term), 100)}%`"
              ></div>
            </div>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
              Remaining for {{ form.term || 'selected term' }}: {{ getRemainingWeightForTerm(form.term) }}%
            </p>
          </div>

          <!-- Submit Button -->
          <div class="flex justify-end gap-2">
            <button
              type="button"
              @click="closeModal"
              class="px-4 py-2 border text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:border-gray-600"
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
</template>
<script setup>
import { ref, computed, defineProps, defineEmits, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { usePage } from '@inertiajs/vue3';

// Props
const props = defineProps({
  showModal: Boolean,
  assessment: {
    type: [Object, Number],
    default: null,
  },
  currentSection: Object,
  assessments: {
    type: Array,
    default: () => [],
  },
  class_id: {
    type: [String, Number],
    required: true,
  },
});

// Emits
const emit = defineEmits(['assessmentAdded', 'closeModal']);
const currentSection = ref(null);
const sections = usePage().props.sections || [];

// Class ID and section name
const classId = computed(() => currentSection.value?.id || props.class_id || 'defaultClassId');
const sectionName = computed(() => currentSection.value?.name || '');

// Watch for changes in currentSection
watch(() => props.currentSection, (newSection) => {
  if (newSection) {
    currentSection.value = newSection;
  }
}, { immediate: true });

// Class sections (if needed)
const classSections = computed(() => {
  if (sections && sections.length > 0) {
    return sections.map((section) => ({
      id: section.id,
      name: section.name || 'Unnamed Section',
      assessments: section.assessments || [],
    }));
  }
  return [];
});

// State
const isEditing = ref(false);
const message = ref({ text: '', type: '' });
const errors = ref({
  assessment_name: '',
  term: '',
  weight: '',
});
const loading = ref(false);

const form = useForm({
  assessment_name: '',
  term: '',
  weight: 0,
  class_id: null,
});

const originalForm = ref({
  assessment_name: '',
  term: '',
  weight: 0,
});

// Reset form
const resetForm = () => {
  form.assessment_name = '';
  form.term = '';
  form.weight = 0;
  form.class_id = null;
  isEditing.value = false;
  errors.value = { assessment_name: '', term: '', weight: '' };
  message.value = { text: '', type: '' };
};

// Watch for changes in the assessment prop
watch(() => props.assessment, (newAssessment) => {
  if (newAssessment && typeof newAssessment === 'object') {
    isEditing.value = true;
    originalForm.value = { ...newAssessment };
    form.assessment_name = newAssessment.assessment_name || newAssessment.name || '';
    form.term = newAssessment.term || '';
    form.weight = newAssessment.weight || 0;
    form.class_id = newAssessment.class_id || props.class_id;
  } else {
    resetForm();
  }
}, { immediate: true });

// Get total weight for a term
const getTotalWeightForTerm = (term) => {
  if (!term || !currentSection.value) return 0;

  const assessmentsInClass = currentSection.value.assessments || [];
  const assessmentsInTerm = assessmentsInClass.filter(a => a.term === term);

  let totalWeight = assessmentsInTerm.reduce(
    (sum, a) => sum + parseFloat(a.weight || 0),
    0
  );

  if (isEditing.value && originalForm.value.id && originalForm.value.term === term) {
    const originalAssessment = assessmentsInTerm.find(a => a.id === originalForm.value.id);
    if (originalAssessment) {
      totalWeight -= parseFloat(originalAssessment.weight || 0);
    }
  }

  if (form.term === term) {
    totalWeight += parseFloat(form.weight || 0);
  }

  return totalWeight;
};

// Get remaining weight
const getRemainingWeightForTerm = (term) => {
  if (!term) return 100;
  const totalWeight = getTotalWeightForTerm(term);
  return Math.max(100 - totalWeight, 0);
};

// ✅ Check if duplicate name exists in the same term
const isDuplicateAssessment = (name, term) => {
  if (!name || !term || !currentSection.value) return false;

  const trimmedName = name.trim().toLowerCase();

  return currentSection.value.assessments.some((assessment) => {
    const sameName = assessment.assessment_name.trim().toLowerCase() === trimmedName;
    const sameTerm = assessment.term === term;
    const notSameId = !isEditing.value || assessment.id !== originalForm.value.id;

    return sameName && sameTerm && notSameId;
  });
};


// Submit form
async function submitForm() {
  if (loading.value) return;

  loading.value = true;
  message.value = { text: '', type: '' };
  errors.value = { assessment_name: '', term: '', weight: '' };

  form.class_id = props.class_id;

  // ✅ Duplicate name validation
  if (isDuplicateAssessment(form.assessment_name, form.term)) {
  errors.value.assessment_name = 'An assessment with the same name already exists in this term.';
  message.value = { text: errors.value.assessment_name, type: 'error' };
  loading.value = false;
  return;
}


  // ✅ Weight validation
  const totalTermWeight = getTotalWeightForTerm(form.term);
  if (totalTermWeight > 100) {
    errors.value.weight = `Total weight for the ${form.term} term cannot exceed 100%`;
    message.value = { text: errors.value.weight, type: 'error' };
    loading.value = false;
    return;
  }

  try {
    if (isEditing.value) {
      await form.put(route('assessments.update', originalForm.value.id));
      message.value = { text: 'Assessment updated successfully!', type: 'success' };
    } else {
      await form.post(route('assessments.store'));
      message.value = { text: 'Assessment added successfully!', type: 'success' };
    }

    emit('assessmentAdded');

    setTimeout(() => {
      closeModal();
    }, 1500);
  } catch (error) {
    console.error('Form submission error:', error);
    if (error.response && error.response.data.errors) {
      errors.value = error.response.data.errors;
    }
    message.value = { text: 'An error occurred. Please try again.', type: 'error' };
  } finally {
    loading.value = false;
  }
}

// Close modal
const closeModal = () => {
  emit('closeModal');
};
</script>

<!-- <script setup>
import { ref, computed, defineProps, defineEmits, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { usePage } from '@inertiajs/vue3'; 

// Props
const props = defineProps({
showModal: Boolean,
assessment: {
  type: [Object, Number],
  default: null,
},
currentSection: Object,
assessments: {
  type: Array,
  default: () => [],
},
class_id: {
  type: [String, Number],
  required: true,
},
});

// Emits
const emit = defineEmits(['assessmentAdded', 'closeModal']);
const currentSection = ref(null);
const sections = usePage().props.sections || [];

// When accessing currentSection, use optional chaining to prevent errors
const classId = computed(() => currentSection.value?.id || props.class_id || 'defaultClassId');

const sectionName = computed(() => currentSection.value?.name || '');

// Watch for changes in currentSection
watch(() => props.currentSection, (newSection) => {
if (newSection) {
  currentSection.value = newSection;
}
}, { immediate: true });

// Class sections
const classSections = computed(() => {
if (sections && sections.length > 0) {
  return sections.map((section) => {
    return {
      id: section.id,
      name: section.name || 'Unnamed Section',
      assessments: section.assessments || [],
    };
  });
}
return [];
});

// State
const isEditing = ref(false);
const message = ref({ text: '', type: '' });
const errors = ref({
assessment_name: '',
term: '',
weight: '',
});
const loading = ref(false);

const form = useForm({
assessment_name: '',
term: '',
weight: 0,
class_id: null,
});

const originalForm = ref({
assessment_name: '',
term: '',
weight: 0,
});

// Reset form
const resetForm = () => {
form.assessment_name = '';
form.term = '';
form.weight = 0;
form.class_id = null;
isEditing.value = false;
errors.value = { assessment_name: '', term: '', weight: '' };
message.value = { text: '', type: '' };
};

// Watch assessment prop
watch(
() => props.assessment,
(newAssessment) => {
  if (newAssessment && typeof newAssessment === 'object') {
    isEditing.value = true;
    originalForm.value = { ...newAssessment };
    form.assessment_name = newAssessment.assessment_name || newAssessment.name || '';
    form.term = newAssessment.term || '';
    form.weight = newAssessment.weight || 0;
    form.class_id = newAssessment.class_id || props.class_id;
  } else {
    resetForm();
  }
},
{ immediate: true }
);

// Get total weight for assessments with the same term in a specific class
const getTotalWeightForTerm = (term) => {
if (!term || !currentSection.value) return 0;

// Get all assessments for this class from the parent component
const assessmentsInClass = currentSection.value.assessments || [];

// Filter assessments by term
const assessmentsInTerm = assessmentsInClass.filter(a => a.term === term);

let totalWeight = assessmentsInTerm.reduce(
  (sum, a) => sum + parseFloat(a.weight || 0),
  0
);

// If editing, subtract the original weight to avoid counting it twice
if (isEditing.value && originalForm.value.id && originalForm.value.term === term) {
  const originalAssessment = assessmentsInTerm.find(a => a.id === originalForm.value.id);
  if (originalAssessment) {
    totalWeight -= parseFloat(originalAssessment.weight || 0);
  }
}

// Add the current form weight if the term matches
if (form.term === term) {
  totalWeight += parseFloat(form.weight || 0);
}

return totalWeight;
};

// Get remaining weight for a specific term
const getRemainingWeightForTerm = (term) => {
if (!term) return 100;
const totalWeight = getTotalWeightForTerm(term);
return Math.max(100 - totalWeight, 0);
};

// Submit form
async function submitForm() {
if (loading.value) return;

loading.value = true;
message.value = { text: '', type: '' };

// Set the class_id from the current section
form.class_id = props.class_id;

// Validate that the total weight for the term doesn't exceed 100%
const totalTermWeight = getTotalWeightForTerm(form.term);
if (totalTermWeight > 100) {
  errors.value.weight = `Total weight for the ${form.term} term cannot exceed 100%`;
  message.value = { text: errors.value.weight, type: 'error' };
  loading.value = false;
  return;
}

try {
  if (isEditing.value) {
    // Update existing assessment
    await form.put(route('assessments.update', originalForm.value.id));
    message.value = { text: 'Assessment updated successfully!', type: 'success' };
  } else {
    // Create new assessment
    await form.post(route('assessments.store'));
    message.value = { text: 'Assessment added successfully!', type: 'success' };
  }
  
  // Emit event to refresh assessments
  emit('assessmentAdded');
  
  // Close modal after successful submission
  setTimeout(() => {
    closeModal();
  }, 1500);
} catch (error) {
  console.error('Form submission error:', error);
  if (error.response && error.response.data.errors) {
    errors.value = error.response.data.errors;
  }
  message.value = { text: 'An error occurred. Please try again.', type: 'error' };
} finally {
  loading.value = false;
}
}

// Close modal
const closeModal = () => {
emit('closeModal');
};
</script> -->