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
const showFilters = ref(false);
const statusFilter = ref('all');
const gradeFilter = ref('all');
const showExportModal = ref(false);
const exportFormat = ref('csv');
const showGradeDistribution = ref(true);
const selectedTerm = ref('midterm'); // Default to midterm

// Available terms
const terms = [
  { id: 'midterm', name: 'Midterm' },
  { id: 'final', name: 'Final Term' }
];

// Fallback data for sections
const fallbackSections = [
  {
    id: 'sci101a',
    name: 'Science 101 - Section A',
    icon: '🧪',
    term: 'Fall Semester 2023',
    studentCount: 28,
    presentCount: 26,
    pendingAssignments: 3,
    toGradeCount: 12,
    averageScore: 87,
    gradeDistribution: {
      a: 8,
      b: 12,
      c: 5,
      d: 2,
      f: 1
    },
    assignments: [
      { id: 'midterm', name: 'Midterm Exam', category: 'exam', weight: 30, totalPoints: 100, dueDate: '2023-10-15', term: 'midterm' },
      { id: 'quiz1', name: 'Quiz 1', category: 'quiz', weight: 10, totalPoints: 50, dueDate: '2023-09-20', term: 'midterm' },
      { id: 'lab', name: 'Lab Report', category: 'lab', weight: 15, totalPoints: 100, dueDate: '2023-10-05', term: 'midterm' },
      { id: 'final', name: 'Final Exam', category: 'exam', weight: 35, totalPoints: 100, dueDate: '2023-12-15', term: 'final' },
      { id: 'project', name: 'Final Project', category: 'project', weight: 25, totalPoints: 100, dueDate: '2023-11-20', term: 'final' }
    ],
    students: [
      {
        id: '2023001',
        name: 'Emma Thompson',
        email: 'emma.t@school.edu',
        status: 'active',
        grades: {
          midterm: 92,
          quiz1: 88,
          lab: 95,
          final: 94,
          project: 90
        },
        finalGrade: 91,
        letterGrade: 'A'
      },
      {
        id: '2023002',
        name: 'Liam Johnson',
        email: 'liam.j@school.edu',
        status: 'active',
        grades: {
          midterm: 85,
          quiz1: 82,
          lab: 88,
          final: 86,
          project: 84
        },
        finalGrade: 85,
        letterGrade: 'B'
      },
      {
        id: '2023003',
        name: 'Olivia Davis',
        email: 'olivia.d@school.edu',
        status: 'active',
        grades: {
          midterm: 78,
          quiz1: 75,
          lab: 82,
          final: 80,
          project: 80
        },
        finalGrade: 79,
        letterGrade: 'C'
      },
      {
        id: '2023004',
        name: 'Noah Wilson',
        email: 'noah.w@school.edu',
        status: 'inactive',
        grades: {
          midterm: 0,
          quiz1: 0,
          lab: 0,
          final: 0,
          project: 0
        },
        finalGrade: 0,
        letterGrade: 'F'
      },
      {
        id: '2023005',
        name: 'Ava Martinez',
        email: 'ava.m@school.edu',
        status: 'active',
        grades: {
          midterm: 95,
          quiz1: 92,
          lab: 98,
          final: 96,
          project: 96
        },
        finalGrade: 95,
        letterGrade: 'A'
      },
      {
        id: '2023006',
        name: 'Ethan Brown',
        email: 'ethan.b@school.edu',
        status: 'active',
        grades: {
          midterm: 82,
          quiz1: 80,
          lab: 85,
          final: 84,
          project: 83
        },
        finalGrade: 83,
        letterGrade: 'B'
      },
      {
        id: '2023007',
        name: 'Sophia Taylor',
        email: 'sophia.t@school.edu',
        status: 'active',
        grades: {
          midterm: 88,
          quiz1: 85,
          lab: 90,
          final: 89,
          project: 87
        },
        finalGrade: 88,
        letterGrade: 'B'
      },
      {
        id: '2023008',
        name: 'Mason Anderson',
        email: 'mason.a@school.edu',
        status: 'active',
        grades: {
          midterm: 79,
          quiz1: 76,
          lab: 82,
          final: 78,
          project: 80
        },
        finalGrade: 79,
        letterGrade: 'C'
      },
      {
        id: '2023009',
        name: 'Isabella Thomas',
        email: 'isabella.t@school.edu',
        status: 'active',
        grades: {
          midterm: 91,
          quiz1: 88,
          lab: 94,
          final: 93,
          project: 92
        },
        finalGrade: 91,
        letterGrade: 'A'
      },
      {
        id: '2023010',
        name: 'James Jackson',
        email: 'james.j@school.edu',
        status: 'active',
        grades: {
          midterm: 84,
          quiz1: 81,
          lab: 87,
          final: 85,
          project: 85
        },
        finalGrade: 84,
        letterGrade: 'B'
      },
    ]
  }
];

