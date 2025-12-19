<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, usePage } from '@inertiajs/vue3';
import { computed, inject, onMounted, ref, watch } from 'vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Assessment Records',
        href: '/assessment-records',
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
const selectedAssignment = ref<string | null>(null);
const searchQuery = ref('');
const sortBy = ref('name');
const sortDirection = ref('asc');
const showFilters = ref(false);
const statusFilter = ref('all');

// Class sections data
const classSections = ref([
    {
        id: 'sci101a',
        name: 'Science 101 - Section A',
        icon: '🧪',
        term: 'Fall Semester 2023',
        studentCount: 28,
        presentCount: 26,
        pendingAssignments: 3,
        toGradeCount: 12,
        averageScore: 87,
        performance: {
            quiz1: 82,
            midterm: 78,
            project: 91,
            quiz2: 85,
        },
        gradeDistribution: {
            a: 60,
            b: 80,
            c: 70,
            d: 40,
            f: 30,
        },
        assignments: [
            { id: 'midterm', name: 'Midterm Exam', category: 'exam', weight: 30, totalPoints: 100, dueDate: '2023-10-15' },
            { id: 'quiz1', name: 'Quiz 1', category: 'quiz', weight: 10, totalPoints: 50, dueDate: '2023-09-20' },
            { id: 'lab', name: 'Lab Report', category: 'lab', weight: 15, totalPoints: 100, dueDate: '2023-10-05' },
            { id: 'project', name: 'Final Project', category: 'project', weight: 25, totalPoints: 100, dueDate: '2023-11-20' },
        ],
        students: [
            {
                id: '2023001',
                name: 'Emma Thompson',
                email: 'emma.t@school.edu',
                status: 'present',
                notes: '',
                score: 92,
                gradeComment: 'Excellent work!',
            },
            { id: '2023002', name: 'Liam Johnson', email: 'liam.j@school.edu', status: 'present', notes: '', score: 85, gradeComment: 'Good effort' },
            {
                id: '2023003',
                name: 'Olivia Davis',
                email: 'olivia.d@school.edu',
                status: 'present',
                notes: '',
                score: 78,
                gradeComment: 'Needs improvement in section 3',
            },
            {
                id: '2023004',
                name: 'Noah Wilson',
                email: 'noah.w@school.edu',
                status: 'absent',
                notes: 'Medical appointment',
                score: 0,
                gradeComment: 'Absent - needs to make up exam',
            },
            {
                id: '2023005',
                name: 'Ava Martinez',
                email: 'ava.m@school.edu',
                status: 'present',
                notes: '',
                score: 95,
                gradeComment: 'Outstanding performance!',
            },
            { id: '2023006', name: 'Ethan Brown', email: 'ethan.b@school.edu', status: 'present', notes: '', score: 82, gradeComment: '' },
            { id: '2023007', name: 'Sophia Taylor', email: 'sophia.t@school.edu', status: 'present', notes: '', score: 88, gradeComment: '' },
            {
                id: '2023008',
                name: 'Mason Anderson',
                email: 'mason.a@school.edu',
                status: 'late',
                notes: 'Arrived 15 minutes late',
                score: 79,
                gradeComment: 'Good work but needs more detail',
            },
            { id: '2023009', name: 'Isabella Thomas', email: 'isabella.t@school.edu', status: 'present', notes: '', score: 91, gradeComment: '' },
            { id: '2023010', name: 'James Jackson', email: 'james.j@school.edu', status: 'present', notes: '', score: 84, gradeComment: '' },
        ],
    },
    // Other sections...
]);

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
    selectedAssignment.value = section.assignments[0]?.id || null;

    // Update URL with the selected section ID without navigating
    const url = new URL(window.location.href);
    url.searchParams.set('sectionId', section.id);
    window.history.replaceState({}, '', url.toString());
}

// Get letter grade based on score
function getLetterGrade(score) {
    if (score >= 90) return 'A';
    if (score >= 80) return 'B';
    if (score >= 70) return 'C';
    if (score >= 60) return 'D';
    return 'F';
}

