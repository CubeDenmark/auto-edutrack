<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, usePage } from '@inertiajs/vue3';
import { computed, inject, onMounted, onUnmounted, ref, watch } from 'vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Attendance',
        href: '/attendance',
    },
];

// Get the page props to access any data passed from the server
const page = usePage();

defineProps<{
    name?: string;
    sectionId?: string; // Add prop to receive section ID from URL or parent
}>();

// Inject the current item ID from the parent component (from sidebar)
const currentItemId = inject('currentItemId', ref(null));

// State variables
const currentDate = ref(new Date().toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }));
const currentSection = ref<any>(null);
const isSaving = ref(false);
const saveMessage = ref('');
const saveStatus = ref('');
const selectedStudent = ref<any>(null);
const showHistoryModal = ref(false);

const searchQuery = ref('');
const sortBy = ref('name');
const sortDirection = ref('asc');
const statusFilter = ref('all');

// Pagination variables
const currentPage = ref(1);
const itemsPerPage = ref(8);

const sections = usePage().props.sections || [];

const classSections = computed(() => {
    if (!sections || !Array.isArray(sections)) {
        console.error('Sections data is not available or not an array:', sections);
        return [];
    }

    return sections.map((section) => {
        const students = section.students || [];

        // Calculate attendance summary
        const attendanceSummary = {
            present: students.filter((s) => s.status === 'present').length,
            absent: students.filter((s) => s.status === 'absent').length,
            late: students.filter((s) => s.status === 'late').length,
            excused: students.filter((s) => s.status === 'excused').length,
            // Fallback for undefined status
            undefined: students.filter((s) => !['present', 'absent', 'late', 'excused'].includes(s.status)).length,
        };

        return {
            id: section.id.toString(),
            name: section.name,
            icon: section.icon || '📚',
            term: section.term || 'Current Term',
            studentCount: students.length,
            presentCount: attendanceSummary.present,
            attendanceSummary,
            students: students.map((student) => ({
                id: student.id,
                avatar: student.avatar,
                school_id: student.school_id,
                name: student.name,
                email: student.email,
                status: student.status || 'absent', // Default to 'absent' if no status is provided
                notes: student.notes || '', // Use notes from data if available
                attendance_history: student.attendance_history || [], // Include attendance history
            })),
        };
    });
});

// Methods
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
                source: 'attendance',
            },
        }),
    );
}

// Find section by ID
function findSectionById(id) {
    if (!id) return null;
    return classSections.value.find((section) => section.id.toString() === id.toString()) || null;
}

function updateAttendanceSummary() {
    if (!currentSection.value) return;

    const students = currentSection.value.students;
    currentSection.value.attendanceSummary = {
        present: students.filter((s) => s.status === 'present').length,
        absent: students.filter((s) => s.status === 'absent').length,
        late: students.filter((s) => s.status === 'late').length,
        excused: students.filter((s) => s.status === 'excused').length,
    };

    currentSection.value.presentCount = currentSection.value.attendanceSummary.present;
}

function updateStudentStatus(student, newStatus) {
    student.status = newStatus;
    updateAttendanceSummary();
}

function viewAttendanceHistory(student) {
    selectedStudent.value = student;
    showHistoryModal.value = true;
}

function closeHistoryModal() {
    showHistoryModal.value = false;
    selectedStudent.value = null;
}

function formatDate(dateString) {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
}