// Get sections from page props or use empty array
const sections = usePage().props.sections || [];

// Create default student structure with empty grades
const createDefaultStudent = (id, name, email, status = 'active') => {
  return {
    id: id || `S${Math.floor(Math.random() * 10000)}`,
    name: name || 'Student Name',
    email: email || 'student@example.com',
    status: status || 'active',
    grades: {
      midterm: 0,
      quiz1: 0,
      lab: 0,
      final: 0,
      project: 0
    },
    finalGrade: 0,
    letterGrade: 'F'
  };
};

// Create default assignment structure
const createDefaultAssignment = (id, name, category, weight, term) => {
  return {
    id: id || 'assignment',
    name: name || 'Assignment',
    category: category || 'exam',
    weight: weight || 10,
    totalPoints: 100,
    dueDate: new Date().toISOString().split('T')[0],
    term: term || 'midterm'
  };
};

const classSections = computed(() => {
  // If sections from the server are available, use them with fallback properties
  if (sections && sections.length > 0) {
    return sections.map(section => {
      // Find a matching fallback section if available
      const fallbackSection = fallbackSections.find(fs => fs.id === section.id) || fallbackSections[0];

      // Ensure students array exists and has proper structure
      const students = section.students && Array.isArray(section.students)
        ? section.students.map(student => {
            // Ensure each student has all required properties
            return {
              id: student.id || `S${Math.floor(Math.random() * 10000)}`,
              name: student.name || 'Student Name',
              email: student.email || `${student.name?.toLowerCase().replace(/\s+/g, '.')}@school.edu` || 'student@example.com',
              status: student.status || 'active',
              grades: student.grades || {
                midterm: 0,
                quiz1: 0,
                lab: 0,
                final: 0,
                project: 0
              },
              finalGrade: student.finalGrade || 0,
              letterGrade: student.letterGrade || 'F'
            };
          })
        : fallbackSection.students || [];

      // Ensure assignments array exists and has proper structure
      const assignments = section.assignments && Array.isArray(section.assignments)
        ? section.assignments.map(assignment => {
            return {
              id: assignment.id || 'assignment',
              name: assignment.name || 'Assignment',
              category: assignment.category || 'exam',
              weight: assignment.weight || 10,
              totalPoints: assignment.totalPoints || 100,
              dueDate: assignment.dueDate || new Date().toISOString().split('T')[0],
              term: assignment.term || 'midterm'
            };
          })
        : fallbackSection.assignments || [];

      return {
        id: section.id,
        name: section.name || fallbackSection.name || 'Unnamed Section',
        icon: section.icon || fallbackSection.icon || '📚',
        term: section.term || fallbackSection.term || 'Current Term',
        studentCount: section.studentCount || students.length || 0,
        presentCount: section.presentCount || fallbackSection.presentCount || 0,
        pendingAssignments: section.pendingAssignments || fallbackSection.pendingAssignments || 0,
        toGradeCount: section.toGradeCount || fallbackSection.toGradeCount || 0,
        averageScore: section.averageScore || fallbackSection.averageScore || 0,
        gradeDistribution: section.gradeDistribution || fallbackSection.gradeDistribution || { a: 0, b: 0, c: 0, d: 0, f: 0 },
        assignments: assignments,
        students: students,
      };
    });
  }
  // If no sections from server, use fallback data
  return fallbackSections;
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

// Save grades
function saveGrades() {
  // In a real application, this would send the data to a server
  const notification = document.createElement('div');
  notification.className = 'fixed bottom-4 right-4 bg-emerald-500 text-white px-6 py-3 rounded-lg shadow-lg transform transition-all duration-500 flex items-center';
  notification.innerHTML = `
    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
    </svg>
    Grades saved successfully!
  `;
  document.body.appendChild(notification);

  setTimeout(() => {
    notification.style.opacity = '0';
    setTimeout(() => {
      document.body.removeChild(notification);
    }, 500);
  }, 3000);
}

// Export grades
function exportGrades() {
  // In a real application, this would generate and download a file
  const notification = document.createElement('div');
  notification.className = 'fixed bottom-4 right-4 bg-blue-500 text-white px-6 py-3 rounded-lg shadow-lg transform transition-all duration-500 flex items-center';
  notification.innerHTML = `
    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
    </svg>
    Grades exported as ${exportFormat.toUpperCase()}!
  `;
  document.body.appendChild(notification);

  setTimeout(() => {
    notification.style.opacity = '0';
    setTimeout(() => {
      document.body.removeChild(notification);
    }, 500);
  }, 3000);

  showExportModal.value = false;
}

// Sort students
function sortStudents(a, b) {
  const direction = sortDirection.value === 'asc' ? 1 : -1;

  if (sortBy.value === 'name') {
    return (a.name || '').localeCompare(b.name || '') * direction;
  } else if (sortBy.value === 'id') {
    return (a.id || '').localeCompare(b.id || '') * direction;
  } else if (sortBy.value === 'finalGrade') {
    return ((a.finalGrade || 0) - (b.finalGrade || 0)) * direction;
  } else if (sortBy.value === 'letterGrade') {
    return (a.letterGrade || '').localeCompare(b.letterGrade || '') * direction;
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

      // Filter by grade
      if (gradeFilter.value !== 'all') {
        if (gradeFilter.value !== (student.letterGrade || 'F')) return false;
      }

      return true;
    })
    .sort(sortStudents);
});

