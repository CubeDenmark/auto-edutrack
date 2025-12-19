<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router, usePage } from '@inertiajs/vue3';
import { computed, inject, onMounted, ref, watch } from 'vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Grades',
        href: '/grades',
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
const isSaving = ref(false);
const currentSection = ref<any>(null);
const searchQuery = ref('');
const sortBy = ref('name');
const sortDirection = ref('asc');
const statusFilter = ref('all');
const gradeFilter = ref('all');
const selectedTerm = ref('Prelim'); // Default to midterm
const saveSuccess = ref(false);
const saveError = ref(false);
const showPdfErrorModal = ref(false);

// Pagination variables
const currentPage = ref(1);
const itemsPerPage = ref(8);

// Term weight variables
const prelimWeight = ref(30); // Default 30%
const midtermWeight = ref(30); // Default 30%
const finalTermWeight = ref(40); // Default 40%

// Available terms
const terms = computed(() => {
    if (!currentSection.value || !currentSection.value.assessments) return [];

    // Extract unique terms from assessments
    const uniqueTerms = new Set();
    currentSection.value.assessments.forEach((assessment) => {
        if (assessment.term && assessment.term !== 'Prelim') {
            uniqueTerms.add(assessment.term);
        }
    });

    // Convert to array of term objects with Prelim first
    return [
        { id: 'Prelim', name: 'Preliminary' },
        ...Array.from(uniqueTerms).map((term) => ({
            id: term,
            name: term === 'Midterm' ? 'Midterm' : term === 'Final Term' ? 'Final Term' : String(term),
        })),
    ];
});

// Get sections from page props
const sections = usePage().props.sections || [];

