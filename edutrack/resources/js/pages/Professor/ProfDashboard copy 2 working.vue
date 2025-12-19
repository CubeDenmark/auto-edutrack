<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, usePage } from '@inertiajs/vue3';
import { AlertTriangle, BookOpen, CheckCircle, ChevronDown, Clock, FileText, Users } from 'lucide-vue-next';
import { computed, inject, onMounted, ref, watch } from 'vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

defineProps<{
    name?: string;
    sectionId?: string;
}>();

// Get the page props to access data passed from the server
const page = usePage();
const sections = computed(() => page.props.sections || []);
const topPerformersByClassAndTerm = computed(() => page.props.topPerformersByClassAndTerm || {});
const totalStudents = computed(() => page.props.totalStudents || 0);
const attendanceStats = computed(() => page.props.attendanceStats || {});
const recentBehaviorReports = computed(() => page.props.recentBehaviorReports || []);

// Inject the current item ID from the parent component
const currentItemId = inject('currentItemId', ref(null));

// State variables
const currentSection = ref<any>(null);
const showSectionDropdown = ref(false);
const currentTerm = ref<string | null>(null);
const currentAssessmentType = ref<string | null>(null);

// Get top performers for the current section and term
const currentSectionTopPerformers = computed(() => {
    if (!currentSection.value) return {};
    return topPerformersByClassAndTerm.value[currentSection.value.id] || {};
});

// Get available terms for the current section
const availableTerms = computed(() => {
    return Object.keys(currentSectionTopPerformers.value || {});
});

// Get available assessment types for the current section and term
const availableAssessmentTypes = computed(() => {
    if (!currentSection.value || !currentTerm.value) return [];
    const termData = currentSectionTopPerformers.value[currentTerm.value];
    return termData ? Object.keys(termData) : [];
});

// Get top performers for the current section, term, and assessment type
const currentTopPerformers = computed(() => {
    if (!currentSection.value || !currentTerm.value || !currentAssessmentType.value) return [];
    const termData = currentSectionTopPerformers.value[currentTerm.value];
    if (!termData) return [];
    return termData[currentAssessmentType.value] || [];
});

// Get attendance stats for the current section
const currentAttendanceStats = computed(() => {
    if (!currentSection.value) return { present: 0, absent: 0, attendanceRate: 0 };
    return attendanceStats.value[currentSection.value.id] || { present: 0, absent: 0, attendanceRate: 0 };
});

// Set default values when section changes
watch(
    currentSection,
    (newSection) => {
        if (newSection) {
            // Reset term and assessment type when section changes
            currentTerm.value = availableTerms.value.length > 0 ? availableTerms.value[0] : null;

            // After setting the term, set the assessment type
            if (currentTerm.value && availableAssessmentTypes.value.length > 0) {
                currentAssessmentType.value = availableAssessmentTypes.value[0];
            } else {
                currentAssessmentType.value = null;
            }
        }
    },
    { immediate: true },
);

// Update assessment type when term changes
watch(currentTerm, (newTerm) => {
    if (newTerm && availableAssessmentTypes.value.length > 0) {
        currentAssessmentType.value = availableAssessmentTypes.value[0];
    } else {
        currentAssessmentType.value = null;
    }
});

// Find section by ID
function findSectionById(id) {
    return sections.value.find((section) => section.id === id) || null;
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
    window.dispatchEvent(
        new CustomEvent('section-changed', {
            detail: {
                sectionId: section.id,
                source: 'dashboard',
            },
        }),
    );

    // Also dispatch the item-changed event for newer sidebar implementations
    window.dispatchEvent(
        new CustomEvent('item-changed', {
            detail: {
                itemId: section.id,
                itemType: 'section',
                source: 'dashboard',
            },
        }),
    );
}

