<template>
  <div v-if="showModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl w-full max-w-3xl max-h-[90vh] overflow-y-auto" @click.stop>
      <div class="p-6">
        <!-- Header with term filter -->
        <div class="flex justify-between items-center mb-4">
          <h2 class="text-xl font-bold text-gray-900 dark:text-white">
            {{ getModalTitle() }}
          </h2>
          <div class="flex items-center space-x-2">
            <button 
              type="button"
              @click="handleTermChange('Prelim')"
              class="px-3 py-1 rounded-md text-xs font-medium transition-colors"
              :class="selectedTerm === 'Prelim' ? 'bg-indigo-600 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300'"
            >
              Prelim
            </button>
            <button 
              type="button"
              @click="handleTermChange('Midterm')"
              class="px-3 py-1 rounded-md text-xs font-medium transition-colors"
              :class="selectedTerm === 'Midterm' ? 'bg-indigo-600 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300'"
            >
              Midterm
            </button>
            <button 
              type="button"
              @click="handleTermChange('Final Term')"
              class="px-3 py-1 rounded-md text-xs font-medium transition-colors"
              :class="selectedTerm === 'Final Term' ? 'bg-indigo-600 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300'"
            >
              Final Term
            </button>
            <button @click="closeModal" class="text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 ml-2">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
        </div>

        <!-- Add Item Form -->
        <div class="mb-6 bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
          <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-3">Add New Item</h3>
          <form @submit.prevent="addAssessmentItem" class="space-y-4">
            <!-- Assessment Selection (only shown if no assessment is pre-selected) -->
            <div v-if="!selectedAssessment">
              <label for="assessment-select" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Select {{ selectedTerm }} Assessment
              </label>
              <select
                id="assessment-select"
                v-model="selectedAssessmentLocal"
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white"
                :class="{'border-red-500': errors.assessment}"
                required
              >
                <option :value="null" disabled>Select an assessment</option>
                <option 
                  v-for="assessment in filteredAssessments" 
                  :key="assessment.id" 
                  :value="assessment"
                >
                  {{ assessment.name || assessment.assessment_name }}
                </option>
              </select>
              <p v-if="errors.assessment" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ errors.assessment }}</p>
            </div>

            <div v-if="!selectedAssessment">
              <label for="assessment-name-search" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Search {{ selectedTerm }} Assessment by Name
              </label>
              <input
                type="text"
                id="assessment-name-search"
                v-model="form.assessment_name"
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white"
                placeholder="Type to filter assessments by name..."
              />
            </div>

            <div v-if="!selectedAssessment">
              <label for="assessment-id-input" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Assessment ID</label>
              <div class="flex space-x-2">
                <input
                  type="text"
                  id="assessment-id-input"
                  v-model="form.assessment_id_input"
                  class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white"
                  placeholder="Enter assessment ID to fetch details"
                />
                <button 
                  type="button"
                  @click="fetchAssessmentByID"
                  class="px-3 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                >
                  Fetch
                </button>
              </div>
              <p v-if="fetchedAssessment" class="mt-1 text-sm text-green-600 dark:text-green-400">
                Found: {{ fetchedAssessment.name || fetchedAssessment.assessment_name }} ({{ fetchedAssessment.term }})
              </p>
            </div>

            <!-- Term-filtered Assessments dropdown -->
            <div class="mt-3 bg-indigo-50 dark:bg-indigo-900/20 p-3 rounded-lg border border-indigo-100 dark:border-indigo-800">
              <label for="assessment-name-dropdown" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                <span class="text-indigo-600 dark:text-indigo-400">★</span> Select {{ selectedTerm }} Assessment
              </label>
              <select
                id="assessment-name-dropdown"
                v-model="selectedAssessmentName"
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white"
                @change="handleAssessmentNameSelected"
              >
                <option value="">-- Select an assessment --</option>
                <option 
                  v-for="assessment in termFilteredAssessments" 
                  :key="assessment.id" 
                  :value="assessment.name || assessment.assessment_name"
                >
                  {{ assessment.name || assessment.assessment_name }}
                </option>
              </select>
              <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                {{ selectedTerm }} assessments only
              </p>
            </div>

            <div>
              <label for="item-name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Item Name</label>
              <input
                type="text"
                id="item-name"
                v-model="form.name"
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white"
                :class="{'border-red-500': errors.name}"
                placeholder="e.g., Multiple Choice Section, Essay Question"
                maxlength="255"
                required
              />
              <p v-if="errors.name" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ errors.name }}</p>
            </div>

            <div>
              <label for="total-score" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Total Score</label>
              <input
                type="number"
                id="total-score"
                v-model.number="form.total_score"
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white"
                :class="{'border-red-500': errors.total_score}"
                min="1"
                step="1"
                required
              />
              <p v-if="errors.total_score" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ errors.total_score }}</p>
            </div>

            <div class="flex justify-end">
              <button
                type="submit"
                class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                :disabled="loading"
              >
                <span v-if="loading" class="flex items-center">
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
          <div v-if="loading && items.length === 0" class="p-8 text-center">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-solid border-indigo-600 border-r-transparent"></div>
            <p class="mt-2 text-gray-600 dark:text-gray-400">Loading assessment items...</p>
          </div>

          <div v-else-if="items.length === 0" class="p-8 text-center">
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
                <tr v-for="item in items" :key="item.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                    {{ item.name }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                    {{ item.total_score }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <button
                      @click="deleteItem(item.id)"
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
                    {{ items.reduce((sum, item) => sum + item.total_score, 0) }}
                  </td>
                  <td></td>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>

        <div class="mt-6 flex justify-end">
          <button
            @click="closeModal"
            class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2"
          >
            Close
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, defineProps, defineEmits } from 'vue';
import { router } from '@inertiajs/vue3';