// Get CSS class for grade display
function getGradeClass(score) {
    if (score >= 90) return 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900 dark:text-emerald-300';
    if (score >= 80) return 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300';
    if (score >= 70) return 'bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-300';
    if (score >= 60) return 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-300';
    return 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300';
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

// Save assessment records
function saveRecords() {
    // In a real application, this would send the data to a server
    const notification = document.createElement('div');
    notification.className =
        'fixed bottom-4 right-4 bg-emerald-500 text-white px-6 py-3 rounded-lg shadow-lg transform transition-all duration-500 flex items-center';
    notification.innerHTML = `
    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
      <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
    </svg>
    Assessment records saved successfully!
  `;
    document.body.appendChild(notification);

    setTimeout(() => {
        notification.style.opacity = '0';
        setTimeout(() => {
            document.body.removeChild(notification);
        }, 500);
    }, 3000);
}

// Save grades for the current assignment
function saveGrades() {
    // In a real application, this would send the data to a server
    alert('Grades saved successfully!');
}

// Export grades to CSV
function exportGrades() {
    // In a real application, this would generate and download a CSV file
    alert('Grades exported successfully!');
}

// Sort students
function sortStudents(a, b) {
    const direction = sortDirection.value === 'asc' ? 1 : -1;

    if (sortBy.value === 'name') {
        return a.name.localeCompare(b.name) * direction;
    } else if (sortBy.value === 'id') {
        return a.id.localeCompare(b.id) * direction;
    } else if (sortBy.value === 'score') {
        return (a.score - b.score) * direction;
    } else if (sortBy.value === 'grade') {
        return (a.score - b.score) * direction;
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
    if (!currentSection.value) return [];

    return currentSection.value.students
        .filter((student) => {
            // Filter by search query
            if (searchQuery.value) {
                const query = searchQuery.value.toLowerCase();
                const matchesSearch =
                    student.name.toLowerCase().includes(query) ||
                    student.id.toLowerCase().includes(query) ||
                    student.email.toLowerCase().includes(query);

                if (!matchesSearch) return false;
            }

            // Filter by status
            if (statusFilter.value !== 'all') {
                return student.status === statusFilter.value;
            }

            return true;
        })
        .sort(sortStudents);
});

// Computed property for selected assignment details
const selectedAssignmentDetails = computed(() => {
    if (!currentSection.value || !selectedAssignment.value) return null;

    return currentSection.value.assignments.find((a) => a.id === selectedAssignment.value) || null;
});

// Computed property for grade distribution
const gradeDistribution = computed(() => {
    if (!currentSection.value) return { a: 0, b: 0, c: 0, d: 0, f: 0 };

    const distribution = { a: 0, b: 0, c: 0, d: 0, f: 0 };

    currentSection.value.students.forEach((student) => {
        const grade = getLetterGrade(student.score).toLowerCase();
        if (distribution[grade] !== undefined) {
            distribution[grade]++;
        }
    });

    return distribution;
});

// Computed property for grade distribution percentages
const gradeDistributionPercentages = computed(() => {
    if (!currentSection.value) return { a: 0, b: 0, c: 0, d: 0, f: 0 };

    const total = currentSection.value.students.length;
    const distribution = gradeDistribution.value;

    return {
        a: Math.round((distribution.a / total) * 100) || 0,
        b: Math.round((distribution.b / total) * 100) || 0,
        c: Math.round((distribution.c / total) * 100) || 0,
        d: Math.round((distribution.d / total) * 100) || 0,
        f: Math.round((distribution.f / total) * 100) || 0,
    };
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
                selectedAssignment.value = section.assignments[0]?.id || null;
            }
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
                selectedAssignment.value = section.assignments[0]?.id || null;
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
                selectedAssignment.value = section.assignments[0]?.id || null;
            }
        }
        // Then check for itemId (new parameter from sidebar)
        else if (itemId) {
            const section = findSectionById(itemId);
            if (section && (!currentSection.value || currentSection.value.id !== section.id)) {
                // Only update if it's a different section
                currentSection.value = section;
                selectedAssignment.value = section.assignments[0]?.id || null;
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
    window.addEventListener('section-changed', (event) => {
        if (event.detail && event.detail.sectionId) {
            const section = findSectionById(event.detail.sectionId);
            if (section && (!currentSection.value || currentSection.value.id !== section.id)) {
                // Only update if it's a different section
                currentSection.value = section;
                selectedAssignment.value = section.assignments[0]?.id || null;
            }
        }
    });

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
            selectedAssignment.value = section.assignments[0]?.id || null;
            return;
        }
    }
    // Then check for itemId (which could be a section ID from sidebar)
    else if (itemIdFromUrl) {
        const section = findSectionById(itemIdFromUrl);
        if (section) {
            currentSection.value = section;
            selectedAssignment.value = section.assignments[0]?.id || null;
            return;
        }
    }
    // Finally check for injected item ID
    else if (currentItemId.value) {
        const section = findSectionById(currentItemId.value);
        if (section) {
            currentSection.value = section;
            selectedAssignment.value = section.assignments[0]?.id || null;
            return;
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
                <div class="overflow-hidden rounded-2xl shadow-lg">
                    <div class="relative p-8">
                        <div class="absolute right-0 top-0 -mr-20 -mt-20 h-64 w-64 rounded-full blur-3xl"></div>
                        <div class="absolute bottom-0 left-0 -mb-10 -ml-10 h-40 w-40 rounded-full blur-2xl"></div>
                        <div class="relative z-10">
                            <div class="flex flex-col justify-between gap-4 md:flex-row md:items-center">
                                <div>
                                    <div class="flex items-center gap-3">
                                        <span class="text-4xl">{{ currentSection.icon }}</span>
                                        <div>
                                            <h1 class="text-2xl font-bold md:text-3xl">{{ currentSection.name }}</h1>
                                            <p class="mt-1">{{ currentSection.term }} • {{ currentSection.studentCount }} Students</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3">
                                    <div class="flex items-center gap-2 rounded-lg px-4 py-2">
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

                <!-- Assessment Records Content -->
                <div class="overflow-hidden rounded-2xl shadow-lg">
    <div class="sticky top-0 z-10 overflow-hidden rounded-lg border-b border-gray-300 bg-white shadow-md backdrop-blur-md dark:border-gray-700 dark:bg-gray-900/80">
      <div class="p-6 mb-6">
    <div class="flex flex-col md:flex-row md:items-end gap-6">
        <!-- Search on the left -->
        <div class="flex-1 md:w-1/3">
            <label class="mb-2 block text-sm font-semibold text-gray-800 dark:text-gray-300">Search</label>
            <input
                v-model="searchQuery"
                type="text"
                placeholder="Search for assignments..."
                class="w-full appearance-none rounded-lg border-gray-300 bg-gray-50 px-4 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-800"
            />
        </div>

        <!-- Assessment dropdown and details on the right -->
        <div class="flex-1 md:w-2/3">
            <div class="flex flex-col gap-6 md:flex-row md:items-end">
                <div class="flex-1">
                    <label class="mb-2 block text-sm font-semibold text-gray-800 dark:text-gray-300">Assessment</label>
                    <select
                        v-model="selectedAssignment"
                        class="w-full appearance-none rounded-lg border-gray-300 bg-gray-50 px-4 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-800"
                    >
                        <option v-for="assignment in currentSection.assignments" :key="assignment.id" :value="assignment.id">
                            {{ assignment.name }}
                        </option>
                    </select>
                </div>

                <div v-if="selectedAssignmentDetails" class="grid w-full grid-cols-2 gap-6 md:w-auto md:grid-cols-3">
                    <div class="rounded-lg bg-gray-100 p-3 shadow dark:bg-gray-800">
                        <label class="mb-1 block text-sm font-semibold text-gray-800 dark:text-gray-300">Category</label>
                        <div class="text-sm font-medium">
                            <span
                                :class="getCategoryColor(selectedAssignmentDetails.category)"
                                class="inline-flex items-center rounded-full px-3 py-1 text-xs"
                            >
                                {{
                                    selectedAssignmentDetails.category.charAt(0).toUpperCase() +
                                    selectedAssignmentDetails.category.slice(1)
                                }}
                            </span>
                        </div>
                    </div>
                    <div class="rounded-lg bg-gray-100 p-3 shadow dark:bg-gray-800">
                        <label class="mb-1 block text-sm font-semibold text-gray-800 dark:text-gray-300">Weight</label>
                        <div class="text-sm font-medium text-gray-900 dark:text-white">{{ selectedAssignmentDetails.weight }}%</div>
                    </div>
                    <div class="rounded-lg bg-gray-100 p-3 shadow dark:bg-gray-800">
                        <label class="mb-1 block text-sm font-semibold text-gray-800 dark:text-gray-300">Total Points</label>
                        <div class="text-sm font-medium text-gray-900 dark:text-white">
                            {{ selectedAssignmentDetails.totalPoints }} pts
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


        <!-- Student Assessment Records Table -->
        <div class="mt-4 mb-6 overflow-x-auto">
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
                            class="px-6 py-3 text-left text-sm font-semibold uppercase tracking-wide text-indigo-700 dark:text-indigo-300"
                        >
                            ID
                        </th>
                        <th
                            scope="col"
                            class="px-6 py-3 text-left text-sm font-semibold uppercase tracking-wide text-indigo-700 dark:text-indigo-300"
                        >
                            Score
                        </th>
                        <th
                            scope="col"
                            class="px-6 py-3 text-left text-sm font-semibold uppercase tracking-wide text-indigo-700 dark:text-indigo-300"
                        >
                            Grade
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
                                v-model="student.score"
                                min="0"
                                class="w-24 rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700"
                            />
                        </td>
                        <td class="px-6 py-4">
                            <span
                                :class="`inline-flex items-center rounded-lg px-3 py-1 text-xs font-medium ${getGradeClass(student.score)}`"
                                >{{ getLetterGrade(student.score) }}</span
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
        <div class="flex justify-end border-t border-gray-200 bg-gray-50 p-6 dark:border-gray-700 dark:bg-gray-800/50">
            <button
                @click="saveRecords"
                class="flex items-center gap-2 rounded-lg bg-gradient-to-r from-indigo-600 to-violet-600 px-6 py-3 text-sm font-medium text-white shadow-md transition-colors duration-200 hover:from-indigo-700 hover:to-violet-700"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                Save Assessment Records
            </button>
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
