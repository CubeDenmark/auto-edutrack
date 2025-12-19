<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed, inject, onMounted, ref, watch } from 'vue';

// Breadcrumbs
const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

// Get the page props to access any data passed from the server
const page = usePage();

// Inject the current item ID from the sidebar
const currentItemId = inject('currentItemId', ref(null));

// State variables
const currentDate = ref(new Date().toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }));
const currentSection = ref<any>(null);

// Student information from the controller
const currentStudent = ref(
    page.props.currentStudent || {
        id: '',
        name: '',
        email: '',
        grade: '',
        gpa: 0,
        behaviorStatus: 'Good',
        behaviorReports: 0,
        attendanceRate: 0,
        presentDays: 0,
        totalDays: 0,
    },
);

// Class sections data from the controller
const classSections = ref(page.props.classSections || []);

// Recent activities data from the controller
const recentActivities = ref(page.props.recentActivities || []);

// Student's enrolled classes
const enrolledClasses = computed(() => {
    return classSections.value;
});

// Academic performance data
const academicPerformance = computed(() => {
    return (
        page.props.academicPerformance ||
        enrolledClasses.value.map((cls) => ({
            subject: cls.name,
            percentage: cls.gradeValue,
            color: getGradeColor(cls.gradeValue),
        }))
    );
});

// Terms data for the dropdown
const terms = ref(page.props.classTerms || []); 
const selectedTerm = ref(terms.value.length > 0 ? terms.value[0] : '');
const isGeneratingPdf = ref(false);

// Error modal state
const showErrorModal = ref(false);
const errorMessage = ref('');

// Helper function for grade styling
const getGradeClass = (score: number) => {
    if (score >= 90) return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300';
    if (score >= 80) return 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300';
    if (score >= 70) return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300';
    if (score >= 60) return 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-300';
    return 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300';
};

// Helper function for grade colors
const getGradeColor = (score: number) => {
    if (score >= 90) return 'bg-green-600';
    if (score >= 80) return 'bg-blue-600';
    if (score >= 70) return 'bg-yellow-600';
    if (score >= 60) return 'bg-orange-600';
    return 'bg-red-600';
};

// Helper function to convert percentage to Philippines grading system (1.0-5.0)
const percentageToPhGrade = (percentage: number) => {
    if (percentage >= 96) return '1.0';
    if (percentage >= 94) return '1.25';
    if (percentage >= 92) return '1.5';
    if (percentage >= 89) return '1.75';
    if (percentage >= 87) return '2.0';
    if (percentage >= 84) return '2.25';
    if (percentage >= 82) return '2.5';
    if (percentage >= 79) return '2.75';
    if (percentage >= 76) return '3.0';
    if (percentage >= 73) return '3.25';
    if (percentage >= 70) return '3.5';
    if (percentage >= 67) return '3.75';
    if (percentage >= 65) return '4.0';
    if (percentage >= 60) return '4.5';
    return '5.0';
};

// Function to close the error modal
const closeErrorModal = () => {
    showErrorModal.value = false;
    errorMessage.value = '';
};

// Function to generate PDF for the selected term
const generatePdf = async () => {
    if (!selectedTerm.value) {
        errorMessage.value = 'Please select a term first';
        showErrorModal.value = true;
        return;
    }

    try {
        isGeneratingPdf.value = true;
        const studentId = currentStudent.value.id;

        const url = `/student/generate-pdf-by-term/${studentId}?term=${encodeURIComponent(selectedTerm.value)}`;
        
        // First check if the endpoint returns an error
        const response = await fetch(url);
        const contentType = response.headers.get('content-type');
        
        if (contentType && contentType.includes('application/json')) {
            // This is an error response
            const data = await response.json();
            errorMessage.value = data.message || 'Failed to generate PDF. Please try again later.';
            showErrorModal.value = true;
        } else {
            // This is a successful PDF response, open it in a new tab
            window.open(url, '_blank');
        }
    } catch (error) {
        console.error('Error generating PDF:', error);
        errorMessage.value = error instanceof Error ? error.message : 'Failed to generate PDF. Please try again later.';
        showErrorModal.value = true;
    } finally {
        isGeneratingPdf.value = false;
    }
};


