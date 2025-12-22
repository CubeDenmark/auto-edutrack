<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed, inject, onMounted, ref, watch } from 'vue';

// Define breadcrumbs
const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Parent Portal',
        href: '/parent',
    },
    {
        title: 'Dashboard',
        href: '/parent/dashboard',
    },
];

const props = defineProps<{
    selectedChild?: string | number;
}>();

// Emits for parent component communication
const emit = defineEmits<{
    'update:selectedChild': [value: string | number];
}>();

// Get the page props to access any data passed from the server
const page = usePage();

// Inject the current item ID from the parent component (from sidebar)
const currentItemId = inject('currentItemId', ref(null));

// State variables
const currentChild = ref<any>(null);
const isMobile = ref(false);

// Get data from the controller via page props
const childrenSections = ref(page.props.childrenSections || []);
const upcomingEvents = ref(page.props.upcomingEvents || []);
const teacherMessages = ref(page.props.teacherMessages || []);
const notifications = ref(page.props.notifications || []);

// Transform childrenSections data to match the dashboard's expected format
// const children = computed(() => {
//   return childrenSections.value.map(child => {
//     // Use dynamic total days from backend
//     const totalDays = child.attendanceSummary.total || 0;

//     // Avoid division by 0
//     const presentDays = child.attendanceSummary.present;
//     const absentDays = child.attendanceSummary.absent;
//     const lateDays = child.attendanceSummary.late;

//     return {
//       id: child.id,
//       name: child.name,
//       grade: child.term,
//       avatar: child.avatar,
//       alerts: child.alerts,
//       stats: {
//         gpa: child.stats.gpa,
//         attendance: child.attendanceSummary.present,
//         behavior: child.stats.behavior
//       },
//       recentActivity: child.recentActivity,
//       attendanceSummary: {
//         present: presentDays,
//         absent: absentDays,
//         late: lateDays,
//         total: totalDays
//       },
//       academicPerformance: page.props.academicPerformance?.[child.id] || []
//     };
//   });
// });

const children = computed(() => {
    return childrenSections.value.map((child) => {
        // Calculate the attendance percentage
        const attendancePercentage =
            child.attendanceSummary.total > 0 ? ((child.attendanceSummary.present / child.attendanceSummary.total) * 100).toFixed(2) : 0;
        // In case total is 0, avoid division by zero

        return {
            id: child.id,
            name: child.name,
            grade: child.grade, // use child.grade instead of child.term
            avatar: child.avatar,
            alerts: child.alerts,
            stats: {
                gpa: child.stats.gpa,
                attendance: attendancePercentage, // Updated to show attendance percentage
                behavior: child.stats.behavior,
            },
            recentActivity: child.recentActivity,
            attendanceSummary: {
                present: child.attendanceSummary.present,
                absent: child.attendanceSummary.absent,
                late: child.attendanceSummary.late,
                total: child.attendanceSummary.total,
            },
            academicPerformance: page.props.academicPerformance?.[child.id] || [],
        };
    });
});

// Flag to track if we're showing all children overview
const showAllChildren = ref(true);

// Helper methods
const getGpaColor = (percentage) => {
    const grade = parseFloat(percentageToPhGrade(percentage)); // Get numeric grade

    if (grade <= 1.75) return 'text-green-600 dark:text-green-400'; // Excellent to Very Good
    if (grade <= 2.25) return 'text-blue-600 dark:text-blue-400'; // Good to Above Average
    if (grade <= 3.0) return 'text-yellow-600 dark:text-yellow-400'; // Average to Passing
    return 'text-red-600 dark:text-red-400'; // Conditional / Failing
};

const percentageToPhGrade = (percentage) => {
    if (percentage >= 99 && percentage <= 100) return '1.0'; // Excellent
    if (percentage >= 96 && percentage <= 98) return '1.25';
    if (percentage >= 93 && percentage <= 95) return '1.5';
    if (percentage >= 90 && percentage <= 92) return '1.75'; // Very Good
    if (percentage >= 87 && percentage <= 89) return '2.0';
    if (percentage >= 84 && percentage <= 86) return '2.25'; // Above Average
    if (percentage >= 81 && percentage <= 83) return '2.5';
    if (percentage >= 78 && percentage <= 80) return '2.75'; // Average
    if (percentage >= 75 && percentage <= 77) return '3.0'; // Passing
    return '5.0'; // Conditional
};