function getStatusClass(status) {
    switch (status) {
        case 'present':
            return 'bg-green-100 text-green-800';
        case 'absent':
            return 'bg-red-100 text-red-800';
        case 'late':
            return 'bg-yellow-100 text-yellow-800';
        case 'excused':
            return 'bg-blue-100 text-blue-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
}

async function saveAttendance() {
    if (!currentSection.value) return;

    isSaving.value = true;
    saveMessage.value = '';
    saveStatus.value = '';

    try {
        // Get today's date in YYYY-MM-DD format
        const today = new Date().toISOString().split('T')[0];

        // Save attendance for each student in the current section
        const promises = currentSection.value.students.map((student) => {
            return fetch('/attendance/save', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                },
                body: JSON.stringify({
                    student_id: student.id,
                    class_id: currentSection.value.id,
                    status: student.status,
                    date: today,
                    notes: student.notes,
                }),
            }).then((response) => {
                if (!response.ok) {
                    throw new Error(`Failed to save attendance for ${student.name}`);
                }
                return response.json();
            });
        });

        // Wait for all requests to complete
        const results = await Promise.all(promises);

        saveMessage.value = 'Attendance saved successfully!';
        saveStatus.value = 'success';

        console.log('Attendance saved:', results);
    } catch (error) {
        console.error('Error saving attendance:', error);
        saveMessage.value = 'Failed to save attendance. Please try again.';
        saveStatus.value = 'error';
    } finally {
        isSaving.value = false;

        // Show the message for 3 seconds then clear it
        setTimeout(() => {
            saveMessage.value = '';
            saveStatus.value = '';
        }, 3000);
    }
}

// Function to handle section-changed events
const handleSectionChanged = (event) => {
    if (event.detail && event.detail.sectionId && event.detail.source !== 'attendance') {
        const section = findSectionById(event.detail.sectionId);
        if (section) {
            currentSection.value = section;
        }
    }
};

// Lifecycle hooks
onMounted(() => {
    console.log('Attendance component mounted');
    console.log('Available sections:', classSections.value);

    // Add event listener for item changes from sidebar
    const handleItemChangeFunction = (event) => {
        if (event.detail) {
            const itemId = event.detail.itemId;
            const itemType = event.detail.itemType;
            const source = event.detail.source || '';

            // Avoid processing our own events to prevent loops
            if (source === 'attendance') return;

            // For professors, the itemType will be 'section' or undefined
            if (itemType === 'section' || !itemType) {
                const section = findSectionById(itemId);
                if (section) {
                    currentSection.value = section;
                }
            }
        }
    };

    window.addEventListener('item-changed', handleItemChangeFunction);

    // For backward compatibility, also listen for section-changed events
    window.addEventListener('section-changed', handleSectionChanged);

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
        }
    }
    // Then check for itemId (which could be a section ID from sidebar)
    else if (itemIdFromUrl) {
        const section = findSectionById(itemIdFromUrl);
        if (section) {
            currentSection.value = section;
        }
    }
    // Finally check for injected item ID
    else if (currentItemId.value) {
        const section = findSectionById(currentItemId.value);
        if (section) {
            currentSection.value = section;
        }
    }

    // If no section is selected and we have sections, select the first one
    if (!currentSection.value && classSections.value.length > 0) {
        selectSection(classSections.value[0]);
    }

    // Clean up event listeners on component unmount
    onUnmounted(() => {
        window.removeEventListener('item-changed', handleItemChangeFunction);
        window.removeEventListener('section-changed', handleSectionChanged);
    });
});

// Toggle sort direction
function toggleSort(field) {
    if (sortBy.value === field) {
        sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortBy.value = field;
        sortDirection.value = 'asc';
    }
}

// Toggle sort direction
const selectedAssessmentItem = ref(null);

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