// Props
const props = defineProps({
  showModal: {
    type: Boolean,
    default: false
  },
  selectedAssessment: {
    type: Object,
    default: null
  },
  assessments: {
    type: Array,
    default: () => []
  },
  items: {
    type: Array,
    default: () => []
  },
  selectedTerm: {
    type: String,
    default: 'Midterm'
  }
});

// Emits
const emit = defineEmits([
  'close', 
  'itemAdded', 
  'itemDeleted', 
  'termChanged',
  'assessmentSelected'
]);

// Local state
const loading = ref(false);
const errors = ref({});
const selectedAssessmentLocal = ref(null);
const fetchedAssessment = ref(null);
const form = ref({
  name: '',
  total_score: 100,
  assessment_id: null,
  assessment_name: '',
  assessment_id_input: ''
});

// Add to the local state section
const selectedAssessmentName = ref('');

// Computed property for term-filtered assessments
const termFilteredAssessments = computed(() => {
  return [...props.assessments]
    .filter(assessment => assessment.term === props.selectedTerm)
    .sort((a, b) => {
      const nameA = a.name || a.assessment_name || '';
      const nameB = b.name || b.assessment_name || '';
      return nameA.localeCompare(nameB);
    });
});

// Watch for changes in the selected assessment from props
watch(() => props.selectedAssessment, (newVal) => {
  if (newVal) {
    selectedAssessmentLocal.value = newVal;
    form.value.assessment_id = newVal.id;
    form.value.assessment_name = newVal.name || newVal.assessment_name || '';
  }
}, { immediate: true });

const filteredAssessments = computed(() => {
  return props.assessments.filter(assessment => {
    const termMatch = assessment.term === props.selectedTerm;
    if (form.value.assessment_name && form.value.assessment_name.trim() !== '') {
      const assessmentName = assessment.name || assessment.assessment_name || '';
      return termMatch && assessmentName.toLowerCase().includes(form.value.assessment_name.toLowerCase());
    }
    return termMatch;
  });
});

// Handle term change
function handleTermChange(term) {
  const currentAssessmentName = form.value.assessment_name;
  selectedAssessmentLocal.value = null;
  selectedAssessmentName.value = '';
  emit('termChanged', term);
  setTimeout(() => {
    form.value.assessment_name = currentAssessmentName;
  }, 50);
}

function closeModal() {
  resetForm();
  emit('close');
}

