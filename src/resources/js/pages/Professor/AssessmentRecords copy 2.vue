<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, usePage } from '@inertiajs/vue3';
import { computed, inject, onMounted, ref, watch } from 'vue';

// Define the getLetterGrade function at the top level so it's properly exposed to the template
function getLetterGrade(grade) {
  if (grade >= 90) return 'A';
  if (grade >= 80) return 'B';
  if (grade >= 70) return 'C';
  if (grade >= 60) return 'D';
  return 'F';
}

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: "Assessment Records",
    href: "/assess_records",
  },
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
const selectedAssignment = ref<string | null>(null);
const searchQuery = ref('');
const sortBy = ref('name');
const sortDirection = ref('asc');
const showFilters = ref(false);
const statusFilter = ref('all');
const selectedTerm = ref('midterm'); // Default to midterm, matching Grades.vue

// Available terms - matching Grades.vue
const terms = [
  { id: 'midterm', name: 'Midterm' },
  { id: 'final', name: 'Final Term' }
];

// Fallback data for sections - using the same structure as Grades.vue
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
        letterGrade: 'A',
        gradeComment: 'Excellent work!'
      },
      // Other students...
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
    letterGrade: 'F',
    gradeComment: ''
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
              letterGrade: student.letterGrade || 'F',
              gradeComment: student.gradeComment || ''
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
  selectedAssignment.value = section.assignments.find(a => a.term === selectedTerm.value)?.id || section.assignments[0]?.id || null;

  // Update URL with the selected section ID without navigating
  const url = new URL(window.location.href);
  url.searchParams.set('sectionId', section.id);
  window.history.replaceState({}, '', url.toString());

  // Dispatch an event to notify other components (like the sidebar)
  window.dispatchEvent(new CustomEvent('section-changed', {
    detail: {
      sectionId: section.id,
      source: 'assessment_records'
    }
  }));

  // Also dispatch the item-changed event for newer sidebar implementations
  window.dispatchEvent(new CustomEvent('item-changed', {
    detail: {
      itemId: section.id,
      itemType: 'section',
      source: 'assessment_records'
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
function updateStudentGrade(student, assignmentId, value) {
  if (!student || !currentSection.value) return;

  // Update the specific grade
  if (!student.grades) {
    student.grades = {};
  }
  student.grades[assignmentId] = value;
}

// Save assessment records
function saveRecords() {
  if (!currentSection.value || !currentSection.value.students) return;

  try {
    // Prepare data for saving
    const recordsToSave = currentSection.value.students.map(student => {
      return {
        student_id: student.id,
        assignment_id: selectedAssignment.value,
        score: student.grades[selectedAssignment.value] || 0,
        grade_comment: student.gradeComment || '',
        status: student.status,
        section_id: currentSection.value.id,
        term: selectedTerm.value
      };
    });

    // In a real application, you would send this data to your backend
    // For demonstration, we'll just log the data
    console.log('Records to save:', {
      section_id: currentSection.value.id,
      assignment_id: selectedAssignment.value,
      term: selectedTerm.value,
      records: recordsToSave
    });

    // Show success notification
    const notification = document.createElement('div');
    notification.className = 'fixed bottom-4 right-4 bg-emerald-500 text-white px-6 py-3 rounded-lg shadow-lg transform transition-all duration-500 flex items-center';
    notification.innerHTML = `
      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
      </svg>
      Assessment records saved successfully!
    `;
    document.body.appendChild(notification);

    setTimeout(() => {
      notification.style.opacity = '0';
      setTimeout(() => {
        document.body.removeChild(notification);
      }, 500);
    }, 3000);

  } catch (error) {
    console.error('Error saving assessment records:', error);

    // Show error notification
    const notification = document.createElement('div');
    notification.className = 'fixed bottom-4 right-4 bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg transform transition-all duration-500 flex items-center';
    notification.innerHTML = `
      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
      </svg>
      Error saving assessment records. Please try again.
    `;
    document.body.appendChild(notification);

    setTimeout(() => {
      notification.style.opacity = '0';
      setTimeout(() => {
        document.body.removeChild(notification);
      }, 500);
    }, 3000);
  }
}

// Export assessment records
function exportRecords() {
  if (!currentSection.value || !currentSection.value.students || !selectedAssignment.value) return;

  try {
    const assignment = currentSection.value.assignments.find(a => a.id === selectedAssignment.value);
    if (!assignment) return;

    // Prepare data for export
    const dataToExport = currentSection.value.students.map(student => {
      return {
        ID: student.id,
        Name: student.name,
        Email: student.email,
        Status: student.status,
        Score: student.grades[selectedAssignment.value] || 0,
        'Letter Grade': getLetterGrade(student.grades[selectedAssignment.value] || 0),
        Comments: student.gradeComment || ''
      };
    });

    // In a real application, you would generate and download the file
    // For demonstration, we'll just log the data
    console.log(`Exporting assessment records for ${assignment.name}:`, dataToExport);

    // Show success notification
    const notification = document.createElement('div');
    notification.className = 'fixed bottom-4 right-4 bg-blue-500 text-white px-6 py-3 rounded-lg shadow-lg transform transition-all duration-500 flex items-center';
    notification.innerHTML = `
      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
      </svg>
      Assessment records exported successfully!
    `;
    document.body.appendChild(notification);

    setTimeout(() => {
      notification.style.opacity = '0';
      setTimeout(() => {
        document.body.removeChild(notification);
      }, 500);
    }, 3000);

  } catch (error) {
    console.error('Error exporting assessment records:', error);

    // Show error notification
    const notification = document.createElement('div');
    notification.className = 'fixed bottom-4 right-4 bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg transform transition-all duration-500 flex items-center';
    notification.innerHTML = `
      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
      </svg>
      Error exporting assessment records. Please try again.
    `;
    document.body.appendChild(notification);

    setTimeout(() => {
      notification.style.opacity = '0';
      setTimeout(() => {
        document.body.removeChild(notification);
      }, 500);
    }, 3000);
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
  } else if (sortBy.value === 'score') {
    const scoreA = a.grades?.[selectedAssignment.value] || 0;
    const scoreB = b.grades?.[selectedAssignment.value] || 0;
    return (scoreA - scoreB) * direction;
  } else if (sortBy.value === 'grade') {
    const scoreA = a.grades?.[selectedAssignment.value] || 0;
    const scoreB = b.grades?.[selectedAssignment.value] || 0;
    return (scoreA - scoreB) * direction;
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

// Filter students by search query and status
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

// Computed property for selected assignment details
const selectedAssignmentDetails = computed(() => {
  if (!currentSection.value || !selectedAssignment.value) return null;

  return currentSection.value.assignments.find(a => a.id === selectedAssignment.value) || null;
});

// Computed property for class average for the selected assignment
const assignmentAverage = computed(() => {
  if (!currentSection.value || !currentSection.value.students || !selectedAssignment.value) return 0;

  const activeStudents = (currentSection.value.students || []).filter(student => (student.status || 'active') === 'active');
  if (activeStudents.length === 0) return 0;

  const sum = activeStudents.reduce((total, student) => {
    const score = student.grades?.[selectedAssignment.value] || 0;
    return total + score;
  }, 0);

  return Math.round(sum / activeStudents.length);
});

// Computed property for grade distribution for the selected assignment
const assignmentGradeDistribution = computed(() => {
  if (!currentSection.value || !currentSection.value.students || !selectedAssignment.value) {
    return { a: 0, b: 0, c: 0, d: 0, f: 0 };
  }

  const distribution = { a: 0, b: 0, c: 0, d: 0, f: 0 };

  (currentSection.value.students || []).forEach(student => {
    const score = student.grades?.[selectedAssignment.value] || 0;
    const grade = getLetterGrade(score).toLowerCase();
    if (distribution[grade] !== undefined) {
      distribution[grade]++;
    } else {
      // Default to F if letterGrade is invalid
      distribution.f++;
    }
  });

  return distribution;
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
        selectedAssignment.value = section.assignments.find(a => a.term === selectedTerm.value)?.id || section.assignments[0]?.id || null;
      }
    }
  }
};

// Function to handle section-changed events
const handleSectionChange = (event) => {
  if (event.detail && event.detail.sectionId && event.detail.source !== 'assessment_records') {
    const section = findSectionById(event.detail.sectionId);
    if (section && (!currentSection.value || currentSection.value.id !== section.id)) {
      // Only update if it's a different section
      currentSection.value = section;
      selectedAssignment.value = section.assignments.find(a => a.term === selectedTerm.value)?.id || section.assignments[0]?.id || null;
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
      selectedAssignment.value = section.assignments.find(a => a.term === selectedTerm.value)?.id || section.assignments[0]?.id || null;
    }
  }
}, { immediate: true });

// Watch for changes in the selected term
watch(selectedTerm, (newTerm) => {
  if (currentSection.value) {
    // Update selected assignment when term changes
    const termAssignments = currentSection.value.assignments.filter(a => a.term === newTerm);
    if (termAssignments.length > 0) {
      selectedAssignment.value = termAssignments[0].id;
    }
  }
});

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
      selectedAssignment.value = section.assignments.find(a => a.term === selectedTerm.value)?.id || section.assignments[0]?.id || null;
    }
  }
  // Then check for itemId (new parameter from sidebar)
  else if (itemId) {
    const section = findSectionById(itemId);
    if (section && (!currentSection.value || currentSection.value.id !== section.id)) {
      // Only update if it's a different section
      currentSection.value = section;
      selectedAssignment.value = section.assignments.find(a => a.term === selectedTerm.value)?.id || section.assignments[0]?.id || null;
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
      selectedAssignment.value = section.assignments.find(a => a.term === selectedTerm.value)?.id || section.assignments[0]?.id || null;
      return;
    }
  }
  // Then check for itemId (which could be a section ID from sidebar)
  else if (itemIdFromUrl) {
    const section = findSectionById(itemIdFromUrl);
    if (section) {
      currentSection.value = section;
      selectedAssignment.value = section.assignments.find(a => a.term === selectedTerm.value)?.id || section.assignments[0]?.id || null;
      return;
    }
  }
  // Finally check for injected item ID
  else if (currentItemId.value) {
    const section = findSectionById(currentItemId.value);
    if (section) {
      currentSection.value = section;
      selectedAssignment.value = section.assignments.find(a => a.term === selectedTerm.value)?.id || section.assignments[0]?.id || null;
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
    <Head title="Assessment Records" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 pt-20">
            <!-- No Section Selected Message -->
            <div v-if="!currentSection" class="flex h-[calc(100vh-10rem)] flex-col items-center justify-center text-center">
                <div
                    class="mb-6 rounded-full bg-gradient-to-br from-indigo-50 to-purple-50 p-8 shadow-inner dark:from-indigo-900/30 dark:to-purple-900/30"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-20 w-20 text-indigo-500 dark:text-indigo-400"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="1.5"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"
                        />
                    </svg>
                </div>
                <h2 class="mb-3 text-3xl font-bold text-gray-900 dark:text-white">Select a Class Section</h2>
                <p class="mb-8 max-w-md text-lg text-gray-600 dark:text-gray-400">
                    Please select a class section to view and manage assessment records.
                </p>
                <div class="grid max-w-4xl grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
                    <button
                        v-for="section in classSections.slice(0, 4)"
                        :key="section.id"
                        @click="selectSection(section)"
                        class="group flex flex-col items-center rounded-xl border border-gray-200 bg-white p-6 shadow-sm transition-colors duration-200 hover:bg-gray-50 hover:shadow-md dark:border-gray-700 dark:bg-gray-800 dark:hover:bg-gray-700"
                    >
                        <span class="mb-3 transform text-4xl transition-transform duration-200 group-hover:scale-110">{{ section.icon }}</span>
                        <span class="text-base font-medium text-gray-900 dark:text-white">{{ section.name }}</span>
                        <span class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ section.studentCount }} Students</span>
                    </button>
                </div>
            </div>

            <!-- Assessment Records Page -->
            <div v-if="currentSection" class="space-y-6">
                <!-- Header with Class Info -->
                <div class="overflow-hidden rounded-2xl bg-gradient-to-br from-indigo-600 to-violet-600 shadow-lg dark:from-indigo-800 dark:to-violet-800">
                    <div class="relative p-8 text-white">
                        <div class="absolute right-0 top-0 -mr-20 -mt-20 h-64 w-64 rounded-full bg-white/10 blur-3xl"></div>
                        <div class="absolute bottom-0 left-0 -mb-10 -ml-10 h-40 w-40 rounded-full bg-white/10 blur-2xl"></div>
                        <div class="relative z-10">
                            <div class="flex flex-col justify-between gap-4 md:flex-row md:items-center">
                                <div>
                                    <div class="flex items-center gap-3">
                                        <span class="text-4xl">{{ currentSection.icon }}</span>
                                        <div>
                                            <h1 class="text-2xl font-bold md:text-3xl">{{ currentSection.name }}</h1>
                                            <p class="mt-1 text-indigo-100 dark:text-indigo-200">{{ currentSection.term }} • {{ currentSection.studentCount }} Students</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3">
                                    <div class="flex items-center gap-2 rounded-lg bg-white/20 px-4 py-2 backdrop-blur-sm">
                                        <span class="text-sm font-medium">Class Average:</span>
                                        <span class="text-xl font-bold">{{ currentSection.averageScore }}%</span>
                                        <span
                                            :class="`ml-1 rounded-full px-2 py-0.5 text-xs font-medium ${getGradeClass(currentSection.averageScore)}`"
                                        >
                                            {{ getLetterGrade(currentSection.averageScore) }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Term Selection -->
                <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-md dark:border-gray-700 dark:bg-gray-800">
                    <div class="border-b border-gray-200 p-6 dark:border-gray-700">
                        <div class="flex items-center justify-between">
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Term Selection</h2>
                        </div>
                    </div>
                    <div class="p-6">
                        <div class="flex flex-col gap-4 sm:flex-row">
                            <div class="w-full">
                                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Select Term</label>
                                <div class="grid grid-cols-2 gap-4">
                                    <button
                                        v-for="term in terms"
                                        :key="term.id"
                                        @click="selectedTerm = term.id"
                                        :class="[
                                            'w-full rounded-lg px-4 py-3 text-center font-medium transition-colors duration-200',
                                            selectedTerm === term.id
                                            ? 'bg-indigo-600 text-white shadow-md'
                                            : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600'
                                        ]"
                                    >
                                        {{ term.name }}
                                    </button>
                                </div>
                                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                    Viewing assessments for {{ selectedTerm === 'midterm' ? 'Midterm' : 'Final Term' }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Assessment Records Content -->
                <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-md dark:border-gray-700 dark:bg-gray-800">
                    <div class="sticky top-0 z-10 border-b border-gray-200 bg-gray-50 p-6 dark:border-gray-700 dark:bg-gray-800/50">
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                            <!-- Search on the left -->
                            <div class="relative w-full sm:w-1/2">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                </div>
                                <input
                                    type="text"
                                    v-model="searchQuery"
                                    placeholder="Search students..."
                                    class="w-full rounded-lg border-gray-300 py-2.5 pl-10 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700"
                                />
                            </div>

                            <!-- Assessment dropdown -->
                            <div class="w-full sm:w-1/2">
                                <select
                                    v-model="selectedAssignment"
                                    class="w-full appearance-none rounded-lg border-gray-300 bg-white py-2.5 pl-4 pr-10 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700"
                                    style="background-image: url('data:image/svg+xml;charset=utf-8,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 fill=%22none%22 viewBox=%220 0 20 20%22%3E%3Cpath stroke=%22%236B7280%22 stroke-linecap=%22round%22 stroke-linejoin=%22round%22 stroke-width=%221.5%22 d=%22M6 8l4 4 4-4%22/%3E%3C/svg%3E'); background-position: right 0.75rem center; background-size: 1.5em 1.5em; background-repeat: no-repeat;"
                                >
                                    <option v-for="assignment in filteredAssignments" :key="assignment.id" :value="assignment.id">
                                        {{ assignment.name }} ({{ assignment.category }})
                                    </option>
                                </select>
                            </div>
                        </div>

                        <!-- Assignment Details -->
                        <div v-if="selectedAssignmentDetails" class="mt-4 grid grid-cols-1 gap-4 rounded-lg bg-white p-4 shadow-sm dark:bg-gray-700 sm:grid-cols-3">
                            <div class="flex flex-col">
                                <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Category</span>
                                <span :class="`mt-1 inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ${getCategoryColor(selectedAssignmentDetails.category)}`">
                                    {{ selectedAssignmentDetails.category.charAt(0).toUpperCase() + selectedAssignmentDetails.category.slice(1) }}
                                </span>
                            </div>
                            <div class="flex flex-col">
                                <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Weight</span>
                                <span class="mt-1 text-sm font-medium text-gray-900 dark:text-white">{{ selectedAssignmentDetails.weight }}%</span>
                            </div>
                            <div class="flex flex-col">
                                <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Total Points</span>
                                <span class="mt-1 text-sm font-medium text-gray-900 dark:text-white">{{ selectedAssignmentDetails.totalPoints }} pts</span>
                            </div>
                        </div>
                    </div>

                    <!-- Student Assessment Records Table -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-indigo-50 dark:bg-indigo-900/30">
                                <tr>
                                    <th
                                        scope="col"
                                        class="cursor-pointer px-6 py-3 text-left text-sm font-semibold uppercase tracking-wide text-indigo-700 dark:text-indigo-300"
                                        @click="toggleSort('name')"
                                    >
                                        <div class="flex items-center">
                                            Student
                                            <svg
                                                v-if="sortBy === 'name'"
                                                xmlns="http://www.w3.org/2000/svg"
                                                class="ml-2 h-5 w-5 text-indigo-500"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                                stroke="currentColor"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    :d="sortDirection === 'asc' ? 'M5 15l7-7 7 7' : 'M19 9l-7 7-7-7'"
                                                />
                                            </svg>
                                        </div>
                                    </th>
                                    <th
                                        scope="col"
                                        class="cursor-pointer px-6 py-3 text-left text-sm font-semibold uppercase tracking-wide text-indigo-700 dark:text-indigo-300"
                                        @click="toggleSort('id')"
                                    >
                                        <div class="flex items-center">
                                            ID
                                            <svg
                                                v-if="sortBy === 'id'"
                                                xmlns="http://www.w3.org/2000/svg"
                                                class="ml-2 h-5 w-5 text-indigo-500"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                                stroke="currentColor"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    :d="sortDirection === 'asc' ? 'M5 15l7-7 7 7' : 'M19 9l-7 7-7-7'"
                                                />
                                            </svg>
                                        </div>
                                    </th>
                                    <th
                                        scope="col"
                                        class="cursor-pointer px-6 py-3 text-left text-sm font-semibold uppercase tracking-wide text-indigo-700 dark:text-indigo-300"
                                        @click="toggleSort('score')"
                                    >
                                        <div class="flex items-center">
                                            Score
                                            <svg
                                                v-if="sortBy === 'score'"
                                                xmlns="http://www.w3.org/2000/svg"
                                                class="ml-2 h-5 w-5 text-indigo-500"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                                stroke="currentColor"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    :d="sortDirection === 'asc' ? 'M5 15l7-7 7 7' : 'M19 9l-7 7-7-7'"
                                                />
                                            </svg>
                                        </div>
                                    </th>
                                    <th
                                        scope="col"
                                        class="cursor-pointer px-6 py-3 text-left text-sm font-semibold uppercase tracking-wide text-indigo-700 dark:text-indigo-300"
                                        @click="toggleSort('grade')"
                                    >
                                        <div class="flex items-center">
                                            Grade
                                            <svg
                                                v-if="sortBy === 'grade'"
                                                xmlns="http://www.w3.org/2000/svg"
                                                class="ml-2 h-5 w-5 text-indigo-500"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                                stroke="currentColor"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    :d="sortDirection === 'asc' ? 'M5 15l7-7 7 7' : 'M19 9l-7 7-7-7'"
                                                />
                                            </svg>
                                        </div>
                                    </th>
                                    <th
                                        scope="col"
                                        class="px-6 py-3 text-left text-sm font-semibold uppercase tracking-wide text-indigo-700 dark:text-indigo-300"
                                    >
                                        Comments
                                    </th>
                                    <th
                                        scope="col"
                                        class="px-6 py-3 text-left text-sm font-semibold uppercase tracking-wide text-indigo-700 dark:text-indigo-300"
                                    >
                                        Status
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                                <tr
                                    v-for="(student, index) in filteredStudents"
                                    :key="index"
                                    class="transition hover:bg-gray-100 dark:hover:bg-gray-700"
                                >
                                    <td class="flex items-center space-x-3 px-6 py-4">
                                        <img class="h-10 w-10 rounded-full shadow-md" :src="`https://i.pravatar.cc/100?img=${index + 10}`" alt="" />
                                        <div>
                                            <p class="text-sm font-medium text-gray-900 dark:text-white">{{ student.name }}</p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ student.email }}</p>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">{{ student.id }}</td>
                                    <td class="px-6 py-4">
                                        <input
                                            type="number"
                                            v-model="student.grades[selectedAssignment]"
                                            @input="updateStudentGrade(student, selectedAssignment, student.grades[selectedAssignment])"
                                            min="0"
                                            :max="selectedAssignmentDetails?.totalPoints || 100"
                                            class="w-24 rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700"
                                        />
                                    </td>
                                    <td class="px-6 py-4">
                                        <span
                                            :class="`inline-flex items-center rounded-lg px-3 py-1 text-xs font-medium ${getGradeClass(student.grades[selectedAssignment] || 0)}`"
                                            >{{ getLetterGrade(student.grades[selectedAssignment] || 0) }}</span
                                        >
                                    </td>
                                    <td class="px-6 py-4">
                                        <input
                                            type="text"
                                            v-model="student.gradeComment"
                                            placeholder="Add comments..."
                                            class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700"
                                        />
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span :class="{
                                            'bg-emerald-100 text-emerald-800 dark:bg-emerald-900 dark:text-emerald-300': (student.status || 'active') === 'active',
                                            'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300': (student.status || 'active') === 'inactive'
                                        }" class="rounded-full px-3 py-1 text-xs font-medium capitalize">
                                            {{ student.status || 'active' }}
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Empty State -->
                    <div v-if="filteredStudents.length === 0" class="p-12 text-center">
                        <div class="mb-4 inline-flex h-16 w-16 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-700">
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-8 w-8 text-gray-400"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
                                />
                            </svg>
                        </div>
                        <h3 class="mb-1 text-lg font-medium text-gray-900 dark:text-white">No students found</h3>
                        <p class="text-gray-500 dark:text-gray-400">Try adjusting your search or filter criteria</p>
                    </div>

                    <!-- Footer with Save Button -->
                    <div class="flex justify-end gap-4 border-t border-gray-200 bg-gray-50 p-6 dark:border-gray-700 dark:bg-gray-800/50">
                        <button
                            @click="exportRecords"
                            class="flex items-center gap-2 rounded-lg bg-indigo-100 px-6 py-3 text-sm font-medium text-indigo-700 shadow-md transition-colors duration-200 hover:bg-indigo-200 dark:bg-indigo-900/50 dark:text-indigo-300 dark:hover:bg-indigo-800/50"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                            Export Records
                        </button>
                        <button
                            @click="saveRecords"
                            class="flex items-center gap-2 rounded-lg bg-gradient-to-r from-indigo-600 to-violet-600 px-6 py-3 text-sm font-medium text-white shadow-md transition-colors duration-200 hover:from-indigo-700 hover:to-violet-700"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Save Assessment Records
                        </button>
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

