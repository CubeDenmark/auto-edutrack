<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import { BarChart3, BookOpen, ChevronLeft, Eye, User } from 'lucide-vue-next';
import { computed, inject, onMounted, ref, watch } from 'vue';

// Breadcrumbs
const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Grades',
        href: '/parent/grades',
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
    return children.value.find((child) => child.id === id || child.id === Number(id)) || null;
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
            const childIndex = children.value.findIndex((c) => c.id === childId || c.id === Number(childId));
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
    window.dispatchEvent(
        new CustomEvent('child-changed', {
            detail: {
                childId: childId,
                source: 'grades',
            },
        }),
    );
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

// Calculate assessment averages for a given assessment type
const calculateAssessmentAverage = (assessments) => {
    if (!assessments || assessments.length === 0) return { average: 'N/A', percentage: 0 };

    let totalScore = 0;
    let totalPossible = 0;

    assessments.forEach((assessment) => {
        totalScore += parseFloat(assessment.score) || 0;
        totalPossible += parseFloat(assessment.total_score) || 0;
    });

    const average = totalPossible > 0 ? ((totalScore / totalPossible) * 100).toFixed(2) : 'N/A';
    const percentage = totalPossible > 0 ? (totalScore / totalPossible) * 100 : 0;

    return { average, percentage, totalScore, totalPossible };
};

// Calculate overall assessment average for all types in a term
const calculateOverallAverage = (assessmentsByType) => {
    if (!assessmentsByType || Object.keys(assessmentsByType).length === 0) {
        return { average: 'N/A', percentage: 0 };
    }

    let totalScore = 0;
    let totalPossible = 0;

    Object.values(assessmentsByType).forEach((assessments: any) => {
        assessments.forEach((assessment) => {
            totalScore += parseFloat(assessment.score) || 0;
            totalPossible += parseFloat(assessment.total_score) || 0;
        });
    });

    const average = totalPossible > 0 ? ((totalScore / totalPossible) * 100).toFixed(2) : 'N/A';
    const percentage = totalPossible > 0 ? (totalScore / totalPossible) * 100 : 0;

    return { average, percentage, totalScore, totalPossible };
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
                selectedSubject.value = currentChild.value.grades.find((grade) => grade.code === subject) || null;
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
});

// Watch for changes in the injected section ID
watch(injectedSectionId, async (newSectionId) => {
    if (newSectionId) {
        await updateChildData(newSectionId);
    }
});

// Watch for changes in the injected item ID from sidebar
watch(injectedItemId, async (newItemId) => {
    if (newItemId) {
        await updateChildData(newItemId);
    }
});

// Watch for changes in the URL query parameters
watch(
    () => window.location.search,
    async (newSearch) => {
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
                selectedSubject.value = currentChild.value.grades.find((grade) => grade.code === subject) || null;
            }
        }
    },
);