function resetForm() {
  form.value = {
    name: '',
    total_score: 100,
    assessment_id: null,
    assessment_name: '',
    assessment_id_input: ''
  };
  selectedAssessmentName.value = '';
  fetchedAssessment.value = null;
  errors.value = {};
}

// ✅ NEW: Check for duplicate name in same term and assessment
function isDuplicateItem(name, assessmentId) {
  return props.items.some(item => {
    const assessment = props.assessments.find(a => a.id === item.assessment_id);
    return (
      item.name.trim().toLowerCase() === name.trim().toLowerCase() &&
      item.assessment_id === assessmentId &&
      assessment &&
      assessment.term === props.selectedTerm
    );
  });
}

function addAssessmentItem() {
  if (loading.value) return;

  const assessmentId = selectedAssessmentLocal.value?.id || props.selectedAssessment?.id;

  if (!assessmentId) {
    errors.value.assessment = 'Please select an assessment';
    return;
  }

  const assessmentName = selectedAssessmentLocal.value?.name || 
                         selectedAssessmentLocal.value?.assessment_name || 
                         props.selectedAssessment?.name || 
                         props.selectedAssessment?.assessment_name || 
                         '';

  // ✅ Check for duplicate name
  if (isDuplicateItem(form.value.name, assessmentId)) {
    errors.value.name = 'This item name already exists in this term for the selected assessment.';
    return;
  }

  loading.value = true;

  const newItem = {
    name: form.value.name,
    total_score: form.value.total_score,
    assessment_id: assessmentId,
    assessment_name: assessmentName
  };

  router.post(route('assessments.storeItems'), newItem, {
    onSuccess: () => {
      resetForm();
      emit('itemAdded', {
        assessmentId,
        item: newItem
      });
      setTimeout(() => {
        closeModal();
        window.location.reload();
      }, 1500);
    },
    onError: (e) => {
      errors.value = e;
    },
    onFinish: () => {
      loading.value = false;
    }
  });
}

function deleteItem(itemId) {
  if (confirm('Are you sure you want to delete this item?')) {
    router.delete(route('assessment-items.destroy', itemId), {
      onSuccess: () => {
        emit('itemDeleted', itemId);
      }
    });
  }
}

watch(selectedAssessmentLocal, (newVal) => {
  if (newVal) {
    form.value.assessment_id = newVal.id;
    form.value.assessment_name = newVal.name || newVal.assessment_name || '';
    emit('assessmentSelected', newVal);
  }
});

watch(() => props.selectedTerm, () => {
  selectedAssessmentName.value = '';
}, { immediate: true });

function getModalTitle() {
  if (selectedAssessmentLocal.value) {
    const name = selectedAssessmentLocal.value.name || selectedAssessmentLocal.value.assessment_name || 'Selected Assessment';
    return `Add Items to ${name}`;
  } else if (props.selectedAssessment) {
    const name = props.selectedAssessment.name || props.selectedAssessment.assessment_name || 'Selected Assessment';
    return `Add Items to ${name}`;
  } else if (form.value.assessment_name) {
    return `Add Items (Filtering: ${form.value.assessment_name})`;
  } else {
    return `Add ${props.selectedTerm} Assessment Item`;
  }
}

function handleAssessmentNameSelected() {
  if (!selectedAssessmentName.value) return;

  const assessment = termFilteredAssessments.value.find(a => {
    const assessmentName = a.name || a.assessment_name || '';
    return assessmentName === selectedAssessmentName.value;
  });

  if (assessment) {
    fetchedAssessment.value = assessment;
    selectedAssessmentLocal.value = assessment;
    form.value.assessment_id = assessment.id;
    form.value.assessment_name = assessment.name || assessment.assessment_name || '';
  }
}

