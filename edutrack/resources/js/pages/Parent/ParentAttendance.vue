<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import { computed, inject, onMounted, ref, watch } from 'vue';

// Breadcrumbs
const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Attendance',
        href: '/parent/attendance',
    },
];

// Get the page props to access any data passed from the server
const page = usePage();

// Inject the current item ID from the parent component (from sidebar)
const currentItemId = inject('currentItemId', ref(null));

// State variables
const currentChild = ref<any>(null);
const selectedMonth = ref(new Date().getMonth() + 1); // API expects 1-12 for months
const selectedYear = ref(new Date().getFullYear());
const loading = ref(false);
const error = ref(null);

// Pagination for attendance records
const currentPage = ref(1);
const recordsPerPage = ref(10);

// Children data - initialize from props passed by the controller
const children = ref(page.props.initialChildren || []);

// Flag to track if we're showing all children overview
const showAllChildren = ref(true);

// Filter states
const statusFilter = ref('all');
const classFilter = ref('all');
const availableClasses = ref<string[]>([]);

// Helper function for status styling
const getStatusClass = (status) => {
    switch (status) {
        case 'present':
            return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300';
        case 'absent':
            return 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300';
        case 'late':
            return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300';
        case 'excused':
            return 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300';
        default:
            return 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300';
    }
};

// Computed properties
const filteredAttendanceRecords = computed(() => {
    if (!currentChild.value || !currentChild.value.attendanceRecords) return [];

    let records = currentChild.value.attendanceRecords;

    // Apply status filter
    if (statusFilter.value !== 'all') {
        records = records.filter((record) => record.status === statusFilter.value);
    }

    // Apply class filter
    if (classFilter.value !== 'all') {
        records = records.filter((record) => record.class === classFilter.value);
    }

    return records;
});

const paginatedAttendanceRecords = computed(() => {
    const startIndex = (currentPage.value - 1) * recordsPerPage.value;
    const endIndex = startIndex + recordsPerPage.value;
    return filteredAttendanceRecords.value.slice(startIndex, endIndex);
});

const totalPages = computed(() => {
    return Math.ceil(filteredAttendanceRecords.value.length / recordsPerPage.value) || 1;
});

const monthName = computed(() => {
    return new Date(selectedYear.value, selectedMonth.value - 1).toLocaleString('default', { month: 'long', year: 'numeric' });
});

// Computed property to limit the number of months shown in the trend card
const limitedMonthlyAttendance = computed(() => {
    if (!currentChild.value || !currentChild.value.monthlyAttendance) return [];
    // Only show the last 5 months to prevent overflow
    return currentChild.value.monthlyAttendance.slice(-5);
});

// Computed property for attendance statistics by status
const attendanceStats = computed(() => {
    if (!currentChild.value || !currentChild.value.attendanceRecords) return [];

    const stats = [
        { name: 'Present', value: 0, color: 'bg-green-500' },
        { name: 'Absent', value: 0, color: 'bg-red-500' },
        { name: 'Late', value: 0, color: 'bg-yellow-500' },
        { name: 'Excused', value: 0, color: 'bg-blue-500' },
    ];

    // Count occurrences of each status
    currentChild.value.attendanceRecords.forEach((record) => {
        switch (record.status) {
            case 'present':
                stats[0].value++;
                break;
            case 'absent':
                stats[1].value++;
                break;
            case 'late':
                stats[2].value++;
                break;
            case 'excused':
                stats[3].value++;
                break;
        }
    });

    // Calculate total for percentages
    const total = stats.reduce((sum, stat) => sum + stat.value, 0);

    // Add percentage to each stat
    stats.forEach((stat) => {
        stat.percentage = total > 0 ? Math.round((stat.value / total) * 100) : 0;
    });

    return stats;
});

// Methods
function findChildById(id) {
    return children.value.find((child) => child.id === id || child.id === Number(id)) || null;
}

// Extract unique classes from attendance records
function extractAvailableClasses(records) {
    if (!records || !records.length) return ['All Classes'];

    const classes = new Set();
    records.forEach((record) => {
        if (record.class) {
            classes.add(record.class);
        }
    });

    return ['all', ...Array.from(classes)];
}

// Reset filters
function resetFilters() {
    statusFilter.value = 'all';
    classFilter.value = 'all';
    currentPage.value = 1;
}