// Get the first letter of a name for profile placeholder
function getNameInitial(name) {
    return name ? name.charAt(0).toUpperCase() : '?';
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
    if (event.detail && event.detail.sectionId && event.detail.source !== 'dashboard') {
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

    let initialSectionSet = false;

    // Define a function to set the current section
    const setSection = (sectionId) => {
        const section = findSectionById(sectionId);
        if (section) {
            currentSection.value = section;
            initialSectionSet = true;
        }
    };

    // Prioritize sectionId from URL or props
    if (sectionIdFromUrl || sectionIdFromProps) {
        const sectionId = sectionIdFromProps || sectionIdFromUrl;
        setSection(sectionId);
    }
    // Then check for itemId (which could be a section ID from sidebar)
    else if (itemIdFromUrl) {
        setSection(itemIdFromUrl);
    }
    // Finally check for injected item ID
    else if (currentItemId.value) {
        setSection(currentItemId.value);
    }

    // If we have sections but no selected section, select the first one
    if (sections.value.length > 0 && !currentSection.value && !initialSectionSet) {
        currentSection.value = sections.value[0];
    }

    // Clean up event listeners on component unmount
    return () => {
        window.removeEventListener('item-changed', handleItemChange);
        window.removeEventListener('section-changed', handleSectionChange);
    };
});
</script>