function fetchAssessmentByID() {
  if (!form.value.assessment_id_input) return;

  const id = form.value.assessment_id_input.trim();
  const assessment = props.assessments.find(a => a.id == id);

  if (assessment) {
    fetchedAssessment.value = assessment;
    selectedAssessmentLocal.value = assessment;
    form.value.assessment_id = assessment.id;
    form.value.assessment_name = assessment.name || assessment.assessment_name || '';
  } else {
    loading.value = true;

    setTimeout(() => {
      const allAssessments = props.assessments;
      const found = allAssessments.find(a => a.id == id);

      if (found) {
        fetchedAssessment.value = found;
        selectedAssessmentLocal.value = found;
        form.value.assessment_id = found.id;
        form.value.assessment_name = found.name || found.assessment_name || '';
      } else {
        alert('Assessment not found with ID: ' + id);
      }

      loading.value = false;
    }, 500);
  }
}
</script>

<!-- <script setup>
import { ref, computed, watch, defineProps, defineEmits } from 'vue';
import { router } from '@inertiajs/vue3';

// Props
const props = defineProps({
  showModal: {
    type: Boolean,
    default: false
  },
  selectedAssessment: {
    type: Object,
    default: null
  },
  assessments: {
    type: Array,
    default: () => []
  },
  items: {
    type: Array,
    default: () => []
  },
  selectedTerm: {
    type: String,
    default: 'Midterm'
  }
});

// Emits
const emit = defineEmits([
  'close', 
  'itemAdded', 
  'itemDeleted', 
  'termChanged',
  'assessmentSelected'
]);

// Local state
const loading = ref(false);
const errors = ref({});
const selectedAssessmentLocal = ref(null);
const fetchedAssessment = ref(null);
const form = ref({
  name: '',
  total_score: 100,
  assessment_id: null,
  assessment_name: '',
  assessment_id_input: ''
});

// Add to the local state section
const selectedAssessmentName = ref('');

// Computed property for term-filtered assessments
const termFilteredAssessments = computed(() => {
  // Filter assessments by the selected term and sort by name
  return [...props.assessments]
    .filter(assessment => assessment.term === props.selectedTerm)
    .sort((a, b) => {
      const nameA = a.name || a.assessment_name || '';
      const nameB = b.name || b.assessment_name || '';
      return nameA.localeCompare(nameB);
    });
});

// Watch for changes in the selected assessment from props
watch(() => props.selectedAssessment, (newVal) => {
  if (newVal) {
    selectedAssessmentLocal.value = newVal;
    // Set the assessment_id in the form when the selected assessment changes
    form.value.assessment_id = newVal.id;
    form.value.assessment_name = newVal.name || newVal.assessment_name || '';
  }
}, { immediate: true });

// Computed properties
const filteredAssessments = computed(() => {
  return props.assessments.filter(assessment => {
    // First filter by term
    const termMatch = assessment.term === props.selectedTerm;
    
    // If assessment_name is provided in the form, also filter by name
    if (form.value.assessment_name && form.value.assessment_name.trim() !== '') {
      const assessmentName = assessment.name || assessment.assessment_name || '';
      return termMatch && assessmentName.toLowerCase().includes(form.value.assessment_name.toLowerCase());
    }
    
    return termMatch;
  });
});

// Methods
function handleTermChange(term) {
  // Keep the current assessment_name filter when changing terms
  const currentAssessmentName = form.value.assessment_name;
  
  // Reset the selected assessment when changing terms
  selectedAssessmentLocal.value = null;
  selectedAssessmentName.value = '';
  
  emit('termChanged', term);
  
  // After term change, restore the assessment_name filter
  // This needs to be done after a short delay to ensure the term change has been processed
  setTimeout(() => {
    form.value.assessment_name = currentAssessmentName;
  }, 50);
}

function closeModal() {
  resetForm();
  emit('close');
}

// Update the resetForm function to include the new field
function resetForm() {
  form.value = {
    name: '',
    total_score: 100,
    assessment_id: null,
    assessment_name: '',
    assessment_id_input: ''
  };
  selectedAssessmentName.value = '';
  fetchedAssessment.value = null;
  errors.value = {};
}