// Fetch attendance data for a specific child
async function fetchChildAttendance(childId) {
    if (!childId) return;

    loading.value = true;
    error.value = null;

    try {
        const response = await axios.get(`/parent/attendance/child/${childId}`, {
            params: {
                month: selectedMonth.value,
                year: selectedYear.value,
            },
        });

        // Check if attendanceRecords exists in the response
        if (!response.data.attendanceRecords) {
            console.error('No attendanceRecords in API response');
            error.value = 'Attendance records not found in server response';
            return;
        }

        // Update the current child with the fetched data
        currentChild.value = {
            ...response.data.child,
            attendanceRecords: response.data.attendanceRecords || [],
            attendanceSummary: response.data.attendanceSummary || {
                present: 0,
                absent: 0,
                late: 0,
                excused: 0,
                total: 0,
            },
            monthlyAttendance: response.data.monthlyAttendance || [],
        };

        // Extract available classes for filtering
        availableClasses.value = extractAvailableClasses(currentChild.value.attendanceRecords);

        // Update the child in the children array to keep it in sync
        const childIndex = children.value.findIndex((c) => c.id === childId);
        if (childIndex !== -1) {
            children.value[childIndex] = { ...children.value[childIndex], ...currentChild.value };
        }

        // Reset filters and pagination
        resetFilters();
    } catch (err) {
        console.error('Error fetching child attendance:', err);
        error.value = 'Failed to load attendance data. Please try again.';
    } finally {
      loading.value = false;
    }
}

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
                fetchChildAttendance(itemId);
            }
        } else if (itemType === 'overview' && itemId === 'all') {
            // Handle "All Children" selection
            currentChild.value = null;
            showAllChildren.value = true;
        }
    }
};

// Navigation methods
const previousMonth = async () => {
    if (selectedMonth.value === 1) {
        selectedMonth.value = 12;
        selectedYear.value--;
    } else {
        selectedMonth.value--;
    }

    // Refetch data for the new month if a child is selected
    if (currentChild.value && !showAllChildren.value) {
        await fetchChildAttendance(currentChild.value.id);
    }
};

const nextMonth = async () => {
    if (selectedMonth.value === 12) {
        selectedMonth.value = 1;
        selectedYear.value++;
    } else {
        selectedMonth.value++;
    }

    // Refetch data for the new month if a child is selected
    if (currentChild.value && !showAllChildren.value) {
        await fetchChildAttendance(currentChild.value.id);
    }
};

const goToPage = (page) => {
    currentPage.value = page;
};

const previousPage = () => {
    if (currentPage.value > 1) {
        currentPage.value--;
    }
};

const nextPage = () => {
    if (currentPage.value < totalPages.value) {
        currentPage.value++;
    }
};

// Calculate attendance percentage with proper validation
const calculateAttendancePercentage = (present, total) => {
    if (!total || total <= 0) return 0;
    const percentage = Math.round((present / total) * 100);
    // Ensure percentage is between 0 and 100
    return Math.min(Math.max(percentage, 0), 100);
};

