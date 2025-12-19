<script setup lang="ts">
import { ref, computed, onMounted, watch, inject } from 'vue';
import AppLayout from "@/layouts/AppLayout.vue";
import { type BreadcrumbItem } from "@/types";
import { Head, usePage, Link } from "@inertiajs/vue3";

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
const isMobile = ref(false);

// Children data
const children = ref([
  {
    id: 'child1',
    name: 'Alex Johnson',
    grade: 'Grade 10',
    avatar: 'https://i.pravatar.cc/150?img=3',
    stats: {
      gpa: 3.7
    },
    grades: [
      {
        subject: 'Mathematics',
        code: 'MATH101',
        icon: '📐',
        iconBg: 'bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-400',
        teacher: 'Dr. Smith',
        grade: 'A',
        gradeClass: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
        percentage: 92,
        updated: 'Oct 12, 2023',
        assignments: [
          {
            title: 'Calculus Quiz 1',
            type: 'Quiz',
            score: 95,
            maxScore: 100,
            date: 'Sep 15, 2023'
          },
          {
            title: 'Homework Set 3',
            type: 'Homework',
            score: 88,
            maxScore: 100,
            date: 'Sep 22, 2023'
          },
          {
            title: 'Midterm Exam',
            type: 'Exam',
            score: 91,
            maxScore: 100,
            date: 'Oct 5, 2023'
          },
          {
            title: 'Group Project',
            type: 'Project',
            score: 94,
            maxScore: 100,
            date: 'Oct 10, 2023'
          }
        ]
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
        updated: 'Oct 10, 2023',
        assignments: [
          {
            title: 'Lab Report: Chemical Reactions',
            type: 'Lab',
            score: 85,
            maxScore: 100,
            date: 'Sep 18, 2023'
          },
          {
            title: 'Periodic Table Quiz',
            type: 'Quiz',
            score: 90,
            maxScore: 100,
            date: 'Sep 25, 2023'
          },
          {
            title: 'Research Paper',
            type: 'Assignment',
            score: 87,
            maxScore: 100,
            date: 'Oct 2, 2023'
          },
          {
            title: 'Midterm Exam',
            type: 'Exam',
            score: 89,
            maxScore: 100,
            date: 'Oct 9, 2023'
          }
        ]
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
        updated: 'Oct 14, 2023',
        assignments: [
          {
            title: 'Essay: Literary Analysis',
            type: 'Essay',
            score: 75,
            maxScore: 100,
            date: 'Sep 20, 2023'
          },
          {
            title: 'Vocabulary Quiz',
            type: 'Quiz',
            score: 80,
            maxScore: 100,
            date: 'Sep 27, 2023'
          },
          {
            title: 'Book Report',
            type: 'Assignment',
            score: 78,
            maxScore: 100,
            date: 'Oct 4, 2023'
          },
          {
            title: 'Class Participation',
            type: 'Participation',
            score: 79,
            maxScore: 100,
            date: 'Oct 11, 2023'
          }
        ]
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
        updated: 'Oct 8, 2023',
        assignments: [
          {
            title: 'Civil War Test',
            type: 'Test',
            score: 83,
            maxScore: 100,
            date: 'Sep 19, 2023'
          },
          {
            title: 'Historical Figure Research',
            type: 'Research',
            score: 87,
            maxScore: 100,
            date: 'Sep 26, 2023'
          },
          {
            title: 'Timeline Project',
            type: 'Project',
            score: 84,
            maxScore: 100,
            date: 'Oct 3, 2023'
          },
          {
            title: 'Map Quiz',
            type: 'Quiz',
            score: 86,
            maxScore: 100,
            date: 'Oct 7, 2023'
          }
        ]
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
        updated: 'Oct 5, 2023',
        assignments: [
          {
            title: 'Fitness Test',
            type: 'Test',
            score: 92,
            maxScore: 100,
            date: 'Sep 14, 2023'
          },
          {
            title: 'Basketball Skills',
            type: 'Skills',
            score: 88,
            maxScore: 100,
            date: 'Sep 21, 2023'
          },
          {
            title: 'Team Participation',
            type: 'Participation',
            score: 95,
            maxScore: 100,
            date: 'Sep 28, 2023'
          },
          {
            title: 'Written Test',
            type: 'Test',
            score: 85,
            maxScore: 100,
            date: 'Oct 5, 2023'
          }
        ]
      }
    ]
  },
  {
    id: 'child2',
    name: 'Emma Johnson',
    grade: 'Grade 8',
    avatar: 'https://i.pravatar.cc/150?img=5',
    stats: {
      gpa: 3.9
    },
    grades: [
      {
        subject: 'Mathematics',
        code: 'MATH8',
        icon: '📐',
        iconBg: 'bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-400',
        teacher: 'Mrs. Garcia',
        grade: 'A',
        gradeClass: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
        percentage: 95,
        updated: 'Oct 13, 2023',
        assignments: [
          {
            title: 'Algebra Quiz',
            type: 'Quiz',
            score: 98,
            maxScore: 100,
            date: 'Sep 15, 2023'
          },
          {
            title: 'Problem Set 4',
            type: 'Homework',
            score: 92,
            maxScore: 100,
            date: 'Sep 22, 2023'
          },
          {
            title: 'Midterm Exam',
            type: 'Exam',
            score: 96,
            maxScore: 100,
            date: 'Oct 6, 2023'
          },
          {
            title: 'Group Project',
            type: 'Project',
            score: 94,
            maxScore: 100,
            date: 'Oct 13, 2023'
          }
        ]
      },
      {
        subject: 'Science',
        code: 'SCI8',
        icon: '🔬',
        iconBg: 'bg-green-100 dark:bg-green-900 text-green-600 dark:text-green-400',
        teacher: 'Dr. Martinez',
        grade: 'A-',
        gradeClass: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
        percentage: 92,
        updated: 'Oct 11, 2023',
        assignments: [
          {
            title: 'Ecosystem Lab Report',
            type: 'Lab',
            score: 90,
            maxScore: 100,
            date: 'Sep 18, 2023'
          },
          {
            title: 'Biology Quiz',
            type: 'Quiz',
            score: 94,
            maxScore: 100,
            date: 'Sep 25, 2023'
          },
          {
            title: 'Science Fair Project',
            type: 'Project',
            score: 95,
            maxScore: 100,
            date: 'Oct 2, 2023'
          },
          {
            title: 'Midterm Exam',
            type: 'Exam',
            score: 89,
            maxScore: 100,
            date: 'Oct 9, 2023'
          }
        ]
      },
      {
        subject: 'English',
        code: 'ENG8',
        icon: '📚',
        iconBg: 'bg-yellow-100 dark:bg-yellow-900 text-yellow-600 dark:text-yellow-400',
        teacher: 'Mr. Wilson',
        grade: 'A',
        gradeClass: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
        percentage: 94,
        updated: 'Oct 12, 2023',
        assignments: [
          {
            title: 'Creative Writing Assignment',
            type: 'Assignment',
            score: 96,
            maxScore: 100,
            date: 'Sep 20, 2023'
          },
          {
            title: 'Grammar Quiz',
            type: 'Quiz',
            score: 92,
            maxScore: 100,
            date: 'Sep 27, 2023'
          },
          {
            title: 'Book Analysis',
            type: 'Essay',
            score: 95,
            maxScore: 100,
            date: 'Oct 4, 2023'
          },
          {
            title: 'Oral Presentation',
            type: 'Presentation',
            score: 93,
            maxScore: 100,
            date: 'Oct 11, 2023'
          }
        ]
      },
      {
        subject: 'History',
        code: 'HIST8',
        icon: '🏛️',
        iconBg: 'bg-purple-100 dark:bg-purple-900 text-purple-600 dark:text-purple-400',
        teacher: 'Ms. Lee',
        grade: 'A-',
        gradeClass: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
        percentage: 91,
        updated: 'Oct 9, 2023',
        assignments: [
          {
            title: 'Ancient Civilizations Test',
            type: 'Test',
            score: 90,
            maxScore: 100,
            date: 'Sep 19, 2023'
          },
          {
            title: 'Historical Document Analysis',
            type: 'Assignment',
            score: 93,
            maxScore: 100,
            date: 'Sep 26, 2023'
          },
          {
            title: 'Research Project',
            type: 'Project',
            score: 92,
            maxScore: 100,
            date: 'Oct 3, 2023'
          },
          {
            title: 'Map Skills Quiz',
            type: 'Quiz',
            score: 89,
            maxScore: 100,
            date: 'Oct 9, 2023'
          }
        ]
      }
    ]
  },
  {
    id: 'child3',
    name: 'Noah Johnson',
    grade: 'Grade 6',
    avatar: 'https://i.pravatar.cc/150?img=8',
    stats: {
      gpa: 3.5
    },
    grades: [
      {
        subject: 'Mathematics',
        code: 'MATH6',
        icon: '📐',
        iconBg: 'bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-400',
        teacher: 'Mr. Rodriguez',
        grade: 'B+',
        gradeClass: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
        percentage: 88,
        updated: 'Oct 13, 2023',
        assignments: [
          {
            title: 'Fractions Quiz',
            type: 'Quiz',
            score: 85,
            maxScore: 100,
            date: 'Sep 15, 2023'
          },
          {
            title: 'Problem Set 2',
            type: 'Homework',
            score: 90,
            maxScore: 100,
            date: 'Sep 22, 2023'
          },
          {
            title: 'Midterm Exam',
            type: 'Exam',
            score: 87,
            maxScore: 100,
            date: 'Oct 6, 2023'
          },
          {
            title: 'Math Project',
            type: 'Project',
            score: 90,
            maxScore: 100,
            date: 'Oct 13, 2023'
          }
        ]
      },
      {
        subject: 'Science',
        code: 'SCI6',
        icon: '🔬',
        iconBg: 'bg-green-100 dark:bg-green-900 text-green-600 dark:text-green-400',
        teacher: 'Mrs. Thompson',
        grade: 'A-',
        gradeClass: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
        percentage: 90,
        updated: 'Oct 11, 2023',
        assignments: [
          {
            title: 'Plant Growth Lab',
            type: 'Lab',
            score: 92,
            maxScore: 100,
            date: 'Sep 18, 2023'
          },
          {
            title: 'Weather Quiz',
            type: 'Quiz',
            score: 88,
            maxScore: 100,
            date: 'Sep 25, 2023'
          },
          {
            title: 'Science Poster',
            type: 'Project',
            score: 91,
            maxScore: 100,
            date: 'Oct 2, 2023'
          },
          {
            title: 'Midterm Exam',
            type: 'Exam',
            score: 89,
            maxScore: 100,
            date: 'Oct 9, 2023'
          }
        ]
      },
      {
        subject: 'English',
        code: 'ENG6',
        icon: '📚',
        iconBg: 'bg-yellow-100 dark:bg-yellow-900 text-yellow-600 dark:text-yellow-400',
        teacher: 'Ms. Parker',
        grade: 'B',
        gradeClass: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
        percentage: 85,
        updated: 'Oct 12, 2023',
        assignments: [
          {
            title: 'Reading Comprehension',
            type: 'Assignment',
            score: 83,
            maxScore: 100,
            date: 'Sep 20, 2023'
          },
          {
            title: 'Spelling Quiz',
            type: 'Quiz',
            score: 87,
            maxScore: 100,
            date: 'Sep 27, 2023'
          },
          {
            title: 'Book Report',
            type: 'Report',
            score: 84,
            maxScore: 100,
            date: 'Oct 4, 2023'
          },
          {
            title: 'Vocabulary Test',
            type: 'Test',
            score: 86,
            maxScore: 100,
            date: 'Oct 11, 2023'
          }
        ]
      },
      {
        subject: 'Social Studies',
        code: 'SOC6',
        icon: '🌎',
        iconBg: 'bg-purple-100 dark:bg-purple-900 text-purple-600 dark:text-purple-400',
        teacher: 'Mr. Adams',
        grade: 'B+',
        gradeClass: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
        percentage: 87,
        updated: 'Oct 9, 2023',
        assignments: [
          {
            title: 'Geography Quiz',
            type: 'Quiz',
            score: 85,
            maxScore: 100,
            date: 'Sep 19, 2023'
          },
          {
            title: 'Current Events Report',
            type: 'Report',
            score: 88,
            maxScore: 100,
            date: 'Sep 26, 2023'
          },
          {
            title: 'History Project',
            type: 'Project',
            score: 89,
            maxScore: 100,
            date: 'Oct 3, 2023'
          },
          {
            title: 'Map Skills Test',
            type: 'Test',
            score: 86,
            maxScore: 100,
            date: 'Oct 9, 2023'
          }
        ]
      }
    ]
  }
]);

