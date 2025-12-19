<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router, usePage } from '@inertiajs/vue3';
import { computed, inject, onMounted, onUnmounted, ref, watch } from 'vue';

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
        title: 'Assessment Records',
        href: '/assess_records',
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
const selectedAssessmentItem = ref<string | null>(null);

const searchQuery = ref('');
const sortBy = ref('name');
const sortDirection = ref('asc');
const showFilters = ref(false);
const statusFilter = ref('all');
const selectedTerm = ref('Prelim'); // Default to midterm
const isSaving = ref(false); // Add a flag to track saving state

// Available terms
const terms = [
{ id: 'Prelim', name: 'Prelim' },
    { id: 'Midterm', name: 'Midterm' },
    { id: 'Final Term', name: 'Final Term' },
];

// Get sections from page props
const sections = usePage().props.sections || [];

// Debug the sections data structure
console.log('Sections data:', sections);

// Updated classSections computed property to match the controller data structure
const classSections = computed(() => {
    if (!sections || !Array.isArray(sections) || sections.length === 0) {
        return [];
    }

    return sections.map((section) => {
        // Process students to ensure they have the correct structure
        const students =
            section.students && Array.isArray(section.students)
                ? section.students.map((student) => {
                      // Create a grades object for each student based on assessments and their items
                      const grades = {};
                      const studentClassId = student.student_class_id || null;

                      // Initialize grades from assessmentScores if available
                      if (student.assessmentScores && Array.isArray(student.assessmentScores)) {
                          student.assessmentScores.forEach((assessmentScore) => {
                              if (assessmentScore.items && Array.isArray(assessmentScore.items)) {
                                  assessmentScore.items.forEach((item) => {
                                      if (item.item_id && item.item_score !== null) {
                                          grades[item.item_id] = parseFloat(item.item_score);
                                      } else {
                                          // Initialize to 0 if no score exists
                                          grades[item.item_id] = 0;
                                      }
                                  });
                              }
                          });
                      }

                      // Ensure all assessment items have a grade entry
                      if (section.assessments && Array.isArray(section.assessments)) {
                          section.assessments.forEach((assessment) => {
                              if (assessment.items && Array.isArray(assessment.items)) {
                                  assessment.items.forEach((item) => {
                                      // Only initialize to 0 if no grade exists
                                      if (grades[item.id] === undefined) {
                                          grades[item.id] = 0;
                                      }
                                  });
                              }
                          });
                      }

                      return {
                          id: student.id,
                          name: student.name || 'Unknown',
                          email: student.email || 'No Email',
                          status: student.status || 'active',
                          score: student.score || 0,
                          grades: grades,
                          gradeComment: student.gradeComment || '',
                          student_class_id: studentClassId, // Store student_class_id for use in saveRecords
                          assessmentScores: student.assessmentScores || [],
                      };
                  })
                : [];

        return {
            id: section.id,
            name: section.name || 'Unnamed Section',
            icon: section.icon || '📚',
            term: section.term || 'Current Term',
            studentCount: section.studentCount || 0,
            averageScore: section.averageScore || 0,
            gradeDistribution: section.gradeDistribution || { a: 0, b: 0, c: 0, d: 0, f: 0 },
            performance: section.performance || {},
            assessments: section.assessments || [],
            students: students,
        };
    });
});

// Find section by ID
function findSectionById(id) {
    return classSections.value.find((section) => section.id === id) || null;
}