// Calculate letter grade based on numeric grade
function calculateLetterGrade(grade) {
    if (typeof grade === 'string') {
        grade = parseFloat(grade);
    }

    if (grade >= 99 && grade <= 100) return 1.0; // Excellent
    if (grade >= 96 && grade <= 98) return 1.25;
    if (grade >= 93 && grade <= 95) return 1.5;
    if (grade >= 90 && grade <= 92) return 1.75; // Very Good
    if (grade >= 87 && grade <= 89) return 2.0;
    if (grade >= 84 && grade <= 86) return 2.25; // Above Average
    if (grade >= 81 && grade <= 83) return 2.5;
    if (grade >= 78 && grade <= 80) return 2.75; // Average
    if (grade >= 75 && grade <= 77) return 3.0; // Passing
    if (grade < 75) return 5.0; // Conditional

    return 5.0; // Failing / fallback
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
            const assessment = assessments.find((a) => a.id === assessmentGrade.assessment_id);

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

const classSections = computed(() => {
    // If sections from the server are available, use them
    if (sections && sections.length > 0) {
        return sections.map((section) => {
            // Process assessments to calculate total points
            const processedAssessments =
                section.assessments && Array.isArray(section.assessments)
                    ? section.assessments.map((assessment) => {
                          const totalPoints = calculateAssessmentTotalPoints(assessment);
                          return {
                              ...assessment,
                              totalPoints: totalPoints,
                          };
                      })
                    : [];

            // Process students to ensure they have all required properties
            const students =
                section.students && Array.isArray(section.students)
                    ? section.students.map((student) => {
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
                              finalLetterGrade,
                          };
                      })
                    : [];

            return {
                ...section,
                assessments: processedAssessments,
                students: students,
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
    return classSections.value.find((section) => section.id === id) || null;
}

// Update the current section
function selectSection(section) {
    // Prevent re-selecting the same section to avoid infinite loops
    if (currentSection.value && currentSection.value.id === section.id) {
        return;
    }

    currentSection.value = section;
    // Reset pagination when changing sections
    currentPage.value = 1;

    // If there are terms available, select the first one
    if (terms.value.length > 0) {
        selectedTerm.value = terms.value[0].id;
    }

    // Update URL with the selected section ID without navigating
    const url = new URL(window.location.href);
    url.searchParams.set('sectionId', section.id);
    window.history.replaceState({}, '', url.toString());

    // Dispatch an event to notify other components (like the sidebar)
    window.dispatchEvent(
        new CustomEvent('section-changed', {
            detail: {
                sectionId: section.id,
                source: 'grades',
            },
        }),
    );

    // Also dispatch the item-changed event for newer sidebar implementations
    window.dispatchEvent(
        new CustomEvent('item-changed', {
            detail: {
                itemId: section.id,
                itemType: 'section',
                source: 'grades',
            },
        }),
    );
}

// Get CSS class for grade display
// Get CSS class for grade display based on numeric grade
function getGradeClass(grade) {
    if (typeof grade === 'string') {
        grade = parseFloat(grade);
    }

    if (grade <= 1.0) {
        return 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900 dark:text-emerald-300'; // Excellent
    } else if (grade <= 1.25) {
        return 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900 dark:text-emerald-300'; // Excellent
    } else if (grade <= 1.5) {
        return 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900 dark:text-emerald-300'; // Excellent
    } else if (grade <= 1.75) {
        return 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300'; // Very Good
    } else if (grade <= 2.0) {
        return 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300'; // Very Good
    } else if (grade <= 2.25) {
        return 'bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-300'; // Above Average
    } else if (grade <= 2.5) {
        return 'bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-300'; // Good
    } else if (grade <= 2.75) {
        return 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-300'; // Average
    } else if (grade <= 3.0) {
        return 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-300'; // Passing
    } else if (grade <= 4.0) {
        return 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300'; // Conditional
    } else if (grade > 4.0) {
        return 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300'; // Failing
    } else {
        return 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300'; // Default/fallback
    }
}

// Get color for category badge
function getCategoryColor(category) {
    switch (category) {
        case 'quiz':
            return 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300';
        case 'exam':
            return 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300';
        case 'lab':
            return 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900 dark:text-emerald-300';
        case 'project':
            return 'bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-300';
        default:
            return 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300';
    }
}

// Calculate final grade (weighted average of all terms)
function calculateFinalGrade(student) {
    if (!student) return 0;

    // Convert weights to decimal percentages
    const pWeight = prelimWeight.value / 100;
    const mWeight = midtermWeight.value / 100;
    const fWeight = finalTermWeight.value / 100;

    // Calculate weighted average
    const weightedGrade = (student.prelimGrade || 0) * pWeight + (student.midtermGrade || 0) * mWeight + (student.finalGrade || 0) * fWeight;

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
        .filter((student) => {
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

// Paginated students
const paginatedStudents = computed(() => {
    const startIndex = (currentPage.value - 1) * itemsPerPage.value;
    const endIndex = startIndex + itemsPerPage.value;
    return filteredStudents.value.slice(startIndex, endIndex);
});

// Total pages
const totalPages = computed(() => {
    return Math.ceil(filteredStudents.value.length / itemsPerPage.value);
});

// Page numbers for pagination
const pageNumbers = computed(() => {
    const pages = [];
    const maxVisiblePages = 5;

    if (totalPages.value <= maxVisiblePages) {
        // If we have fewer pages than the max visible, show all pages
        for (let i = 1; i <= totalPages.value; i++) {
            pages.push(i);
        }
    } else {
        // Always show first page
        pages.push(1);

        // Calculate start and end of visible page range
        let startPage = Math.max(2, currentPage.value - Math.floor(maxVisiblePages / 2));
        let endPage = Math.min(totalPages.value - 1, startPage + maxVisiblePages - 3);

        // Adjust if we're near the end
        if (endPage - startPage < maxVisiblePages - 3) {
            startPage = Math.max(2, endPage - (maxVisiblePages - 3));
        }

        // Add ellipsis if needed
        if (startPage > 2) {
            pages.push('...');
        }

        // Add visible page numbers
        for (let i = startPage; i <= endPage; i++) {
            pages.push(i);
        }

        // Add ellipsis if needed
        if (endPage < totalPages.value - 1) {
            pages.push('...');
        }

        // Always show last page
        pages.push(totalPages.value);
    }

    return pages;
});

// Go to specific page
function goToPage(page) {
    if (typeof page === 'number' && page >= 1 && page <= totalPages.value) {
        currentPage.value = page;
    }
}

// Go to previous page
function previousPage() {
    if (currentPage.value > 1) {
        currentPage.value--;
    }
}

// Go to next page
function nextPage() {
    if (currentPage.value < totalPages.value) {
        currentPage.value++;
    }
}

// Filter assessments by selected term
const filteredAssessments = computed(() => {
    if (!currentSection.value || !currentSection.value.assessments) return [];

    // If Final Grading is selected, return empty array (we'll show different columns)
    if (selectedTerm.value === 'Final') return [];

    return (currentSection.value.assessments || []).filter((assessment) => !assessment.term || assessment.term === selectedTerm.value);
});

// Computed property for class average - based on current term
const classAverage = computed(() => {
    if (!currentSection.value || !currentSection.value.students) return 0;

    const activeStudents = (currentSection.value.students || []).filter((student) => (student.status || 'active') === 'active');
    if (activeStudents.length === 0) return 0;

    // Sum the grades for the current term
    const sum = activeStudents.reduce((total, student) => {
        return total + getCurrentTermGrade(student);
    }, 0);

    return Math.round(sum / activeStudents.length);
});
// Function to save grades to the database
import { route } from 'ziggy-js';

function saveGrades() {
    if (!currentSection.value || !currentSection.value.students) {
        console.warn('No current section or students found.');
        return;
    }

    isSaving.value = true;
    saveSuccess.value = false;
    saveError.value = false;

    const records = [];

    currentSection.value.students.forEach((student) => {
        const final = calculateFinalGrade(student);

        // Capture remarks directly from student data
        const remarks = student.remarks || ''; // Retrieve the remarks from the input field

        const record = {
            student_id: student.id,

            // ✅ FIX: Use the actual student_class_id from the student object
            student_class_id: student.student_class_id, // This must be included from the backend

            prelim: student.prelimGrade || null,
            midterm: student.midtermGrade || null,
            final_term: student.finalGrade || null,
            final_grade: final || null,
            final_remarks: remarks || null, // Include remarks in the saved record
        };

        console.log('Prepared record for student:', student.id, record);

        records.push(record);
    });

    console.log('Sending records to backend:', records);

    router.post(
        route('grades.storeOrUpdate'),
        { records },
        {
            onSuccess: () => {
                console.log('Grades saved successfully.');
                isSaving.value = false;
                saveSuccess.value = true;

                setTimeout(() => {
                    saveSuccess.value = false;
                }, 3000);
            },
            onError: (errors) => {
                console.error('Error saving grades:', errors);
                isSaving.value = false;
                saveError.value = true;

                setTimeout(() => {
                    saveError.value = false;
                }, 3000);
            },
        },
    );
}

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
watch(
    currentItemId,
    (newItemId) => {
        if (newItemId) {
            const section = findSectionById(newItemId);
            if (section && (!currentSection.value || currentSection.value.id !== section.id)) {
                // Only update if it's a different section
                currentSection.value = section;
            }
        }
    },
    { immediate: true },
);

// Watch for changes in the URL query parameters
watch(
    () => window.location.search,
    (newSearch) => {
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
    },
    { immediate: true },
);

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

    window.addEventListener('item-changed', handleItemChange);
    window.addEventListener('section-changed', handleSectionChange);
});

// Clean up event listeners when component is unmounted
onMounted(() => {
    return () => {
        window.removeEventListener('item-changed', handleItemChange);
        window.removeEventListener('section-changed', handleSectionChange);
    };
});

function getInitial(name: string): string {
    if (!name) return '?';
    return name.charAt(0).toUpperCase(); // Returns first initial of the user's name
}

function generatePdfReport() {
    if (!currentSection.value || !currentSection.value.id) {
        console.warn('No current section found.');
        alert('Please select a section first.');
        return;
    }

    const sectionId = currentSection.value.id;

    fetch(`/grades/generate-pdf/${sectionId}`, {
        method: 'GET'
    })
    .then(async (response) => {
        if (!response.ok) {
            const errorData = await response.json().catch(() => ({}));
            throw new Error(errorData.message || 'Unknown error');
        }
        return response.blob(); // response is a PDF binary
    })
    .then(blob => {
        const blobUrl = URL.createObjectURL(blob);
        window.open(blobUrl, '_blank');
    })
    .catch(error => {
        console.error('Error generating PDF:', error);
        showPdfErrorModal.value = true;
    });
}


</script>

<template>
    <Head title="Grades" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="w-full p-6 pt-20">
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

            <!-- Grades Page -->
            <div v-if="currentSection" class="w-full space-y-8">
                <!-- Header with Class Info -->
                <div class="w-full overflow-hidden rounded-2xl shadow-lg dark:bg-gray-800">
                    <div class="relative p-8 text-white">
                        <!-- Decorative Blurred Background Circles -->
                        <div class="absolute right-0 top-0 -mr-20 -mt-20 h-64 w-64 rounded-full bg-white/10 blur-3xl"></div>
                        <div class="absolute bottom-0 left-0 -mb-10 -ml-10 h-40 w-40 rounded-full bg-white/10 blur-2xl"></div>

                        <!-- Content Section -->
                        <div class="relative z-10">
                            <div class="flex w-full flex-col justify-between gap-4 md:flex-row md:items-center">
                                <!-- Section Info -->
                                <div>
                                    <div class="flex items-center gap-4">
                                        <span class="text-4xl">{{ currentSection.icon }}</span>
                                        <div>
                                            <h1 class="text-2xl font-bold text-black dark:text-white md:text-3xl">
                                                {{ currentSection.name }}
                                            </h1>
                                            <p class="mt-2 font-semibold text-black dark:text-gray-300">
                                                {{ currentSection.term || 'Current Term' }} • {{ currentSection.studentCount || 0 }} Students
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Class Average Info -->
                                <!-- <div class="flex items-center gap-4">
                                    <div class="flex items-center gap-2 rounded-lg bg-transparent px-4 py-2 backdrop-blur-sm">
                                        <span class="text-sm font-medium text-black dark:text-gray-300">Class Average:</span>
                                        <span class="text-xl font-bold text-black dark:text-white">{{ classAverage }}%</span>
                                        <span :class="`ml-1 rounded-full px-2 py-0.5 text-xs font-medium ${getGradeClass(classAverage)}`">
                                            {{
                                                classAverage >= 90
                                                    ? 'A'
                                                    : classAverage >= 80
                                                      ? 'B'
                                                      : classAverage >= 70
                                                        ? 'C'
                                                        : classAverage >= 60
                                                          ? 'D'
                                                          : 'F'
                                            }}
                                        </span>
                                    </div>
                                </div> -->
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Term Selection -->
                <div class="w-full overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-md dark:border-gray-700 dark:bg-gray-800">
                    <div class="border-b border-gray-200 p-6 dark:border-gray-700">
                        <div class="flex w-full items-center justify-between">
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Term Selection</h2>
                        </div>
                    </div>
                    <div class="p-6">
                        <div class="flex w-full flex-col gap-4 sm:flex-row">
                            <div class="w-full">
                                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Select Term</label>
                                <div class="grid w-full grid-cols-4 gap-4">
                                    <button
                                        v-for="term in terms"
                                        :key="term.id"
                                        @click="selectedTerm = term.id"
                                        :class="[
                                            'w-full rounded-lg px-4 py-3 text-center font-medium transition-colors duration-200',
                                            selectedTerm === term.id
                                                ? 'bg-indigo-600 text-white shadow-md'
                                                : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600',
                                        ]"
                                    >
                                        {{ term.name }}
                                    </button>
                                    <button
                                        @click="selectedTerm = 'Final'"
                                        :class="[
                                            'w-full rounded-lg px-4 py-3 text-center font-medium transition-colors duration-200',
                                            selectedTerm === 'Final'
                                                ? 'bg-indigo-600 text-white shadow-md'
                                                : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600',
                                        ]"
                                    >
                                        Final Grading
                                    </button>
                                </div>
                                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                    {{ selectedTerm === 'Final' ? 'Viewing final grades for all terms' : `Viewing grades for ${selectedTerm}` }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Grades Content -->
                <div class="w-full overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-md dark:border-gray-700 dark:bg-gray-800">
                    <!-- Search and Filter -->
                    <div class="w-full border-b border-gray-200 bg-gray-50 p-6 dark:border-gray-700 dark:bg-gray-800/50">
                        <div class="flex w-full flex-col items-start justify-between gap-4 sm:flex-row sm:items-center">
                            <div class="relative flex w-full items-center gap-4">
                                <div class="relative flex-1">
                                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            class="h-5 w-5 text-gray-400"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                                            />
                                        </svg>
                                    </div>
                                    <input
                                        type="text"
                                        v-model="searchQuery"
                                        placeholder="Search students..."
                                        class="w-full rounded-lg border-gray-300 py-2.5 pl-10 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700"
                                    />
                                </div>

                                <!-- Action Buttons -->
                                <div class="flex space-x-2">
                                    <!-- Save Grades Button -->
                                    <button
                                        v-if="selectedTerm === 'Final'"
                                        @click="saveGrades"
                                        :disabled="isSaving"
                                        class="flex items-center rounded-lg bg-indigo-600 px-6 py-2.5 text-sm font-medium text-white transition-colors duration-200 hover:bg-indigo-700 focus:ring-4 focus:ring-indigo-300 disabled:cursor-not-allowed disabled:opacity-70"
                                    >
                                        <svg
                                            v-if="isSaving"
                                            class="-ml-1 mr-2 h-4 w-4 animate-spin text-white"
                                            xmlns="http://www.w3.org/2000/svg"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                        >
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path
                                                class="opacity-75"
                                                fill="currentColor"
                                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                            ></path>
                                        </svg>
                                        <svg
                                            v-else
                                            xmlns="http://www.w3.org/2000/svg"
                                            class="mr-2 h-5 w-5"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"
                                            />
                                        </svg>
                                        {{ isSaving ? 'Saving...' : 'Save Grades' }}
                                    </button>

                                    <!-- Generate PDF Report Button -->
                                    <button
                                        v-if="selectedTerm === 'Final'"
                                        @click="generatePdfReport"
                                        class="flex items-center rounded-lg bg-emerald-600 px-6 py-2.5 text-sm font-medium text-white transition-colors duration-200 hover:bg-emerald-700 focus:ring-4 focus:ring-emerald-300 disabled:cursor-not-allowed disabled:opacity-70"
                                    >
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            class="mr-2 h-5 w-5"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"
                                            />
                                        </svg>
                                        Generate Grades PDF
                                    </button>
                                </div>

                                <!-- Sort Dropdown -->
                                <div class="relative">
                                    <button
                                        class="flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600"
                                        @click="sortDirection = sortDirection === 'asc' ? 'desc' : 'asc'"
                                    >
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            class="mr-2 h-5 w-5"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                :d="
                                                    sortDirection === 'asc'
                                                        ? 'M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12'
                                                        : 'M3 4h13M3 8h9m-9 4h9m5-4v12m0 0l-4-4m4 4l4-4'
                                                "
                                            />
                                        </svg>
                                        Sort {{ sortDirection === 'asc' ? 'A-Z' : 'Z-A' }}
                                    </button>
                                </div>
                            </div>

                            <!-- Status messages -->
                            <div class="flex items-center">
                                <div v-if="saveSuccess" class="flex items-center text-emerald-600 dark:text-emerald-400">
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="mr-2 h-5 w-5"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                    >
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    Grades saved successfully!
                                </div>
                                <div v-if="saveError" class="flex items-center text-red-600 dark:text-red-400">
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="mr-2 h-5 w-5"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                        />
                                    </svg>
                                    Error saving grades. Please try again.
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Student Grades Table -->
                    <div class="w-full overflow-x-auto">
                        <table class="w-full min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-indigo-50 dark:bg-indigo-900/30">
                                <tr>
                                    <th
                                        scope="col"
                                        class="cursor-pointer px-6 py-4 text-left text-sm font-semibold uppercase tracking-wide text-indigo-700 dark:text-indigo-300"
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
                                    <!-- <th
                                        scope="col"
                                        class="cursor-pointer px-6 py-4 text-left text-sm font-semibold uppercase tracking-wide text-indigo-700 dark:text-indigo-300"
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
                                    </th> -->
                                    <!-- Show assessment columns only when not in Final Grading mode -->
                                    <template v-if="selectedTerm !== 'Final'">
                                        <th
                                            v-for="assessment in filteredAssessments"
                                            :key="assessment.id"
                                            scope="col"
                                            class="px-6 py-4 text-left text-sm font-semibold uppercase tracking-wide text-indigo-700 dark:text-indigo-300"
                                        >
                                            {{ assessment.assessment_name }}
                                            <div class="text-xs font-normal normal-case text-indigo-500 dark:text-indigo-400">
                                                Percentage: {{ assessment.weight }}%
                                            </div>
                                        </th>
                                    </template>
                                    <!-- Show term grade columns when in Final Grading mode -->
                                    <template v-if="selectedTerm === 'Final'">
                                        <th
                                            scope="col"
                                            class="px-6 py-4 text-left text-sm font-semibold uppercase tracking-wide text-indigo-700 dark:text-indigo-300"
                                        >
                                            Prelim Grade
                                            <div class="mt-1 flex items-center text-xs font-normal normal-case text-indigo-500 dark:text-indigo-400">
                                                Percentage:
                                                <input
                                                    type="number"
                                                    v-model="prelimWeight"
                                                    @input="adjustWeights('prelim', $event.target.value)"
                                                    min="0"
                                                    max="100"
                                                    class="ml-1 w-12 rounded border border-indigo-300 bg-white px-1 py-0.5 text-center dark:border-indigo-700 dark:bg-gray-800"
                                                />%
                                            </div>
                                        </th>
                                        <th
                                            scope="col"
                                            class="px-6 py-4 text-left text-sm font-semibold uppercase tracking-wide text-indigo-700 dark:text-indigo-300"
                                        >
                                            Midterm Grade
                                            <div class="mt-1 flex items-center text-xs font-normal normal-case text-indigo-500 dark:text-indigo-400">
                                                Percentage:
                                                <input
                                                    type="number"
                                                    v-model="midtermWeight"
                                                    @input="adjustWeights('midterm', $event.target.value)"
                                                    min="0"
                                                    max="100"
                                                    class="ml-1 w-12 rounded border border-indigo-300 bg-white px-1 py-0.5 text-center dark:border-indigo-700 dark:bg-gray-800"
                                                />%
                                            </div>
                                        </th>
                                        <th
                                            scope="col"
                                            class="px-6 py-4 text-left text-sm font-semibold uppercase tracking-wide text-indigo-700 dark:text-indigo-300"
                                        >
                                            Final Term Grade
                                            <div class="mt-1 flex items-center text-xs font-normal normal-case text-indigo-500 dark:text-indigo-400">
                                                Percentage:
                                                <input
                                                    type="number"
                                                    v-model="finalTermWeight"
                                                    @input="adjustWeights('final', $event.target.value)"
                                                    min="0"
                                                    max="100"
                                                    class="ml-1 w-12 rounded border border-indigo-300 bg-white px-1 py-0.5 text-center dark:border-indigo-700 dark:bg-gray-800"
                                                />%
                                            </div>
                                        </th>
                                    </template>
                                    <th
                                        scope="col"
                                        class="cursor-pointer px-6 py-4 text-left text-sm font-semibold uppercase tracking-wide text-indigo-700 dark:text-indigo-300"
                                        @click="toggleSort('finalGrade')"
                                    >
                                        <div class="flex items-center">
                                            {{ selectedTerm === 'Final' ? 'Final Grade' : `${selectedTerm} Grade` }}
                                            <svg
                                                v-if="sortBy === 'finalGrade'"
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
                                    <!-- Remarks Header (visible only if selectedTerm is 'Final') -->
                                    <th
                                        v-if="selectedTerm === 'Final'"
                                        class="whitespace-nowrap px-6 py-4 text-left text-sm font-semibold uppercase text-indigo-700 dark:text-indigo-300"
                                    >
                                        Remarks
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                                <tr
                                    v-for="(student, index) in paginatedStudents"
                                    :key="index"
                                    class="transition hover:bg-gray-100 dark:hover:bg-gray-700"
                                >
                                    <td class="flex items-center space-x-3 px-6 py-4">
                                        <img v-if="student.avatar" :src="`/${student.avatar}`" alt="User Avatar" class="h-10 w-10 rounded-full" />
                                        <div
                                            v-else
                                            class="flex h-10 w-10 items-center justify-center rounded-full bg-gray-400 text-sm font-bold text-white"
                                        >
                                            {{ getInitial(student.name) }}
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-gray-900 dark:text-white">{{ student.name || 'Unnamed Student' }}</p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ student.email || 'No email' }}</p>
                                        </div>
                                    </td>
                                    <!-- <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">{{ student.school_id || 'No ID' }}</td> -->

                                    <!-- Show assessment columns only when not in Final Grading mode -->
                                    <template v-if="selectedTerm !== 'Final'">
                                        <td v-for="assessment in filteredAssessments" :key="assessment.id" class="px-6 py-4">
                                            <div class="space-y-2">
                                                <!-- Find the matching assessment grade for this student and assessment -->
                                                <div
                                                    v-if="
                                                        student.assessment_grades &&
                                                        Object.values(student.assessment_grades).find((g) => g.assessment_id === assessment.id)
                                                    "
                                                    class="text-sm font-medium"
                                                >
                                                    {{
                                                        Object.values(student.assessment_grades).find((g) => g.assessment_id === assessment.id)
                                                            .total_score || 0
                                                    }}
                                                    / {{ calculateAssessmentTotalPoints(assessment) }}
                                                    <span class="ml-1 text-xs text-gray-500 dark:text-gray-400">
                                                        ({{
                                                            calculateAssessmentTotalPoints(assessment) > 0
                                                                ? Math.round(
                                                                      ((Object.values(student.assessment_grades).find(
                                                                          (g) => g.assessment_id === assessment.id,
                                                                      ).total_score || 0) /
                                                                          calculateAssessmentTotalPoints(assessment)) *
                                                                          100,
                                                                  )
                                                                : 0
                                                        }}%)
                                                    </span>
                                                </div>
                                                <!-- If no grade exists for this assessment -->
                                                <div v-else class="text-sm font-medium text-gray-400 dark:text-gray-500">
                                                    0 / {{ calculateAssessmentTotalPoints(assessment) }}
                                                    <span class="ml-1 text-xs text-gray-400 dark:text-gray-500">(0%)</span>
                                                </div>
                                            </div>
                                        </td>
                                    </template>

                                    <!-- Show term grade columns when in Final Grading mode -->
                                    <template v-if="selectedTerm === 'Final'">
                                        <td class="whitespace-nowrap px-6 py-4">
                                            <span class="text-sm font-medium text-gray-900 dark:text-white">
                                                {{ isFinite(student.prelimGrade) ? student.prelimGrade.toFixed(2) : '0.00' }}%
                                            </span>
                                            <span
                                                :class="`ml-2 inline-flex items-center rounded-lg px-2 py-0.5 text-xs font-medium ${getGradeClass(student.prelimLetterGrade)}`"
                                            >
                                                {{ student.prelimLetterGrade }}
                                            </span>
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4">
                                            <span class="text-sm font-medium text-gray-900 dark:text-white">
                                                {{ isFinite(student.midtermGrade) ? student.midtermGrade.toFixed(2) : '0.00' }}%
                                            </span>
                                            <span
                                                :class="`ml-2 inline-flex items-center rounded-lg px-2 py-0.5 text-xs font-medium ${getGradeClass(student.midtermLetterGrade)}`"
                                            >
                                                {{ student.midtermLetterGrade }}
                                            </span>
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4">
                                            <span class="text-sm font-medium text-gray-900 dark:text-white">
                                                {{ isFinite(student.finalGrade) ? student.finalGrade.toFixed(2) : '0.00' }}%
                                            </span>
                                            <span
                                                :class="`ml-2 inline-flex items-center rounded-lg px-2 py-0.5 text-xs font-medium ${getGradeClass(student.finalLetterGrade)}`"
                                            >
                                                {{ student.finalLetterGrade }}
                                            </span>
                                        </td>
                                    </template>

                                    <!-- Grade and Letter Grade -->
                                    <td class="whitespace-nowrap px-6 py-4">
                                        <span class="text-sm font-medium text-gray-900 dark:text-white">
                                            {{
                                                isFinite(selectedTerm === 'Final' ? calculateFinalGrade(student) : getCurrentTermGrade(student))
                                                    ? (selectedTerm === 'Final'
                                                          ? calculateFinalGrade(student)
                                                          : getCurrentTermGrade(student)
                                                      ).toFixed(2)
                                                    : '0.00'
                                            }}%
                                        </span>
                                        <span
                                            :class="`ml-2 inline-flex items-center rounded-lg px-2 py-0.5 text-xs font-medium ${
                                                selectedTerm === 'Final'
                                                    ? getGradeClass(calculateLetterGrade(calculateFinalGrade(student)))
                                                    : getGradeClass(getCurrentTermLetterGrade(student))
                                            }`"
                                        >
                                            {{
                                                selectedTerm === 'Final'
                                                    ? calculateLetterGrade(calculateFinalGrade(student))
                                                    : getCurrentTermLetterGrade(student)
                                            }}
                                        </span>
                                    </td>

                                    <!-- Remarks Input (visible only if selectedTerm is 'Final') -->
                                    <td class="whitespace-nowrap px-6 py-4" v-if="selectedTerm === 'Final'">
                                        <input
                                            type="text"
                                            class="w-full rounded-lg border px-3 py-1 text-sm"
                                            v-model="student.remarks"
                                            placeholder="Enter remarks"
                                        />
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Empty State -->
                    <div v-if="filteredStudents.length === 0" class="w-full p-12 text-center">
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
                                    d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                />
                            </svg>
                        </div>
                        <h3 class="mb-1 text-lg font-medium text-gray-900 dark:text-white">No students found</h3>
                        <p class="text-gray-500 dark:text-gray-400">Try adjusting your search or filter criteria</p>
                    </div>

                    <!-- Pagination -->
                    <div
                        class="flex items-center justify-between border-t border-gray-200 bg-white px-4 py-3 dark:border-gray-700 dark:bg-gray-800 sm:px-6"
                    >
                        <div class="flex flex-1 justify-between sm:hidden">
                            <button
                                @click="previousPage"
                                :disabled="currentPage === 1"
                                class="relative inline-flex items-center rounded-[10%] border border-blue-300 bg-white px-4 py-2 text-sm font-medium text-blue-700 hover:bg-blue-50 disabled:cursor-not-allowed disabled:opacity-50 dark:border-blue-600 dark:bg-blue-800 dark:text-blue-200 dark:hover:bg-blue-700"
                            >
                                Previous
                            </button>
                            <button
                                @click="nextPage"
                                :disabled="currentPage === totalPages"
                                class="relative ml-3 inline-flex items-center rounded-[10%] border border-blue-300 bg-white px-4 py-2 text-sm font-medium text-blue-700 hover:bg-blue-50 disabled:cursor-not-allowed disabled:opacity-50 dark:border-blue-600 dark:bg-blue-800 dark:text-blue-200 dark:hover:bg-blue-700"
                            >
                                Next
                            </button>
                        </div>
                        <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
                            <div>
                                <p class="text-sm text-gray-700 dark:text-gray-300">
                                    Showing <span class="font-medium">{{ (currentPage - 1) * itemsPerPage + 1 }}</span> to
                                    <span class="font-medium">{{ Math.min(currentPage * itemsPerPage, filteredStudents.length) }}</span> of
                                    <span class="font-medium">{{ filteredStudents.length }}</span> results
                                </p>
                            </div>
                            <div>
                                <nav class="isolate flex inline-flex gap-2 shadow-sm" aria-label="Pagination">
                                    <button
                                        @click="previousPage"
                                        :disabled="currentPage === 1"
                                        class="relative inline-flex items-center rounded-[10%] border border-blue-300 bg-white px-2 py-2 text-sm font-medium text-blue-500 hover:bg-blue-50 focus:z-20 disabled:cursor-not-allowed disabled:opacity-50 dark:border-blue-600 dark:bg-blue-800 dark:text-blue-300 dark:hover:bg-blue-700"
                                    >
                                        <span class="sr-only">Previous</span>
                                        <svg
                                            class="h-5 w-5"
                                            xmlns="http://www.w3.org/2000/svg"
                                            fill="currentColor"
                                            viewBox="0 0 20 20"
                                            aria-hidden="true"
                                        >
                                            <path
                                                fill-rule="evenodd"
                                                d="M12.79 5.23a.75.75 0 01-.02 1.06L8.832 10l3.938 3.71a.75.75 0 11-1.04 1.08l-4.5-4.25a.75.75 0 010-1.08l4.5-4.25a.75.75 0 011.06.02z"
                                                clip-rule="evenodd"
                                            />
                                        </svg>
                                    </button>

                                    <!-- Page numbers -->
                                    <template v-for="(page, index) in pageNumbers" :key="index">
                                        <button
                                            v-if="page !== '...'"
                                            @click="goToPage(page)"
                                            :class="[
                                                'relative inline-flex items-center border px-4 py-2 text-sm font-medium',
                                                'rounded-[10%]',
                                                currentPage === page
                                                    ? 'z-10 border-blue-500 bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300'
                                                    : 'border-blue-300 bg-white text-blue-500 hover:bg-blue-50 dark:border-blue-600 dark:bg-blue-800 dark:text-blue-300 dark:hover:bg-blue-700',
                                            ]"
                                        >
                                            {{ page }}
                                        </button>
                                        <span
                                            v-else
                                            class="relative inline-flex items-center rounded-[10%] border border-blue-300 bg-white px-4 py-2 text-sm font-medium text-blue-700 dark:border-blue-600 dark:bg-blue-800 dark:text-blue-300"
                                        >
                                            ...
                                        </span>
                                    </template>

                                    <button
                                        @click="nextPage"
                                        :disabled="currentPage === totalPages"
                                        class="relative inline-flex items-center rounded-[10%] border border-blue-300 bg-white px-2 py-2 text-sm font-medium text-blue-500 hover:bg-blue-50 focus:z-20 disabled:cursor-not-allowed disabled:opacity-50 dark:border-blue-600 dark:bg-blue-800 dark:text-blue-300 dark:hover:bg-blue-700"
                                    >
                                        <span class="sr-only">Next</span>
                                        <svg
                                            class="h-5 w-5"
                                            xmlns="http://www.w3.org/2000/svg"
                                            fill="currentColor"
                                            viewBox="0 0 20 20"
                                            aria-hidden="true"
                                        >
                                            <path
                                                fill-rule="evenodd"
                                                d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z"
                                                clip-rule="evenodd"
                                            />
                                        </svg>
                                    </button>
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- PDF Error Modal -->
        <div v-if="showPdfErrorModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
            <div class="w-full max-w-md rounded-lg bg-white p-6 shadow-lg dark:bg-gray-800">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">Unable to Generate PDF</h3>
                    <button @click="showPdfErrorModal = false" class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="mb-6">
                    <p class="text-gray-700 dark:text-gray-300">Please save the grades first before generating a PDF report.</p>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">No grades were found for this class. Click the "Save Grades" button to save your current grades.</p>
                </div>
                <div class="flex justify-end space-x-3">
                    <button
                        @click="showPdfErrorModal = false"
                        class="rounded-lg bg-gray-200 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600"
                    >
                        Cancel
                    </button>
                    <button
                        @click="saveGrades(); showPdfErrorModal = false;"
                        class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 focus:ring-4 focus:ring-indigo-300"
                    >
                        Save Grades Now
                    </button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