// Computed property for grade history data
const gradeHistory = computed(() => {
    if (!currentChild.value || !currentChild.value.grades) return [];

    // Create a simple grade history visualization
    return currentChild.value.grades.map((grade) => {
        // Calculate percentage from the Philippine grade (1.0-5.0)
        // 1.0 = 100%, 5.0 = 50%
        const gradeValue = parseFloat(grade.final_grade);
        const percentage = isNaN(gradeValue) ? 0 : Math.max(50, Math.min(100, 100 - ((gradeValue - 1.0) / 4.0) * 50));

        return {
            subject: grade.subject,
            percentage: Math.round(percentage),
            color: 'blue', // Use a single color for all subjects
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

    child.grades.forEach((grade) => {
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
        <div class="w-full p-4 pt-16 md:p-6 md:pt-20">
            <!-- Loading state -->
            <!-- Loading state -->
            <div v-if="loading" class="fixed inset-0 z-50 flex items-center justify-center bg-white">
                <div class="relative h-20 w-20">
                    <!-- Outer circle: Dark Blue -->
                    <div class="absolute inset-0 animate-spin rounded-full border-4 border-blue-900 border-t-transparent"></div>
                    <!-- Middle circle: Medium Blue -->
                    <div class="reverse-spin absolute inset-2 animate-spin rounded-full border-4 border-blue-600 border-t-transparent"></div>
                    <!-- Inner circle: Light Blue -->
                    <div class="absolute inset-4 animate-spin rounded-full border-4 border-blue-300 border-t-transparent"></div>
                </div>
            </div>

            <!-- Error message -->
            <div v-else-if="error" class="mb-6 w-full rounded-lg border border-red-200 bg-red-50 p-4 dark:border-red-800 dark:bg-red-900">
                <p class="text-red-800 dark:text-red-200">{{ error }}</p>
                <button @click="error = null" class="mt-2 rounded-md bg-red-100 px-4 py-2 text-sm text-red-800 dark:bg-red-800 dark:text-red-200">
                    Dismiss
                </button>
            </div>

            <div v-else class="w-full">
                <!-- Mobile Child Selector Toggle -->
                <div v-if="currentChild && isMobile" class="mb-4 w-full">
                    <button
                        @click="toggleMobileChildSelector"
                        class="flex w-full items-center justify-between rounded-lg bg-white p-3 shadow dark:bg-gray-800"
                    >
                        <div class="flex items-center">
                            <div
                                class="mr-2 flex h-8 w-8 items-center justify-center overflow-hidden rounded-full bg-gray-300 text-lg font-medium text-white"
                            >
                                <img
                                    v-if="currentChild.avatar"
                                    :src="`/${currentChild.avatar}`"
                                    :alt="`${currentChild.name} Avatar`"
                                    class="h-8 w-8 rounded-full object-cover"
                                />
                                <span v-else>
                                    {{ currentChild.name.charAt(0).toUpperCase() }}
                                </span>
                            </div>
                            <span class="font-medium">{{ currentChild.name }}</span>
                        </div>

                        <span class="dark:text-primary-400 text-primary-600">
                            <ChevronLeft v-if="showMobileChildSelector" class="rotate-90 transform" size="18" />
                            <ChevronLeft v-else class="-rotate-90 transform" size="18" />
                        </span>
                    </button>
                </div>

                <!-- Child selector (visible on mobile when toggled or when no child is selected) -->
                <div v-if="!currentChild || (isMobile && showMobileChildSelector)" class="mb-6 w-full">
                    <div class="w-full rounded-lg bg-white p-4 shadow dark:bg-gray-800 md:p-6">
                        <h3 class="mb-4 flex items-center font-medium">
                            <User size="18" class="mr-2" />
                            My Children
                        </h3>
                        <div class="grid w-full grid-cols-1 gap-3 md:grid-cols-2 md:gap-4 lg:grid-cols-3">
                            <div
                                v-for="(child, index) in children"
                                :key="index"
                                class="cursor-pointer rounded-lg border border-gray-200 p-3 transition-colors hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-700 md:p-4"
                                :class="{ 'dark:ring-primary-400 ring-2 ring-primary-500': currentChild && currentChild.id === child.id }"
                                @click="selectChild(child.id)"
                            >
                                <div class="flex items-center">
                                    <div
                                        class="mr-3 flex h-10 w-10 items-center justify-center rounded-full bg-gray-300 text-lg font-medium text-white md:h-12 md:w-12"
                                    >
                                        <img
                                            v-if="child.avatar"
                                            :src="`/${child.avatar}`"
                                            :alt="`${child.name} Avatar`"
                                            class="h-10 w-10 rounded-full object-cover md:h-12 md:w-12"
                                        />
                                        <span v-else>
                                            {{ child.name.charAt(0).toUpperCase() }}
                                        </span>
                                    </div>
                                    <div>
                                        <h4 class="font-medium">{{ child.name }}</h4>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 md:text-sm">{{ child.grade }}</p>
                                        <p class="mt-1 text-xs md:text-sm">
                                            GWA:
                                            <span :class="`rounded px-1.5 py-0.5 text-xs ${getGradeClass(calculateGPA(child))}`">{{
                                                calculateGPA(child)
                                            }}</span>
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
                    <div class="mb-6 flex w-full flex-col gap-4 rounded-lg bg-white p-4 shadow dark:bg-gray-800 md:flex-row md:items-center">
                        <div class="flex items-center">
                            <div
                                class="mr-4 flex h-12 w-12 items-center justify-center rounded-full bg-gray-300 text-lg font-medium text-white md:h-16 md:w-16"
                            >
                                <img
                                    v-if="currentChild.avatar"
                                    :src="`/${currentChild.avatar}`"
                                    :alt="`${currentChild.name} Avatar`"
                                    class="h-12 w-12 rounded-full object-cover md:h-16 md:w-16"
                                />
                                <span v-else>
                                    {{ currentChild.name.charAt(0).toUpperCase() }}
                                </span>
                            </div>

                            <div>
                                <h2 class="text-lg font-semibold md:text-xl">{{ currentChild.name }}</h2>
                                <div class="mt-1 flex items-center">
                                    <span class="mr-2 text-xs text-gray-500 dark:text-gray-400 md:text-sm">GWA:</span>
                                    <span
                                        :class="`inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium ${getGradeClass(calculateGPA(currentChild))}`"
                                    >
                                        {{ calculateGPA(currentChild) }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <button
                            @click="selectChild('all')"
                            class="hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300 flex items-center text-sm text-primary-600 md:ml-auto"
                        >
                            <ChevronLeft size="16" class="mr-1" />
                            Back to Overview
                        </button>
                    </div>

                    <!-- Subject-specific grade details -->
                    <div v-if="selectedSubject" class="mb-6 w-full overflow-hidden rounded-lg bg-white shadow dark:bg-gray-800">
                        <div class="flex flex-col gap-3 border-b p-4 dark:border-gray-700 md:flex-row md:items-center md:justify-between md:p-6">
                            <!-- <div>
                                <h3 class="flex items-center text-lg font-medium">
                                    <BookOpen size="18" class="mr-2" />
                                    {{ selectedSubject.subject }} Grades
                                </h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Teacher: {{ selectedSubject.teacher }}</p>
                            </div> -->
                            <div class="flex items-center space-x-3">
                                <!-- Avatar or Initial -->
                                <div v-if="selectedSubject.avatar" class="h-10 w-10 overflow-hidden rounded-full">
                                    <img :src="`/${selectedSubject.avatar}`" alt="Teacher Avatar" class="h-full w-full object-cover" />
                                </div>
                                <div v-else class="flex h-10 w-10 items-center justify-center rounded-full bg-gray-400 font-bold text-white">
                                    {{ selectedSubject.teacher.split(' ')[0].charAt(0).toUpperCase() }}
                                </div>

                                <!-- Subject and Teacher Info -->
                                <div>
                                    <h3 class="flex items-center text-lg font-medium">
                                        <span class="mr-2 text-xl">{{ selectedSubject.icon }}</span>
                                        {{ selectedSubject.subject }} Grades
                                    </h3>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Teacher: {{ selectedSubject.teacher }}</p>
                                </div>
                            </div>

                            <button
                                @click="selectedSubject = null"
                                class="hover:text-primary-800 dark:text-primary-400 dark:hover:text-primary-300 flex items-center text-sm text-primary-600"
                            >
                                <ChevronLeft size="16" class="mr-1" />
                                Back to All Subjects
                            </button>
                        </div>
                        <div class="p-4 md:p-6">
                            <div
                                class="mb-6 flex flex-col gap-4 rounded-lg bg-gray-50 p-4 dark:bg-gray-700 md:flex-row md:items-center md:justify-between"
                            >
                                <div>
                                    <h4 class="text-base font-medium md:text-lg">Current Grade</h4>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 md:text-sm">Last Updated: {{ selectedSubject.updated }}</p>
                                </div>
                                <div class="flex flex-col items-start md:items-end">
                                    <span :class="`rounded-full px-3 py-1 text-sm font-medium ${getGradeClass(selectedSubject.final_grade)}`">
                                        {{ selectedSubject.final_grade }}
                                    </span>
                                    <span class="mt-1 text-sm">{{ selectedSubject.remarks }}</span>
                                </div>
                            </div>

                            <!-- Term grades -->
                            <div class="mb-6 w-full">
                                <h4 class="mb-4 font-medium">Term Grades</h4>
                                <div class="grid w-full grid-cols-1 gap-3 md:grid-cols-3 md:gap-4">
                                    <div
                                        v-if="selectedSubject.terms && selectedSubject.terms.prelim"
                                        class="w-full cursor-pointer rounded-lg bg-gray-50 p-4 dark:bg-gray-700"
                                        :class="{ 'ring-2 ring-primary-500': selectedTerm === 'Prelim' }"
                                        @click="selectTerm('Prelim')"
                                    >
                                        <h5 class="mb-1 text-sm font-medium">Preliminary</h5>
                                        <p class="text-lg font-semibold">{{ selectedSubject.terms.prelim }}</p>
                                    </div>
                                    <div
                                        v-if="selectedSubject.terms && selectedSubject.terms.midterm"
                                        class="w-full cursor-pointer rounded-lg bg-gray-50 p-4 dark:bg-gray-700"
                                        :class="{ 'ring-2 ring-primary-500': selectedTerm === 'Midterm' }"
                                        @click="selectTerm('Midterm')"
                                    >
                                        <h5 class="mb-1 text-sm font-medium">Midterm</h5>
                                        <p class="text-lg font-semibold">{{ selectedSubject.terms.midterm }}</p>
                                    </div>
                                    <div
                                        v-if="selectedSubject.terms && selectedSubject.terms.final_term"
                                        class="w-full cursor-pointer rounded-lg bg-gray-50 p-4 dark:bg-gray-700"
                                        :class="{ 'ring-2 ring-primary-500': selectedTerm === 'Final Term' }"
                                        @click="selectTerm('Final Term')"
                                    >
                                        <h5 class="mb-1 text-sm font-medium">Final Term</h5>
                                        <p class="text-lg font-semibold">{{ selectedSubject.terms.final_term }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Dynamic term selection based on available assessment terms -->
                            <div v-if="availableTerms.length > 0" class="mb-6 w-full">
                                <h4 class="mb-4 font-medium">Assessment Terms</h4>
                                <div class="flex flex-wrap gap-2">
                                    <button
                                        v-for="term in availableTerms"
                                        :key="term"
                                        @click="selectTerm(term)"
                                        class="rounded-md bg-gray-100 px-3 py-1 text-sm transition-colors hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600"
                                        :class="{
                                            'bg-primary-100 dark:bg-primary-900 text-primary-800 dark:text-primary-300': selectedTerm === term,
                                        }"
                                    >
                                        {{ term }}
                                    </button>
                                </div>
                            </div>

                            <!-- Assessment scores for the selected term -->
                            <div v-if="selectedTerm && Object.keys(currentAssessments).length > 0" class="mb-6 w-full">
                                <h4 class="mb-4 font-medium">{{ selectedTerm }} Assessments</h4>

                                <div class="grid w-full grid-cols-1 gap-6">
                                    <div v-for="(assessments, type) in currentAssessments" :key="type" class="w-full">
                                        <div class="flex items-center justify-between rounded-t-lg bg-gray-50 p-3 dark:bg-gray-700">
                                            <h5 class="text-sm font-medium capitalize">{{ type }}</h5>

                                            <!-- Assessment type average -->
                                            <div class="flex items-center">
                                                <span class="mr-2 text-xs text-gray-500 dark:text-gray-400">Average:</span>
                                                <span
                                                    class="bg-primary-100 dark:bg-primary-900 text-primary-800 dark:text-primary-300 rounded-full px-2 py-0.5 text-xs font-medium"
                                                >
                                                    {{ calculateAssessmentAverage(assessments).average }}% ({{
                                                        calculateAssessmentAverage(assessments).totalScore
                                                    }}/{{ calculateAssessmentAverage(assessments).totalPossible }})
                                                </span>
                                            </div>
                                        </div>

                                        <div class="grid auto-rows-fr grid-cols-1 gap-0 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                                            <div
                                                v-for="(assessment, index) in assessments"
                                                :key="index"
                                                class="flex flex-col justify-between border border-gray-100 p-4 dark:border-gray-600"
                                                :class="{
                                                    'sm:col-span-2': index === 0 && assessments.length > 1 && assessments.length % 2 !== 0,
                                                    'lg:col-span-3': index === 0 && assessments.length > 2 && assessments.length % 3 === 1,
                                                    'xl:col-span-2': index === 0 && assessments.length > 3 && assessments.length % 4 === 1,
                                                    'sm:col-span-2 lg:col-span-2 xl:col-span-2':
                                                        index === 0 && assessments.length > 3 && assessments.length % 4 === 2,
                                                    'rounded-tl-lg': index === 0,
                                                    'rounded-tr-lg':
                                                        (index === 1 && assessments.length === 2) ||
                                                        (index === 2 && assessments.length === 3) ||
                                                        (index === 3 && assessments.length === 4) ||
                                                        (index === 0 && assessments.length === 1),
                                                    'rounded-bl-lg':
                                                        index === assessments.length - (assessments.length % 3) ||
                                                        (assessments.length <= 3 && index === 0),
                                                    'rounded-br-lg': index === assessments.length - 1,
                                                    'bg-white dark:bg-gray-700': true,
                                                }"
                                            >
                                                <div class="flex items-start justify-between">
                                                    <h6 class="text-sm font-medium">{{ assessment.title }}</h6>
                                                    <span class="rounded-full bg-gray-100 px-2.5 py-1 text-xs font-medium dark:bg-gray-600">
                                                        {{ assessment.score }}/{{ assessment.total_score }}
                                                    </span>
                                                </div>
                                                <div class="mt-2 text-xs text-gray-500 dark:text-gray-400">Assessment #{{ index + 1 }}</div>
                                                <!-- Added remarks display -->
                                                <div
                                                    v-if="assessment.remarks"
                                                    class="mt-2 border-t border-gray-100 pt-2 text-xs italic dark:border-gray-600"
                                                >
                                                    {{ assessment.remarks }}
                                                </div>
                                            </div>
                                        </div>

                                        <div
                                            v-if="assessments.length === 0"
                                            class="w-full rounded-b-lg border border-t-0 border-gray-100 bg-white py-4 text-center dark:border-gray-600 dark:bg-gray-700"
                                        >
                                            <p class="text-gray-500 dark:text-gray-400">No {{ type }} assessments found for this term.</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Overall assessment average for the term -->
                                <!-- <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg mt-4 mb-4">
                  <div class="flex justify-between items-center">
                    <div class="flex items-center">
                      <Calculator size="16" class="mr-2 text-primary-600 dark:text-primary-400" />
                      <h5 class="text-sm font-medium">Overall Assessment Average</h5>
                    </div>
                    <span class="text-sm font-medium px-2.5 py-1 rounded-full bg-primary-100 dark:bg-primary-900 text-primary-800 dark:text-primary-300">
                      {{ calculateOverallAverage(currentAssessments).average }}%
                      ({{ calculateOverallAverage(currentAssessments).totalScore }}/{{ calculateOverallAverage(currentAssessments).totalPossible }})
                    </span>
                  </div>
                </div> -->

                                <div
                                    v-if="Object.keys(currentAssessments).length === 0"
                                    class="w-full rounded-lg bg-gray-50 py-4 text-center dark:bg-gray-700"
                                >
                                    <p class="text-gray-500 dark:text-gray-400">No assessments found for this term.</p>
                                </div>
                            </div>

                            <!-- <h4 class="font-medium mb-4">Remarks</h4>
              <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg mb-6 w-full">
                <p class="text-sm">{{ selectedSubject.remarks || 'No remarks available.' }}</p>
              </div> -->
                        </div>
                    </div>

                    <!-- Subjects overview (when no specific subject is selected) - GRID VERSION -->
                    <div v-else class="mb-6 w-full rounded-lg bg-white shadow dark:bg-gray-800">
                        <div class="border-b p-4 dark:border-gray-700 md:p-6">
                            <h3 class="flex items-center font-medium">
                                <BookOpen size="18" class="mr-2" />
                                Subjects Overview
                            </h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Current Semester</p>
                        </div>

                        <div v-if="currentChild.grades && currentChild.grades.length > 0" class="p-4 md:p-6">
                            <!-- Grid layout for subjects -->
                            <div class="grid w-full grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                                <div
                                    v-for="(subject, index) in currentChild.grades"
                                    :key="index"
                                    class="rounded-lg border border-gray-100 bg-gray-50 p-4 dark:border-gray-600 dark:bg-gray-700"
                                >
                                    <div class="mb-3 flex items-center">
                                        <div
                                            class="mr-3 flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full"
                                            :class="subject.iconBg"
                                        >
                                            <span class="text-lg">{{ subject.icon }}</span>
                                        </div>
                                        <div>
                                            <h4 class="font-medium">{{ subject.subject }}</h4>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ subject.code }}</p>
                                        </div>
                                    </div>

                                    <div class="mb-3 flex flex-wrap gap-2">
                                        <div v-if="subject.terms && subject.terms.prelim" class="flex flex-col items-center">
                                            <span class="text-xs text-gray-500 dark:text-gray-400">Preliminary</span>
                                            <span class="font-medium">{{ subject.terms.prelim }}</span>
                                        </div>
                                        <div v-if="subject.terms && subject.terms.midterm" class="ml-4 flex flex-col items-center">
                                            <span class="text-xs text-gray-500 dark:text-gray-400">Midterm</span>
                                            <span class="font-medium">{{ subject.terms.midterm }}</span>
                                        </div>
                                        <div v-if="subject.terms && subject.terms.final_term" class="ml-4 flex flex-col items-center">
                                            <span class="text-xs text-gray-500 dark:text-gray-400">Final Term</span>
                                            <span class="font-medium">{{ subject.terms.final_term }}</span>
                                        </div>
                                    </div>

                                    <!-- Added remarks display in grid -->
                                    <div class="mb-3 border-t border-gray-200 pt-2 text-xs italic dark:border-gray-600">
                                        <span class="font-medium">Remarks:</span> {{ subject.remarks || 'No remarks available.' }}
                                    </div>

                                    <div class="flex items-center justify-between">
                                        <span
                                            :class="`inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ${getGradeClass(subject.final_grade)}`"
                                        >
                                            {{ subject.final_grade }}
                                        </span>
                                        <button
                                            @click="selectSubject(subject)"
                                            class="hover:text-primary-800 dark:text-primary-400 dark:hover:text-primary-300 flex items-center text-sm text-primary-600"
                                        >
                                            <Eye size="14" class="mr-1" />
                                            View Details
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div v-else class="w-full py-8 text-center text-gray-500 dark:text-gray-400">
                            No subjects or grades found for this student.
                        </div>
                    </div>

                    <!-- Grade History -->
                    <div v-if="gradeHistory.length > 0" class="w-full rounded-lg bg-white p-4 shadow dark:bg-gray-800 md:p-6">
                        <h3 class="mb-4 flex items-center font-medium">
                            <BarChart3 size="18" class="mr-2" />
                            Grade History
                        </h3>
                        <div class="h-64 w-full">
                            <!-- Chart visualization -->
                            <div class="flex h-full w-full items-center justify-center rounded-lg bg-gray-50 p-4 dark:bg-gray-700">
                                <div class="w-full space-y-4 px-4 md:px-8">
                                    <div v-for="(item, index) in gradeHistory" :key="index">
                                        <div class="mb-1 flex justify-between">
                                            <span class="max-w-[70%] truncate text-xs font-medium md:text-sm">{{ item.subject }}</span>
                                            <span class="text-xs font-medium md:text-sm">{{ item.percentage }}%</span>
                                        </div>
                                        <div class="h-2.5 w-full rounded-full bg-gray-200 dark:bg-gray-600">
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
                <div v-else class="w-full rounded-lg bg-white p-4 text-center shadow dark:bg-gray-800 md:p-6">
                    <h3 class="mb-4 text-xl font-medium">Grades Overview</h3>
                    <p class="mb-6 text-gray-600 dark:text-gray-300">
                        Select a child to view their grades and academic performance. You can see detailed information about each subject, including
                        assignments and assessments.
                    </p>
                    <div class="grid w-full grid-cols-1 gap-3 md:grid-cols-2 md:gap-4 lg:grid-cols-3">
                        <div
                            v-for="(child, index) in children"
                            :key="index"
                            class="cursor-pointer rounded-lg border border-gray-200 p-4 transition-colors hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-700"
                            @click="selectChild(child.id)"
                        >
                            <div class="flex items-center">
                                <img
                                    v-if="child.avatar"
                                    :src="`/${child.avatar}`"
                                    :alt="`${child.name} Avatar`"
                                    class="mr-3 h-10 w-10 rounded-full md:mr-4 md:h-12 md:w-12"
                                />

                                <div>
                                    <h4 class="font-medium">{{ child.name }}</h4>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 md:text-sm">{{ child.grade }}</p>
                                    <p class="mt-1 text-xs md:text-sm">
                                        GPA:
                                        <span :class="`rounded px-1.5 py-0.5 text-xs ${getGradeClass(calculateGPA(child))}`">{{
                                            calculateGPA(child)
                                        }}</span>
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

.bg-primary-50 {
    background-color: rgb(var(--primary-50));
}
.bg-primary-100 {
    background-color: rgb(var(--primary-100));
}
.bg-primary-500 {
    background-color: rgb(var(--primary-500));
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
.dark .dark\:bg-primary-500 {
    background-color: rgb(var(--primary-500));
}
.dark .dark\:text-primary-400 {
    color: rgb(var(--primary-400));
}
.dark .dark\:text-primary-300 {
    color: rgb(var(--primary-300));
}

/* Responsive table styles */
@media (max-width: 768px) {
    .overflow-x-auto {
        -webkit-overflow-scrolling: touch;
    }
}

.reverse-spin {
    animation: spinReverse 1s linear infinite;
}

@keyframes spinReverse {
    0% {
        transform: rotate(360deg);
    }
    100% {
        transform: rotate(0deg);
    }
}
</style>
