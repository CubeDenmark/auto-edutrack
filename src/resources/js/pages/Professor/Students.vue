<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import { computed, inject, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Students',
        href: '/students',
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
const showAddStudentsByEmailModal = ref(false);
const searchQuery = ref('');
const sortBy = ref('name');
const sortOrder = ref('asc');
const studentEmails = ref('');
const isAddingStudents = ref(false);
const addResult = ref<{ added: string[]; skipped: string[] } | null>(null);
const errorMessage = ref('');
const isRemovingStudent = ref(false);
const studentToRemove = ref(null);
const showRemoveModal = ref(false);

// New state variables for parent linking
const showLinkParentModal = ref(false);
const selectedStudentClassId = ref('');
const parentId = ref('');
const parentSearchQuery = ref('');
const searchResults = ref<any[]>([]);
const isSearchingParents = ref(false);
const selectedParent = ref<any>(null);
const isLinkingParent = ref(false);
const linkParentResult = ref('');
const linkParentError = ref('');
const validationErrors = ref<Record<string, string>>({});

// New state variables for bulk import
const showImportStudentsModal = ref(false);
const importFile = ref<File | null>(null);
const isImportingStudents = ref(false);
const importSuccessMessage = ref('');
const importErrorMessage = ref('');

// Pagination variables
const currentPage = ref(1);
const itemsPerPage = ref(10);

const sections = usePage().props.sections || [];

const classSections = computed(() => {
    return sections.map((section) => ({
        id: section.id,
        name: section.name,
        icon: section.icon || '📚',
        term: section.term || 'Current Term',
        studentCount: section.studentCount || 0,
        presentCount: section.presentCount || 0,
        pendingAssignments: section.pendingAssignments || 0,
        toGradeCount: section.toGradeCount || 0,
        averageScore: section.averageScore || 0,
        attendanceSummary: section.attendanceSummary || { present: 0, absent: 0, late: 0, excused: 0 },
        performance: section.performance || { quiz1: 0, midterm: 0, project: 0, quiz2: 0 },
        students: section.students || [], // Include students array
    }));
});

// Calculate class average
const classAverage = computed(() => {
    if (!currentSection.value || !currentSection.value.students || currentSection.value.students.length === 0) {
        return 0;
    }

    const studentsWithScores = currentSection.value.students.filter((s) => s.score > 0);
    if (studentsWithScores.length === 0) return 0;

    return Math.round(studentsWithScores.reduce((sum, s) => sum + s.score, 0) / studentsWithScores.length);
});

// Get grade class based on score
const getGradeClass = (score) => {
    if (score >= 90) return 'bg-green-100 text-green-800';
    if (score >= 80) return 'bg-blue-100 text-blue-800';
    if (score >= 70) return 'bg-yellow-100 text-yellow-800';
    if (score >= 60) return 'bg-orange-100 text-orange-800';
    return 'bg-red-100 text-red-800';
};

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

    // Update URL with the selected section ID without navigating
    const url = new URL(window.location.href);
    url.searchParams.set('sectionId', section.id);
    window.history.replaceState({}, '', url.toString());

    // Dispatch an event to notify other components (like the sidebar)
    // This is the key addition to make the sidebar highlight the selected section
    window.dispatchEvent(
        new CustomEvent('section-changed', {
            detail: {
                sectionId: section.id,
                source: 'students',
            },
        }),
    );

    // Also dispatch the item-changed event for newer sidebar implementations
    window.dispatchEvent(
        new CustomEvent('item-changed', {
            detail: {
                itemId: section.id,
                itemType: 'section',
                source: 'students',
            },
        }),
    );
}

// Open add students by email modal
function openAddStudentsByEmailModal() {
    if (!currentSection.value) return;

    // Reset form
    studentEmails.value = '';
    addResult.value = null;

    showAddStudentsByEmailModal.value = true;
}

// Open import students modal
function openImportStudentsModal() {
    if (!currentSection.value) return;

    // Reset form
    importFile.value = null;
    importSuccessMessage.value = '';
    importErrorMessage.value = '';

    showImportStudentsModal.value = true;
}