function addAssessmentItem() {
  if (loading.value) return;
  
  // Get assessment ID from either the local selected assessment or the props
  const assessmentId = selectedAssessmentLocal.value?.id || props.selectedAssessment?.id;
  
  if (!assessmentId) {
    errors.value.assessment = 'Please select an assessment';
    return;
  }
  
  loading.value = true;
  
  // Get the assessment name from the appropriate source
  const assessmentName = selectedAssessmentLocal.value?.name || 
                         selectedAssessmentLocal.value?.assessment_name || 
                         props.selectedAssessment?.name || 
                         props.selectedAssessment?.assessment_name || 
                         '';
  
  // Create a new item with the form data
  const newItem = {
    name: form.value.name,
    total_score: form.value.total_score,
    assessment_id: assessmentId,
    assessment_name: assessmentName
  };
  
  // Send the request to create the item
  router.post(route('assessments.storeItems'), newItem, {
    onSuccess: () => {
      // Reset the form
      resetForm();
      
      // Emit event to notify parent component
      emit('itemAdded', {
        assessmentId,
        item: newItem
      });
      
      // Close the modal after a delay
      setTimeout(() => {
        closeModal();
        window.location.reload();
      }, 1500);
    },
    onError: (errors) => {
      errors.value = errors;
    },
    onFinish: () => {
      loading.value = false;
    }
  });
}

function deleteItem(itemId) {
  if (confirm('Are you sure you want to delete this item?')) {
    router.delete(route('assessment-items.destroy', itemId), {
      onSuccess: () => {
        emit('itemDeleted', itemId);
      }
    });
  }
}

// Watch for changes in the selected assessment
watch(selectedAssessmentLocal, (newVal) => {
  if (newVal) {
    form.value.assessment_id = newVal.id;
    form.value.assessment_name = newVal.name || newVal.assessment_name || '';
    emit('assessmentSelected', newVal);
  }
});

// Watch for changes in the selected term
watch(() => props.selectedTerm, () => {
  // Reset the selected assessment name when term changes
  selectedAssessmentName.value = '';
}, { immediate: true });

function getModalTitle() {
  // Check for selectedAssessmentLocal first
  if (selectedAssessmentLocal.value) {
    const name = selectedAssessmentLocal.value.name || selectedAssessmentLocal.value.assessment_name || 'Selected Assessment';
    return `Add Items to ${name}`;
  } 
  // Then check for props.selectedAssessment
  else if (props.selectedAssessment) {
    const name = props.selectedAssessment.name || props.selectedAssessment.assessment_name || 'Selected Assessment';
    return `Add Items to ${name}`;
  } 
  // Then check if filtering by name
  else if (form.value.assessment_name) {
    return `Add Items (Filtering: ${form.value.assessment_name})`;
  } 
  // Default title
  else {
    return `Add ${props.selectedTerm} Assessment Item`;
  }
}

// Add this method
function handleAssessmentNameSelected() {
  if (!selectedAssessmentName.value) return;
  
  const assessment = termFilteredAssessments.value.find(a => {
    const assessmentName = a.name || a.assessment_name || '';
    return assessmentName === selectedAssessmentName.value;
  });
  
  if (assessment) {
    fetchedAssessment.value = assessment;
    selectedAssessmentLocal.value = assessment;
    form.value.assessment_id = assessment.id;
    form.value.assessment_name = assessment.name || assessment.assessment_name || '';
  }
}

function fetchAssessmentByID() {
  if (!form.value.assessment_id_input) return;
  
  const id = form.value.assessment_id_input.trim();
  const assessment = props.assessments.find(a => a.id == id);
  
  if (assessment) {
    fetchedAssessment.value = assessment;
    selectedAssessmentLocal.value = assessment;
    form.value.assessment_id = assessment.id;
    form.value.assessment_name = assessment.name || assessment.assessment_name || '';
  } else {
    // If not found in the current filtered list, try to fetch it
    loading.value = true;
    
    // This is a placeholder - in a real app, you would make an API call
    // For now, we'll just simulate a search across all assessments regardless of term
    setTimeout(() => {
      const allAssessments = props.assessments;
      const found = allAssessments.find(a => a.id == id);
      
      if (found) {
        fetchedAssessment.value = found;
        selectedAssessmentLocal.value = found;
        form.value.assessment_id = found.id;
        form.value.assessment_name = found.name || found.assessment_name || '';
      } else {
        alert('Assessment not found with ID: ' + id);
      }
      
      loading.value = false;
    }, 500);
  }
}
</script> -->