const getAttendanceColor = (attendance) => {
    if (attendance >= 95) return 'text-green-600 dark:text-green-400';
    if (attendance >= 90) return 'text-blue-600 dark:text-blue-400';
    if (attendance >= 80) return 'text-yellow-600 dark:text-yellow-400';
    return 'text-red-600 dark:text-red-400';
};

const getBehaviorColor = (behavior) => {
    if (behavior === 'Excellent') return 'text-green-600 dark:text-green-400';
    if (behavior === 'Good') return 'text-blue-600 dark:text-blue-400';
    if (behavior === 'Fair') return 'text-yellow-600 dark:text-yellow-400';
    return 'text-red-600 dark:text-red-400';
};

// Find child by ID
function findChildById(id) {
    return children.value.find((child) => child.id === id || child.id === Number(id)) || null;
}

// Method to select a child
const selectChild = (childId) => {
    if (childId === 'all') {
        currentChild.value = null;
        showAllChildren.value = true;
    } else {
        const child = findChildById(childId);
        if (child) {
            currentChild.value = child;
            showAllChildren.value = false;
        }
    }
    emit('update:selectedChild', childId);
};

// Check if we're on mobile
const checkMobile = () => {
    isMobile.value = window.innerWidth < 768;
};

// Listen for item changes from the sidebar
const handleItemChange = (event) => {
    if (event.detail && event.detail.source === 'sidebar') {
        const itemId = event.detail.itemId;
        const itemType = event.detail.itemType;

        if (itemType === 'child') {
            // Handle child selection
            const child = findChildById(itemId);
            if (child) {
                currentChild.value = child;
                showAllChildren.value = false;
            }
        } else if (itemType === 'overview' && itemId === 'all') {
            // Handle "All Children" selection
            currentChild.value = null;
            showAllChildren.value = true;
        }
    }
};

// Watch for changes in the injected item ID
watch(
    currentItemId,
    (newItemId) => {
        if (newItemId) {
            const child = findChildById(newItemId);
            if (child) {
                currentChild.value = child;
                showAllChildren.value = false;
            }
        } else {
            // If itemId is null or undefined, show all children
            showAllChildren.value = true;
            currentChild.value = null;
        }
    },
    { immediate: true },
);

// Watch for changes in the URL query parameters
watch(
    () => window.location.search,
    (newSearch) => {
        const params = new URLSearchParams(newSearch);
        const childId = params.get('childId');
        const itemId = params.get('itemId');

        // First check for childId (legacy parameter)
        if (childId) {
            const child = findChildById(childId);
            if (child) {
                currentChild.value = child;
                showAllChildren.value = false;
            }
        }
        // Then check for itemId (new parameter from sidebar)
        else if (itemId) {
            const child = findChildById(itemId);
            if (child) {
                currentChild.value = child;
                showAllChildren.value = false;
            } else if (itemId === 'all') {
                currentChild.value = null;
                showAllChildren.value = true;
            }
        } else {
            // Default to showing all children
            currentChild.value = null;
            showAllChildren.value = true;
        }
    },
    { immediate: true },
);

// Lifecycle hooks
onMounted(() => {
    // Check if we're on mobile
    checkMobile();
    window.addEventListener('resize', checkMobile);

    // Add event listener for item changes from sidebar
    window.addEventListener('item-changed', handleItemChange);

    // Initialize child selection based on URL parameters or page props
    const initializeChildSelection = () => {
        const urlParams = new URLSearchParams(window.location.search);
        const childIdFromUrl = urlParams.get('childId');
        const itemIdFromUrl = urlParams.get('itemId');
        const childIdFromProps = page.props.childId;

        let selectedChildId = childIdFromUrl || childIdFromProps || itemIdFromUrl;

        if (selectedChildId) {
            const child = findChildById(selectedChildId);
            if (child) {
                currentChild.value = child;
                showAllChildren.value = false;
            } else if (selectedChildId === 'all') {
                currentChild.value = null;
                showAllChildren.value = true;
            } else {
                // If child ID is invalid, default to showing all children
                currentChild.value = null;
                showAllChildren.value = true;
            }
        } else {
            // Default to showing all children
            currentChild.value = null;
            showAllChildren.value = true;
        }
    };

    initializeChildSelection();

    // Clean up event listeners on component unmount
    return () => {
        window.removeEventListener('resize', checkMobile);
        window.removeEventListener('item-changed', handleItemChange);
    };
});
</script>