// Methods
function findSectionById(id) {
    if (!id) return null;
    console.log('Finding section with ID:', id);
    console.log(
        'Available sections:',
        classSections.value.map((s) => ({ id: s.id, name: s.name })),
    );

    // Try to find by exact match first
    let section = classSections.value.find((section) => section.id === id);

    // If not found, try string comparison (in case of type mismatch)
    if (!section) {
        section = classSections.value.find((section) => section.id.toString() === id.toString());
    }

    console.log('Found section:', section);
    return section || null;
}

// Handle item changes from the sidebar
const handleItemChange = (event) => {
    console.log('Item changed event received:', event.detail);
    if (event.detail && event.detail.source === 'sidebar') {
        const itemId = event.detail.itemId;
        const itemType = event.detail.itemType;

        // For students, the itemType will be 'section' or undefined
        if (itemType === 'section' || !itemType) {
            const section = findSectionById(itemId);
            if (section) {
                console.log('Setting current section from event:', section.name);
                currentSection.value = section;
            }
        }
    }
};

// Handle section changes from other components
const handleSectionChange = (event) => {
    console.log('Section changed event received:', event.detail);
    if (event.detail && event.detail.sectionId) {
        const section = findSectionById(event.detail.sectionId);
        if (section) {
            console.log('Setting current section from section-changed event:', section.name);
            currentSection.value = section;
        }
    }
};