// Watch for changes in the injected item ID
watch(
    currentItemId,
    (newItemId) => {
        if (newItemId) {
            const section = findSectionById(newItemId);
            if (section) {
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
            if (section) {
                currentSection.value = section;
            }
        }
        // Then check for itemId (new parameter from sidebar)
        else if (itemId) {
            const section = findSectionById(itemId);
            if (section) {
                currentSection.value = section;
            }
        }
    },
    { immediate: true },
);

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

function getInitial(name: string): string {
    if (!name) return '?';
    return name.charAt(0).toUpperCase(); // Returns first initial of the user's name
}
</script>

<template>
    <Head title="Attendance Management" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 pt-20">
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
                    Please select a class from the sidebar to view class-specific information. You can also add class from the sidebar, just click the
                    '+' button.
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

            <!-- Dashboard Page -->
            <div v-if="currentSection">
                <div class="mb-6 grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4">
                    <!-- Total Students Card -->
                    <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
                        <div class="mb-4 flex items-center justify-between">
                            <h3 class="font-medium">Total Students</h3>
                        </div>
                        <div class="flex items-center space-x-4">
                            <div class="flex h-16 w-16 items-center justify-center rounded-full bg-blue-100 text-blue-500 dark:bg-blue-900">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"
                                    />
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Registered Students</p>
                                <p class="text-2xl font-bold">{{ currentSection.studentCount || 0 }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Present Students Card -->
                    <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
                        <div class="mb-4 flex items-center justify-between">
                            <h3 class="font-medium">Present</h3>
                        </div>
                        <div class="flex items-center space-x-4">
                            <div class="flex h-16 w-16 items-center justify-center rounded-full bg-green-100 text-green-500 dark:bg-green-900">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Present Students</p>
                                <p class="text-2xl font-bold">{{ currentSection.attendanceSummary?.present || 0 }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Absent Students Card -->
                    <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
                        <div class="mb-4 flex items-center justify-between">
                            <h3 class="font-medium">Absent</h3>
                        </div>
                        <div class="flex items-center space-x-4">
                            <div class="flex h-16 w-16 items-center justify-center rounded-full bg-red-100 text-red-500 dark:bg-red-900">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Absent Students</p>
                                <p class="text-2xl font-bold">{{ currentSection.attendanceSummary?.absent || 0 }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Late Students Card -->
                    <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
                        <div class="mb-4 flex items-center justify-between">
                            <h3 class="font-medium">Late</h3>
                        </div>
                        <div class="flex items-center space-x-4">
                            <div class="flex h-16 w-16 items-center justify-center rounded-full bg-yellow-100 text-yellow-500 dark:bg-yellow-900">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                                    />
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Late Students</p>
                                <p class="text-2xl font-bold">{{ currentSection.attendanceSummary?.late || 0 }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Attendance Records -->
                <div class="mb-6 overflow-hidden rounded-lg bg-white shadow dark:bg-gray-800">
                    <div class="flex flex-wrap items-center justify-between gap-4 border-b p-6 dark:border-gray-700">
                        <!-- Title and subtitle -->
                        <div>
                            <h3 class="font-medium">Attendance Records</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Class: {{ currentSection.name }} - Today's Date: {{ currentDate }}</p>
                        </div>

                        <!-- Controls: Search + Sort + Save -->
                        <div class="flex flex-wrap items-center gap-2">
                            <!-- Search Input -->
                            <input
                                type="text"
                                v-model="searchQuery"
                                placeholder="Search..."
                                class="w-80 rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white dark:placeholder-gray-400"
                            />

                            <!-- Sort Button -->
                            <button
                                class="flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600"
                                @click="sortDirection = sortDirection === 'asc' ? 'desc' : 'asc'"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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

                            <!-- Save Button -->
                            <button
                                @click="saveAttendance"
                                class="hover:bg-primary-700 flex items-center gap-2 rounded-lg bg-primary-600 px-4 py-2.5 text-sm font-medium text-white transition-colors"
                                :disabled="isSaving"
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
                                {{ isSaving ? 'Saving...' : 'Save Attendance' }}
                            </button>
                        </div>
                    </div>

                    <!-- Save Status Message -->
                    <div
                        v-if="saveMessage"
                        :class="`p-2 text-center ${saveStatus === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}`"
                    >
                        {{ saveMessage }}
                    </div>

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
                                    <!-- <th
                                        scope="col"
                                        class="cursor-pointer px-6 py-3 text-left text-sm font-semibold uppercase tracking-wide text-indigo-700 dark:text-indigo-300"
                                    >
                                        ID
                                    </th> -->
                                    <th
                                        scope="col"
                                        class="cursor-pointer px-6 py-3 text-left text-sm font-semibold uppercase tracking-wide text-indigo-700 dark:text-indigo-300"
                                    >
                                        Status
                                    </th>
                                    <th
                                        scope="col"
                                        class="cursor-pointer px-6 py-3 text-left text-sm font-semibold uppercase tracking-wide text-indigo-700 dark:text-indigo-300"
                                    >
                                        Notes
                                    </th>
                                    <th
                                        scope="col"
                                        class="cursor-pointer px-6 py-3 text-left text-sm font-semibold uppercase tracking-wide text-indigo-700 dark:text-indigo-300"
                                    >
                                        History
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                                <tr v-for="(student, index) in paginatedStudents" :key="index">
                                    <td class="whitespace-nowrap px-6 py-4">
                                        <div class="flex items-center">
                                            <div class="h-10 w-10 flex-shrink-0">
                                                <img
                                                    v-if="student.avatar && student.avatar.trim() !== ''"
                                                    :src="`/${student.avatar}`"
                                                    alt="User Avatar"
                                                    class="h-10 w-10 rounded-full"
                                                />

                                                <div
                                                    v-else
                                                    class="flex h-10 w-10 items-center justify-center rounded-full bg-gray-400 text-sm font-bold text-white"
                                                >
                                                    {{ getInitial(student.name) }}
                                                </div>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium">{{ student.name }}</div>
                                                <div class="text-sm text-gray-500 dark:text-gray-400">{{ student.email }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <!-- <td class="whitespace-nowrap px-6 py-4 text-sm">{{ student.school_id }}</td> -->
                                    <td class="whitespace-nowrap px-6 py-4">
                                        <select
                                            v-model="student.status"
                                            @change="updateStudentStatus(student, student.status)"
                                            class="w-full rounded-md border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700"
                                        >
                                            <option value="present">Present</option>
                                            <option value="absent">Absent</option>
                                            <option value="late">Late</option>
                                            <option value="excused">Excused</option>
                                        </select>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4">
                                        <input
                                            type="text"
                                            v-model="student.notes"
                                            placeholder="Add notes..."
                                            class="w-full rounded-md border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700"
                                        />
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4">
                                        <button
                                            @click="viewAttendanceHistory(student)"
                                            class="rounded-md bg-blue-100 px-2 py-1 text-xs font-medium text-blue-800 hover:bg-blue-200 dark:bg-blue-900 dark:text-blue-100"
                                        >
                                            View History
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
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

        <!-- Attendance History Modal -->
        <div v-if="showHistoryModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
            <div class="w-full max-w-3xl rounded-lg bg-white p-6 shadow-lg dark:bg-gray-800">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-lg font-medium">Attendance History: {{ selectedStudent?.name }}</h3>
                    <button @click="closeHistoryModal" class="rounded-full p-1 hover:bg-gray-100 dark:hover:bg-gray-700">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div v-if="selectedStudent?.attendance_history?.length" class="max-h-96 overflow-y-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">
                                    Date
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">
                                    Status
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">
                                    Notes
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                            <tr v-for="(record, index) in selectedStudent.attendance_history" :key="index">
                                <td class="whitespace-nowrap px-6 py-4 text-sm">
                                    {{ formatDate(record.date) }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    <span :class="`inline-flex rounded-full px-2 py-1 text-xs font-semibold ${getStatusClass(record.status)}`">
                                        {{ record.status.charAt(0).toUpperCase() + record.status.slice(1) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    {{ record.notes || 'No notes' }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-else class="py-8 text-center text-gray-500">No attendance history available for this student.</div>

                <div class="mt-4 flex justify-end">
                    <button
                        @click="closeHistoryModal"
                        class="rounded-md bg-gray-200 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600"
                    >
                        Close
                    </button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>