.bg-primary-50 {
    background-color: rgb(var(--primary-50));
}
.bg-primary-100 {
    background-color: rgb(var(--primary-100));
}
.bg-primary-600 {
    background-color: rgb(var(--primary-600));
}
.text-primary-600 {
    color: rgb(var(--primary-600));
}
.text-primary-400 {
    color: rgb(var(--primary-400));
}

/* Add Tailwind dark mode support */
.dark .dark\:bg-primary-900 {
    background-color: rgb(var(--primary-900));
}
.dark .dark\:text-primary-400 {
    color: rgb(var(--primary-400));
}
.dark .dark\:text-primary-300 {
    color: rgb(var(--primary-300));
}

/* Transitions */
.transition-all {
  transition-property: all;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 150ms;
}

.transition-colors {
  transition-property: color, background-color, border-color, text-decoration-color, fill, stroke;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 150ms;
}

.transition-transform {
  transition-property: transform;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 150ms;
}

.blur-2xl {
  --tw-blur: blur(40px);
  filter: var(--tw-blur) var(--tw-brightness) var(--tw-contrast) var(--tw-grayscale) var(--tw-hue-rotate) var(--tw-invert) var(--tw-saturate) var(--tw-sepia) var(--tw-drop-shadow);
}

.blur-3xl {
  --tw-blur: blur(64px);
  filter: var(--tw-blur) var(--tw-brightness) var(--tw-contrast) var(--tw-grayscale) var(--tw-hue-rotate) var(--tw-invert) var(--tw-saturate) var(--tw-sepia) var(--tw-drop-shadow);
}

.group:hover .group-hover\:scale-110 {
  transform: scale(1.1);
}

.shadow-inner {
  --tw-shadow: inset 0 2px 4px 0 rgba(0, 0, 0, 0.06);
  box-shadow: var(--tw-ring-offset-shadow, 0 0 #0000), var(--tw-ring-shadow, 0 0 #0000), var(--tw-shadow);
}

.backdrop-blur-sm {
  --tw-backdrop-blur: blur(4px);
  backdrop-filter: var(--tw-backdrop-blur) var(--tw-backdrop-brightness) var(--tw-backdrop-contrast) var(--tw-backdrop-grayscale) var(--tw-backdrop-hue-rotate) var(--tw-backdrop-invert) var(--tw-backdrop-opacity) var(--tw-backdrop-saturate) var(--tw-backdrop-sepia);
}
</style>