// Helper function for grade styling
const getGradeClass = (score: number) => {
  if (score >= 90) return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300';
  if (score >= 80) return 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300';
  if (score >= 70) return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300';
  if (score >= 60) return 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-300';
  return 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300';
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

// Select a child and notify other components
const selectChild = (childId) => {
  if (childId === 'all') {
    currentChild.value = null;
    selectedSubject.value = null;
  } else {
    const child = findChildById(childId);
    if (child) {
      currentChild.value = child;
      selectedSubject.value = null;
    }
  }

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
};

// Handle item-changed event from sidebar
const handleItemChanged = (event) => {
  if (event.detail) {
    const { itemId, itemType, source } = event.detail;

    console.log('Item changed event received:', itemId, itemType, source);

    if (source === 'sidebar') {
      if (itemId === 'all') {
        // Handle "All Children" selection
        currentChild.value = null;
        selectedSubject.value = null;
      } else {
        // Handle specific child selection
        const child = findChildById(itemId);
        if (child) {
          currentChild.value = child;
          selectedSubject.value = null;
        }
      }
    }
  }
};

// Handle child-changed event from other components
const handleChildChanged = (event) => {
  if (event.detail && event.detail.source !== 'grades') {
    const { childId } = event.detail;

    if (childId === 'all') {
      currentChild.value = null;
      selectedSubject.value = null;
    } else {
      const child = findChildById(childId);
      if (child) {
        currentChild.value = child;
        selectedSubject.value = null;
      }
    }
  }
};

// Handle section-changed event (for backward compatibility)
const handleSectionChanged = (event) => {
  if (event.detail) {
    const { sectionId, source } = event.detail;

    if (source === 'sidebar') {
      if (sectionId === 'all') {
        currentChild.value = null;
        selectedSubject.value = null;
      } else {
        const child = findChildById(sectionId);
        if (child) {
          currentChild.value = child;
          selectedSubject.value = null;
        }
      }
    }
  }
};

// Watch for changes in the injected child ID
watch(injectedChildId, (newChildId) => {
  if (newChildId && newChildId !== 'all') {
    const child = findChildById(newChildId);
    if (child) {
      currentChild.value = child;
      selectedSubject.value = null;
    }
  } else if (newChildId === 'all') {
    currentChild.value = null;
    selectedSubject.value = null;
  }
}, { immediate: true });

// Watch for changes in the injected section ID
watch(injectedSectionId, (newSectionId) => {
  if (newSectionId && newSectionId !== 'all') {
    const child = findChildById(newSectionId);
    if (child) {
      currentChild.value = child;
      selectedSubject.value = null;
    }
  } else if (newSectionId === 'all') {
    currentChild.value = null;
    selectedSubject.value = null;
  }
}, { immediate: true });

// Watch for changes in the injected item ID from sidebar
watch(injectedItemId, (newItemId) => {
  console.log('Injected item ID changed:', newItemId);
  if (newItemId && newItemId !== 'all') {
    const child = findChildById(newItemId);
    if (child) {
      currentChild.value = child;
      selectedSubject.value = null;
    }
  } else if (newItemId === 'all') {
    currentChild.value = null;
    selectedSubject.value = null;
  }
}, { immediate: true });

// Watch for changes in the URL query parameters
watch(() => window.location.search, (newSearch) => {
  const params = new URLSearchParams(newSearch);
  const childId = params.get('childId');
  const subject = params.get('subject');
  const sectionId = params.get('sectionId');
  const itemId = params.get('itemId');

  console.log('URL params changed:', { childId, subject, sectionId, itemId });

  // Check for childId first
  if (childId) {
    const child = findChildById(childId);
    if (child) {
      currentChild.value = child;
      if (subject && child.grades) {
        selectedSubject.value = child.grades.find(grade => grade.code === subject) || null;
      } else {
        selectedSubject.value = null;
      }
    }
  }
  // Then check for sectionId or itemId (which could be a child ID for parents)
  else if (sectionId || itemId) {
    const id = sectionId || itemId;
    const child = findChildById(id);
    if (child) {
      currentChild.value = child;
      if (subject && child.grades) {
        selectedSubject.value = child.grades.find(grade => grade.code === subject) || null;
      } else {
        selectedSubject.value = null;
      }
    }
  }
}, { immediate: true });

// Lifecycle hooks
onMounted(() => {
  console.log('ParentGrades component mounted');

  // Check if we're on mobile
  checkMobile();
  window.addEventListener('resize', checkMobile);

  // Add event listeners for various events
  window.addEventListener('item-changed', handleItemChanged);
  window.addEventListener('child-changed', handleChildChanged);
  window.addEventListener('section-changed', handleSectionChanged);

  // Check if there's an item ID in the URL and initialize if present
  const urlParams = new URLSearchParams(window.location.search);
  const childIdFromUrl = urlParams.get('childId');
  const subject = urlParams.get('subject');
  const sectionId = urlParams.get('sectionId');
  const itemId = urlParams.get('itemId');
  const childIdFromProps = page.props.childId;

  console.log('URL params on mount:', { childIdFromUrl, subject, sectionId, itemId, childIdFromProps });

  // First check for childId from URL or props
  if (childIdFromUrl || childIdFromProps) {
    const childId = childIdFromProps || childIdFromUrl;
    const child = findChildById(childId);
    if (child) {
      currentChild.value = child;
      if (subject && child.grades) {
        selectedSubject.value = child.grades.find(grade => grade.code === subject) || null;
      }
    }
  }
  // Then check for sectionId or itemId (which could be a child ID for parents)
  else if (sectionId || itemId) {
    const id = sectionId || itemId;
    const child = findChildById(id);
    if (child) {
      currentChild.value = child;
      if (subject && child.grades) {
        selectedSubject.value = child.grades.find(grade => grade.code === subject) || null;
      }
    }
  }

  // Clean up event listeners on component unmount
  return () => {
    window.removeEventListener('resize', checkMobile);
    window.removeEventListener('item-changed', handleItemChanged);
    window.removeEventListener('child-changed', handleChildChanged);
    window.removeEventListener('section-changed', handleSectionChanged);
  };
});

// Computed property for grade history data
const gradeHistory = computed(() => {
  if (!currentChild.value) return [];

  // Create a simple grade history visualization
  return currentChild.value.grades.map(grade => ({
    subject: grade.subject,
    percentage: grade.percentage,
    color: grade.subject === 'Mathematics' ? 'blue' :
           grade.subject === 'Science' ? 'green' :
           grade.subject === 'English' ? 'yellow' :
           grade.subject === 'History' ? 'purple' : 'red'
  }));
});
</script>

<template>
  <Head title="Parent Grades View" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-6 pt-20">
      <!-- Child selector (visible on mobile or when no child is selected) -->
      <div v-if="!currentChild || isMobile" class="mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
          <h3 class="font-medium mb-4">My Children</h3>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div
              v-for="(child, index) in children"
              :key="index"
              class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
              :class="{'ring-2 ring-primary-500 dark:ring-primary-400': currentChild && currentChild.id === child.id}"
              @click="selectChild(child.id)"
            >
              <div class="flex items-center">
                <img :src="child.avatar" :alt="`${child.name} Avatar`" class="w-12 h-12 rounded-full mr-4" />
                <div>
                  <h4 class="font-medium">{{ child.name }}</h4>
                  <p class="text-sm text-gray-500 dark:text-gray-400">{{ child.grade }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Child-specific grades content -->
      <div v-if="currentChild">
        <!-- Child header -->
        <div class="flex items-center mb-6">
          <img :src="currentChild.avatar" :alt="`${currentChild.name} Avatar`" class="w-16 h-16 rounded-full mr-4" />
          <div>
            <h2 class="text-xl font-semibold">{{ currentChild.name }}</h2>
            <p class="text-gray-500 dark:text-gray-400">{{ currentChild.grade }}</p>
            <div class="flex items-center mt-1">
              <span class="text-sm text-gray-500 dark:text-gray-400 mr-2">GPA:</span>
              <span :class="`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${getGradeClass(currentChild.stats.gpa * 25)}`">
                {{ currentChild.stats.gpa }}
              </span>
            </div>
          </div>
          <button
            @click="selectChild('all')"
            class="ml-auto text-sm text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300"
          >
            Back to Overview
          </button>
        </div>

        <!-- Subject-specific grade details -->
        <div v-if="selectedSubject" class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden mb-6">
          <div class="p-6 border-b dark:border-gray-700 flex justify-between items-center">
            <div>
              <h3 class="font-medium">{{ selectedSubject.subject }} Grades</h3>
              <p class="text-sm text-gray-500 dark:text-gray-400">Teacher: {{ selectedSubject.teacher }}</p>
            </div>
            <button
              @click="selectedSubject = null"
              class="text-primary-600 hover:text-primary-800 dark:text-primary-400 dark:hover:text-primary-300 text-sm"
            >
              Back to All Subjects
            </button>
          </div>
          <div class="p-6">
            <div class="flex items-center justify-between mb-6">
              <div>
                <h4 class="text-lg font-medium">Current Grade: {{ selectedSubject.grade }}</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Last Updated: {{ selectedSubject.updated }}</p>
              </div>
              <span :class="`px-3 py-1 rounded-full text-sm font-medium ${getGradeClass(selectedSubject.percentage)}`">
                {{ selectedSubject.percentage }}%
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
                  <tr v-for="(assignment, index) in selectedSubject.assignments" :key="index">
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="text-sm font-medium">{{ assignment.title }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">{{ assignment.type }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="flex items-center">
                        <span :class="`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${getGradeClass(assignment.score)}`">
                          {{ assignment.score }}/{{ assignment.maxScore }}
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

        <!-- Subjects overview (when no specific subject is selected) -->
        <div v-else class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden mb-6">
          <div class="p-6 border-b dark:border-gray-700">
            <h3 class="font-medium">Subjects Overview</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400">Current Semester</p>
          </div>
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
              <thead class="bg-gray-50 dark:bg-gray-700">
                <tr>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Subject</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Teacher</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Grade</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Last Updated</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                </tr>
              </thead>
              <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                <tr v-for="(subject, index) in currentChild.grades" :key="index">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                      <div class="flex-shrink-0 h-8 w-8 rounded-full flex items-center justify-center" :class="subject.iconBg">
                        <span class="text-lg">{{ subject.icon }}</span>
                      </div>
                      <div class="ml-4">
                        <div class="text-sm font-medium">{{ subject.subject }}</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ subject.code }}</div>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm">{{ subject.teacher }}</td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span :class="`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${subject.gradeClass}`">
                      {{ subject.grade }} ({{ subject.percentage }}%)
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ subject.updated }}</td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <button
                      @click="selectSubject(subject)"
                      class="text-primary-600 hover:text-primary-800 dark:text-primary-400 dark:hover:text-primary-300 text-sm"
                    >
                      View Details
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Grade History -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
          <h3 class="font-medium mb-4">Grade History</h3>
          <div class="h-64 w-full">
            <!-- Chart visualization -->
            <div class="h-full w-full flex items-center justify-center bg-gray-100 dark:bg-gray-700 rounded-lg">
              <div class="space-y-4 w-full px-8">
                <div v-for="(item, index) in gradeHistory" :key="index">
                  <div class="flex justify-between mb-1">
                    <span class="text-sm font-medium">{{ item.subject }}</span>
                    <span class="text-sm font-medium">{{ item.percentage }}%</span>
                  </div>
                  <div class="w-full bg-gray-200 dark:bg-gray-600 rounded-full h-2.5">
                    <div
                      class="h-2.5 rounded-full"
                      :class="{
                        'bg-blue-600': item.color === 'blue',
                        'bg-green-600': item.color === 'green',
                        'bg-yellow-600': item.color === 'yellow',
                        'bg-purple-600': item.color === 'purple',
                        'bg-red-600': item.color === 'red'
                      }"
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
      <div v-else class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 text-center">
        <h3 class="text-xl font-medium mb-4">Grades Overview</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-6">
          Select a child to view their grades and academic performance. You can see detailed information about each subject, including assignments and assessments.
        </p>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div
            v-for="(child, index) in children"
            :key="index"
            class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
            @click="selectChild(child.id)"
          >
            <div class="flex items-center">
              <img :src="child.avatar" :alt="`${child.name} Avatar`" class="w-12 h-12 rounded-full mr-4" />
              <div>
                <h4 class="font-medium">{{ child.name }}</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ child.grade }}</p>
                <p class="text-sm mt-1">
                  GPA: <span :class="getGradeClass(child.stats.gpa * 25)">{{ child.stats.gpa }}</span>
                </p>
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