// Filter assignments by selected term
const filteredAssignments = computed(() => {
  if (!currentSection.value || !currentSection.value.assignments) return [];

  return (currentSection.value.assignments || []).filter(assignment =>
    (assignment.term || 'midterm') === selectedTerm.value
  );
});

// Computed property for class average
const classAverage = computed(() => {
  if (!currentSection.value || !currentSection.value.students) return 0;

  const activeStudents = (currentSection.value.students || []).filter(student => (student.status || 'active') === 'active');
  if (activeStudents.length === 0) return 0;

  const sum = activeStudents.reduce((total, student) => total + (student.finalGrade || 0), 0);
  return Math.round(sum / activeStudents.length);
});

// Computed property for grade distribution
const gradeDistribution = computed(() => {
  if (!currentSection.value || !currentSection.value.students) {
    return { a: 0, b: 0, c: 0, d: 0, f: 0 };
  }

  const distribution = { a: 0, b: 0, c: 0, d: 0, f: 0 };

  (currentSection.value.students || []).forEach(student => {
    const grade = (student.letterGrade || 'F').toLowerCase();
    if (distribution[grade] !== undefined) {
      distribution[grade]++;
    } else {
      // Default to F if letterGrade is invalid
      distribution.f++;
    }
  });

  return distribution;
});

