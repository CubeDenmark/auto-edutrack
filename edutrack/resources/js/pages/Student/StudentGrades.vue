<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, usePage } from '@inertiajs/vue3';
import { computed, inject, onMounted, ref, watch } from 'vue';

// Breadcrumbs
const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Grades',
        href: '/grades',
    },
];

// Get the page props to access any data passed from the server
const page = usePage();

// Inject the current item ID from the sidebar
const currentItemId = inject('currentItemId', ref(null));

// State variables
const currentSection = ref<any>(null);
const isLoading = ref(false);
const currentTerm = ref('Prelim'); // Default to prelim term
const showFinalGrades = ref(false); // State to toggle final grades display

// Available terms
const terms = [
    { id: 'Prelim', name: 'Preliminary', icon: '📝' },
    { id: 'Midterm', name: 'Midterm', icon: '📊' },
    { id: 'Final Term', name: 'Final Term', icon: '🏆' },
];

// Class sections data from props
const classSections = ref(page.props.classSections || []);

// Helper function for grade styling
const getGradeClass = (score: number, total: number = 100) => {
    const percentage = total > 0 ? (score / total) * 100 : 0;

    if (percentage >= 90) return 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-300';
    if (percentage >= 80) return 'bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-300';
    if (percentage >= 70) return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-300';
    if (percentage >= 60) return 'bg-orange-100 text-orange-800 dark:bg-orange-900/50 dark:text-orange-300';
    return 'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-300';
};

// Helper function to get letter grade
const getLetterGrade = (score: number): number | string => {
    if (score >= 99 && score <= 100) return 1.0; // Excellent
    if (score >= 96 && score <= 98) return 1.25;
    if (score >= 93 && score <= 95) return 1.5;
    if (score >= 90 && score <= 92) return 1.75; // Very Good
    if (score >= 87 && score <= 89) return 2.0;
    if (score >= 84 && score <= 86) return 2.25; // Above Average
    if (score >= 81 && score <= 83) return 2.5;
    if (score >= 78 && score <= 80) return 2.75; // Average
    if (score >= 75 && score <= 77) return 3.0; // Passing
    if (score < 75) return 4.0; // Conditional

    return 5.0; // Failing / fallback
};

// Helper function to get grade icon
const getGradeIcon = (score: number) => {
    if (score >= 90) return '🏆';
    if (score >= 80) return '🥈';
    if (score >= 70) return '🥉';
    if (score >= 60) return '👍';
    return '📚';
};

// Filtered assessments based on current term
const currentTermAssessments = computed(() => {
    if (!currentSection.value || !currentSection.value.assessments) {
        return [];
    }

    return currentSection.value.assessments.filter((assessment) => assessment.term === currentTerm.value);
});

// Final grade for the current term
const currentTermFinalGrade = computed(() => {
    if (!currentSection.value || !currentSection.value.finalGrade) {
        return null;
    }

    const termMap = {
        Prelim: 'prelim',
        Midterm: 'midterm',
        'Final Term': 'final_term',
    };

    return currentSection.value.finalGrade[termMap[currentTerm.value]];
});

// Final grades data
const finalGrades = computed(() => {
    if (!currentSection.value || !currentSection.value.finalGrade) {
        return null;
    }

    return currentSection.value.finalGrade;
});

// Add a computed property to track the selected section ID from URL or other sources
const selectedSectionId = computed(() => {
    const urlParams = new URLSearchParams(window.location.search);
    const sectionId = urlParams.get('sectionId');
    const itemId = urlParams.get('itemId');

    // Check URL parameters first
    if (sectionId) {
        return sectionId;
    }

    if (itemId) {
        return itemId;
    }

    // Then check injected ID
    if (currentItemId.value) {
        return currentItemId.value;
    }

    return null;
});

// Methods
function findSectionById(id) {
    return classSections.value.find((section) => section.id === id) || null;
}

// Toggle final grades display
function toggleFinalGrades() {
    showFinalGrades.value = !showFinalGrades.value;
}

// Select a section
async function selectSection(section) {
    // Prevent re-selecting the same section to avoid infinite loops
    if (currentSection.value && currentSection.value.id === section.id) {
        return;
    }

    isLoading.value = true;

    try {
        // If the section doesn't have assessments yet, fetch them
        if (!section.assessments || section.assessments.length === 0) {
            await fetchSectionAssessments(section.id);
        }

        currentSection.value = section;

        // Update URL with the selected section ID without navigating
        const url = new URL(window.location.href);
        url.searchParams.set('sectionId', section.id);
        window.history.replaceState({}, '', url.toString());

        // Dispatch an event to notify the sidebar of the selection
        window.dispatchEvent(
            new CustomEvent('section-changed', {
                detail: {
                    sectionId: section.id,
                    source: 'grades',
                },
            }),
        );

        // Also dispatch the newer item-changed event for better compatibility
        window.dispatchEvent(
            new CustomEvent('item-changed', {
                detail: {
                    itemId: section.id,
                    itemType: 'section',
                    source: 'grades',
                },
            }),
        );
    } catch (error) {
        console.error('Error selecting section:', error);
    } finally {
        isLoading.value = false;
    }
}