// Handle file input for import
function handleFileUpload(e: Event) {
    const target = e.target as HTMLInputElement;
    if (target.files && target.files.length > 0) {
        importFile.value = target.files[0];
    }
}

// Submit import students form
async function importStudents() {
    if (!currentSection.value || !importFile.value) {
        importErrorMessage.value = 'Please select a file to upload.';
        return;
    }

    isImportingStudents.value = true;
    importSuccessMessage.value = '';
    importErrorMessage.value = '';

    try {
        const formData = new FormData();
        formData.append('students_file', importFile.value);
        formData.append('class_id', String(currentSection.value.id));

        router.post('/students/import', formData, {
            forceFormData: true,
            onSuccess: () => {
                importSuccessMessage.value = 'Students uploaded successfully!';
                importFile.value = null;

                // Wait 3 seconds before reloading
                setTimeout(() => {
                    window.location.reload();
                }, 3000);
            },
            onError: (errors) => {
                console.error('Upload error:', errors);
                importErrorMessage.value = 'Failed to upload students. Please check your file and try again.';
            },
        });
    } catch (error) {
        console.error('Error importing students:', error);
        importErrorMessage.value = 'An unexpected error occurred. Please try again.';
    } finally {
        isImportingStudents.value = false;
    }
}

// Add students by email
async function addStudentsByEmail() {
    if (!currentSection.value) return;

    isAddingStudents.value = true;
    addResult.value = null;
    errorMessage.value = ''; // Clear any previous error message

    try {
        // Split emails by commas, newlines, or spaces
        const emails = studentEmails.value
            .split(/[\s,;]+/)
            .map((email) => email.trim())
            .filter((email) => email.length > 0);

        if (emails.length === 0) {
            errorMessage.value = 'Please enter at least one valid email address.';
            isAddingStudents.value = false;
            return;
        }

        // Call the API to add students
        const response = await axios.post('/add-students-to-class', {
            emails: emails,
            class_id: currentSection.value.id,
        });

        // Update the result
        addResult.value = {
            added: response.data.added || [],
            skipped: response.data.skipped || [],
        };

        // Update the student count if students were added
        if (response.data.added && response.data.added.length > 0) {
            currentSection.value.studentCount += response.data.added.length;

            // Wait 3 seconds before reloading
            setTimeout(() => {
                window.location.reload();
            }, 3000);
        }
    } catch (error) {
        console.error('Error adding students:', error);

        if (error.response && error.response.data && error.response.data.message) {
            errorMessage.value = `Error: ${error.response.data.message}`;
        } else {
            errorMessage.value = 'Failed to add students. Please try again.';
        }
    } finally {
        isAddingStudents.value = false;
    }
}

// Function to open the link parent modal
function openLinkParentModal(studentClassId) {
    // Debugging: Log the student class ID passed to the modal
    console.log('Opening modal for StudentClass ID:', studentClassId);

    // Reset and set the student class ID
    selectedStudentClassId.value = studentClassId;
    parentId.value = ''; // Reset parent ID
    parentSearchQuery.value = ''; // Clear the search query
    searchResults.value = []; // Clear search results
    selectedParent.value = null; // Reset selected parent
    linkParentResult.value = ''; // Clear result message
    linkParentError.value = ''; // Clear error message
    validationErrors.value = {}; // Clear validation errors

    showLinkParentModal.value = true; // Show the modal

    // Debugging: Log when the modal is shown
    console.log('Link Parent modal opened');
}