// Watch for changes in the injected item ID
watch(
    currentItemId,
    (newItemId) => {
        console.log('currentItemId changed:', newItemId);
        if (newItemId) {
            const section = findSectionById(newItemId);
            if (section) {
                console.log('Setting current section from injected ID:', section.name);
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

        console.log('URL params changed:', { sectionId, itemId });

        // First check for sectionId (legacy parameter)
        if (sectionId) {
            const section = findSectionById(sectionId);
            if (section) {
                console.log('Setting current section from sectionId param:', section.name);
                currentSection.value = section;
            }
        }
        // Then check for itemId (new parameter from sidebar)
        else if (itemId) {
            const section = findSectionById(itemId);
            if (section) {
                console.log('Setting current section from itemId param:', section.name);
                currentSection.value = section;
            }
        }
    },
    { immediate: true },
);

// Function to select a class from the grid
function selectClass(cls) {
    console.log('Class selected from grid:', cls.name);
    currentSection.value = cls;
}

// Function to clear the current section selection
function clearSectionSelection() {
    console.log('Clearing section selection');
    currentSection.value = null;
}

const fetchTerms = async () => {
    try {
        const response = await fetch('/student/terms');
        const data = await response.json();
        terms.value = data.terms;

        // Set default selected term
        if (terms.value.length > 0) {
            selectedTerm.value = terms.value[0];
        }
    } catch (error) {
        console.error('Error fetching terms:', error);
    }
};


onMounted(() => {
    console.log('Component mounted');
    console.log('Initial classSections:', classSections.value);
    console.log('Initial currentItemId:', currentItemId.value);
    console.log('Initial URL params:', new URLSearchParams(window.location.search).toString());

    // Add event listener for item changes from sidebar
    window.addEventListener('item-changed', handleItemChange);

    // For backward compatibility, also listen for section-changed events
    window.addEventListener('section-changed', handleSectionChange);

    // Initialize currentSection based on URL parameters, props, or injected item ID
    const initializeCurrentSection = () => {
        const urlParams = new URLSearchParams(window.location.search);
        const sectionIdFromUrl = urlParams.get('sectionId');
        const itemIdFromUrl = urlParams.get('itemId');
        const sectionIdFromProps = page.props.sectionId;

        console.log('Initializing current section with:', {
            sectionIdFromUrl,
            itemIdFromUrl,
            sectionIdFromProps,
            currentItemIdValue: currentItemId.value,
        });

        let section;

        // First check for sectionId from URL or props
        if (sectionIdFromUrl || sectionIdFromProps) {
            const sectionId = sectionIdFromProps || sectionIdFromUrl;
            section = findSectionById(sectionId);
            if (section) {
                console.log('Setting initial section from URL/props:', section.name);
            }
        }
        // Then check for itemId (which could be a section ID from sidebar)
        else if (itemIdFromUrl) {
            section = findSectionById(itemIdFromUrl);
            if (section) {
                console.log('Setting initial section from itemId URL param:', section.name);
            }
        }
        // Finally check for injected item ID
        else if (currentItemId.value) {
            section = findSectionById(currentItemId.value);
            if (section) {
                console.log('Setting initial section from injected currentItemId:', section.name);
            }
        }

        if (section) {
            currentSection.value = section;
        }
    };

    initializeCurrentSection();
    
    // Fetch available terms
    fetchTerms();

    // Clean up event listeners on component unmount
    return () => {
        window.removeEventListener('item-changed', handleItemChange);
        window.removeEventListener('section-changed', handleSectionChange);
    };
});
</script>

<template>
    <Head title="Student Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 pt-20">
            <!-- Class-specific dashboard content -->
            <div v-if="currentSection" class="mb-6">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-2xl font-bold">{{ currentSection.name }} Overview</h2>
                    <button
                        @click="clearSectionSelection"
                        class="hover:text-primary-800 dark:text-primary-400 dark:hover:text-primary-300 flex items-center text-sm text-primary-600"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="mr-1 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                        Back to Dashboard
                    </button>
                </div>

                <div class="mb-6 rounded-lg bg-white p-6 shadow dark:bg-gray-800">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                        <div class="rounded-lg border bg-white p-4 shadow-sm dark:border-gray-600 dark:bg-gray-700">
                            <div class="flex items-center space-x-3">
                                <div
                                    class="flex h-10 w-10 items-center justify-center rounded-full bg-blue-100 text-blue-600 dark:bg-blue-900 dark:text-blue-400"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                                        />
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Current Grade</p>
                                    <div class="flex items-center">
                                        <p class="text-xl font-bold">{{ percentageToPhGrade(currentSection.gradeValue) }}</p>
                                        <span
                                            :class="`ml-2 rounded-full px-2 py-0.5 text-xs font-medium ${getGradeClass(currentSection.gradeValue)}`"
                                        >
                                            {{ currentSection.gradeValue }}%
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="rounded-lg border bg-white p-4 shadow-sm dark:border-gray-600 dark:bg-gray-700">
                            <div class="flex items-center space-x-3">
                                <div
                                    class="flex h-10 w-10 items-center justify-center rounded-full bg-green-100 text-green-600 dark:bg-green-900 dark:text-green-400"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                                        />
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Attendance</p>
                                    <p class="text-xl font-bold">{{ currentSection.attendance }}%</p>
                                </div>
                            </div>
                        </div>
                        <div class="rounded-lg border bg-white p-4 shadow-sm dark:border-gray-600 dark:bg-gray-700">
                            <div class="flex items-center space-x-3">
                                <div
                                    class="flex h-10 w-10 items-center justify-center rounded-full bg-purple-100 text-purple-600 dark:bg-purple-900 dark:text-purple-400"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"
                                        />
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Pending Assessments</p>
                                    <p class="text-xl font-bold">{{ currentSection.pendingAssessments }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Teacher Information -->
                <div class="mb-6 rounded-lg bg-white p-6 shadow dark:bg-gray-800">
                    <h3 class="mb-4 font-medium">Teacher Information</h3>
                    <div class="flex items-center">
                        <div
                            class="bg-primary-100 dark:bg-primary-900 dark:text-primary-400 mr-4 flex h-12 w-12 items-center justify-center overflow-hidden rounded-full text-primary-600"
                        >
                            <template v-if="currentSection.avatar">
                                <img :src="`/${currentSection.avatar}`" alt="Avatar" class="h-full w-full rounded-full object-cover" />
                            </template>
                            <template v-else>
                                <span class="text-lg font-semibold">
                                    {{ currentSection.teacher.charAt(0).toUpperCase() }}
                                </span>
                            </template>
                        </div>

                        <div>
                            <h4 class="font-medium">{{ currentSection.teacher }}</h4>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Room: {{ currentSection.room }}</p>
                            <p v-if="currentSection.teacherEmail" class="text-sm text-gray-500 dark:text-gray-400">
                                Email: {{ currentSection.teacherEmail }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Class Schedule -->
                <div class="mb-6 rounded-lg bg-white p-6 shadow dark:bg-gray-800">
                    <h3 class="mb-4 font-medium">Class Schedule</h3>
                    <div v-if="currentSection.schedule && currentSection.schedule.length > 0" class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th
                                        scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300"
                                    >
                                        Day
                                    </th>
                                    <th
                                        scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300"
                                    >
                                        Time
                                    </th>
                                    <th
                                        scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300"
                                    >
                                        Room
                                    </th>
                                    <th
                                        scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300"
                                    >
                                        Teacher
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                                <tr v-for="(schedule, index) in currentSection.schedule" :key="index">
                                    <td class="whitespace-nowrap px-6 py-4 text-sm">{{ schedule.day }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm">{{ schedule.time }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm">{{ schedule.room }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm">{{ schedule.teacher }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div v-else class="py-6 text-center text-gray-500 dark:text-gray-400">No schedule information available for this class.</div>
                </div>

                <!-- Upcoming Assessments -->
                <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
                    <div class="mb-4 flex items-center justify-between">
                        <h3 class="font-medium">Upcoming Assessments</h3>
                        <Link
                            :href="`/student/attendance?sectionId=${currentSection.id}`"
                            class="hover:text-primary-800 dark:text-primary-400 dark:hover:text-primary-300 text-sm text-primary-600"
                        >
                            View Attendance
                        </Link>
                    </div>
                    <div v-if="currentSection.assignments && currentSection.assignments.length > 0" class="space-y-4">
                        <div
                            v-for="(assignment, index) in currentSection.assignments"
                            :key="index"
                            class="flex items-start space-x-4 border-b pb-4 last:border-0 last:pb-0 dark:border-gray-700"
                        >
                            <div :class="`flex h-10 w-10 items-center justify-center rounded-full ${assignment.iconBg} ${assignment.iconColor}`">
                                <span class="text-lg">{{ assignment.icon }}</span>
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center justify-between">
                                    <p class="font-medium">{{ assignment.title }}</p>
                                    <span
                                        :class="`inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ${assignment.statusClass}`"
                                    >
                                        {{ assignment.status }}
                                    </span>
                                </div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ assignment.description }}</p>
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Due: {{ assignment.dueDate }}</p>
                            </div>
                        </div>
                    </div>
                    <div v-else class="py-6 text-center text-gray-500 dark:text-gray-400">No upcoming assessments for this class.</div>
                </div>
            </div>

            <!-- General dashboard content (when no class is selected) -->
            <div v-if="!currentSection">
                <!-- PDF Generation Card -->
                <div class="mb-6 rounded-lg bg-white p-6 shadow dark:bg-gray-800">
                    <h3 class="mb-4 font-medium">Generate Grade Report</h3>
                    <div class="flex flex-col space-y-4 sm:flex-row sm:items-end sm:space-x-4 sm:space-y-0">
                        <div class="flex-1">
                            <label for="term-select" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Select Term
                            </label>
                            <select
                                id="term-select"
                                v-model="selectedTerm"
                                class="block w-full rounded-lg border border-gray-300 bg-white p-2.5 text-sm text-gray-900 focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-primary-500 dark:focus:ring-primary-500"
                            >
                                <option value="">Choose a term</option>
                                <option v-for="term in terms" :key="term" :value="term">{{ term }}</option>
                            </select>
                        </div>
                        <button
                            @click="generatePdf"
                            :disabled="!selectedTerm || isGeneratingPdf"
                            class="inline-flex items-center justify-center rounded-lg bg-primary-600 px-5 py-2.5 text-center text-sm font-medium text-white hover:bg-primary-700 focus:outline-none focus:ring-4 focus:ring-primary-300 disabled:cursor-not-allowed disabled:opacity-50 dark:bg-primary-600 dark:hover:bg-primary-700 dark:focus:ring-primary-800"
                        >
                            <svg 
                                v-if="isGeneratingPdf" 
                                class="mr-2 h-4 w-4 animate-spin" 
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
                                class="mr-2 h-4 w-4" 
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
                            {{ isGeneratingPdf ? 'Generating...' : 'Generate PDF' }}
                        </button>
                    </div>
                    <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                        Generate a PDF report of your grades for the selected term.
                    </p>
                </div>

                <div class="mb-6 grid grid-cols-1 gap-6 md:grid-cols-3">
                    <!-- Attendance Card -->
                    <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
                        <div class="mb-4 flex items-center justify-between">
                            <h3 class="font-medium">Attendance</h3>
                            <span class="rounded-full bg-green-100 px-2 py-1 text-xs font-medium text-green-500 dark:bg-green-900"
                                >{{ currentStudent.attendanceRate }}%</span
                            >
                        </div>
                        <div class="flex items-center space-x-4">
                            <div class="flex h-16 w-16 items-center justify-center rounded-full bg-green-100 text-green-500 dark:bg-green-900">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                                    />
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Present Days</p>
                                <p class="text-2xl font-bold">{{ currentStudent.presentDays }}/{{ currentStudent.totalDays }}</p>
                            </div>
                        </div>
                        <div class="mt-4">
                            <Link
                                href="/student/attendance"
                                class="hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300 text-sm text-primary-600"
                            >
                                View Attendance Details
                            </Link>
                        </div>
                    </div>

                    <!-- Grades Card -->
                    <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
                        <div class="mb-4 flex items-center justify-between">
                            <h3 class="font-medium">GWA</h3>
                            <span class="rounded-full bg-blue-100 px-2 py-1 text-xs font-medium text-blue-500 dark:bg-blue-900">
                                {{ percentageToPhGrade(currentStudent.gpa * 25) }}
                            </span>
                        </div>
                        <div class="flex items-center space-x-4">
                            <div class="flex h-16 w-16 items-center justify-center rounded-full bg-blue-100 text-blue-500 dark:bg-blue-900">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path d="M12 14l9-5-9-5-9 5z" />
                                    <path
                                        d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998a12.078 12.078 0 01.665-6.479L12 14z"
                                    />
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M12 14l9-5-9-5-9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998a12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222"
                                    />
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Current GWA</p>
                                <p class="text-2xl font-bold">{{ currentStudent.gpa }}</p>
                            </div>
                        </div>
                        <div class="mt-4">
                            <Link
                                href="/student/grades"
                                class="hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300 text-sm text-primary-600"
                            >
                                View Grades Details
                            </Link>
                        </div>
                    </div>

                    <!-- Behavior Card -->
                    <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
                        <div class="mb-4 flex items-center justify-between">
                            <h3 class="font-medium">Behavior</h3>
                            <span class="rounded-full bg-purple-100 px-2 py-1 text-xs font-medium text-purple-500 dark:bg-purple-900">{{
                                currentStudent.behaviorStatus
                            }}</span>
                        </div>
                        <div class="flex items-center space-x-4">
                            <div class="flex h-16 w-16 items-center justify-center rounded-full bg-purple-100 text-purple-500 dark:bg-purple-900">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                    />
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Reports</p>
                                <p class="text-2xl font-bold">{{ currentStudent.behaviorReports }}</p>
                            </div>
                        </div>
                        <div class="mt-4">
                            <Link
                                href="/student/behavior-reports"
                                class="hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300 text-sm text-primary-600"
                            >
                                View Behavior Reports
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Performance Chart -->
                <div class="mb-6 rounded-lg bg-white p-6 shadow dark:bg-gray-800">
                    <h3 class="mb-4 font-medium">Academic Performance</h3>
                    <div class="h-64 w-full">
                        <div class="flex h-full w-full items-center justify-center rounded-lg bg-gray-100 dark:bg-gray-700">
                            <div v-if="academicPerformance.length > 0" class="w-full space-y-4 px-8">
                                <div v-for="(subject, index) in academicPerformance" :key="index">
                                    <div class="mb-1 flex justify-between">
                                        <span class="text-sm font-medium">{{ subject.subject }}</span>
                                        <div class="flex items-center">
                                            <span class="mr-2 text-sm font-medium">{{ percentageToPhGrade(subject.percentage) }}</span>
                                            <span class="text-sm font-medium">({{ subject.percentage }}%)</span>
                                        </div>
                                    </div>
                                    <div class="h-2.5 w-full rounded-full bg-gray-200 dark:bg-gray-600">
                                        <div :class="subject.color" class="h-2.5 rounded-full" :style="`width: ${subject.percentage}%`"></div>
                                    </div>
                                </div>
                            </div>
                            <div v-else class="text-center text-gray-500 dark:text-gray-400">No academic performance data available.</div>
                        </div>
                    </div>
                </div>

                <!-- My Classes -->
                <div class="mb-6 rounded-lg bg-white p-6 shadow dark:bg-gray-800">
                    <div class="mb-4 flex items-center justify-between">
                        <h3 class="font-medium">My Classes</h3>
                        <Link
                            href="/student/attendance"
                            class="hover:text-primary-800 dark:text-primary-400 dark:hover:text-primary-300 text-sm text-primary-600"
                        >
                            View All Attendance
                        </Link>
                    </div>
                    <div v-if="enrolledClasses.length > 0" class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
                        <div
                            v-for="(cls, index) in enrolledClasses"
                            :key="index"
                            class="cursor-pointer rounded-lg border border-gray-200 p-4 transition-colors hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-700"
                            @click="selectClass(cls)"
                        >
                            <div class="mb-4 flex items-center">
                                <div
                                    class="bg-primary-100 dark:bg-primary-900 dark:text-primary-400 mr-3 flex h-10 w-10 items-center justify-center rounded-full text-primary-600"
                                >
                                    <span class="text-lg">{{ cls.icon }}</span>
                                </div>
                                <div>
                                    <h4 class="font-medium">{{ cls.name }}</h4>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ cls.teacher }}</p>
                                </div>
                            </div>
                            <div class="mb-2 flex items-center justify-between text-sm">
                                <span class="text-gray-500 dark:text-gray-400">Grade:</span>
                                <div class="flex items-center">
                                    <span class="mr-1 font-medium">{{ percentageToPhGrade(cls.gradeValue) }}</span>
                                    <span :class="`rounded-full px-2 py-0.5 text-xs font-medium ${getGradeClass(cls.gradeValue)}`">
                                        {{ cls.gradeValue }}%
                                    </span>
                                </div>
                            </div>
                            <div class="mb-2 flex items-center justify-between text-sm">
                                <span class="text-gray-500 dark:text-gray-400">Attendance:</span>
                                <span class="font-medium">{{ cls.attendance }}%</span>
                            </div>
                            <div class="mb-4 h-2 w-full rounded-full bg-gray-200 dark:bg-gray-700">
                                <div class="h-2 rounded-full bg-primary-600" :style="`width: ${cls.attendance}%`"></div>
                            </div>
                        </div>
                    </div>
                    <div v-else class="py-6 text-center text-gray-500 dark:text-gray-400">You are not enrolled in any classes.</div>
                </div>

                <!-- Recent Activity -->
                <!-- <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
                    <h3 class="mb-4 font-medium">Recent Activity</h3>
                    <div v-if="recentActivities.length > 0" class="space-y-4">
                        <div
                            v-for="(activity, index) in recentActivities"
                            :key="index"
                            class="flex items-start space-x-4 border-b pb-4 last:border-0 last:pb-0 dark:border-gray-700"
                        >
                            <div :class="`flex h-10 w-10 items-center justify-center rounded-full ${activity.iconBg} ${activity.iconColor}`">
                                <span class="text-lg">{{ activity.icon }}</span>
                            </div>
                            <div class="flex-1">
                                <p class="font-medium">{{ activity.title }}</p>
                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ activity.description }}</p>
                            </div>
                            <span class="text-xs text-gray-500 dark:text-gray-400">{{ activity.time }}</span>
                        </div>
                    </div>
                    <div v-else class="py-6 text-center text-gray-500 dark:text-gray-400">No recent activities.</div>
                </div> -->
            </div>
        </div>
        
        <!-- Error Modal -->
        <div v-if="showErrorModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
            <div class="w-full max-w-md rounded-lg bg-white p-6 shadow-lg dark:bg-gray-800">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-lg font-medium text-red-600 dark:text-red-400">Alert</h3>
                    <button 
                        @click="closeErrorModal" 
                        class="rounded-md p-1 text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-gray-300"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>
                <div class="mb-4">
                    <div class="flex items-center">
                        <div class="mr-3 flex h-10 w-10 items-center justify-center rounded-full bg-red-100 text-red-600 dark:bg-red-900 dark:text-red-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <p class="text-gray-700 dark:text-gray-300">{{ errorMessage }}</p>
                    </div>
                </div>
                <div class="flex justify-end">
                    <button 
                        @click="closeErrorModal" 
                        class="rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-4 focus:ring-red-300 dark:bg-red-600 dark:hover:bg-red-700 dark:focus:ring-red-800"
                    >
                        Close
                    </button>
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
</style>