<template>
    <Head title="Professor Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 pt-20">
            <!-- No Section Selected Message -->
            <div v-if="!currentSection && sections.length > 0" class="flex h-[calc(100vh-10rem)] flex-col items-center justify-center text-center">
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
                <h2 class="mb-2 text-2xl font-bold">Select a Class Section</h2>
                <p class="mb-6 max-w-md text-gray-500 dark:text-gray-400">
                    Please select a class section from the sidebar or use the dropdown menu to view class-specific information.
                </p>
                <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
                    <button
                        v-for="section in sections.slice(0, 4)"
                        :key="section.id"
                        @click="selectSection(section)"
                        class="flex flex-col items-center rounded-lg border border-gray-200 bg-white p-4 transition-all hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:hover:bg-gray-700"
                    >
                        <span class="mb-2 text-3xl">{{ section.icon }}</span>
                        <span class="text-sm font-medium">{{ section.name }}</span>
                    </button>
                </div>
            </div>

            <!-- No Classes Message -->
            <div v-if="sections.length === 0" class="flex h-[calc(100vh-10rem)] flex-col items-center justify-center text-center">
                <div class="mb-4 rounded-full bg-gray-100 p-6 dark:bg-gray-800">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
                        />
                    </svg>
                </div>
                <h2 class="mb-2 text-2xl font-bold">No Classes Found</h2>
                <p class="mb-6 max-w-md text-gray-500 dark:text-gray-400">
                    You don't have any classes assigned to you yet. Please contact the administrator if you believe this is an error.
                </p>
            </div>

            <!-- Dashboard Page -->
            <div v-if="currentSection" class="space-y-6">
                <!-- Header with Class Selection -->
                <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                    <div class="flex items-center gap-3">
                        <div
                            class="flex h-12 w-12 items-center justify-center rounded-lg bg-indigo-100 text-2xl text-indigo-600 dark:bg-indigo-900 dark:text-indigo-300"
                        >
                            {{ currentSection.icon }}
                        </div>
                        <div>
                            <h1 class="text-2xl font-bold">{{ currentSection.name }}</h1>
                            <p class="text-gray-500 dark:text-gray-400">{{ currentSection.term }}</p>
                        </div>
                    </div>

                    <div class="relative">
                        <button
                            @click="showSectionDropdown = !showSectionDropdown"
                            class="flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-4 py-2 shadow-sm transition-colors hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:hover:bg-gray-700"
                        >
                            <span>Change Section</span>
                            <ChevronDown class="h-4 w-4" />
                        </button>
                        <div
                            v-if="showSectionDropdown"
                            class="absolute right-0 z-10 mt-2 w-64 rounded-lg border border-gray-200 bg-white shadow-lg dark:border-gray-700 dark:bg-gray-800"
                        >
                            <div class="p-2">
                                <div class="mb-2 px-2 text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Select Class</div>
                                <button
                                    v-for="section in sections"
                                    :key="section.id"
                                    @click="
                                        selectSection(section);
                                        showSectionDropdown = false;
                                    "
                                    class="flex w-full items-center gap-3 rounded-md p-2 text-left transition-colors hover:bg-gray-100 dark:hover:bg-gray-700"
                                    :class="{ 'bg-indigo-50 dark:bg-indigo-900/20': currentSection.id === section.id }"
                                >
                                    <span class="text-xl">{{ section.icon }}</span>
                                    <div class="min-w-0 flex-1">
                                        <p class="truncate font-medium">{{ section.name }}</p>
                                        <p class="truncate text-xs text-gray-500 dark:text-gray-400">{{ section.term }}</p>
                                    </div>
                                    <span
                                        class="rounded-full bg-indigo-100 px-2 py-1 text-xs font-medium text-indigo-600 dark:bg-indigo-900 dark:text-indigo-300"
                                    >
                                        {{ section.studentCount }} students
                                    </span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Stats Cards -->
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    <!-- Total Students Card -->
                    <div
                        class="rounded-lg border border-gray-100 bg-white p-6 shadow-sm transition-all hover:shadow-md dark:border-gray-700 dark:bg-gray-800"
                    >
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Students</p>
                                <p class="mt-1 text-3xl font-bold">{{ currentSection.studentCount }}</p>
                            </div>
                            <div class="rounded-lg bg-blue-100 p-2 dark:bg-blue-900/30">
                                <Users class="h-6 w-6 text-blue-600 dark:text-blue-400" />
                            </div>
                        </div>
                        <div class="mt-4 flex items-center text-sm">
                            <span class="text-gray-500 dark:text-gray-400">Class capacity</span>
                            <div class="ml-auto flex items-center gap-1.5">
                                <div class="h-2 w-16 overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700">
                                    <div
                                        class="h-full rounded-full bg-blue-500"
                                        :style="`width: ${Math.min((currentSection.studentCount / 40) * 100, 100)}%`"
                                    ></div>
                                </div>
                                <span class="text-xs font-medium">{{ Math.round((currentSection.studentCount / 40) * 100) }}%</span>
                            </div>
                        </div>
                    </div>

                    <!-- Attendance Rate Card -->
                    <div
                        class="rounded-lg border border-gray-100 bg-white p-6 shadow-sm transition-all hover:shadow-md dark:border-gray-700 dark:bg-gray-800"
                    >
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Attendance Rate</p>
                                <p class="mt-1 text-3xl font-bold">{{ currentAttendanceStats.attendanceRate }}%</p>
                            </div>
                            <div class="rounded-lg bg-green-100 p-2 dark:bg-green-900/30">
                                <CheckCircle class="h-6 w-6 text-green-600 dark:text-green-400" />
                            </div>
                        </div>
                        <div class="mt-4 flex items-center text-sm">
                            <span class="text-gray-500 dark:text-gray-400">Present / Absent</span>
                            <div class="ml-auto flex items-center gap-2">
                                <span class="font-medium text-green-600 dark:text-green-400">{{ currentAttendanceStats.present }}</span>
                                <span class="text-gray-400">/</span>
                                <span class="font-medium text-red-600 dark:text-red-400">{{ currentAttendanceStats.absent }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Top Performers Card -->
                    <div
                        class="rounded-lg border border-gray-100 bg-white p-6 shadow-sm transition-all hover:shadow-md dark:border-gray-700 dark:bg-gray-800"
                    >
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Top Performers</p>
                                <p class="mt-1 text-3xl font-bold">{{ currentTopPerformers.length }}</p>
                            </div>
                            <div class="rounded-lg bg-purple-100 p-2 dark:bg-purple-900/30">
                                <BookOpen class="h-6 w-6 text-purple-600 dark:text-purple-400" />
                            </div>
                        </div>
                        <div class="mt-4 flex items-center text-sm">
                            <select
                                v-model="currentTerm"
                                class="border-none bg-transparent p-0 pr-6 text-sm text-gray-500 focus:ring-0 dark:text-gray-400"
                            >
                                <option v-for="term in availableTerms" :key="term" :value="term">{{ term }}</option>
                            </select>
                            <div class="ml-auto">
                                <span
                                    class="rounded-full bg-purple-100 px-2 py-1 text-xs font-medium text-purple-600 dark:bg-purple-900/30 dark:text-purple-400"
                                >
                                    View All
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Reports Card -->
                    <div
                        class="rounded-lg border border-gray-100 bg-white p-6 shadow-sm transition-all hover:shadow-md dark:border-gray-700 dark:bg-gray-800"
                    >
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Behavior Reports</p>
                                <p class="mt-1 text-3xl font-bold">{{ recentBehaviorReports.length }}</p>
                            </div>
                            <div class="rounded-lg bg-amber-100 p-2 dark:bg-amber-900/30">
                                <AlertTriangle class="h-6 w-6 text-amber-600 dark:text-amber-400" />
                            </div>
                        </div>
                        <div class="mt-4 flex items-center text-sm">
                            <span class="text-gray-500 dark:text-gray-400">Recent reports</span>
                            <div class="ml-auto">
                                <span
                                    class="rounded-full bg-amber-100 px-2 py-1 text-xs font-medium text-amber-600 dark:bg-amber-900/30 dark:text-amber-400"
                                >
                                    Last 7 days
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Top Performers and Behavior Reports -->
                <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                    <!-- Top Performers -->
                    <div class="overflow-hidden rounded-lg border border-gray-100 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4 dark:border-gray-700">
                            <h2 class="text-lg font-semibold">Top Performers</h2>
                            <div class="flex items-center gap-2">
                                <select
                                    v-model="currentTerm"
                                    class="rounded-md border border-gray-200 bg-transparent text-sm focus:ring-indigo-500 dark:border-gray-700 dark:focus:ring-indigo-400"
                                >
                                    <option v-for="term in availableTerms" :key="term" :value="term">{{ term }}</option>
                                </select>
                                <select
                                    v-model="currentAssessmentType"
                                    class="rounded-md border border-gray-200 bg-transparent text-sm focus:ring-indigo-500 dark:border-gray-700 dark:focus:ring-indigo-400"
                                >
                                    <option v-for="type in availableAssessmentTypes" :key="type" :value="type">{{ type }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="divide-y divide-gray-100 dark:divide-gray-700">
                            <div
                                v-if="!currentTerm || !currentAssessmentType || currentTopPerformers.length === 0"
                                class="py-8 text-center text-gray-500 dark:text-gray-400"
                            >
                                No data available for this term and assessment type
                            </div>
                            <div
                                v-for="(performer, index) in currentTopPerformers"
                                :key="index"
                                class="flex items-center gap-4 p-4 transition-colors hover:bg-gray-50 dark:hover:bg-gray-700"
                            >
                                <div
                                    class="flex h-10 w-10 flex-shrink-0 items-center justify-center overflow-hidden rounded-full bg-indigo-100 font-bold text-indigo-600 dark:bg-indigo-900 dark:text-indigo-300"
                                >
                                    <img v-if="performer.avatar" :src="`/${performer.avatar}`" alt="Profile" class="h-full w-full object-cover" />
                                    <span v-else>{{ getNameInitial(performer.student_name) }}</span>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="truncate font-medium text-gray-900 dark:text-gray-100">{{ performer.student_name }}</p>
                                    <p class="truncate text-sm text-gray-500 dark:text-gray-400">{{ performer.email }}</p>
                                </div>
                                <div class="flex-shrink-0">
                                    <span
                                        class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                                        :class="{
                                            'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300': performer.grade >= 90,
                                            'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300':
                                                performer.grade >= 80 && performer.grade < 90,
                                            'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300':
                                                performer.grade >= 70 && performer.grade < 80,
                                            'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300': performer.grade < 70,
                                        }"
                                    >
                                        {{ performer.grade }}%
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="dark:bg-gray-750 border-t border-gray-100 bg-gray-50 px-6 py-3 dark:border-gray-700">
                            <a
                                href="/prof/grades"
                                class="text-sm font-medium text-indigo-600 hover:text-indigo-700 dark:text-indigo-400 dark:hover:text-indigo-300"
                            >
                                View all grades →
                            </a>
                        </div>
                    </div>

                    <!-- Recent Behavior Reports -->
                    <div class="overflow-hidden rounded-lg border border-gray-100 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <div class="border-b border-gray-100 px-6 py-4 dark:border-gray-700">
                            <h2 class="text-lg font-semibold">Recent Behavior Reports</h2>
                        </div>
                        <div class="divide-y divide-gray-100 dark:divide-gray-700">
                            <div v-if="recentBehaviorReports.length === 0" class="py-8 text-center text-gray-500 dark:text-gray-400">
                                No recent behavior reports
                            </div>
                            <div
                                v-for="(report, index) in recentBehaviorReports"
                                :key="index"
                                class="dark:hover:bg-gray-750 p-4 transition-colors hover:bg-gray-50"
                            >
                                <div class="mb-2 flex items-center justify-between">
                                    <h3 class="font-medium">{{ report.student }}</h3>
                                    <span
                                        class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                                        :class="{
                                            'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300': report.type === 'Serious',
                                            'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300': report.type === 'Moderate',
                                            'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300': report.type === 'Minor',
                                            'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300': report.type === 'General',
                                        }"
                                    >
                                        {{ report.type }}
                                    </span>
                                </div>
                                <p class="mb-2 text-sm text-gray-600 dark:text-gray-300">{{ report.description }}</p>
                                <div class="flex items-center text-xs text-gray-500 dark:text-gray-400">
                                    <Clock class="mr-1 h-3 w-3" />
                                    <span>{{ report.date }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="dark:bg-gray-750 border-t border-gray-100 bg-gray-50 px-6 py-3 dark:border-gray-700">
                            <a
                                href="/prof/behavior_reports"
                                class="text-sm font-medium text-indigo-600 hover:text-indigo-700 dark:text-indigo-400 dark:hover:text-indigo-300"
                            >
                                View all reports →
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Attendance Stats -->
                <div class="rounded-lg border border-gray-100 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <h2 class="mb-4 text-lg font-semibold">Attendance Statistics</h2>
                    <div class="space-y-4">
                        <div class="mb-2 flex items-center justify-between">
                            <div>
                                <span class="text-sm font-medium">Present</span>
                                <span class="ml-2 text-xs text-gray-500 dark:text-gray-400"> {{ currentAttendanceStats.present }} students </span>
                            </div>
                            <span class="text-sm font-medium">
                                {{
                                    Math.round(
                                        (currentAttendanceStats.present / (currentAttendanceStats.present + currentAttendanceStats.absent || 1)) *
                                            100,
                                    )
                                }}%
                            </span>
                        </div>
                        <div class="h-2.5 w-full rounded-full bg-gray-200 dark:bg-gray-700">
                            <div
                                class="h-2.5 rounded-full bg-green-500"
                                :style="`width: ${Math.round((currentAttendanceStats.present / (currentAttendanceStats.present + currentAttendanceStats.absent || 1)) * 100)}%`"
                            ></div>
                        </div>

                        <div class="mb-2 flex items-center justify-between">
                            <div>
                                <span class="text-sm font-medium">Absent</span>
                                <span class="ml-2 text-xs text-gray-500 dark:text-gray-400"> {{ currentAttendanceStats.absent }} students </span>
                            </div>
                            <span class="text-sm font-medium">
                                {{
                                    Math.round(
                                        (currentAttendanceStats.absent / (currentAttendanceStats.present + currentAttendanceStats.absent || 1)) * 100,
                                    )
                                }}%
                            </span>
                        </div>
                        <div class="h-2.5 w-full rounded-full bg-gray-200 dark:bg-gray-700">
                            <div
                                class="h-2.5 rounded-full bg-red-500"
                                :style="`width: ${Math.round((currentAttendanceStats.absent / (currentAttendanceStats.present + currentAttendanceStats.absent || 1)) * 100)}%`"
                            ></div>
                        </div>

                        <div class="mt-4 text-center">
                            <a
                                href="/prof/attendance"
                                class="text-sm font-medium text-indigo-600 hover:text-indigo-700 dark:text-indigo-400 dark:hover:text-indigo-300"
                            >
                                View detailed attendance →
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="rounded-lg border border-gray-100 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <h2 class="mb-4 text-lg font-semibold">Quick Actions</h2>
                    <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
                        <a
                            href="/prof/attendance"
                            class="flex flex-col items-center rounded-lg border border-gray-200 bg-white p-4 transition-all hover:bg-gray-50 hover:shadow-md dark:border-gray-700 dark:bg-gray-800 dark:hover:bg-gray-700"
                        >
                            <div
                                class="mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-green-100 text-green-600 dark:bg-green-900/30 dark:text-green-400"
                            >
                                <CheckCircle class="h-6 w-6" />
                            </div>
                            <span class="text-sm font-medium">Take Attendance</span>
                        </a>
                        <a
                            href="/prof/grades"
                            class="flex flex-col items-center rounded-lg border border-gray-200 bg-white p-4 transition-all hover:bg-gray-50 hover:shadow-md dark:border-gray-700 dark:bg-gray-800 dark:hover:bg-gray-700"
                        >
                            <div
                                class="mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-purple-100 text-purple-600 dark:bg-purple-900/30 dark:text-purple-400"
                            >
                                <FileText class="h-6 w-6" />
                            </div>
                            <span class="text-sm font-medium">Grade Assignments</span>
                        </a>
                        <a
                            href="/prof/behavior_reports"
                            class="flex flex-col items-center rounded-lg border border-gray-200 bg-white p-4 transition-all hover:bg-gray-50 hover:shadow-md dark:border-gray-700 dark:bg-gray-800 dark:hover:bg-gray-700"
                        >
                            <div
                                class="mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-amber-100 text-amber-600 dark:bg-amber-900/30 dark:text-amber-400"
                            >
                                <AlertTriangle class="h-6 w-6" />
                            </div>
                            <span class="text-sm font-medium">Behavior Reports</span>
                        </a>
                        <a
                            href="/prof/students"
                            class="flex flex-col items-center rounded-lg border border-gray-200 bg-white p-4 transition-all hover:bg-gray-50 hover:shadow-md dark:border-gray-700 dark:bg-gray-800 dark:hover:bg-gray-700"
                        >
                            <div
                                class="mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-blue-100 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400"
                            >
                                <Users class="h-6 w-6" />
                            </div>
                            <span class="text-sm font-medium">Student Profiles</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style>
:root {
    --primary-50: 238, 242, 255;
    --primary-100: 224, 231, 255;
    --primary-200: 199, 210, 254;
    --primary-300: 165, 180, 252;
    --primary-400: 129, 140, 248;
    --primary-500: 99, 102, 241;
    --primary-600: 79, 70, 229;
    --primary-700: 67, 56, 202;
    --primary-800: 55, 48, 163;
    --primary-900: 49, 46, 129;
}

/* Dark mode specific styles */
.dark .dark\:bg-gray-750 {
    background-color: rgb(31, 35, 45);
}

/* Transitions */
.transition-all {
    transition-property: all;
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
    transition-duration: 150ms;
}

/* Hover effects */
.hover\:shadow-md:hover {
    box-shadow:
        0 4px 6px -1px rgba(0, 0, 0, 0.1),
        0 2px 4px -1px rgba(0, 0, 0, 0.06);
}
</style>