// Change the current term
function changeTerm(term) {
    currentTerm.value = term;
}

// Fetch assessments for a specific section
async function fetchSectionAssessments(sectionId) {
    try {
        // Make API call to fetch assessments for the section
        const response = await fetch(`/grades/section/${sectionId}/assessments`);

        if (!response.ok) {
            throw new Error(`Failed to fetch assessments: ${response.status}`);
        }

        const data = await response.json();

        // Find the section in our array and update its assessments
        const sectionIndex = classSections.value.findIndex((s) => s.id === sectionId);
        if (sectionIndex !== -1) {
            classSections.value[sectionIndex].assessments = data.assessments || [];
            classSections.value[sectionIndex].finalGrade = data.finalGrade || {};
        }
    } catch (error) {
        console.error('Error fetching section assessments:', error);
        // Handle error state if needed
    }
}

// Fetch all sections data
async function fetchAllSections() {
    isLoading.value = true;

    try {
        // Make API call to fetch all sections data
        const response = await fetch('/api/grades/sections');

        if (!response.ok) {
            throw new Error(`Failed to fetch sections: ${response.status}`);
        }

        const data = await response.json();

        // Update the class sections with the fetched data
        classSections.value = data.classSections || [];

        // If we have a current section selected, update it with the new data
        if (currentSection.value) {
            const updatedSection = classSections.value.find((s) => s.id === currentSection.value.id);
            if (updatedSection) {
                currentSection.value = updatedSection;
            }
        }
    } catch (error) {
        console.error('Error fetching sections:', error);
        // Handle error state if needed
    } finally {
        isLoading.value = false;
    }
}

// Listen for item changes from the sidebar
const handleItemChange = async (event) => {
    if (event.detail && event.detail.source === 'sidebar') {
        const itemId = event.detail.itemId;
        const itemType = event.detail.itemType;

        // For students, the itemType will be 'section' or undefined
        if (itemType === 'section' || !itemType) {
            const section = findSectionById(itemId);
            if (section) {
                if (!section.assessments || section.assessments.length === 0) {
                    await fetchSectionAssessments(section.id);
                }

                if (!currentSection.value || currentSection.value.id !== section.id) {
                    // Only update if it's a different section to avoid infinite loops
                    currentSection.value = section;
                }
            }
        }
    }
};

// For backward compatibility, also listen for section-changed events
const handleSectionChange = async (event) => {
    if (event.detail && event.detail.source !== 'grades') {
        const sectionId = event.detail.sectionId;
        if (sectionId) {
            const section = findSectionById(sectionId);
            if (section) {
                if (!section.assessments || section.assessments.length === 0) {
                    await fetchSectionAssessments(section.id);
                }

                if (!currentSection.value || currentSection.value.id !== section.id) {
                    currentSection.value = section;
                }
            }
        }
    }
};

// Watch for changes in the injected item ID
watch(currentItemId, async (newItemId) => {
    if (newItemId) {
        const section = findSectionById(newItemId);
        if (section) {
            if (!section.assessments || section.assessments.length === 0) {
                await fetchSectionAssessments(section.id);
            }

            if (!currentSection.value || currentSection.value.id !== section.id) {
                // Only update if it's a different section
                currentSection.value = section;
            }
        }
    }
});

// Watch for changes in the URL query parameters
watch(
    () => window.location.search,
    async (newSearch) => {
        const params = new URLSearchParams(newSearch);
        const sectionId = params.get('sectionId');
        const itemId = params.get('itemId');

        // First check for sectionId (legacy parameter)
        if (sectionId) {
            const section = findSectionById(sectionId);
            if (section) {
                if (!section.assessments || section.assessments.length === 0) {
                    await fetchSectionAssessments(section.id);
                }

                if (!currentSection.value || currentSection.value.id !== section.id) {
                    // Only update if it's a different section
                    currentSection.value = section;
                }
            }
        }
        // Then check for itemId (new parameter from sidebar)
        else if (itemId) {
            const section = findSectionById(itemId);
            if (section) {
                if (!section.assessments || section.assessments.length === 0) {
                    await fetchSectionAssessments(section.id);
                }

                if (!currentSection.value || currentSection.value.id !== section.id) {
                    // Only update if it's a different section
                    currentSection.value = section;
                }
            }
        }
    },
);