// Function to link the parent to the student
async function linkParentToStudent() {
    console.log('Attempting to link parent:', parentId.value, 'to student class:', selectedStudentClassId.value);

    // Check if both parent and student class are selected
    if (!parentId.value || !selectedStudentClassId.value) {
        linkParentError.value = 'Please select a parent and a student.';
        console.log('Error: Parent or Student Class not selected.');
        return;
    }

    isLinkingParent.value = true;
    linkParentResult.value = '';
    linkParentError.value = '';
    validationErrors.value = {};

    try {
        // Debugging: Log the data being sent to the backend
        console.log('Sending data to backend:', {
            parent_id: parentId.value,
            student_class_id: selectedStudentClassId.value,
        });

        // Send POST request to link parent to student
        const response = await axios.post('/link-parent', {
            parent_id: parentId.value, // Parent ID to link
            student_class_id: selectedStudentClassId.value, // StudentClass ID to link
        });

        // Debugging: Log the response from the backend
        console.log('Response from backend:', response.data);

        // Display success message
        linkParentResult.value = response.data.message || 'Parent successfully linked to student.';

        // Close modal after 2 seconds
        setTimeout(() => {
            showLinkParentModal.value = false;
            window.location.reload();
        }, 2000);
    } catch (error) {
        console.error('Error linking parent:', error);

        if (error.response) {
            // Debugging: Log the error response details
            console.log('Error response:', error.response);

            if (error.response.status === 422) {
                validationErrors.value = error.response.data.errors || {};
                linkParentError.value = error.response.data.message || 'Validation failed. Please check the form.';
            } else if (error.response.status === 409) {
                linkParentError.value = error.response.data.message || 'This parent is already linked to the student.';
            } else {
                linkParentError.value = error.response.data.message || 'Failed to link parent. Please try again.';
            }
        } else {
            linkParentError.value = 'Failed to link parent. Please try again.';
        }
    } finally {
        isLinkingParent.value = false;
        console.log('Finished linking process.');
    }
}

// Search for parents
async function searchParents() {
    if (!parentSearchQuery.value || parentSearchQuery.value.length < 2) {
        searchResults.value = [];
        return;
    }

    isSearchingParents.value = true;

    try {
        const response = await axios.get(`/available-parents?query=${encodeURIComponent(parentSearchQuery.value)}`);
        searchResults.value = response.data;
    } catch (error) {
        console.error('Error searching parents:', error);
        searchResults.value = [];
    } finally {
        isSearchingParents.value = false;
    }
}

// Select a parent from search results
function selectParent(parent) {
    selectedParent.value = parent;
    parentId.value = parent.id;
    parentSearchQuery.value = parent.name;
    searchResults.value = [];
}

// Watch for changes in parent search query
watch(parentSearchQuery, (newQuery) => {
    if (newQuery && newQuery.length >= 2) {
        // Debounce the search to avoid too many requests
        const debounce = setTimeout(() => {
            searchParents();
        }, 300);

        return () => clearTimeout(debounce);
    } else {
        searchResults.value = [];
    }
});

// Computed property for filtered students
const filteredStudents = computed(() => {
    if (!currentSection.value) return [];

    let students = [...currentSection.value.students];

    // Apply search filter
    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        students = students.filter((student) => {
            const basicInfoMatch = student.name?.toLowerCase().includes(query) || student.email?.toLowerCase().includes(query);

            const parentMatch = student.parents?.some(
                (parent) => parent.name?.toLowerCase().includes(query) || parent.email?.toLowerCase().includes(query),
            );

            return basicInfoMatch || parentMatch;
        });
    }

    // Sort by name (A-Z or Z-A only)
    students.sort((a, b) => {
        const comparison = a.name.localeCompare(b.name);
        return sortOrder.value === 'asc' ? comparison : -comparison;
    });

    return students;
});

// Pagination functions
const totalPages = computed(() => {
    return Math.ceil(filteredStudents.value.length / itemsPerPage.value);
});

const paginatedStudents = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage.value;
    const end = start + itemsPerPage.value;
    return filteredStudents.value.slice(start, end);
});

function previousPage() {
    if (currentPage.value > 1) {
        currentPage.value--;
    }
}

function nextPage() {
    if (currentPage.value < totalPages.value) {
        currentPage.value++;
    }
}

function goToPage(page) {
    if (page >= 1 && page <= totalPages.value) {
        currentPage.value = page;
    }
}