// Computed property for grade distribution percentages
const gradeDistributionPercentages = computed(() => {
  if (!currentSection.value || !currentSection.value.students) {
    return { a: 0, b: 0, c: 0, d: 0, f: 0 };
  }

  const total = (currentSection.value.students || []).length;
  if (total === 0) return { a: 0, b: 0, c: 0, d: 0, f: 0 };

  const distribution = gradeDistribution.value;

  return {
    a: Math.round((distribution.a / total) * 100) || 0,
    b: Math.round((distribution.b / total) * 100) || 0,
    c: Math.round((distribution.c / total) * 100) || 0,
    d: Math.round((distribution.d / total) * 100) || 0,
    f: Math.round((distribution.f / total) * 100) || 0
  };
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

  // Clean up event listeners on component unmount
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
              <div class="grid grid-cols-2 gap-4 w-full">
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
              </div>
              <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                Viewing grades for {{ selectedTerm === 'midterm' ? 'Midterm' : 'Final Term' }} assessments
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- Grade Distribution -->
      <div v-if="showGradeDistribution" class="bg-white dark:bg-gray-800 rounded-2xl shadow-md overflow-hidden border border-gray-100 dark:border-gray-700 w-full">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
          <div class="flex justify-between items-center w-full">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Grade Distribution</h2>
            <button @click="showGradeDistribution = false" class="text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
        </div>
        <div class="p-6">
          <div class="grid grid-cols-1 md:grid-cols-5 gap-4 w-full">
            <div class="bg-emerald-50 dark:bg-emerald-900/20 rounded-lg p-4 flex flex-col items-center w-full">
              <span class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">{{ gradeDistribution.a }}</span>
              <span class="text-sm font-medium text-emerald-800 dark:text-emerald-300">A</span>
              <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2.5 mt-2">
                <div class="bg-emerald-500 h-2.5 rounded-full" :style="`width: ${gradeDistributionPercentages.a}%`"></div>
              </div>
              <span class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ gradeDistributionPercentages.a }}%</span>
            </div>
            <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4 flex flex-col items-center w-full">
              <span class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ gradeDistribution.b }}</span>
              <span class="text-sm font-medium text-blue-800 dark:text-blue-300">B</span>
              <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2.5 mt-2">
                <div class="bg-blue-500 h-2.5 rounded-full" :style="`width: ${gradeDistributionPercentages.b}%`"></div>
              </div>
              <span class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ gradeDistributionPercentages.b }}%</span>
            </div>
            <div class="bg-amber-50 dark:bg-amber-900/20 rounded-lg p-4 flex flex-col items-center w-full">
              <span class="text-2xl font-bold text-amber-600 dark:text-amber-400">{{ gradeDistribution.c }}</span>
              <span class="text-sm font-medium text-amber-800 dark:text-amber-300">C</span>
              <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2.5 mt-2">
                <div class="bg-amber-500 h-2.5 rounded-full" :style="`width: ${gradeDistributionPercentages.c}%`"></div>
              </div>
              <span class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ gradeDistributionPercentages.c }}%</span>
            </div>
            <div class="bg-orange-50 dark:bg-orange-900/20 rounded-lg p-4 flex flex-col items-center w-full">
              <span class="text-2xl font-bold text-orange-600 dark:text-orange-400">{{ gradeDistribution.d }}</span>
              <span class="text-sm font-medium text-orange-800 dark:text-orange-300">D</span>
              <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2.5 mt-2">
                <div class="bg-orange-500 h-2.5 rounded-full" :style="`width: ${gradeDistributionPercentages.d}%`"></div>
              </div>
              <span class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ gradeDistributionPercentages.d }}%</span>
            </div>
            <div class="bg-red-50 dark:bg-red-900/20 rounded-lg p-4 flex flex-col items-center w-full">
              <span class="text-2xl font-bold text-red-600 dark:text-red-400">{{ gradeDistribution.f }}</span>
              <span class="text-sm font-medium text-red-800 dark:text-red-300">F</span>
              <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2.5 mt-2">
                <div class="bg-red-500 h-2.5 rounded-full" :style="`width: ${gradeDistributionPercentages.f}%`"></div>
              </div>
              <span class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ gradeDistributionPercentages.f }}%</span>
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

            <div class="flex items-center gap-3 w-full sm:w-auto">
              <button
                @click="showFilters = !showFilters"
                class="px-3.5 py-2.5 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg text-sm font-medium hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors duration-200 flex items-center gap-1.5 w-full sm:w-auto"
              >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                </svg>
                Filters
              </button>

              <button
                @click="showExportModal = true"
                class="px-3.5 py-2.5 bg-indigo-100 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300 rounded-lg text-sm font-medium hover:bg-indigo-200 dark:hover:bg-indigo-800/50 transition-colors duration-200 flex items-center gap-1.5 w-full sm:w-auto"
              >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                Export
              </button>

              <button
                v-if="!showGradeDistribution"
                @click="showGradeDistribution = true"
                class="px-3.5 py-2.5 bg-purple-100 dark:bg-purple-900/50 text-purple-700 dark:text-purple-300 rounded-lg text-sm font-medium hover:bg-purple-200 dark:hover:bg-purple-800/50 transition-colors duration-200 flex items-center gap-1.5 w-full sm:w-auto"
              >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                Show Distribution
              </button>
            </div>
          </div>

          <!-- Expanded Filters -->
          <div v-if="showFilters" class="mt-5 p-5 bg-gray-100 dark:bg-gray-700 rounded-lg w-full">
            <div class="flex flex-wrap gap-6 w-full">
              <div class="w-full sm:w-auto">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Status</label>
                <select
                  v-model="statusFilter"
                  class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 text-sm focus:ring-indigo-500 focus:border-indigo-500 shadow-sm appearance-none bg-no-repeat pl-4 pr-10 py-2.5"
                  style="background-image: url('data:image/svg+xml;charset=utf-8,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 fill=%22none%22 viewBox=%220 0 20 20%22%3E%3Cpath stroke=%22%236B7280%22 stroke-linecap=%22round%22 stroke-linejoin=%22round%22 stroke-width=%221.5%22 d=%22M6 8l4 4 4-4%22/%3E%3C/svg%3E'); background-position: right 0.75rem center; background-size: 1.5em 1.5em;"
                >
                  <option value="all">All Statuses</option>
                  <option value="active">Active</option>
                  <option value="inactive">Inactive</option>
                </select>
              </div>

              <div class="w-full sm:w-auto">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Grade</label>
                <select
                  v-model="gradeFilter"
                  class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 text-sm focus:ring-indigo-500 focus:border-indigo-500 shadow-sm appearance-none bg-no-repeat pl-4 pr-10 py-2.5"
                  style="background-image: url('data:image/svg+xml;charset=utf-8,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 fill=%22none%22 viewBox=%220 0 20 20%22%3E%3Cpath stroke=%22%236B7280%22 stroke-linecap=%22round%22 stroke-linejoin=%22round%22 stroke-width=%221.5%22 d=%22M6 8l4 4 4-4%22/%3E%3C/svg%3E'); background-position: right 0.75rem center; background-size: 1.5em 1.5em;"
                >
                  <option value="all">All Grades</option>
                  <option value="A">A</option>
                  <option value="B">B</option>
                  <option value="C">C</option>
                  <option value="D">D</option>
                  <option value="F">F</option>
                </select>
              </div>

              <div class="w-full sm:w-auto">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Sort By</label>
                <select
                  v-model="sortBy"
                  class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 text-sm focus:ring-indigo-500 focus:border-indigo-500 shadow-sm appearance-none bg-no-repeat pl-4 pr-10 py-2.5"
                  style="background-image: url('data:image/svg+xml;charset=utf-8,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 fill=%22none%22 viewBox=%220 0 20 20%22%3E%3Cpath stroke=%22%236B7280%22 stroke-linecap=%22round%22 stroke-linejoin=%22round%22 stroke-width=%221.5%22 d=%22M6 8l4 4 4-4%22/%3E%3C/svg%3E'); background-position: right 0.75rem center; background-size: 1.5em 1.5em;"
                >
                  <option value="name">Name</option>
                  <option value="id">ID</option>
                  <option value="finalGrade">Final Grade</option>
                  <option value="letterGrade">Letter Grade</option>
                </select>
              </div>

              <div class="w-full sm:w-auto">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Direction</label>
                <select
                  v-model="sortDirection"
                  class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 text-sm focus:ring-indigo-500 focus:border-indigo-500 shadow-sm appearance-none bg-no-repeat pl-4 pr-10 py-2.5"
                  style="background-image: url('data:image/svg+xml;charset=utf-8,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 fill=%22none%22 viewBox=%220 0 20 20%22%3E%3Cpath stroke=%22%236B7280%22 stroke-linecap=%22round%22 stroke-linejoin=%22round%22 stroke-width=%221.5%22 d=%22M6 8l4 4 4-4%22/%3E%3C/svg%3E'); background-position: right 0.75rem center; background-size: 1.5em 1.5em;"
                >
                  <option value="asc">Ascending</option>
                  <option value="desc">Descending</option>
                </select>
              </div>
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
                <th scope="col" class="px-6 py-4 text-left text-sm font-semibold text-indigo-700 dark:text-indigo-300 uppercase tracking-wide">ID</th>
                <th v-for="assignment in filteredAssignments" :key="assignment.id" scope="col" class="px-6 py-4 text-left text-sm font-semibold text-indigo-700 dark:text-indigo-300 uppercase tracking-wide">
                  {{ assignment.name }}
                  <div class="text-xs font-normal text-indigo-500 dark:text-indigo-400 normal-case">{{ assignment.weight }}%</div>
                </th>
                <th scope="col" class="px-6 py-4 text-left text-sm font-semibold text-indigo-700 dark:text-indigo-300 uppercase tracking-wide cursor-pointer" @click="toggleSort('finalGrade')">
                  <div class="flex items-center">
                    Final Grade
                    <svg v-if="sortBy === 'finalGrade'" xmlns="http://www.w3.org/2000/svg" class="ml-2 h-5 w-5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="sortDirection === 'asc' ? 'M5 15l7-7 7 7' : 'M19 9l-7 7-7-7'" />
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
                <td v-for="assignment in filteredAssignments" :key="assignment.id" class="px-6 py-4">
                  <input
                    type="number"
                    v-model="student.grades[assignment.id]"
                    min="0"
                    :max="assignment.totalPoints || 100"
                    class="w-full px-3 py-2 rounded-md border border-gray-300 dark:border-gray-600 dark:bg-gray-700 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm text-sm"
                  />
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span class="text-sm font-medium text-gray-900 dark:text-white">{{ student.finalGrade || 0 }}%</span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span :class="`inline-flex items-center px-3 py-1 rounded-lg text-xs font-medium ${getGradeClass(student.letterGrade || 'F')}`">
                    {{ student.letterGrade || 'F' }}
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
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
          </div>
          <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-1">No students found</h3>
          <p class="text-gray-500 dark:text-gray-400">Try adjusting your search or filter criteria</p>
        </div>

        <!-- Footer with Save Button -->
        <div class="p-8 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 flex justify-end w-full">
          <button
            @click="saveGrades"
            class="px-8 py-3 bg-gradient-to-r from-indigo-600 to-violet-600 text-white rounded-lg text-sm font-medium hover:from-indigo-700 hover:to-violet-700 transition-colors duration-200 flex items-center gap-2 shadow-md w-full sm:w-auto"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            Save Grades
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Export Modal -->
  <div v-if="showExportModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl w-full max-w-md p-6 m-4">
      <div class="flex justify-between items-center mb-6">
        <h3 class="text-xl font-bold text-gray-900 dark:text-white">Export Grades</h3>
        <button @click="showExportModal = false" class="text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>
      <form @submit.prevent="exportGrades" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Export Format</label>
          <div class="grid grid-cols-3 gap-3">
            <label class="flex items-center p-3 border border-gray-300 dark:border-gray-600 rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200" :class="{ 'bg-indigo-50 border-indigo-300 dark:bg-indigo-900/20 dark:border-indigo-500': exportFormat === 'csv' }">
              <input type="radio" v-model="exportFormat" value="csv" class="sr-only" />
              <div class="flex flex-col items-center w-full">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 mb-2 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>

                <span class="text-sm font-medium text-gray-900 dark:text-white">CSV</span>
              </div>
            </label>
            <label class="flex items-center p-3 border border-gray-300 dark:border-gray-600 rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200" :class="{ 'bg-indigo-50 border-indigo-300 dark:bg-indigo-900/20 dark:border-indigo-500': exportFormat === 'excel' }">
              <input type="radio" v-model="exportFormat" value="excel" class="sr-only" />
              <div class="flex flex-col items-center w-full">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 mb-2 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span class="text-sm font-medium text-gray-900 dark:text-white">Excel</span>
              </div>
            </label>
            <label class="flex items-center p-3 border border-gray-300 dark:border-gray-600 rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200" :class="{ 'bg-indigo-50 border-indigo-300 dark:bg-indigo-900/20 dark:border-indigo-500': exportFormat === 'pdf' }">
              <input type="radio" v-model="exportFormat" value="pdf" class="sr-only" />
              <div class="flex flex-col items-center w-full">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 mb-2 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                </svg>

                <span class="text-sm font-medium text-gray-900 dark:text-white">PDF</span>
              </div>
            </label>
          </div>
        </div>
        <div class="flex justify-end space-x-2 pt-4">
          <button
            type="button"
            @click="showExportModal = false"
            class="px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-md text-sm font-medium hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200"
          >
            Cancel
          </button>
          <button
            type="submit"
            class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm font-medium hover:bg-indigo-700 transition-colors duration-200 w-full sm:w-auto"
          >
            Export
          </button>
        </div>
      </form>
    </div>
  </div>
</AppLayout>
</template>