// Lifecycle hooks
onMounted(async () => {
    // Fetch all sections data if not provided in props
    if (!page.props.classSections || page.props.classSections.length === 0) {
        await fetchAllSections();
    }

    // Add event listener for item changes from sidebar
    window.addEventListener('item-changed', handleItemChange);

    // For backward compatibility, also listen for section-changed events
    window.addEventListener('section-changed', handleSectionChange);

    // Check if there's a section ID in the URL or page props
    const urlParams = new URLSearchParams(window.location.search);
    const sectionIdFromUrl = urlParams.get('sectionId');
    const itemIdFromUrl = urlParams.get('itemId');
    const sectionIdFromProps = page.props.sectionId;

    let initialSectionId;

    // First check for sectionId from URL or props
    if (sectionIdFromUrl || sectionIdFromProps) {
        initialSectionId = sectionIdFromProps || sectionIdFromUrl;
    }
    // Then check for itemId (which could be a section ID from sidebar)
    else if (itemIdFromUrl) {
        initialSectionId = itemIdFromUrl;
    }
    // Finally check for injected item ID
    else if (currentItemId.value) {
        initialSectionId = currentItemId.value;
    }

    if (initialSectionId) {
        const initialSection = findSectionById(initialSectionId);
        if (initialSection) {
            if (!initialSection.assessments || initialSection.assessments.length === 0) {
                await fetchSectionAssessments(initialSection.id);
            }
            currentSection.value = initialSection;
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
    <Head title="Student Grades" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="min-h-screen w-full bg-gray-50 p-4 pt-16 dark:bg-gray-900 md:p-6 md:pt-20">
            <!-- Loading State -->
            <div v-if="isLoading" class="flex h-[calc(100vh-10rem)] items-center justify-center">
                <div
                    class="border-primary-200 dark:border-primary-800 dark:border-t-primary-400 h-16 w-16 animate-spin rounded-full border-4 border-t-primary-600"
                ></div>
            </div>

            <!-- No Section Selected Message -->
            <div v-else-if="!currentSection" class="flex h-[calc(100vh-10rem)] flex-col items-center justify-center text-center">
                <div
                    class="from-primary-50 to-primary-100 dark:from-primary-900/30 dark:to-primary-800/30 mb-6 rounded-full bg-gradient-to-br p-8 shadow-md"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="dark:text-primary-400 h-20 w-20 text-primary-500"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"
                        />
                    </svg>
                </div>
                <h2 class="mb-3 text-3xl font-bold text-gray-800 dark:text-gray-100">Select a Class Section</h2>
                <p class="mb-8 max-w-md text-gray-500 dark:text-gray-400">
                    Please select a class section from the sidebar to view your assessments and grades.
                </p>

                <div class="grid w-full grid-cols-1 gap-6 px-4 sm:grid-cols-2 lg:grid-cols-4">
                    <button
                        v-for="section in classSections.slice(0, 4)"
                        :key="section.id"
                        @click="selectSection(section)"
                        :class="[
                            'flex transform flex-col items-center rounded-xl p-6 transition-all duration-300 hover:scale-105',
                            selectedSectionId === section.id
                                ? 'from-primary-50 to-primary-100 dark:from-primary-900/40 dark:to-primary-800/40 border-primary-200 dark:border-primary-700 border bg-gradient-to-br shadow-lg'
                                : 'border border-gray-200 bg-white shadow-md hover:shadow-lg dark:border-gray-700 dark:bg-gray-800',
                        ]"
                    >
                        <div
                            :class="[
                                'mb-3 rounded-full p-4 text-4xl',
                                selectedSectionId === section.id
                                    ? 'bg-primary-200 dark:bg-primary-800 text-primary-700 dark:text-primary-300'
                                    : 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300',
                            ]"
                        >
                            {{ section.icon || '📚' }}
                        </div>
                        <span
                            :class="[
                                'text-base font-medium',
                                selectedSectionId === section.id ? 'text-primary-700 dark:text-primary-300' : 'text-gray-800 dark:text-gray-200',
                            ]"
                            >{{ section.name }}</span
                        >
                        <span class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ section.term || 'Current Term' }}</span>
                    </button>
                </div>
            </div>

            <!-- Class-specific assessments content -->
            <div v-else class="w-full">
                <!-- Header Section with Class Info -->
                <div class="mb-6 overflow-hidden rounded-xl bg-white shadow-md transition-all duration-300 dark:bg-gray-800">
                    <div
                        class="from-primary-50 dark:from-primary-900/20 border-b bg-gradient-to-r to-transparent p-6 dark:border-gray-700 dark:to-transparent"
                    >
                        <div class="flex flex-col justify-between gap-4 md:flex-row md:items-center">
                            <div class="flex items-center gap-4">
                                <div class="bg-primary-100 dark:bg-primary-800 dark:text-primary-300 rounded-lg p-3 text-3xl text-primary-600">
                                    {{ currentSection.icon || '📚' }}
                                </div>
                                <div>
                                    <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ currentSection.name }}</h2>
                                    <p class="flex items-center gap-1 text-sm text-gray-500 dark:text-gray-400">
                                        <span>{{ currentSection.term || 'Current Term' }}</span>
                                        <span class="inline-block h-1.5 w-1.5 rounded-full bg-gray-400 dark:bg-gray-600"></span>
                                        <span>{{ currentSection.teacher || 'Instructor' }}</span>
                                    </p>
                                </div>
                            </div>

                            <div class="flex flex-wrap items-center gap-2">
                                <!-- Term Selector -->
                                <div class="flex flex-wrap gap-2">
                                    <button
                                        v-for="term in terms"
                                        :key="term.id"
                                        @click="changeTerm(term.id)"
                                        :class="[
                                            'flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-medium transition-all duration-200',
                                            currentTerm === term.id
                                                ? 'bg-primary-100 text-primary-800 dark:bg-primary-900 dark:text-primary-100 shadow-sm'
                                                : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600',
                                        ]"
                                    >
                                        <span>{{ term.icon }}</span>
                                        <span>{{ term.name }}</span>
                                    </button>
                                </div>

                                <!-- Final Grade Button -->
                                <button
                                    v-if="finalGrades"
                                    @click="toggleFinalGrades"
                                    class="ml-2 flex items-center gap-2 rounded-lg bg-gradient-to-r from-emerald-50 to-emerald-100 px-4 py-2 text-sm font-medium text-emerald-800 transition-all duration-200 hover:shadow-md dark:from-emerald-900/50 dark:to-emerald-800/50 dark:text-emerald-100"
                                >
                                    <span>{{ showFinalGrades ? '🔍' : '🎓' }}</span>
                                    <span>{{ showFinalGrades ? 'Hide Final Grades' : 'Show Final Grades' }}</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Final Grades Panel -->
                    <div
                        v-if="showFinalGrades && finalGrades"
                        class="dark:to-gray-750 border-b bg-gradient-to-br from-gray-50 to-white p-6 transition-all duration-500 ease-in-out dark:border-gray-700 dark:from-gray-800"
                    >
                        <h3 class="mb-5 flex items-center gap-2 text-xl font-bold text-gray-800 dark:text-gray-100">
                            <span class="text-2xl">🎓</span>
                            <span>Final Grades Summary</span>
                        </h3>

                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                            <!-- Prelim Grade -->
                            <div
                                class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm transition-shadow duration-300 hover:shadow-md dark:border-gray-700 dark:bg-gray-800"
                            >
                                <div class="mb-3 flex items-center gap-3">
                                    <div class="rounded-lg bg-blue-100 p-2 dark:bg-blue-900/30">
                                        <span class="text-xl">📝</span>
                                    </div>
                                    <h4 class="text-base font-medium text-gray-700 dark:text-gray-300">Preliminary</h4>
                                </div>
                                <div class="flex items-center justify-between">
                                    <div class="flex items-baseline gap-1">
                                        <span class="text-3xl font-bold text-gray-800 dark:text-gray-100">{{ finalGrades.prelim }}</span>
                                        <span class="text-gray-500 dark:text-gray-400">%</span>
                                    </div>
                                    <div class="flex flex-col items-end">
                                        <span :class="`rounded-full px-3 py-1 text-sm font-medium ${getGradeClass(finalGrades.prelim)}`">
                                            {{ getLetterGrade(finalGrades.prelim) }}
                                        </span>
                                        <span class="mt-1 text-xl">{{ getGradeIcon(finalGrades.prelim) }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Midterm Grade -->
                            <div
                                class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm transition-shadow duration-300 hover:shadow-md dark:border-gray-700 dark:bg-gray-800"
                            >
                                <div class="mb-3 flex items-center gap-3">
                                    <div class="rounded-lg bg-yellow-100 p-2 dark:bg-yellow-900/30">
                                        <span class="text-xl">📊</span>
                                    </div>
                                    <h4 class="text-base font-medium text-gray-700 dark:text-gray-300">Midterm</h4>
                                </div>
                                <div class="flex items-center justify-between">
                                    <div class="flex items-baseline gap-1">
                                        <span class="text-3xl font-bold text-gray-800 dark:text-gray-100">{{ finalGrades.midterm }}</span>
                                        <span class="text-gray-500 dark:text-gray-400">%</span>
                                    </div>
                                    <div class="flex flex-col items-end">
                                        <span :class="`rounded-full px-3 py-1 text-sm font-medium ${getGradeClass(finalGrades.midterm)}`">
                                            {{ getLetterGrade(finalGrades.midterm) }}
                                        </span>
                                        <span class="mt-1 text-xl">{{ getGradeIcon(finalGrades.midterm) }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Final Term Grade -->
                            <div
                                class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm transition-shadow duration-300 hover:shadow-md dark:border-gray-700 dark:bg-gray-800"
                            >
                                <div class="mb-3 flex items-center gap-3">
                                    <div class="rounded-lg bg-orange-100 p-2 dark:bg-orange-900/30">
                                        <span class="text-xl">🏆</span>
                                    </div>
                                    <h4 class="text-base font-medium text-gray-700 dark:text-gray-300">Final Term</h4>
                                </div>
                                <div class="flex items-center justify-between">
                                    <div class="flex items-baseline gap-1">
                                        <span class="text-3xl font-bold text-gray-800 dark:text-gray-100">{{ finalGrades.final_term }}</span>
                                        <span class="text-gray-500 dark:text-gray-400">%</span>
                                    </div>
                                    <div class="flex flex-col items-end">
                                        <span :class="`rounded-full px-3 py-1 text-sm font-medium ${getGradeClass(finalGrades.final_term)}`">
                                            {{ getLetterGrade(finalGrades.final_term) }}
                                        </span>
                                        <span class="mt-1 text-xl">{{ getGradeIcon(finalGrades.final_term) }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Final Grade -->
                            <div
                                class="from-primary-50 to-primary-100 dark:from-primary-900/30 dark:to-primary-800/30 border-primary-200 dark:border-primary-700 rounded-xl border bg-gradient-to-br p-5 shadow-md transition-shadow duration-300 hover:shadow-lg"
                            >
                                <div class="mb-3 flex items-center gap-3">
                                    <div class="rounded-lg bg-white p-2 dark:bg-gray-800">
                                        <span class="text-xl">🎓</span>
                                    </div>
                                    <h4 class="text-primary-700 dark:text-primary-300 text-base font-medium">Final Grade</h4>
                                </div>
                                <div class="flex items-center justify-between">
                                    <div class="flex items-baseline gap-1">
                                        <span class="text-primary-700 dark:text-primary-300 text-3xl font-bold">{{ finalGrades.final_grade }}</span>
                                        <span class="dark:text-primary-400 text-primary-500">%</span>
                                    </div>
                                    <div class="flex flex-col items-end">
                                        <span :class="`rounded-full px-3 py-1 text-sm font-medium ${getGradeClass(finalGrades.final_grade)}`">
                                            {{ getLetterGrade(finalGrades.final_grade) }}
                                        </span>
                                        <span class="mt-1 text-xl">{{ getGradeIcon(finalGrades.final_grade) }}</span>
                                    </div>
                                </div>
                                <div
                                    v-if="finalGrades.final_remarks"
                                    class="border-primary-200 dark:border-primary-700/50 mt-3 rounded-lg border bg-white/50 p-2 text-sm text-gray-700 dark:bg-gray-800/50 dark:text-gray-300"
                                >
                                    {{ finalGrades.final_remarks }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="overflow-hidden rounded-xl bg-white shadow-md transition-all duration-300 dark:bg-gray-800">
                    <div class="p-6">
                        <!-- Term Final Grade Summary -->
                        <div
                            v-if="currentTermFinalGrade"
                            class="dark:to-gray-750 mb-6 rounded-xl border border-gray-100 bg-gradient-to-r from-gray-50 to-white p-5 shadow-sm dark:border-gray-700 dark:from-gray-700"
                        >
                            <div class="flex flex-col justify-between gap-4 md:flex-row md:items-center">
                                <div class="flex items-center gap-3">
                                    <div class="bg-primary-100 dark:bg-primary-800 dark:text-primary-300 rounded-lg p-2 text-xl text-primary-600">
                                        {{ terms.find((t) => t.id === currentTerm)?.icon || '📊' }}
                                    </div>
                                    <div>
                                        <h4 class="text-lg font-medium text-gray-800 dark:text-gray-100">
                                            {{ terms.find((t) => t.id === currentTerm)?.name }} Grade
                                        </h4>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">Overall: {{ currentTermFinalGrade }}%</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span :class="`rounded-full px-4 py-2 text-sm font-medium ${getGradeClass(currentTermFinalGrade)}`">
                                        {{ getLetterGrade(currentTermFinalGrade) }}
                                    </span>
                                    <span class="text-2xl">{{ getGradeIcon(currentTermFinalGrade) }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Loading state for assessments -->
                        <div v-if="isLoading" class="flex items-center justify-center py-16">
                            <div
                                class="border-primary-200 dark:border-primary-800 dark:border-t-primary-400 h-10 w-10 animate-spin rounded-full border-4 border-t-primary-600"
                            ></div>
                        </div>

                        <!-- No assessments message -->
                        <div v-else-if="!currentTermAssessments || currentTermAssessments.length === 0" class="py-16 text-center">
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="mx-auto mb-4 h-16 w-16 text-gray-400"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                                />
                            </svg>
                            <h4 class="mb-2 text-xl font-bold text-gray-800 dark:text-gray-100">No Assessments Available</h4>
                            <p class="text-gray-500 dark:text-gray-400">There are no assessments available for this term yet.</p>
                        </div>

                        <!-- Assessments List -->
                        <template v-else>
                            <div v-for="(assessment, assessmentIndex) in currentTermAssessments" :key="assessmentIndex" class="mb-8">
                                <div
                                    class="dark:bg-gray-750 border-primary-400 mb-4 flex items-center justify-between rounded-lg border-l-4 bg-gray-50 p-4 dark:border-primary-600"
                                >
                                    <div class="flex items-center gap-3">
                                        <div class="bg-primary-100 dark:bg-primary-800 dark:text-primary-300 rounded-lg p-2 text-xl text-primary-600">
                                            {{ assessment.icon || '📝' }}
                                        </div>
                                        <h4 class="text-lg font-medium text-gray-800 dark:text-gray-100">{{ assessment.assessment_name }}</h4>
                                    </div>
                                    <span
                                        class="bg-primary-100 dark:bg-primary-800/50 text-primary-700 dark:text-primary-300 rounded-full px-3 py-1 text-sm font-medium"
                                    >
                                        Weight: {{ assessment.weight }}%
                                    </span>
                                </div>

                                <div class="overflow-x-auto rounded-xl border border-gray-100 shadow-sm dark:border-gray-700">
                                    <table class="min-w-full table-auto divide-y divide-gray-200 overflow-hidden dark:divide-gray-700">
                                        <thead
                                            class="dark:bg-gray-750 bg-gray-50 text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300"
                                        >
                                            <tr class="bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-100">
                                                <th class="px-6 py-4 text-left">Assessment Item</th>
                                                <th class="px-6 py-4 text-left">Score</th>
                                                <th class="px-6 py-4 text-left">Total</th>
                                                <th class="px-6 py-4 text-left">Percentage</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-100 bg-white dark:divide-gray-700 dark:bg-gray-800">
                                            <tr
                                                v-for="(item, itemIndex) in assessment.items"
                                                :key="itemIndex"
                                                class="transition-colors duration-150 hover:bg-gray-50 dark:hover:bg-gray-700"
                                            >
                                                <td class="whitespace-nowrap px-6 py-4">
                                                    <div class="flex items-center gap-4">
                                                        <div
                                                            class="flex h-12 w-12 items-center justify-center rounded-lg text-lg font-semibold shadow-sm"
                                                            :class="item.iconBg || 'bg-blue-100 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400'"
                                                        >
                                                            {{ item.icon || '📝' }}
                                                        </div>
                                                        <div>
                                                            <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                                {{ item.name }}
                                                            </div>
                                                            <div v-if="item.description" class="text-xs text-gray-500 dark:text-gray-400">
                                                                {{ item.description }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 text-sm font-medium text-gray-800 dark:text-gray-200">
                                                    {{ item.score }}
                                                </td>
                                                <td class="px-6 py-4 text-sm text-gray-800 dark:text-gray-200">
                                                    {{ item.total_score }}
                                                </td>
                                                <td class="px-6 py-4">
                                                    <span
                                                        :class="`inline-flex items-center rounded-full px-3 py-1.5 text-xs font-semibold ${getGradeClass(item.score, item.total_score)}`"
                                                    >
                                                        {{ ((item.score / item.total_score) * 100).toFixed(1) }}%
                                                    </span>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Assessment Summary -->
                                <div
                                    class="dark:bg-gray-750 mt-4 flex flex-col justify-between rounded-lg border border-gray-100 bg-gray-50 p-4 dark:border-gray-700 sm:flex-row sm:items-center"
                                >
                                    <span class="flex items-center gap-2 text-sm font-medium">
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            class="h-5 w-5 text-primary-500"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"
                                            />
                                        </svg>
                                        Assessment Total
                                    </span>
                                    <div class="mt-2 flex items-center gap-4 sm:mt-0">
                                        <span
                                            class="rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-sm font-medium dark:border-gray-700 dark:bg-gray-800"
                                        >
                                            {{ assessment.totalScore || 0 }} / {{ assessment.totalPossible || 0 }}
                                        </span>
                                        <span
                                            :class="`inline-flex items-center gap-1 rounded-full px-3 py-1.5 text-sm font-medium ${getGradeClass(assessment.totalScore || 0, assessment.totalPossible || 100)}`"
                                        >
                                            {{
                                                assessment.totalPossible ? ((assessment.totalScore / assessment.totalPossible) * 100).toFixed(1) : 0
                                            }}%
                                            <span>{{
                                                getGradeIcon(assessment.totalPossible ? (assessment.totalScore / assessment.totalPossible) * 100 : 0)
                                            }}</span>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- General sections list (when no class is selected) -->
                <div
                    v-if="!currentSection && !isLoading && classSections.length > 0"
                    class="mt-6 w-full overflow-hidden rounded-xl bg-white shadow-md transition-all duration-300 dark:bg-gray-800"
                >
                    <div
                        class="from-primary-50 dark:from-primary-900/20 border-b bg-gradient-to-r to-transparent p-6 dark:border-gray-700 dark:to-transparent"
                    >
                        <h3 class="text-xl font-bold text-gray-800 dark:text-gray-100">Current Classes</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Select a class to view assessments</p>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="dark:bg-gray-750 bg-gray-50">
                                <tr>
                                    <th
                                        scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300"
                                    >
                                        Subject
                                    </th>
                                    <th
                                        scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300"
                                    >
                                        Teacher
                                    </th>
                                    <th
                                        scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300"
                                    >
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                                <tr
                                    v-for="(section, index) in classSections"
                                    :key="section.id"
                                    class="dark:hover:bg-gray-750 transition-colors duration-150 hover:bg-gray-50"
                                >
                                    <td class="whitespace-nowrap px-6 py-4">
                                        <div class="flex items-center">
                                            <div
                                                class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-lg shadow-sm"
                                                :class="
                                                    section.iconBg || 'bg-primary-100 dark:bg-primary-800/50 dark:text-primary-400 text-primary-600'
                                                "
                                            >
                                                <span class="text-lg">{{ section.icon || '📚' }}</span>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ section.name }}</div>
                                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ section.room || 'No room assigned' }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700 dark:text-gray-300">{{ section.teacher }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm">
                                        <button
                                            @click="selectSection(section)"
                                            class="bg-primary-50 hover:bg-primary-100 dark:bg-primary-900/30 dark:hover:bg-primary-800/50 text-primary-700 dark:text-primary-300 inline-flex items-center gap-1 rounded-lg px-4 py-2 font-medium transition-colors duration-200"
                                        >
                                            <svg
                                                xmlns="http://www.w3.org/2000/svg"
                                                class="h-4 w-4"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                                stroke="currentColor"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                                                />
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                                                />
                                            </svg>
                                            View Assessments
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
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

/* Primary Colors */
.bg-primary-50 {
    background-color: rgb(var(--primary-50));
}
.bg-primary-100 {
    background-color: rgb(var(--primary-100));
}
.bg-primary-200 {
    background-color: rgb(var(--primary-200));
}
.bg-primary-400 {
    background-color: rgb(var(--primary-400));
}
.bg-primary-500 {
    background-color: rgb(var(--primary-500));
}
.bg-primary-600 {
    background-color: rgb(var(--primary-600));
}
.bg-primary-800 {
    background-color: rgb(var(--primary-800));
}
.text-primary-300 {
    color: rgb(var(--primary-300));
}
.text-primary-400 {
    color: rgb(var(--primary-400));
}
.text-primary-500 {
    color: rgb(var(--primary-500));
}
.text-primary-600 {
    color: rgb(var(--primary-600));
}
.text-primary-700 {
    color: rgb(var(--primary-700));
}
.border-primary-200 {
    border-color: rgb(var(--primary-200));
}
.border-primary-400 {
    border-color: rgb(var(--primary-400));
}
.border-primary-600 {
    border-color: rgb(var(--primary-600));
}
.border-primary-700 {
    border-color: rgb(var(--primary-700));
}

/* Dark Mode */
.dark .dark\:bg-primary-800 {
    background-color: rgb(var(--primary-800));
}
.dark .dark\:bg-primary-800\/50 {
    background-color: rgba(var(--primary-800), 0.5);
}
.dark .dark\:bg-primary-900 {
    background-color: rgb(var(--primary-900));
}
.dark .dark\:bg-primary-900\/20 {
    background-color: rgba(var(--primary-900), 0.2);
}
.dark .dark\:bg-primary-900\/30 {
    background-color: rgba(var(--primary-900), 0.3);
}
.dark .dark\:bg-primary-900\/40 {
    background-color: rgba(var(--primary-900), 0.4);
}
.dark .dark\:text-primary-100 {
    color: rgb(var(--primary-100));
}
.dark .dark\:text-primary-300 {
    color: rgb(var(--primary-300));
}
.dark .dark\:text-primary-400 {
    color: rgb(var(--primary-400));
}
.dark .dark\:border-primary-700 {
    border-color: rgb(var(--primary-700));
}
.dark .dark\:border-primary-700\/50 {
    border-color: rgba(var(--primary-700), 0.5);
}
.dark .dark\:border-primary-800 {
    border-color: rgb(var(--primary-800));
}

/* Additional colors for dark mode */
.dark .dark\:bg-gray-750 {
    background-color: rgb(31, 41, 55);
}
.dark .dark\:bg-gray-800\/50 {
    background-color: rgba(31, 41, 55, 0.5);
}

/* Gradient backgrounds */
.bg-gradient-to-r {
    background-image: linear-gradient(to right, var(--tw-gradient-stops));
}
.bg-gradient-to-br {
    background-image: linear-gradient(to bottom right, var(--tw-gradient-stops));
}
.from-primary-50 {
    --tw-gradient-from: rgb(var(--primary-50));
    --tw-gradient-stops: var(--tw-gradient-from), transparent;
}
.to-primary-100 {
    --tw-gradient-to: rgb(var(--primary-100));
}
.to-transparent {
    --tw-gradient-to: transparent;
}
.from-gray-50 {
    --tw-gradient-from: rgb(249, 250, 251);
    --tw-gradient-stops: var(--tw-gradient-from), transparent;
}
.to-white {
    --tw-gradient-to: white;
}
.from-emerald-50 {
    --tw-gradient-from: rgb(236, 253, 245);
    --tw-gradient-stops: var(--tw-gradient-from), transparent;
}
.to-emerald-100 {
    --tw-gradient-to: rgb(209, 250, 229);
}

/* Dark mode gradients */
.dark .dark\:from-primary-900\/20 {
    --tw-gradient-from: rgba(var(--primary-900), 0.2);
    --tw-gradient-stops: var(--tw-gradient-from), transparent;
}
.dark .dark\:from-primary-900\/30 {
    --tw-gradient-from: rgba(var(--primary-900), 0.3);
    --tw-gradient-stops: var(--tw-gradient-from), transparent;
}
.dark .dark\:from-primary-900\/40 {
    --tw-gradient-from: rgba(var(--primary-900), 0.4);
    --tw-gradient-stops: var(--tw-gradient-from), transparent;
}
.dark .dark\:to-primary-800\/30 {
    --tw-gradient-to: rgba(var(--primary-800), 0.3);
}
.dark .dark\:to-primary-800\/40 {
    --tw-gradient-to: rgba(var(--primary-800), 0.4);
}
.dark .dark\:to-primary-800\/50 {
    --tw-gradient-to: rgba(var(--primary-800), 0.5);
}
.dark .dark\:from-gray-800 {
    --tw-gradient-from: rgb(31, 41, 55);
    --tw-gradient-stops: var(--tw-gradient-from), transparent;
}
.dark .dark\:to-gray-750 {
    --tw-gradient-to: rgb(31, 41, 55);
}
.dark .dark\:from-emerald-900\/50 {
    --tw-gradient-from: rgba(6, 78, 59, 0.5);
    --tw-gradient-stops: var(--tw-gradient-from), transparent;
}
.dark .dark\:to-emerald-800\/50 {
    --tw-gradient-to: rgba(6, 95, 70, 0.5);
}

/* Transitions and animations */
.transition-all {
    transition-property: all;
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
}
.duration-200 {
    transition-duration: 200ms;
}
.duration-300 {
    transition-duration: 300ms;
}
.duration-500 {
    transition-duration: 500ms;
}
.ease-in-out {
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
}
.transform {
    transform: translateX(var(--tw-translate-x)) translateY(var(--tw-translate-y)) rotate(var(--tw-rotate)) skewX(var(--tw-skew-x))
        skewY(var(--tw-skew-y)) scaleX(var(--tw-scale-x)) scaleY(var(--tw-scale-y));
}
.hover\:scale-105:hover {
    --tw-scale-x: 1.05;
    --tw-scale-y: 1.05;
}
</style>