<template>
    <Head title="Parent Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 pt-20">
            <!-- Child selector (visible on mobile or when no child is selected) -->
            <div v-if="!currentChild || isMobile" class="mb-6">
                <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
                    <h3 class="mb-4 font-medium">My Children</h3>
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div
                            v-for="(child, index) in children"
                            :key="index"
                            class="cursor-pointer rounded-lg border border-gray-200 p-4 transition-colors hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-700"
                            :class="{ 'dark:ring-primary-400 ring-2 ring-primary-500': currentChild && currentChild.id === child.id }"
                            @click="selectChild(child.id)"
                        >
                            <div class="flex items-center">
                                <!-- Show image if avatar exists, otherwise show first letter of name -->
                                <div v-if="child.avatar">
                                    <img :src="`/${child.avatar}`" class="mr-4 h-12 w-12 rounded-full" />
                                </div>
                                <div v-else class="mr-4 flex h-12 w-12 items-center justify-center rounded-full bg-gray-400 text-white">
                                    {{ child.name.charAt(0).toUpperCase() }}
                                </div>

                                <div>
                                    <h4 class="font-medium">{{ child.name }}</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ child.grade }}</p>
                                </div>

                                <div
                                    v-if="child.alerts"
                                    class="ml-auto flex h-5 w-5 items-center justify-center rounded-full bg-red-500 text-xs font-bold text-white"
                                >
                                    {{ child.alerts }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- All Children Overview (when no child is selected) -->
            <div v-if="showAllChildren">
                <h2 class="mb-4 text-xl font-semibold">Children Overview</h2>
                <div class="mb-6 grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                    <div v-for="child in children" :key="child.id" class="overflow-hidden rounded-lg bg-white shadow dark:bg-gray-800">
                        <div class="flex items-center justify-between border-b p-4 dark:border-gray-700">
                            <div class="flex items-center space-x-3">
                                <!-- If avatar exists, show the image, otherwise show the first letter of the name -->
                                <div v-if="child.avatar">
                                    <img :src="`/${child.avatar}`" :alt="`${child.name} Avatar`" class="h-10 w-10 rounded-full" />
                                </div>
                                <div v-else class="flex h-10 w-10 items-center justify-center rounded-full bg-gray-400 text-white">
                                    {{ child.name.charAt(0).toUpperCase() }}
                                </div>

                                <div>
                                    <h3 class="font-medium">{{ child.name }}</h3>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ child.grade }}</p>
                                </div>
                            </div>

                            <button
                                @click="selectChild(child.id)"
                                class="hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300 text-sm text-primary-600"
                            >
                                View Details
                            </button>
                        </div>
                        <div class="p-4">
                            <div class="mb-4 grid grid-cols-3 gap-4">
                                <div class="text-center">
                                    <p class="text-sm text-gray-500 dark:text-gray-400">GWA</p>
                                    <p class="text-lg font-bold" :class="getGpaColor(child.stats.gpa)">
                                        {{ Number(child.stats.gpa).toFixed(2) }}
                                    </p>
                                </div>
                                <div class="text-center">
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Attendance</p>
                                    <p class="text-lg font-bold" :class="getAttendanceColor(child.stats.attendance)">
                                        {{ parseFloat(child.stats.attendance).toFixed(2) }}%
                                        <!-- Ensures the attendance percentage is displayed with two decimal places -->
                                    </p>
                                </div>

                                <div class="text-center">
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Behavior</p>
                                    <p class="text-lg font-bold" :class="getBehaviorColor(child.stats.behavior)">{{ child.stats.behavior }}</p>
                                </div>
                            </div>
                            <div v-if="child.alerts" class="rounded-md bg-red-50 p-2 text-sm text-red-600 dark:bg-red-900/20 dark:text-red-400">
                                <div class="flex items-center space-x-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
                                        />
                                    </svg>
                                    <span>{{ child.alerts }} alert{{ child.alerts > 1 ? 's' : '' }} requiring attention</span>
                                </div>
                            </div>
                        </div>
                        <!-- <div class="bg-gray-50 p-4 dark:bg-gray-700">
                            <h4 class="mb-2 text-sm font-medium">Recent Activity</h4>
                            <div class="text-sm text-gray-600 dark:text-gray-300">
                                <p>{{ child.recentActivity }}</p>
                            </div>
                        </div> -->
                    </div>
                </div>

                <!-- <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-6">
          <h3 class="font-medium mb-4">Upcoming Events</h3>
          <div class="space-y-4">
            <div v-for="(event, index) in upcomingEvents" :key="index" class="flex items-start space-x-4 pb-4 border-b dark:border-gray-700 last:border-0 last:pb-0">
              <div class="w-12 h-12 rounded-lg flex flex-col items-center justify-center bg-primary-100 dark:bg-primary-900 text-primary-600 dark:text-primary-400">
                <span class="text-sm font-bold">{{ event.date.split(' ')[0] }}</span>
                <span class="text-xs">{{ event.date.split(' ')[1] }}</span>
              </div>
              <div class="flex-1">
                <p class="font-medium">{{ event.title }}</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ event.description }}</p>
              </div>
              <button class="px-3 py-1 text-xs rounded-full bg-primary-50 text-primary-600 dark:bg-gray-700 dark:text-primary-400 hover:bg-primary-100 dark:hover:bg-gray-600">
                {{ event.action }}
              </button>
            </div>
          </div>
        </div> -->

                <!-- <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
          <div class="flex items-center justify-between mb-4">
            <h3 class="font-medium">Recent Notifications</h3>
            <Link href="/parent/notifications" class="text-sm text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300">
              View All
            </Link>
          </div>
          <div class="space-y-4">
            <div v-for="(notification, index) in notifications" :key="index"
              :class="[
                'p-4 rounded-lg',
                notification.unread ? 'bg-blue-50 dark:bg-blue-900/10' : 'bg-gray-50 dark:bg-gray-700/50'
              ]"
            >
              <div class="flex items-start space-x-4">
                <div :class="`w-10 h-10 rounded-full flex items-center justify-center ${notification.iconBg} ${notification.iconColor}`">
                  <span class="text-lg">{{ notification.icon }}</span>
                </div>
                <div class="flex-1">
                  <div class="flex items-start justify-between mb-1">
                    <h4 class="font-medium">{{ notification.title }}</h4>
                    <span class="text-xs text-gray-500 dark:text-gray-400">{{ notification.time }}</span>
                  </div>
                  <p class="text-sm text-gray-600 dark:text-gray-300">{{ notification.message }}</p>
                  <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    For: {{ notification.forChild }}
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div> -->
            </div>

            <!-- Individual Child Dashboard -->
            <div v-else-if="currentChild">
                <div class="mb-6 flex items-center">
                    <!-- If avatar exists, show the image, otherwise show the first letter of the name -->
                    <div v-if="currentChild.avatar">
                        <img :src="`/${currentChild.avatar}`" :alt="`${currentChild.name} Avatar`" class="mr-4 h-16 w-16 rounded-full" />
                    </div>
                    <div v-else class="mr-4 flex h-16 w-16 items-center justify-center rounded-full bg-gray-400 text-white">
                        {{ currentChild.name.charAt(0).toUpperCase() }}
                    </div>

                    <div>
                        <h2 class="text-xl font-semibold">{{ currentChild.name }}</h2>
                        <p class="text-gray-500 dark:text-gray-400">{{ currentChild.grade }}</p>
                    </div>

                    <button
                        @click="selectChild('all')"
                        class="hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300 ml-auto text-sm text-primary-600"
                    >
                        Back to Overview
                    </button>
                </div>

                <div class="mb-6 grid grid-cols-1 gap-6 md:grid-cols-3">
                    <!-- Attendance Card -->
                    <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
                        <div class="mb-4 flex items-center justify-between">
                            <h3 class="font-medium">Attendance</h3>
                            <span class="rounded-full bg-green-100 px-2 py-1 text-xs font-medium text-green-500 dark:bg-green-900"
                                >{{ currentChild.stats.attendance }}%</span
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
                                <p class="text-2xl font-bold">
                                    {{ currentChild.attendanceSummary.present }}/{{ currentChild.attendanceSummary.total }}
                                </p>
                            </div>
                        </div>
                        <div class="mt-4">
                            <Link
                                :href="`/parent/attendance?childId=${currentChild.id}`"
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
                            <span
                                :class="`rounded-full bg-blue-100 px-2 py-1 text-xs font-medium dark:bg-blue-900 ${getGpaColor(currentChild.stats.gpa)}`"
                            >
                                {{ percentageToPhGrade(currentChild.stats.gpa) }}
                            </span>
                        </div>
                        <div class="flex items-center space-x-4">
                            <div class="flex h-16 w-16 items-center justify-center rounded-full bg-blue-100 text-blue-500 dark:bg-blue-900">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path d="M12 14l9-5-9-5-9 5 9 5z" />
                                    <path
                                        d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998a12.078 12.078 0 01.665-6.479L12 14z"
                                    />
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222"
                                    />
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Current GWA</p>
                                <p class="text-2xl font-bold">
                                    {{ Number(currentChild.stats.gpa).toFixed(2) }}
                                </p>
                            </div>
                        </div>
                        <div class="mt-4">
                            <Link
                                :href="`/parent/grades?childId=${currentChild.id}`"
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
                                currentChild.stats.behavior
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
                                <p class="text-2xl font-bold">{{ currentChild.alerts || 0 }}</p>
                            </div>
                        </div>
                        <div class="mt-4">
                            <Link
                                :href="`/parent/behavior-reports?childId=${currentChild.id}`"
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
                            <div class="w-full space-y-4 px-8">
                                <div v-for="(subject, index) in currentChild.academicPerformance" :key="index">
                                    <div class="mb-1 flex justify-between">
                                        <span class="text-sm font-medium">{{ subject.subject }}</span>
                                        <span class="text-sm font-medium">{{ subject.percentage }}%</span>
                                    </div>
                                    <div class="h-2.5 w-full rounded-full bg-gray-200 dark:bg-gray-600">
                                        <div class="h-2.5 rounded-full bg-blue-600" :style="`width: ${subject.percentage}%`"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Upcoming Events -->
                <!-- <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-6">
          <h3 class="font-medium mb-4">Upcoming Events</h3>
          <div class="space-y-4">
            <div v-for="(event, index) in upcomingEvents" :key="index" class="flex items-start space-x-4 pb-4 border-b dark:border-gray-700 last:border-0 last:pb-0">
              <div class="w-12 h-12 rounded-lg flex flex-col items-center justify-center bg-primary-100 dark:bg-primary-900 text-primary-600 dark:text-primary-400">
                <span class="text-sm font-bold">{{ event.date.split(' ')[0] }}</span>
                <span class="text-xs">{{ event.date.split(' ')[1] }}</span>
              </div>
              <div class="flex-1">
                <p class="font-medium">{{ event.title }}</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ event.description }}</p>
              </div>
              <button class="px-3 py-1 text-xs rounded-full bg-primary-50 text-primary-600 dark:bg-gray-700 dark:text-primary-400 hover:bg-primary-100 dark:hover:bg-gray-600">
                {{ event.action }}
              </button>
            </div>
          </div>
        </div> -->

                <!-- Teacher Messages -->
                <!-- <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
          <div class="flex items-center justify-between mb-4">
            <h3 class="font-medium">Recent Teacher Messages</h3>
            <a href="#" class="text-sm text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300">
              View All
            </a>
          </div>
          <div class="space-y-4">
            <div v-for="(message, index) in teacherMessages" :key="index" class="flex items-start space-x-4 pb-4 border-b dark:border-gray-700 last:border-0 last:pb-0">
              <img :src="message.teacherAvatar" alt="Teacher Avatar" class="w-10 h-10 rounded-full" />
              <div class="flex-1">
                <div class="flex items-center justify-between mb-1">
                  <p class="font-medium">{{ message.teacherName }} <span class="text-sm font-normal text-gray-500 dark:text-gray-400">({{ message.subject }})</span></p>
                  <span class="text-xs text-gray-500 dark:text-gray-400">{{ message.time }}</span>
                </div>
                <p class="text-sm text-gray-600 dark:text-gray-300">{{ message.content }}</p>
              </div>
            </div>
          </div>
        </div> -->
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