// Update the selectSection function
function selectSection(section) {
    // Prevent re-selecting the same section to avoid infinite loops
    if (currentSection.value && currentSection.value.id === section.id) {
        return;
    }

    currentSection.value = section;

    // Debug the selected section
    console.log('Selected section:', section);

    // Find first assessment item for the selected term
    if (section.assessments && section.assessments.length > 0) {
        let foundItem = false;

        for (const assessment of section.assessments) {
            // Check if the assessment matches the selected term
            if ((assessment.category || 'midterm').toLowerCase() === selectedTerm.value.toLowerCase()) {
                if (assessment.items && assessment.items.length > 0) {
                    selectedAssessmentItem.value = assessment.items[0].id;
                    foundItem = true;
                    console.log('Found assessment item for term:', selectedTerm.value, assessment.items[0]);
                    break;
                }
            }
        }

        // If no item found for the selected term, use the first available item
        if (!foundItem && section.assessments[0].items && section.assessments[0].items.length > 0) {
            selectedAssessmentItem.value = section.assessments[0].items[0].id;
            console.log('Using first available assessment item:', section.assessments[0].items[0]);
        }
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
                source: 'assessment_records',
            },
        }),
    );

    // Also dispatch the item-changed event for newer sidebar implementations
    window.dispatchEvent(
        new CustomEvent('item-changed', {
            detail: {
                itemId: section.id,
                itemType: 'section',
                source: 'assessment_records',
            },
        }),
    );
}

function getAssessmentItems(assessmentId) {
    if (!currentSection.value || !assessmentId) return [];

    const assessment = currentSection.value.assessments.find((a) => a.id === assessmentId);
    return assessment?.items || [];
}

// Updated filteredAssessmentItems to correctly access the data structure
const filteredAssessmentItems = computed(() => {
    if (!currentSection.value || !currentSection.value.assessments) {
        console.log('No current section or assessments');
        return [];
    }

    console.log('Current section assessments:', currentSection.value.assessments);
    console.log('Selected term:', selectedTerm.value);

    const items = [];

    currentSection.value.assessments.forEach((assessment) => {
        // Debug each assessment
        console.log('Processing assessment:', assessment);

        // Only include assessments that match the selected term
        // Check both category and term fields since the controller uses term
        const assessmentTerm = assessment.category || assessment.term || 'Midterm';
        console.log('Assessment term/category:', assessmentTerm);

        if (assessmentTerm.toLowerCase() === selectedTerm.value.toLowerCase()) {
            if (assessment.items && Array.isArray(assessment.items)) {
                assessment.items.forEach((item) => {
                    console.log('Adding item to filtered list:', item);
                    items.push({
                        id: item.id,
                        name: item.name,
                        assessmentId: assessment.id,
                        assessmentName: assessment.name, // Use name from the controller
                        category: assessmentTerm,
                        totalScore: item.total_score || 100,
                    });
                });
            } else {
                console.log('No items in assessment or items is not an array');
            }
        }
    });

    console.log('Filtered assessment items:', items);
    return items;
});