// Generate page numbers for pagination
const pageNumbers = computed(() => {
    if (totalPages.value <= 7) {
        return Array.from({ length: totalPages.value }, (_, i) => i + 1);
    }

    const pages = [];
    if (currentPage.value <= 3) {
        // Show first 5 pages, then ellipsis, then last page
        for (let i = 1; i <= 5; i++) {
            pages.push(i);
        }
        pages.push('...');
        pages.push(totalPages.value);
    } else if (currentPage.value >= totalPages.value - 2) {
        // Show first page, then ellipsis, then last 5 pages
        pages.push(1);
        pages.push('...');
        for (let i = totalPages.value - 4; i <= totalPages.value; i++) {
            pages.push(i);
        }
    } else {
        // Show first page, ellipsis, current page and neighbors, ellipsis, last page
        pages.push(1);
        pages.push('...');
        for (let i = currentPage.value - 1; i <= currentPage.value + 1; i++) {
            pages.push(i);
        }
        pages.push('...');
        pages.push(totalPages.value);
    }
    return pages;
});

// Get selected student details
const selectedStudent = computed(() => {
    if (!selectedStudentClassId.value || !currentSection.value) return null;
    return currentSection.value.students.find((s) => s.id === selectedStudentClassId.value) || null;
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
            }
        }
    }
};

// Function to handle section-changed events
const handleSectionChange = (event) => {
    if (event.detail && event.detail.sectionId && event.detail.source !== 'students') {
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

    // Initialize currentSection based on URL parameters, props, or injected ID
    let initialSectionId = sectionIdFromProps || sectionIdFromUrl || itemIdFromUrl || currentItemId.value;
    if (initialSectionId) {
        const section = findSectionById(initialSectionId);
        if (section) {
            currentSection.value = section;
        }
    }
});

// Clean up event listeners on component unmount
onBeforeUnmount(() => {
    window.removeEventListener('item-changed', handleItemChange);
    window.removeEventListener('section-changed', handleSectionChange);
});

function removeStudent(id) {
    console.log('Removing student with ID:', id); // Debugging
    studentToRemove.value = id;
    showRemoveModal.value = true;
}

async function confirmRemoveStudent() {
    if (!studentToRemove.value) {
        console.error('No student ID set!');
        return;
    }

    try {
        isRemovingStudent.value = true;
        await axios.delete(`/students/${studentToRemove.value}`);
        console.log('Student deleted successfully');
        showRemoveModal.value = false;
        studentToRemove.value = null;
        window.location.reload(); // Refresh the page
    } catch (error) {
        console.error('Error deleting student:', error);
    } finally {
        isRemovingStudent.value = false;
    }
}

function getInitial(nameOrStudent: string | { parents?: Array<{ name?: string }> }): string {
    if (typeof nameOrStudent === 'string') {
        return nameOrStudent.trim() ? nameOrStudent.trim().charAt(0).toUpperCase() : '?';
    }

    const firstParent = nameOrStudent.parents && nameOrStudent.parents.length > 0 ? nameOrStudent.parents[0] : null;

    if (!firstParent || !firstParent.name || firstParent.name.trim() === '') {
        return '?';
    }

    return firstParent.name.trim().charAt(0).toUpperCase();
}
</script>