// Watch for changes in the injected item ID
watch(
    currentItemId,
    async (newItemId) => {
        if (newItemId) {
            const child = findChildById(newItemId);
            if (child) {
                currentChild.value = child;
                showAllChildren.value = false;
                await fetchChildAttendance(newItemId);
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
    async (newSearch) => {
        const params = new URLSearchParams(newSearch);
        const childId = params.get('childId');
        const itemId = params.get('itemId');

        // First check for childId (legacy parameter)
        if (childId) {
            const child = findChildById(childId);
            if (child) {
                currentChild.value = child;
                showAllChildren.value = false;
                await fetchChildAttendance(childId);
            }
        }
        // Then check for itemId (new parameter from sidebar)
        else if (itemId) {
            const child = findChildById(itemId);
            if (child) {
                currentChild.value = child;
                showAllChildren.value = false;
                await fetchChildAttendance(itemId);
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

// Watch for filter changes to reset pagination
watch([statusFilter, classFilter], () => {
    currentPage.value = 1;
});

// Lifecycle hooks
onMounted(async () => {
    // Add event listener for item changes from sidebar
    window.addEventListener('item-changed', handleItemChange);

    // Set initial month to current month
    selectedMonth.value = new Date().getMonth() + 1; // 1-12 for API
    selectedYear.value = new Date().getFullYear();

    // Check if there's a child ID or item ID in the URL or page props
    const urlParams = new URLSearchParams(window.location.search);
    const childIdFromUrl = urlParams.get('childId');
    const itemIdFromUrl = urlParams.get('itemId');
    const childIdFromProps = page.props.childId;

    let initialChildId = null;

    if (childIdFromUrl || childIdFromProps) {
        initialChildId = childIdFromProps || childIdFromUrl;
    } else if (itemIdFromUrl) {
        initialChildId = itemIdFromUrl;
    }

    if (initialChildId) {
        const child = findChildById(initialChildId);
        if (child) {
            currentChild.value = child;
            showAllChildren.value = false;
            await fetchChildAttendance(initialChildId);
        } else if (initialChildId === 'all') {
            currentChild.value = null;
            showAllChildren.value = true;
        }
    } else {
        currentChild.value = null;
        showAllChildren.value = true;
    }

    // Clean up event listeners on component unmount
    return () => {
        window.removeEventListener('item-changed', handleItemChange);
    };
});
</script>

<template>
    <Head title="Parent Attendance View" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <!-- Add top padding to prevent header overlap -->
        <div class="p-6 pt-20">
            <!-- Child-specific attendance content -->
            <div v-if="loading" class="fixed inset-0 flex items-center justify-center bg-white z-50">
    <div class="relative w-20 h-20">
        <!-- Outer circle: Dark Blue -->
        <div class="absolute inset-0 border-4 border-blue-900 border-t-transparent rounded-full animate-spin"></div>
        <!-- Middle circle: Medium Blue -->
        <div class="absolute inset-2 border-4 border-blue-600 border-t-transparent rounded-full animate-spin reverse-spin"></div>
        <!-- Inner circle: Light Blue -->
        <div class="absolute inset-4 border-4 border-blue-300 border-t-transparent rounded-full animate-spin"></div>
    </div>
</div>
            <div v-if="currentChild">
                <!-- Child header -->
                <div class="mb-6 flex items-center">
                    <div
                        class="mr-4 flex h-16 w-16 items-center justify-center overflow-hidden rounded-full bg-gray-300 text-2xl font-medium text-white"
                    >
                        <img
                            v-if="currentChild.avatar"
                            :src="`/${currentChild.avatar}`"
                            :alt="`${currentChild.name} Avatar`"
                            class="h-16 w-16 rounded-full object-cover"
                        />
                        <span v-else>
                            {{ currentChild.name.charAt(0).toUpperCase() }}
                        </span>
                    </div>

                    <div>
                        <h2 class="text-xl font-semibold">{{ currentChild.name }}</h2>
                        <p class="text-gray-500 dark:text-gray-400">{{ currentChild.grade }}</p>
                        <div class="mt-1 flex items-center">
                            <span class="mr-2 text-sm text-gray-500 dark:text-gray-400">Attendance Rate:</span>
                            <span class="text-sm font-medium">{{ currentChild.stats.attendance }}%</span>
                        </div>
                    </div>
                </div>

                <div class="mb-6 grid grid-cols-1 gap-6 md:grid-cols-3">
                    <!-- Attendance Summary Card -->
                    <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
                        <h3 class="mb-4 font-medium">Attendance Summary</h3>
                        <div class="flex items-center space-x-4">
                            <div
                                class="flex h-20 w-20 items-center justify-center rounded-full bg-blue-100 text-xl font-bold text-blue-600 dark:bg-blue-900 dark:text-blue-400"
                            >
                                {{ currentChild.stats.attendance }}%
                            </div>
                            <div class="flex-1">
                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Present</p>
                                        <p class="font-medium">{{ currentChild.attendanceSummary.present }} days</p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Absent</p>
                                        <p class="font-medium">{{ currentChild.attendanceSummary.absent }} days</p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Late</p>
                                        <p class="font-medium">{{ currentChild.attendanceSummary.late }} days</p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Total</p>
                                        <p class="font-medium">{{ currentChild.attendanceSummary.total }} days</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Monthly Trend Card -->
                    <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
                        <h3 class="mb-4 font-medium">Monthly Trend</h3>
                        <div class="h-40 w-full">
                            <!-- Use a scrollable container with fixed height -->
                            <div class="flex h-full w-full flex-col overflow-y-auto rounded-lg bg-gray-100 p-4 dark:bg-gray-700">
                                <!-- Show only the last 5 months to prevent overflow -->
                                <div
                                    v-if="limitedMonthlyAttendance.length === 0"
                                    class="flex h-full items-center justify-center text-sm text-gray-500"
                                >
                                    No monthly data available
                                </div>
                                <div v-for="(month, index) in limitedMonthlyAttendance" :key="index" class="mb-2 last:mb-0">
                                    <div class="mb-1 flex justify-between">
                                        <span class="text-xs font-medium">{{ month.month }}</span>
                                        <span class="text-xs font-medium">{{ calculateAttendancePercentage(month.present, month.total) }}%</span>
                                    </div>
                                    <div class="h-2 w-full rounded-full bg-gray-200 dark:bg-gray-600">
                                        <div
                                            class="h-2 rounded-full bg-blue-600"
                                            :style="`width: ${calculateAttendancePercentage(month.present, month.total)}%`"
                                        ></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Attendance Distribution Card (Replacing Upcoming Events) -->
                    <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
                        <h3 class="mb-4 font-medium">Attendance Distribution</h3>
                        <div class="space-y-3">
                            <div v-if="attendanceStats.length === 0" class="flex h-32 items-center justify-center text-sm text-gray-500">
                                No attendance data available
                            </div>
                            <div v-else>
                                <div v-for="(stat, index) in attendanceStats" :key="index" class="mb-3">
                                    <div class="mb-1 flex justify-between">
                                        <span class="text-xs font-medium">{{ stat.name }}</span>
                                        <span class="text-xs font-medium">{{ stat.value }} ({{ stat.percentage }}%)</span>
                                    </div>
                                    <div class="h-2 w-full rounded-full bg-gray-200 dark:bg-gray-600">
                                        <div :class="`${stat.color} h-2 rounded-full`" :style="`width: ${stat.percentage}%`"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Attendance Records with Filters -->
                <div class="overflow-hidden rounded-lg bg-white shadow dark:bg-gray-800">
                    <div class="border-b p-6 dark:border-gray-700">
                        <div class="mb-4 flex flex-col items-start justify-between md:flex-row md:items-center">
                            <div>
                                <h3 class="font-medium">Attendance Records</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ monthName }}</p>
                            </div>
                            <div class="mt-2 flex space-x-2 md:mt-0">
                                <button @click="previousMonth" class="rounded-full p-2 hover:bg-gray-100 dark:hover:bg-gray-700">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                                    </svg>
                                </button>
                                <button @click="nextMonth" class="rounded-full p-2 hover:bg-gray-100 dark:hover:bg-gray-700">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Filters -->
                        <div class="mb-4 flex flex-col gap-4 md:flex-row">
                            <div class="flex-1">
                                <label for="status-filter" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Filter by Status
                                </label>
                                <select
                                    id="status-filter"
                                    v-model="statusFilter"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700"
                                >
                                    <option value="all">All Statuses</option>
                                    <option value="present">Present</option>
                                    <option value="absent">Absent</option>
                                    <option value="late">Late</option>
                                    <option value="excused">Excused</option>
                                </select>
                            </div>

                            <div class="flex-1">
                                <label for="class-filter" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Filter by Class
                                </label>
                                <select
                                    id="class-filter"
                                    v-model="classFilter"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700"
                                >
                                    <!-- <option value="all">All Classes</option> -->
                                    <option v-for="(className, index) in availableClasses" :key="index" :value="className">
                                        {{ className }}
                                    </option>
                                </select>
                            </div>

                            <div class="flex items-end">
                                <button
                                    @click="resetFilters"
                                    class="rounded-md bg-gray-100 px-4 py-2 text-sm hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600"
                                >
                                    Reset Filters
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th
                                        scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300"
                                    >
                                        Date
                                    </th>
                                    <th
                                        scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300"
                                    >
                                        Status
                                    </th>
                                    <th
                                        scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300"
                                    >
                                        Class
                                    </th>
                                    <th
                                        scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300"
                                    >
                                        Notes
                                    </th>
                                    <th
                                        scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300"
                                    >
                                        Action
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                                <tr v-for="(record, index) in paginatedAttendanceRecords" :key="index">
                                    <td class="whitespace-nowrap px-6 py-4 text-sm">
                                        {{
                                            new Date(record.date).toLocaleDateString('en-US', {
                                                weekday: 'short',
                                                month: 'short',
                                                day: 'numeric',
                                                year: 'numeric',
                                            })
                                        }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4">
                                        <span
                                            :class="`inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium capitalize ${getStatusClass(record.status)}`"
                                        >
                                            {{ record.status }}
                                        </span>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm">{{ record.class }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                        {{ record.notes || 'No notes' }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm">
                                        <button
                                            v-if="record.status === 'absent'"
                                            class="hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300 text-primary-600"
                                        >
                                            Submit Excuse
                                        </button>
                                        <span v-else>-</span>
                                    </td>
                                </tr>
                                <tr v-if="paginatedAttendanceRecords.length === 0">
                                    <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                                        No attendance records match your filters.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <!-- Pagination controls -->
                    <div class="flex items-center justify-between border-t p-4 dark:border-gray-700">
                        <div class="text-sm text-gray-500 dark:text-gray-400">
                            Showing <span class="font-medium">{{ paginatedAttendanceRecords.length }}</span> of
                            <span class="font-medium">{{ filteredAttendanceRecords.length }}</span> records
                        </div>
                        <div class="flex space-x-2">
                            <!-- Pagination buttons -->
                            <div class="flex items-center space-x-1">
                                <button
                                    @click="previousPage"
                                    :disabled="currentPage === 1"
                                    :class="[
                                        'rounded border px-3 py-1 text-sm dark:border-gray-600',
                                        currentPage === 1 ? 'cursor-not-allowed opacity-50' : '',
                                    ]"
                                >
                                    Previous
                                </button>

                                <button
                                    v-for="page in totalPages"
                                    :key="page"
                                    @click="goToPage(page)"
                                    :class="[
                                        'rounded px-3 py-1 text-sm',
                                        currentPage === page
                                            ? 'bg-primary-600 text-white'
                                            : 'border hover:bg-gray-100 dark:border-gray-600 dark:hover:bg-gray-700',
                                    ]"
                                >
                                    {{ page }}
                                </button>

                                <button
                                    @click="nextPage"
                                    :disabled="currentPage === totalPages"
                                    :class="[
                                        'rounded border px-3 py-1 text-sm dark:border-gray-600',
                                        currentPage === totalPages ? 'cursor-not-allowed opacity-50' : '',
                                    ]"
                                >
                                    Next
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- All Children Overview (when no specific child is selected) -->
            <div v-else-if="showAllChildren" class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
                <h3 class="mb-6 text-xl font-medium">Attendance Overview</h3>

                <div class="mb-6 grid grid-cols-1 gap-6 md:grid-cols-3">
                    <div
                        v-for="child in children"
                        :key="child.id"
                        class="overflow-hidden rounded-lg border bg-white shadow-sm dark:border-gray-600 dark:bg-gray-700"
                    >
                        <div class="border-b p-4 dark:border-gray-600">
                            <div class="flex items-center">
                                <div
                                    class="mr-4 flex h-12 w-12 items-center justify-center overflow-hidden rounded-full bg-gray-300 text-xl font-medium text-white"
                                >
                                    <img
                                        v-if="child.avatar"
                                        :src="`/${child.avatar}`"
                                        :alt="`${child.name} Avatar`"
                                        class="h-12 w-12 rounded-full object-cover"
                                    />
                                    <span v-else>
                                        {{ child.name.charAt(0).toUpperCase() }}
                                    </span>
                                </div>

                                <div>
                                    <h4 class="font-medium">{{ child.name }}</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ child.grade }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="p-4">
                            <div class="mb-2 flex items-center justify-between">
                                <span class="text-sm text-gray-500 dark:text-gray-400">Attendance Rate</span>
                                <span class="text-sm font-medium">{{ child.stats.attendance }}%</span>
                            </div>
                            <div class="mb-4 h-2 w-full rounded-full bg-gray-200 dark:bg-gray-600">
                                <div class="h-2 rounded-full bg-blue-600" :style="`width: ${child.stats.attendance}%`"></div>
                            </div>

                            <div class="grid grid-cols-2 gap-2 text-sm">
                                <div>
                                    <p class="text-gray-500 dark:text-gray-400">Present</p>
                                    <p class="font-medium">{{ child.attendanceSummary.present }} days</p>
                                </div>
                                <div>
                                    <p class="text-gray-500 dark:text-gray-400">Absent</p>
                                    <p class="font-medium">{{ child.attendanceSummary.absent }} days</p>
                                </div>
                                <div>
                                    <p class="text-gray-500 dark:text-gray-400">Late</p>
                                    <p class="font-medium">{{ child.attendanceSummary.late }} days</p>
                                </div>
                                <div>
                                    <p class="text-gray-500 dark:text-gray-400">Total</p>
                                    <p class="font-medium">{{ child.attendanceSummary.total }} days</p>
                                </div>
                            </div>
                        </div>
                        <div class="border-t bg-gray-50 p-4 dark:border-gray-600 dark:bg-gray-800">
                            <button
                                @click="
                                    currentChild = child;
                                    showAllChildren = false;
                                    fetchChildAttendance(child.id);
                                "
                                class="hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300 w-full text-center text-sm text-primary-600"
                            >
                                View Detailed Records
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Attendance Overview Summary -->
                <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
                    <h3 class="mb-4 font-medium">Attendance Overview</h3>
                    <div class="space-y-3">
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            Select a child from above to view detailed attendance records and statistics.
                        </p>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            You can filter attendance by status and class, and navigate through different months to track your child's attendance
                            history.
                        </p>
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

.reverse-spin {
        animation: spinReverse 1s linear infinite;
    }

    @keyframes spinReverse {
        0% { transform: rotate(360deg); }
        100% { transform: rotate(0deg); }
    }
</style>