// Get letter grade based on numeric score
// Get CSS class for grade display
function getGradeClass(grade) {
    if (typeof grade === 'string') {
        switch (grade) {
            case 'A':
                return 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900 dark:text-emerald-300';
            case 'B':
                return 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300';
            case 'C':
                return 'bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-300';
            case 'D':
                return 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-300';
            case 'F':
                return 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300';
            default:
                return 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300';
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
        case 'quiz':
            return 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300';
        case 'exam':
            return 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300';
        case 'lab':
            return 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900 dark:text-emerald-300';
        case 'project':
            return 'bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-300';
        case 'homework':
            return 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-300';
        case 'midterm':
            return 'bg-violet-100 text-violet-800 dark:bg-violet-900 dark:text-violet-300';
        case 'final':
            return 'bg-rose-100 text-rose-800 dark:bg-rose-900 dark:text-rose-300';
        default:
            return 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300';
    }
}

// Update student grade
function updateStudentGrade(student, assessmentItemId, value) {
    if (!student || !currentSection.value) return;

    // Update the specific grade
    if (!student.grades) {
        student.grades = {};
    }

    // Parse the value as a float and store it
    const numericValue = parseFloat(value);
    student.grades[assessmentItemId] = isNaN(numericValue) ? 0 : numericValue;

    // Also update the overall score if this is the currently selected assessment item
    if (assessmentItemId === selectedAssessmentItem.value) {
        student.score = numericValue;
    }

    // Update the assessmentScores array to keep it in sync
    if (student.assessmentScores) {
        // Find the assessment that contains this item
        let foundAndUpdated = false;

        for (const assessmentScore of student.assessmentScores) {
            if (assessmentScore.items && Array.isArray(assessmentScore.items)) {
                const itemIndex = assessmentScore.items.findIndex((item) => item.item_id === assessmentItemId);

                if (itemIndex >= 0) {
                    // Update the score in the assessmentScores structure
                    assessmentScore.items[itemIndex].item_score = numericValue;
                    foundAndUpdated = true;
                    break;
                }
            }
        }

        // If we didn't find the item in assessmentScores, we might need to add it
        if (!foundAndUpdated) {
            // Find which assessment this item belongs to
            for (const assessment of currentSection.value.assessments) {
                if (assessment.items) {
                    const item = assessment.items.find((item) => item.id === assessmentItemId);
                    if (item) {
                        // Check if this assessment exists in the student's assessmentScores
                        let assessmentScoreIndex = student.assessmentScores.findIndex((as) => as.assessment_id === assessment.id);

                        if (assessmentScoreIndex >= 0) {
                            // Add the item to the existing assessment
                            if (!student.assessmentScores[assessmentScoreIndex].items) {
                                student.assessmentScores[assessmentScoreIndex].items = [];
                            }

                            student.assessmentScores[assessmentScoreIndex].items.push({
                                item_id: assessmentItemId,
                                item_name: item.name,
                                item_score: numericValue,
                                total_score: item.total_score || 100,
                            });
                        } else {
                            // Create a new assessment score entry
                            student.assessmentScores.push({
                                assessment_id: assessment.id,
                                assessment_name: assessment.name,
                                items: [
                                    {
                                        item_id: assessmentItemId,
                                        item_name: item.name,
                                        item_score: numericValue,
                                        total_score: item.total_score || 100,
                                    },
                                ],
                            });
                        }
                        break;
                    }
                }
            }
        }
    }
}

// Create a deep copy of the current grades to preserve them
function createGradeSnapshot() {
    if (!currentSection.value || !currentSection.value.students) return {};

    const snapshot = {};

    currentSection.value.students.forEach((student) => {
        snapshot[student.id] = { ...student.grades };
    });

    return snapshot;
}


function saveRecords() {
    if (!currentSection.value || !currentSection.value.students || !selectedAssessmentItem.value) {
        console.warn('Missing required data. Make sure you have a section, students, and selected assessment item.');
        return;
    }

    isSaving.value = true;

    const gradeSnapshot = createGradeSnapshot();

    try {
        let selectedAssessment = null;
        let selectedItem = null;

        for (const assessment of currentSection.value.assessments || []) {
            if (assessment.items) {
                const item = assessment.items.find((item) => item.id === selectedAssessmentItem.value);
                if (item) {
                    selectedAssessment = assessment;
                    selectedItem = item;
                    break;
                }
            }
        }

        if (!selectedAssessment || !selectedItem) {
            console.warn('Selected assessment or assessment item not found.');
            isSaving.value = false;
            return;
        }

        console.log('Found assessment item details:', selectedItem, 'in assessment:', selectedAssessment);

        const totalScore = parseFloat(selectedItem.total_score || 0);
        const records = [];

        for (const student of currentSection.value.students) {
            if (!student.student_class_id) {
                console.warn(`Student ${student.id} is missing student_class_id. Skipping this student.`);
                continue;
            }

            const rawScore = student.grades?.[selectedAssessmentItem.value];
            const score = rawScore !== null && rawScore !== undefined ? parseFloat(rawScore) : 0;

            if (score > totalScore) {
                showErrorToast(`Score for student ${student.id} (${score}) exceeds the total score of ${totalScore}.`);
                isSaving.value = false;
                return;
            }

            const record = {
                assessment_id: selectedAssessment.id,
                assessment_item_id: selectedItem.id,
                student_id: student.id,
                student_class_id: student.student_class_id,
                score,
                remarks: student.gradeComment || '',
            };

            console.log('Mapped record:', record);
            records.push(record);
        }

        if (records.length === 0) {
            console.error('No valid records to save.');
            isSaving.value = false;
            return;
        }

        console.log('Sending records:', records);

        router.post(
            route('storeScores'),
            { records },
            {
                preserveState: true,
                preserveScroll: true,
                onSuccess: () => {
                    showSuccessToast('Assessment records saved successfully!');
                    isSaving.value = false;
                },
                onError: (errors) => {
                    console.error('Error saving assessment records:', errors);
                    restoreGradesFromSnapshot(gradeSnapshot);
                    showErrorToast('Error saving assessment records.');
                    isSaving.value = false;
                },
            }
        );
    } catch (error) {
        console.error('Unexpected error during save:', error);
        restoreGradesFromSnapshot(gradeSnapshot);
        showErrorToast('Unexpected error occurred.');
        isSaving.value = false;
    }
}

// ✅ Toast utilities
function showSuccessToast(message) {
    showToast(message, 'bg-emerald-500');
}

function showErrorToast(message) {
    showToast(message, 'bg-red-500');
}

function showToast(message, bgColorClass) {
    const notification = document.createElement('div');
    notification.className =
        `fixed bottom-4 right-4 ${bgColorClass} text-white px-6 py-3 rounded-lg shadow-lg transform transition-all duration-500 flex items-center z-50`;
    notification.innerHTML = `
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
        </svg>
        ${message}
    `;
    document.body.appendChild(notification);

    setTimeout(() => {
        notification.style.opacity = '0';
        setTimeout(() => {
            document.body.removeChild(notification);
        }, 500);
    }, 3000);
}

function restoreGradesFromSnapshot(snapshot) {
    if (!currentSection.value?.students) return;
    currentSection.value.students.forEach((student) => {
        if (snapshot[student.id]) {
            student.grades = { ...snapshot[student.id] };
        }
    });
}

// function saveRecords() {
//     if (!currentSection.value || !currentSection.value.students || !selectedAssessmentItem.value) {
//         console.warn('Missing required data. Make sure you have a section, students, and selected assessment item.');
//         return;
//     }

//     isSaving.value = true;

//     const gradeSnapshot = createGradeSnapshot();

//     try {
//         let selectedAssessment = null;
//         let selectedItem = null;

//         for (const assessment of currentSection.value.assessments || []) {
//             if (assessment.items) {
//                 const item = assessment.items.find((item) => item.id === selectedAssessmentItem.value);
//                 if (item) {
//                     selectedAssessment = assessment;
//                     selectedItem = item;
//                     break;
//                 }
//             }
//         }

//         if (!selectedAssessment || !selectedItem) {
//             console.warn('Selected assessment or assessment item not found.');
//             isSaving.value = false;
//             return;
//         }

//         console.log('Found assessment item details:', selectedItem, 'in assessment:', selectedAssessment);

//         const records = currentSection.value.students
//             .map((student) => {
//                 if (!student.student_class_id) {
//                     console.warn(`Student ${student.id} is missing student_class_id. Skipping this student.`);
//                     return null;
//                 }

//                 const score = student.grades?.[selectedAssessmentItem.value];
//                 const validScore = score !== null && score !== undefined ? score : 0;

//                 const record = {
//                     assessment_id: selectedAssessment.id,
//                     assessment_item_id: selectedItem.id,
//                     student_id: student.id,
//                     student_class_id: student.student_class_id,
//                     score: validScore,
//                     remarks: student.gradeComment || '',
//                 };

//                 console.log('Mapped record:', record);
//                 return record;
//             })
//             .filter((record) => record !== null);

//         console.log('Sending records:', records);

//         if (records.length === 0) {
//             console.error('No valid records to save.');
//             isSaving.value = false;
//             return;
//         }

//         router.post(
//             route('storeScores'),
//             { records },
//             {
//                 preserveState: true,
//                 preserveScroll: true,
//                 onSuccess: () => {
//                     const notification = document.createElement('div');
//                     notification.className =
//                         'fixed bottom-4 right-4 bg-emerald-500 text-white px-6 py-3 rounded-lg shadow-lg transform transition-all duration-500 flex items-center z-50';
//                     notification.innerHTML = `
//                         <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
//                             <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
//                         </svg>
//                         Assessment records saved successfully!
//                     `;
//                     document.body.appendChild(notification);

//                     setTimeout(() => {
//                         notification.style.opacity = '0';
//                         setTimeout(() => {
//                             document.body.removeChild(notification);
//                         }, 500);
//                     }, 3000);

//                     isSaving.value = false;
//                 },
//                 onError: (errors) => {
//                     console.error('Error saving assessment records:', errors);

//                     if (currentSection.value && currentSection.value.students) {
//                         currentSection.value.students.forEach((student) => {
//                             if (gradeSnapshot[student.id]) {
//                                 student.grades = { ...gradeSnapshot[student.id] };
//                             }
//                         });
//                     }

//                     const notification = document.createElement('div');
//                     notification.className =
//                         'fixed bottom-4 right-4 bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg transform transition-all duration-500 flex items-center z-50';
//                     notification.innerHTML = `
//                         <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
//                             <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
//                         </svg>
//                         Error saving assessment records: ${JSON.stringify(errors)}
//                     `;
//                     document.body.appendChild(notification);

//                     setTimeout(() => {
//                         notification.style.opacity = '0';
//                         setTimeout(() => {
//                             document.body.removeChild(notification);
//                         }, 500);
//                     }, 3000);

//                     isSaving.value = false;
//                 },
//             }
//         );
//     } catch (error) {
//         console.error('Unexpected error during save:', error);

//         if (currentSection.value && currentSection.value.students) {
//             currentSection.value.students.forEach((student) => {
//                 if (gradeSnapshot[student.id]) {
//                     student.grades = { ...gradeSnapshot[student.id] };
//                 }
//             });
//         }

//         isSaving.value = false;
//     }
// }

// Toggle sort direction
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
        const scoreA = a.grades?.[selectedAssessmentItem.value] || 0;
        const scoreB = b.grades?.[selectedAssessmentItem.value] || 0;
        return (scoreA - scoreB) * direction;
    } else if (sortBy.value === 'grade') {
        const scoreA = a.grades?.[selectedAssessmentItem.value] || 0;
        const scoreB = b.grades?.[selectedAssessmentItem.value] || 0;
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
        .filter((student) => {
            // Filter by search query
            if (searchQuery.value) {
                const query = searchQuery.value.toLowerCase();
                const matchesSearch =
                    (student.name || '').toLowerCase().includes(query) ||
                    (student.id || '').toString().toLowerCase().includes(query) ||
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

// Get selected assessment item details
const selectedAssessmentItemDetails = computed(() => {
    if (!currentSection.value || !selectedAssessmentItem.value) {
        console.log('No current section or selected assessment item');
        return null;
    }

    console.log('Looking for assessment item details for:', selectedAssessmentItem.value);

    // Find the assessment item
    for (const assessment of currentSection.value.assessments || []) {
        if (assessment.items) {
            const item = assessment.items.find((item) => item.id === selectedAssessmentItem.value);
            if (item) {
                console.log('Found assessment item details:', item, 'in assessment:', assessment);
                return {
                    id: item.id,
                    name: item.name,
                    category: assessment.term === 'Prelim'
                        ? 'Prelim'
                        : assessment.term === 'Midterm'
                        ? 'Midterm'
                        : assessment.term === 'Final Term'
                        ? 'Final Term'
                        : assessment.category || 'Midterm',
                    weight: assessment.weight,
                    totalPoints: item.total_score || 100,
                    assessmentName: assessment.name,
                };
            }
        }
    }

    console.log('Assessment item details not found');
    return null;
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
                selectedAssessmentItem.value =
                    section.assessments.find((a) => (a.category || a.term) === selectedTerm.value)?.items?.[0]?.id ||
                    section.assessments[0]?.items?.[0]?.id ||
                    null;
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
            selectedAssessmentItem.value =
                section.assessments.find((a) => (a.category || a.term) === selectedTerm.value)?.items?.[0]?.id ||
                section.assessments[0]?.items?.[0]?.id ||
                null;
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
                selectedAssessmentItem.value =
                    section.assessments.find((a) => (a.category || a.term) === selectedTerm.value)?.items?.[0]?.id ||
                    section.assessments[0]?.items?.[0]?.id ||
                    null;
            }
        }
    },
    { immediate: true },
);

// Watch for changes in the selected term
watch(selectedTerm, (newTerm) => {
    if (currentSection.value) {
        // Find first assessment item for the selected term
        let found = false;

        for (const assessment of currentSection.value.assessments || []) {
            const assessmentTerm = assessment.category || assessment.term || 'midterm';
            if (assessmentTerm.toLowerCase() === newTerm.toLowerCase() && assessment.items && assessment.items.length > 0) {
                selectedAssessmentItem.value = assessment.items[0].id;
                found = true;
                break;
            }
        }

        // If no item found for the selected term, clear the selection
        if (!found) {
            selectedAssessmentItem.value = null;
        }
    }
});

// Watch for changes in the URL query parameters
watch(
    () => window.location.search,
    (newSearch) => {
        const params = new URLSearchParams(newSearch);
        const sectionId = params.get('sectionId');
        const itemId = params.get('itemId');

        if (sectionId) {
            const section = findSectionById(sectionId);
            if (section && (!currentSection.value || currentSection.value.id !== section.id)) {
                currentSection.value = section;
                selectedAssessmentItem.value =
                    section.assessments.find((a) => (a.category || a.term) === selectedTerm.value)?.items?.[0]?.id ||
                    section.assessments[0]?.items?.[0]?.id ||
                    null;
            }
        } else if (itemId) {
            const section = findSectionById(itemId);
            if (section && (!currentSection.value || currentSection.value.id !== section.id)) {
                currentSection.value = section;
                selectedAssessmentItem.value =
                    section.assessments.find((a) => (a.category || a.term) === selectedTerm.value)?.items?.[0]?.id ||
                    section.assessments[0]?.items?.[0]?.id ||
                    null;
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
            selectedAssessmentItem.value =
                section.assessments.find((a) => (a.category || a.term) === selectedTerm.value)?.items?.[0]?.id ||
                section.assessments[0]?.items?.[0]?.id ||
                null;
            return;
        }
    }
    // Then check for itemId (which could be a section ID from sidebar)
    else if (itemIdFromUrl) {
        const section = findSectionById(itemIdFromUrl);
        if (section) {
            currentSection.value = section;
            selectedAssessmentItem.value =
                section.assessments.find((a) => (a.category || a.term) === selectedTerm.value)?.items?.[0]?.id ||
                section.assessments[0]?.items?.[0]?.id ||
                null;
            return;
        }
    }
    // Finally check for injected item ID
    else if (currentItemId.value) {
        const section = findSectionById(currentItemId.value);
        if (section) {
            currentSection.value = section;
            selectedAssessmentItem.value =
                section.assessments.find((a) => (a.category || a.term) === selectedTerm.value)?.items?.[0]?.id ||
                section.assessments[0]?.items?.[0]?.id ||
                null;
            return;
        }
    }

    // If no section is selected and we have sections, select the first one
    if (!currentSection.value && classSections.value.length > 0) {
        selectSection(classSections.value[0]);
    }
});

// Clean up event listeners when component is unmounted
onUnmounted(() => {
    window.removeEventListener('item-changed', handleItemChange);
    window.removeEventListener('section-changed', handleSectionChange);
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
                <div
                    class="overflow-hidden rounded-2xl bg-gradient-to-br from-indigo-600 to-violet-600 shadow-lg dark:from-indigo-800 dark:to-violet-800"
                >
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
                                            <p class="mt-1 text-indigo-100 dark:text-indigo-200">
                                                {{ currentSection.term }} • {{ currentSection.studentCount }} Students
                                            </p>
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
                                <div class="grid grid-cols-3 gap-4">
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
                                </div>
                                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                    Viewing assessments for {{selectedTerm === 'Prelim'? 'Prelim': selectedTerm === 'Midterm' ? 'Midterm' : 'Final Term' }}
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

                            <!-- Assessment dropdown -->
                            <div class="w-full sm:w-1/2">
                                <select
                                    v-model="selectedAssessmentItem"
                                    class="w-full appearance-none rounded-lg border-gray-300 bg-white py-2.5 pl-4 pr-10 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700"
                                    style="
                                        background-image: url('data:image/svg+xml;charset=utf-8,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 fill=%22none%22 viewBox=%220 0 20 20%22%3E%3Cpath stroke=%22%236B7280%22 stroke-linecap=%22round%22 stroke-linejoin=%22round%22 stroke-width=%221.5%22 d=%22M6 8l4 4 4-4%22/%3E%3C/svg%3E');
                                        background-position: right 0.75rem center;
                                        background-size: 1.5em 1.5em;
                                        background-repeat: no-repeat;
                                    "
                                >
                                    <option v-for="item in filteredAssessmentItems" :key="item.id" :value="item.id">
                                        {{ item.name }} ({{ item.assessmentName }})
                                    </option>
                                </select>
                            </div>
                        </div>

                        <div
                            v-if="selectedAssessmentItemDetails"
                            class="mt-4 grid grid-cols-1 gap-4 rounded-lg bg-white p-4 shadow-sm dark:bg-gray-700 sm:grid-cols-4"
                        >
                            <div class="flex flex-col">
                                <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Assessment</span>
                                <span class="mt-1 text-sm font-medium text-gray-900 dark:text-white">{{
                                    selectedAssessmentItemDetails.assessmentName
                                }}</span>
                            </div>
                            <div class="flex flex-col">
                                <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Item Name</span>
                                <span class="mt-1 text-sm font-medium text-gray-900 dark:text-white">{{ selectedAssessmentItemDetails.name }}</span>
                            </div>
                            <div class="flex flex-col">
                                <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Category</span>
                                <span
                                    :class="`mt-1 inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ${getCategoryColor(selectedAssessmentItemDetails.category)}`"
                                >
                                    {{
                                        selectedAssessmentItemDetails.category.charAt(0).toUpperCase() +
                                        selectedAssessmentItemDetails.category.slice(1)
                                    }}
                                </span>
                            </div>
                            <div class="flex flex-col">
                                <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Total Points</span>
                                <span class="mt-1 text-sm font-medium text-gray-900 dark:text-white"
                                    >{{ selectedAssessmentItemDetails.totalPoints }} pts</span
                                >
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
                                            v-model="student.grades[selectedAssessmentItem]"
                                            @input="updateStudentGrade(student, selectedAssessmentItem, student.grades[selectedAssessmentItem])"
                                            min="0"
                                            :max="selectedAssessmentItemDetails?.totalPoints || 100"
                                            class="w-24 rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700"
                                        />
                                    </td>
                                    <td class="px-6 py-4">
                                        <span
                                            :class="`inline-flex items-center rounded-lg px-3 py-1 text-xs font-medium ${getGradeClass(student.grades[selectedAssessmentItem] || 0)}`"
                                            >{{ getLetterGrade(student.grades[selectedAssessmentItem] || 0) }}</span
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
                            @click="saveRecords"
                            :disabled="isSaving"
                            class="flex items-center gap-2 rounded-lg bg-gradient-to-r from-indigo-600 to-violet-600 px-6 py-3 text-sm font-medium text-white shadow-md transition-colors duration-200 hover:from-indigo-700 hover:to-violet-700 disabled:opacity-70"
                        >
                            <svg v-if="isSaving" class="h-5 w-5 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path
                                    class="opacity-75"
                                    fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                ></path>
                            </svg>
                            <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            {{ isSaving ? 'Saving...' : 'Save Assessment Records' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