<template>
    <Head title="Students Management" />

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

            <!-- Students Management Page -->
            <div v-if="currentSection">
                <!-- New Header Design -->
                <div class="mb-6 w-full overflow-hidden rounded-2xl shadow-lg dark:bg-gray-800">
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
                                            <h1 class="text-2xl font-bold text-black dark:text-white md:text-3xl">{{ currentSection.name }}</h1>
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

                <!-- Student List -->
                <div class="mb-6 overflow-hidden rounded-lg bg-white shadow dark:bg-gray-800">
                    <div class="flex flex-col items-start justify-between gap-4 border-b p-6 dark:border-gray-700 md:flex-row md:items-center">
                        <div>
                            <h3 class="text-lg font-medium">Student List</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Class: {{ currentSection.name }} - {{ currentSection.term }}</p>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <button
                                @click="openAddStudentsByEmailModal"
                                class="hover:bg-primary-700 flex items-center gap-2 rounded-lg bg-primary-600 px-4 py-2.5 text-sm font-medium text-white transition-colors"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                                Add Students by Email
                            </button>

                            <!-- New Import Students Button -->
                            <button
                                @click="openImportStudentsModal"
                                class="flex items-center gap-2 rounded-lg bg-green-600 px-4 py-2.5 text-sm font-medium text-white transition-colors hover:bg-green-700"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                                </svg>
                                Import Students
                            </button>
                        </div>
                    </div>

                    <!-- Search and Filter -->
                    <div class="border-b bg-gray-50 p-5 dark:border-gray-600 dark:bg-gray-700">
                        <div class="flex flex-wrap items-center gap-4">
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
                                    placeholder="Search students by name, email or ID..."
                                    class="w-full rounded-lg border-gray-300 py-2.5 pl-10 pr-4 text-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-800"
                                />
                            </div>
                            <div class="flex items-center gap-3">
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Sort by:</label>
                                <select
                                    v-model="sortOrder"
                                    class="ml-2 rounded-lg border-gray-300 px-3 py-2.5 text-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-800"
                                >
                                    <option value="asc">Name (A - Z)</option>
                                    <option value="desc">Name (Z - A)</option>
                                </select>

                                <button
                                    @click="sortOrder = sortOrder === 'asc' ? 'desc' : 'asc'"
                                    class="rounded-lg p-2 hover:bg-gray-200 dark:hover:bg-gray-600"
                                >
                                    <svg
                                        v-if="sortOrder === 'asc'"
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="h-5 w-5"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12"
                                        />
                                    </svg>
                                    <svg
                                        v-else
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="h-5 w-5"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M3 4h13M3 8h9m-9 4h9m5-4v12m0 0l-4-4m4 4l4-4"
                                        />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-indigo-50 dark:bg-indigo-900/30">
                                <tr>
                                    <th
                                        scope="col"
                                        class="cursor-pointer px-6 py-3 text-left text-sm font-semibold uppercase tracking-wide text-indigo-700 dark:text-indigo-300"
                                    >
                                        Student
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
                                        Parent/Guardian
                                    </th>

                                    <th
                                        scope="col"
                                        class="cursor-pointer px-6 py-3 text-left text-sm font-semibold uppercase tracking-wide text-indigo-700 dark:text-indigo-300"
                                    >
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                                <tr v-for="(student, index) in paginatedStudents" :key="student.id">
                                    <td class="whitespace-nowrap px-6 py-5">
                                        <div class="flex items-center">
                                            <div class="h-10 w-10 flex-shrink-0">
                                                <!-- Show Avatar or Initial -->
                                                <img
                                                    v-if="student.avatar"
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

                                    <!-- <td class="whitespace-nowrap px-6 py-5 text-sm">{{ student.school_id }}</td> -->
                                    <td class="whitespace-nowrap px-6 py-5">
                                        <div class="flex items-center">
                                            <div class="h-10 w-10 flex-shrink-0">
                                                <img
                                                    v-if="student.parents && student.parents.length > 0 && student.parents[0].avatar"
                                                    :src="`/${student.parents[0].avatar}`"
                                                    alt="User Avatar"
                                                    class="h-10 w-10 rounded-full"
                                                />
                                                <div
                                                    v-else
                                                    class="flex h-10 w-10 items-center justify-center rounded-full bg-gray-400 text-sm font-bold text-white"
                                                >
                                                    {{ getInitial(student) }}
                                                </div>
                                            </div>

                                            <div class="ml-4">
                                                <!-- Display parent information from the nested structure -->
                                                <div v-if="student.parents && student.parents.length > 0" class="text-sm font-medium">
                                                    {{ student.parents[0].name }}
                                                </div>
                                                <div v-else class="text-sm font-medium text-gray-400">No Parent Assigned</div>

                                                <div
                                                    v-if="student.parents && student.parents.length > 0"
                                                    class="text-sm text-gray-500 dark:text-gray-400"
                                                >
                                                    {{ student.parents[0].email }}
                                                </div>
                                                <div v-else class="text-sm text-gray-500 dark:text-gray-400">-</div>

                                                <!-- If there are multiple parents, show a count -->
                                                <div v-if="student.parents && student.parents.length > 1" class="mt-1 text-xs text-blue-500">
                                                    +{{ student.parents.length - 1 }} more parent(s)
                                                </div>
                                            </div>
                                        </div>
                                    </td>


                                    <td class="whitespace-nowrap px-6 py-5 text-sm text-gray-500">
                                        <div class="flex space-x-3">
                                            <!-- <button
                                                class="rounded-md p-1.5 text-blue-600 transition-colors hover:bg-blue-50 hover:text-blue-800 dark:text-blue-400 dark:hover:bg-blue-900/30 dark:hover:text-blue-300"
                                            >
                                                <svg
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    class="h-5 w-5"
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
                                            </button> -->
                                            <!-- Changed this button to Link Parent -->
                                            <button
                                                @click="openLinkParentModal(student.id)"
                                                class="rounded-md p-1.5 text-purple-600 transition-colors hover:bg-purple-50 hover:text-purple-800 dark:text-purple-400 dark:hover:bg-purple-900/30 dark:hover:text-purple-300"
                                            >
                                                <svg
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    class="h-5 w-5"
                                                    fill="none"
                                                    viewBox="0 0 24 24"
                                                    stroke="currentColor"
                                                >
                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"
                                                    />
                                                </svg>
                                            </button>
                                            <button
                                                @click="removeStudent(student.id)"
                                                class="rounded-md p-1.5 text-red-600 transition-colors hover:bg-red-50 hover:text-red-800 dark:text-red-400 dark:hover:bg-red-900/30 dark:hover:text-red-300"
                                            >
                                                <svg
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    class="h-5 w-5"
                                                    fill="none"
                                                    viewBox="0 0 24 24"
                                                    stroke="currentColor"
                                                >
                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                                                    />
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- New Pagination Component -->
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

        <!-- Add Students by Email Modal -->
        <div v-if="showAddStudentsByEmailModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
            <div class="w-full max-w-md rounded-lg bg-white p-6 shadow-lg dark:bg-gray-800">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-lg font-medium">Add Students by Email</h3>

                    <button @click="showAddStudentsByEmailModal = false" class="text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form @submit.prevent="addStudentsByEmail" class="space-y-4">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300"> Email Addresses </label>
                        <textarea
                            v-model="studentEmails"
                            placeholder="Enter email addresses separated by commas, spaces, or new lines"
                            class="w-full rounded-lg border-gray-300 p-3 text-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700"
                            rows="5"
                            required
                        ></textarea>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Enter emails of existing students to add them to this class</p>
                    </div>

                    <!-- Error message display -->
                    <div v-if="errorMessage" class="rounded-md border bg-red-50 p-3 text-red-700">
                        <p>{{ errorMessage }}</p>
                    </div>

                    <!-- Results display -->
                    <div v-if="addResult" class="rounded-md border p-3 text-sm">
                        <div v-if="addResult.added.length > 0" class="mb-2">
                            <p class="mb-1 font-medium text-green-600 dark:text-green-400">Successfully added:</p>
                            <ul class="list-disc pl-5 text-gray-600 dark:text-gray-300">
                                <li v-for="email in addResult.added" :key="email">{{ email }}</li>
                            </ul>
                        </div>
                        <div v-if="addResult.skipped.length > 0">
                            <p class="mb-1 font-medium text-amber-600 dark:text-amber-400">Skipped (already enrolled or not found):</p>
                            <ul class="list-disc pl-5 text-gray-600 dark:text-gray-300">
                                <li v-for="email in addResult.skipped" :key="email">{{ email }}</li>
                            </ul>
                        </div>
                    </div>

                    <div class="flex justify-end space-x-2 pt-4">
                        <button
                            type="button"
                            @click="showAddStudentsByEmailModal = false"
                            class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium transition-colors hover:bg-gray-50 dark:border-gray-600 dark:hover:bg-gray-700"
                        >
                            Close
                        </button>
                        <button
                            type="submit"
                            class="hover:bg-primary-700 flex items-center gap-2 rounded-lg bg-primary-600 px-4 py-2.5 text-sm font-medium text-white transition-colors"
                            :disabled="isAddingStudents"
                        >
                            <svg
                                v-if="isAddingStudents"
                                class="h-4 w-4 animate-spin text-white"
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
                            {{ isAddingStudents ? 'Adding...' : 'Add Students' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- New Import Students Modal -->
        <div v-if="showImportStudentsModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
            <div class="w-full max-w-md rounded-lg bg-white p-6 shadow-lg dark:bg-gray-800">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-lg font-medium">Import Students</h3>
                    <button @click="showImportStudentsModal = false" class="text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form @submit.prevent="importStudents" class="space-y-4">
                    <!-- Hidden input for class ID -->
                    <input type="hidden" :value="currentSection?.id" />

                    <!-- File Input -->
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Upload Excel File</label>
                        <div class="mt-1 flex justify-center rounded-md border-2 border-dashed border-gray-300 px-6 pb-6 pt-5 dark:border-gray-600">
                            <div class="space-y-1 text-center">
                                <svg
                                    class="mx-auto h-12 w-12 text-gray-400"
                                    stroke="currentColor"
                                    fill="none"
                                    viewBox="0 0 48 48"
                                    aria-hidden="true"
                                >
                                    <path
                                        d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02"
                                        stroke-width="2"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                    />
                                </svg>
                                <div class="flex text-sm text-gray-600">
                                    <label
                                        for="file-upload"
                                        class="relative cursor-pointer rounded-md bg-white font-medium text-blue-600 focus-within:outline-none focus-within:ring-2 focus-within:ring-blue-500 focus-within:ring-offset-2 hover:text-blue-500 dark:bg-gray-800 dark:text-blue-400 dark:hover:text-blue-300"
                                    >
                                        <span>Upload a file</span>
                                        <input
                                            id="file-upload"
                                            name="file-upload"
                                            type="file"
                                            class="sr-only"
                                            @change="handleFileUpload"
                                            accept=".xlsx,.xls,.csv"
                                            required
                                        />
                                    </label>
                                    <!-- <p class="pl-1 dark:text-gray-400">or drag and drop</p> -->
                                </div>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Excel file only</p>
                                <p v-if="importFile" class="mt-2 text-sm font-medium text-green-600">
                                    Selected: {{ importFile.name }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Error message display -->
                    <div v-if="importErrorMessage" class="rounded-md border bg-red-50 p-3 text-red-700">
                        <p>{{ importErrorMessage }}</p>
                    </div>

                    <!-- Success message display -->
                    <div v-if="importSuccessMessage" class="rounded-md border bg-green-50 p-3 text-green-700">
                        <p>{{ importSuccessMessage }}</p>
                    </div>

                    <div class="flex justify-end space-x-2 pt-4">
                        <button
                            type="button"
                            @click="showImportStudentsModal = false"
                            class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium transition-colors hover:bg-gray-50 dark:border-gray-600 dark:hover:bg-gray-700"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            class="flex items-center gap-2 rounded-lg bg-green-600 px-4 py-2.5 text-sm font-medium text-white transition-colors hover:bg-green-700"
                            :disabled="isImportingStudents || !importFile"
                        >
                            <svg
                                v-if="isImportingStudents"
                                class="h-4 w-4 animate-spin text-white"
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
                            {{ isImportingStudents ? 'Uploading...' : 'Upload Students' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Link Parent Modal -->
        <div v-if="showLinkParentModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
            <div class="w-full max-w-md rounded-lg bg-white p-6 shadow-lg dark:bg-gray-800">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-lg font-medium">Link Parent to Student</h3>
                    <button @click="showLinkParentModal = false" class="text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form @submit.prevent="linkParentToStudent" class="space-y-4">
                    <!-- Hidden input for student class ID -->
                    <input type="hidden" v-model="selectedStudentClassId" />

                    <!-- Search for Parent -->
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300"> Search for Parent </label>
                        <div class="relative">
                            <input
                                type="text"
                                v-model="parentSearchQuery"
                                placeholder="Search by name or email..."
                                class="w-full rounded-lg border-gray-300 p-3 pr-10 text-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700"
                            />
                            <div v-if="isSearchingParents" class="absolute right-3 top-3">
                                <svg class="h-5 w-5 animate-spin text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                                    <path
                                        class="opacity-75"
                                        fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                    />
                                </svg>
                            </div>
                        </div>

                        <!-- Search results -->
                        <div v-if="searchResults.length > 0" class="mt-2 overflow-hidden rounded-lg border">
                            <ul class="max-h-40 overflow-y-auto">
                                <li
                                    v-for="parent in searchResults"
                                    :key="parent.id"
                                    @click="selectParent(parent)"
                                    class="cursor-pointer border-b p-2 last:border-b-0 hover:bg-gray-100 dark:hover:bg-gray-700"
                                >
                                    <div class="font-medium">{{ parent.name }}</div>
                                    <div class="text-xs text-gray-500">{{ parent.email }}</div>
                                </li>
                            </ul>
                        </div>

                        <!-- Selected parent -->
                        <div v-if="selectedParent" class="mt-3 rounded-lg bg-blue-50 p-3 dark:bg-blue-900/20">
                            <div class="font-medium">{{ selectedParent.name }}</div>
                            <div class="text-sm text-gray-600 dark:text-gray-400">{{ selectedParent.email }}</div>
                        </div>

                        <div v-if="validationErrors.parent_id" class="mt-1 text-sm text-red-600">
                            {{ validationErrors.parent_id }}
                        </div>
                    </div>

                    <!-- Selected Student -->
                    <div v-if="selectedStudent">
                        <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300"> Selected Student </label>
                        <div class="rounded-lg border bg-gray-50 p-3 dark:bg-gray-700">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="font-medium">{{ selectedStudent.name }}</p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ selectedStudent.email }}</p>
                                </div>
                            </div>
                        </div>
                        <div v-if="validationErrors.student_class_id" class="mt-1 text-sm text-red-600">
                            {{ validationErrors.student_class_id }}
                        </div>
                    </div>

                    <!-- Success message -->
                    <div v-if="linkParentResult" class="rounded-md border bg-green-50 p-3 text-green-700">
                        <p>{{ linkParentResult }}</p>
                    </div>

                    <!-- Error message -->
                    <div v-if="linkParentError" class="rounded-md border bg-red-50 p-3 text-red-700">
                        <p>{{ linkParentError }}</p>
                    </div>

                    <!-- Buttons -->
                    <div class="flex justify-end space-x-2 pt-4">
                        <button
                            type="button"
                            @click="showLinkParentModal = false"
                            class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium transition-colors hover:bg-gray-50 dark:border-gray-600 dark:hover:bg-gray-700"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            class="hover:bg-primary-700 flex items-center gap-2 rounded-lg bg-primary-600 px-4 py-2.5 text-sm font-medium text-white transition-colors"
                            :disabled="isLinkingParent || !selectedParent || !selectedStudentClassId"
                        >
                            <svg
                                v-if="isLinkingParent"
                                class="h-4 w-4 animate-spin text-white"
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                            >
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                                <path
                                    class="opacity-75"
                                    fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                />
                            </svg>
                            {{ isLinkingParent ? 'Linking...' : 'Link Parent' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div v-if="showRemoveModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
            <div class="w-full max-w-md rounded-lg bg-white p-6 shadow-lg dark:bg-gray-800">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-lg font-medium">Confirm Removal</h3>
                    <button @click="showRemoveModal = false" class="text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <p class="mb-4">Are you sure you want to remove this student?</p>
                <div class="flex justify-end space-x-2 pt-4">
                    <button
                        @click="showRemoveModal = false"
                        class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium transition-colors hover:bg-gray-50 dark:border-gray-600 dark:hover:bg-gray-700"
                    >
                        Cancel
                    </button>
                    <button
                        @click="confirmRemoveStudent"
                        class="rounded-lg bg-red-600 px-4 py-2.5 text-sm font-medium text-white transition-colors hover:bg-red-700"
                        :disabled="isRemovingStudent"
                    >
                        <svg
                            v-if="isRemovingStudent"
                            class="mr-2 inline h-4 w-4 animate-spin text-white"
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
                        {{ isRemovingStudent ? 'Removing...' : 'Confirm Remove' }}
                    </button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
