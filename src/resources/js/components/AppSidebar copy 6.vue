<script setup lang="ts">
import type { SharedData, User } from '@/types';
import { Link, router, usePage, useRoute } from '@inertiajs/vue3';
import { computed, onMounted, provide, reactive, ref, watch, onUnmounted } from 'vue';

// // Define props or state management for `userType`
// // Define props or state management for `userType`
const userItems = ref([]);
const openDropdownId = ref(null); // Track which dropdown is open

// Close dropdown when clicking outside
const closeDropdownOnClickOutsideSidebar = (event) => {
    // Check if the click is outside any dropdown button or menu
    const isOutsideDropdown = !event.target.closest('.dropdown-trigger') &&
                             !event.target.closest('.dropdown-menu');
    if (isOutsideDropdown && openDropdownId.value !== null) {
        openDropdownId.value = null;
    }
};

// Toggle dropdown visibility
const toggleDropdown = (id) => {
    openDropdownId.value = openDropdownId.value === id ? null : id;
};


// Modify the deleteClass function to properly handle the API call
const deleteClass = async (id) => {
    if (confirm('Are you sure you want to delete this class?')) {
        try {
            // Send delete request to your backend
            await router.delete(`/classes/${id}`, {
                onSuccess: () => {
                    // Remove the class from the UI
                    userItems.value = userItems.value.filter(item => item.id !== id);
                },
                onError: (errors) => {
                    console.error('Error deleting class:', errors);
                    alert('Failed to delete class. Please try again.');
                }
            });
        } catch (error) {
            console.error('Error deleting class:', error);
            alert('An unexpected error occurred. Please try again.');
        }
    }

    // Close the dropdown
    openDropdownId.value = null;
};

// Implement the editClass function to navigate to edit page or open edit modal// Implement the editClass function to navigate to edit page or open edit modal// Implement the editClass function to navigate to edit page or open edit modal
const editClass = (classItem) => {
    // Close the dropdown first
    openDropdownId.value = null;

    // Set editing mode
    isEditing.value = true;

    // Log the class item to see what data we have
    console.log("Class data for editing:", classItem);

    // Pre-fill the form with class data - ensure all fields have default values
    Object.assign(newClass, {
        id: classItem.id,
        name: classItem.name || '',
        icon: classItem.icon || '📚',
        term: classItem.term || '',
        // Use empty string instead of null for text fields
        description: classItem.description || '',
        room: classItem.room || '',
        // Use 0 instead of null for numeric fields if needed
        max_students: classItem.max_students || 0,
        professor_id: user.id,
    });

    // Parse schedule if available
    if (classItem.schedule) {
        try {
            // Check if schedule is already an object or a JSON string
            if (typeof classItem.schedule === 'string') {
                scheduleEntries.value = JSON.parse(classItem.schedule);
            } else if (Array.isArray(classItem.schedule)) {
                scheduleEntries.value = classItem.schedule;
            } else {
                // Default schedule if format is unexpected
                scheduleEntries.value = [{ day: 'Monday', time: '8:00 AM - 10:00 AM' }];
            }
        } catch (e) {
            console.error('Error parsing schedule:', e);
            scheduleEntries.value = [{ day: 'Monday', time: '8:00 AM - 10:00 AM' }];
        }
    } else {
        scheduleEntries.value = [{ day: 'Monday', time: '8:00 AM - 10:00 AM' }];
    }

    // Show the modal
    showAddClassModal.value = true;
};
// Add a function to handle form submission for both add and edit
const isEditing = ref(false);

function openAddClassModal() {
    isEditing.value = false;
    Object.assign(newClass, {
        id: generateId(),
        name: '',
        icon: '📚',
        term: '',
        description: '',
        schedule: '',
        room: '',
        max_students: null,
        professor_id: user.id,
    });
    scheduleEntries.value = [{ day: 'Monday', time: '8:00 AM - 10:00 AM' }];
    showAddClassModal.value = true;
}

// Modify addClass to handle both add and edit
// Modify addClass to handle both add and edit
// Add this to your script section
const formErrors = ref({});

// Modify addClass to handle both add and edit with better error handling
function addClass() {
    // Format the schedule entries
    newClass.schedule = JSON.stringify(scheduleEntries.value);

    // Ensure no null values are sent for text fields
    newClass.description = newClass.description || '';
    newClass.room = newClass.room || '';

    // Ensure max_students is a number or 0, not null
    newClass.max_students = newClass.max_students || 0;

    console.log("Submitting class data:", newClass);

    if (isEditing.value) {
        // Update existing class
        router.put(`/classes/${newClass.id}`, newClass, {
            onSuccess: () => {
                // Refresh the classes list to get updated data
                fetchClasses();
                showAddClassModal.value = false; // Close modal
            },
            onError: (errors) => {
                console.error('Error updating class:', errors);
                // You could display these errors to the user
                alert('Error updating class: ' + JSON.stringify(errors));
            },
        });
    } else {
        // Add new class code...
    }
}

// function addClass() {
//     // Format the schedule entries
//     newClass.schedule = JSON.stringify(scheduleEntries.value);

//     if (isEditing.value) {
//         // Update existing class
//         router.put(`/classes/${newClass.id}`, newClass, {
//             onSuccess: (response) => {
//                 // Update the class in the UI
//                 const index = userItems.value.findIndex(item => item.id === newClass.id);
//                 if (index !== -1) {
//                     userItems.value[index] = {
//                         ...userItems.value[index],
//                         name: newClass.name,
//                         icon: newClass.icon,
//                         term: newClass.term,
//                     };
//                 }
//                 showAddClassModal.value = false;
//             },
//             onError: (errors) => {
//                 console.error(errors);
//             },
//         });
//     } else {
//         // Add new class (your existing code)
//         router.post('/classes', newClass, {
//             onSuccess: (response) => {
//                 if (response?.props?.class) {
//                     const newClassData = response.props.class;
//                     userItems.value.push({
//                         id: newClassData.id.toString(),
//                         name: newClassData.name,
//                         icon: newClassData.icon || '📚',
//                         term: newClassData.term,
//                         studentCount: 0,
//                         presentCount: 0,
//                         attendanceSummary: {
//                             present: 0,
//                             absent: 0,
//                             late: 0,
//                             excused: 0,
//                         },
//                         students: [],
//                     });
//                 } else {
//                     userItems.value.push({
//                         id: newClass.id.toString(),
//                         name: newClass.name,
//                         icon: newClass.icon,
//                         term: newClass.term,
//                         studentCount: 0,
//                         presentCount: 0,
//                         attendanceSummary: {
//                             present: 0,
//                             absent: 0,
//                             late: 0,
//                             excused: 0,
//                         },
//                         students: [],
//                     });
//                 }
//                 showAddClassModal.value = false;
//             },
//             onError: (errors) => {
//                 console.error(errors);
//             },
//         });
//     }
// }

const fetchClasses = async () => {
    try {
        let url = '';

        if (userType === 'prof') {
            url = `/classes`;
        } else if (userType === 'student') {
            url = `/student/classes`;
        } else if (userType === 'parent') {
            url = `/parent/children`; // Parent-specific endpoint
        }

        const response = await fetch(url);
        const data = await response.json();

        if (userType === 'parent' && data.children) {
            userItems.value = data.children.map((child) => ({
                id: child.id.toString(),
                name: child.name, // Updated to use the full name
                icon: child.icon || '👧👦', // Default icon if not set in the database
            }));
        } else if (data.classes) {
            userItems.value = data.classes.map((cls) => ({
                id: cls.id.toString(),
                name: cls.name,
                icon: cls.icon || '📘',
                term: cls.term,
                studentCount: cls.students?.length || 0,
                presentCount: 0,
                attendanceSummary: {
                    present: 0,
                    absent: 0,
                    late: 0,
                    excused: 0,
                },
                students: cls.students || [],
            }));
        }
    } catch (error) {
        console.error('Error fetching data:', error);
    }
};

onMounted(() => {
    if (userType === 'prof' || userType === 'student') {
        fetchClasses();
    } else if (userType === 'parent') {
        fetchClasses();
    }

    // Add click event listener to close dropdowns when clicking outside
    document.addEventListener('click', closeDropdownOnClickOutsideSidebar);
});

// Clean up event listener on component unmount
onUnmounted(() => {
    document.removeEventListener('click', closeDropdownOnClickOutsideSidebar);
});

// // Fetch classes from API
// const fetchClasses = async () => {
//   try {
//     let url = '';

//     if (userType === 'prof') {
//       url = `/classes`; // Professor-specific endpoint
//     } else if (userType === 'student') {
//       url = `/student/classes`; // Student-specific endpoint
//     }

//     const response = await fetch(url);
//     const data = await response.json();

//     if (data.classes) {
//       userItems.value = data.classes.map(cls => ({
//         id: cls.id.toString(),
//         name: cls.name,
//         icon: cls.icon || '📘',
//         term: cls.term,
//         studentCount: cls.students?.length || 0, // For prof view
//         presentCount: 0, // Initialize with 0
//         attendanceSummary: {
//           present: 0,
//           absent: 0,
//           late: 0,
//           excused: 0
//         },
//         students: cls.students || []
//       }));
//     }
//   } catch (error) {
//     console.error('Error fetching classes:', error);
//   }
// };

// onMounted(() => {
//   if (userType === 'prof' || userType === 'student') {
//     fetchClasses();
//   } else if (userType === 'parent') {
//     // Example data for parents, modify based on your API
//     userItems.value = [
//       { id: 'child1', name: 'Alex Johnson', icon: '👦' },
//       { id: 'child2', name: 'Emma Johnson', icon: '👧' },
//     ];
//   }
// });

// const userItems = ref([]);

// // Fetch classes from API
// const fetchClasses = async () => {
//   try {
//     const professorId = user.id; // Assuming you have the professor's ID in `user`
//     const response = await fetch(`/classes/${professorId}`); // Replace with your actual API endpoint
//     const data = await response.json();

//     if (data.classes) {
//       userItems.value = data.classes.map(cls => ({
//         id: cls.id.toString(),  // Convert to string if needed
//         name: cls.name,
//         icon: cls.icon || '📘', // Default icon if not provided
//         term: cls.term,
//         studentCount: cls.students?.length || 0, // Add student count
//         presentCount: 0, // Initialize with 0
//         attendanceSummary: {
//           present: 0,
//           absent: 0,
//           late: 0,
//           excused: 0
//         },
//         students: cls.students || []
//       }));
//     }
//   } catch (error) {
//     console.error('Error fetching classes:', error);
//   }
// };

// onMounted(() => {
//   if (userType === 'prof' || userType === 'student') {
//     fetchClasses();
//   } else if (userType === 'parent') {
//     // Example data for parents, modify based on your API
//     userItems.value = [
//       { id: 'child1', name: 'Alex Johnson', icon: '👦' },
//       { id: 'child2', name: 'Emma Johnson', icon: '👧' },
//     ];
//   }
// });

const showAddClassModal = ref(false);
const showLogoutModal = ref(false);

const scheduleDays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
const timeSlots = [
    '7:00 AM',
    '7:30 AM',
    '8:00 AM',
    '8:30 AM',
    '9:00 AM',
    '9:30 AM',
    '10:00 AM',
    '10:30 AM',
    '11:00 AM',
    '11:30 AM',
    '12:00 PM',
    '12:30 PM',
    '1:00 PM',
    '1:30 PM',
    '2:00 PM',
    '2:30 PM',
    '3:00 PM',
    '3:30 PM',
    '4:00 PM',
    '4:30 PM',
    '5:00 PM',
    '5:30 PM',
    '6:00 PM',
    '6:30 PM',
    '7:00 PM',
    '7:30 PM',
    '8:00 PM',
    '8:30 PM',
    '9:00 PM',
];

// Schedule state for the new class - using the exact format requested
const scheduleEntries = ref([{ day: 'Monday', time: '8:00 AM - 10:00 AM' }]);

// Add a new schedule entry
const addScheduleEntry = () => {
    scheduleEntries.value.push({
        day: scheduleDays[0],
        time: '10:00 AM - 12:00 PM',
    });
};

// Remove a schedule entry
const removeScheduleEntry = (index) => {
    scheduleEntries.value.splice(index, 1);
};

// Update time for a schedule entry
const updateScheduleTime = (index, startTime, endTime) => {
    scheduleEntries.value[index].time = `${startTime} - ${endTime}`;
};

// Parse time string to get start and end times
const parseTimeRange = (timeRange) => {
    const [startTime, endTime] = timeRange.split(' - ');
    return { startTime, endTime };
};

const newClass = reactive({
    id: '',
    name: '',
    icon: '📚',
    term: '',
    description: '',
    schedule: '',
    room: '',
    max_students: null,
    professor_id: '',
});

function generateId() {
    return 'class_' + Math.random().toString(36).substr(2, 9);
}

// function addClass() {
//     // Format the schedule entries in the exact format requested
//     newClass.schedule = JSON.stringify(scheduleEntries.value);

//     router.post('/classes', newClass, {
//         onSuccess: (response) => {
//             // If the backend returns the created class, add it to the UI
//             if (response?.props?.class) {
//                 const newClassData = response.props.class;
//                 userItems.value.push({
//                     id: newClassData.id.toString(),
//                     name: newClassData.name,
//                     icon: newClassData.icon || '📚',
//                     term: newClassData.term,
//                     studentCount: 0,
//                     presentCount: 0,
//                     attendanceSummary: {
//                         present: 0,
//                         absent: 0,
//                         late: 0,
//                         excused: 0,
//                     },
//                     students: [],
//                 });
//             } else {
//                 // Fallback if no class is returned
//                 userItems.value.push({
//                     id: newClass.id.toString(),
//                     name: newClass.name,
//                     icon: newClass.icon,
//                     term: newClass.term,
//                     studentCount: 0,
//                     presentCount: 0,
//                     attendanceSummary: {
//                         present: 0,
//                         absent: 0,
//                         late: 0,
//                         excused: 0,
//                     },
//                     students: [],
//                 });
//             }
//             showAddClassModal.value = false; // Close modal
//         },
//         onError: (errors) => {
//             console.error(errors); // Handle validation errors
//         },
//     });
// }

const availableIcons = [
    // General & Education
    '📚', // General studies
    '📝', // Writing/Exams
    '📖', // Literature/Reading

    // Science & Technology
    '🧪', // Science
    '🔬', // Research/Laboratory
    '🧬', // Biology
    '⚗️', // Chemistry
    '🌍', // Environmental Science/Geography
    '🦠', // Microbiology
    '📡', // Telecommunications/Engineering
    '🚀', // Aerospace Engineering

    // **Programming, IT & Computer Science**
    '💻', // General IT/Computer Science
    '🖥️', // Software Development/Programming
    '⌨️', // Coding/Typing
    '📟', // Embedded Systems/Hardware
    '🗄️', // Databases/Data Science
    '🧑‍💻', // Cybersecurity/Hacking
    '📊', // Data Analytics/AI
    '📡', // Networking
    '🖲️', // Computer Hardware/Peripherals
    '⚙️', // System Administration/DevOps
    '📁', // File Management/Cloud Computing

    // **Accountancy & Business Administration**
    '💰', // Finance/Accounting
    '📈', // Business/Economics/Investment
    '📉', // Risk Management
    '📝', // Reports/Auditing
    '🔖', // Business Law/Policies
    '🏦', // Banking
    '🏛️', // Corporate Management
    '💳', // Commerce/Marketing
    '🗂️', // Documentation/Record-Keeping
    '📑', // Invoices/Reports
    '📠', // Office Work/Admin

    // **Criminology & Law Enforcement**
    '⚖️', // Law
    '🔎', // Investigation/Forensics
    '🕵️‍♂️', // Detective/Intelligence
    '🚔', // Law Enforcement
    '👮‍♂️', // Police Studies
    '🩸', // Forensic Science
    '🔫', // Firearms Training
    '🚨', // Emergency Response
    '🏛️', // Criminal Justice
    '🔐', // Security/Prison Management
    '🧑‍⚖️', // Legal Studies/Judiciary
    '📜', // Evidence/Laws/Legislation
    '🛡️', // Homeland Security

    // Engineering & Technical Fields
    '🏗️', // Civil Engineering
    '🛠️', // Mechanical Engineering
    '🔋', // Electrical Engineering
    '📡', // Electronics/Communication
    '⚙️', // Industrial Engineering
    '🛩️', // Aviation
    '📷', // Photography/Multimedia
    '🎥', // Film/Cinematography
    '🎙️', // Communication/Journalism
];

// ==========================================
// PROPS AND EMITS
// ==========================================

const props = defineProps<{
    mainNavItems: { title: string; href: string; icon: string }[];
    activePage: string;
}>();

const emit = defineEmits<{
    'update:activePage': [value: string];
    'update:content': [type: string, id: string | number];
}>();

// ==========================================
// USER INFORMATION AND AUTHENTICATION
// ==========================================

const page = usePage<SharedData>();
const user = page.props.auth.user as User;
const userType = user.user_type;

const portalTitles = {
    prof: 'Professor Portal',
    student: 'Student Portal',
    parent: 'Parent Portal',
};

const userRoles = {
    prof: 'Professor/Admin',
    student: 'Student',
    parent: 'Parent/Guardian',
};

const portalTitle = computed(() => portalTitles[userType] || 'Portal');
const userRole = computed(() => userRoles[userType] || 'User');

// ==========================================
// UI STATE MANAGEMENT
// ==========================================

const sidebarOpen = ref(false);
const toggleSidebar = () => (sidebarOpen.value = !sidebarOpen.value);

const showUserDropdown = ref(false);
const toggleUserDropdown = () => (showUserDropdown.value = !showUserDropdown.value);

const closeDropdownOnClickOutside = (event) => {
    const dropdown = document.getElementById('user-dropdown');
    const trigger = document.getElementById('user-dropdown-trigger');
    if (dropdown && trigger && !dropdown.contains(event.target) && !trigger.contains(event.target)) {
        showUserDropdown.value = false;
    }
};

// ==========================================
// LOGOUT FUNCTIONALITY
// ==========================================

const confirmLogout = () => (showLogoutModal.value = true);
const cancelLogout = () => (showLogoutModal.value = false);
const proceedWithLogout = () => {
    showLogoutModal.value = false;
    router.post(route('logout'));
};

// ==========================================
// NAVIGATION AND ROUTING
// ==========================================

const updateActivePage = (href: string) => {
    emit('update:activePage', href);
    router.visit(href, {
        preserveState: true,
        only: ['content'],
        replace: true,
        data: { itemId: currentItem.value },
    });
};

watch(
    () => props.activePage,
    (newValue) => {
        if (newValue) emit('update:activePage', newValue);
    },
    { immediate: true },
);

// ==========================================
// DARK MODE FUNCTIONALITY
// ==========================================

const darkMode = ref(false);
const toggleDarkMode = () => {
    darkMode.value = !darkMode.value;
    document.documentElement.classList.toggle('dark', darkMode.value);
};

// ==========================================
// USER ITEMS DATA AND FUNCTIONALITY
// ==========================================

const sectionTitles = {
    prof: 'MY CLASSES',
    student: 'MY CLASSES',
    parent: 'YOUR CHILDREN',
};

const getSectionTitle = () => sectionTitles[userType] || 'MY ITEMS';

// ==========================================
// ITEM SELECTION FUNCTIONALITY
// ==========================================

// Track the currently selected item
const currentItem = ref<string | null>(null);
// For parent view, track if "all" is selected
const showAllItems = ref(userType === 'parent');

// Provide the current item ID to child components
provide('currentItemId', currentItem);

// Select item without changing the current page
const selectItem = (item) => {
    // Prevent re-selecting the same item to avoid infinite loops
    if (currentItem.value === item.id) {
        return;
    }

    currentItem.value = item.id;
    showAllItems.value = false;

    // Emit the item ID to the parent component
    const itemType = userType === 'parent' ? 'child' : 'section';
    emit('update:content', itemType, item.id);

    // Update the URL with the item ID without navigating
    const url = new URL(window.location.href);
    const paramName = userType === 'parent' ? 'childId' : 'sectionId';
    url.searchParams.set(paramName, item.id);
    window.history.replaceState({}, '', url.toString());

    // Dispatch a custom event that components can listen for
    window.dispatchEvent(
        new CustomEvent('item-changed', {
            detail: {
                itemId: item.id,
                itemType: itemType,
                source: 'sidebar',
            },
        }),
    );
};

// Select "all" view for parent users
const selectAllItems = () => {
    showAllItems.value = true;
    currentItem.value = null;

    emit('update:content', 'overview', 'all');

    // Update URL
    const url = new URL(window.location.href);
    url.searchParams.delete('childId');
    window.history.replaceState({}, '', url.toString());

    // Dispatch event
    window.dispatchEvent(
        new CustomEvent('item-changed', {
            detail: {
                itemId: 'all',
                itemType: 'overview',
                source: 'sidebar',
            },
        }),
    );
};

// Listen for item-changed events from other components
const handleItemChanged = (event) => {
    if (event.detail && event.detail.source !== 'sidebar') {
        const itemId = event.detail.itemId;
        if (itemId && itemId !== 'all') {
            // Update the sidebar's current item
            currentItem.value = itemId;
            showAllItems.value = false;
        } else if (itemId === 'all' && userType === 'parent') {
            showAllItems.value = true;
            currentItem.value = null;
        }
    }
};

// Listen for section-changed events (for backward compatibility)
const handleSectionChanged = (event) => {
    if (event.detail && event.detail.sectionId) {
        const sectionId = event.detail.sectionId;
        // Update the sidebar's current item
        currentItem.value = sectionId;
        showAllItems.value = false;
    }
};

// ==========================================
// LIFECYCLE HOOKS AND INITIALIZATION
// ==========================================

// Setup event listeners and initialize state on component mount
onMounted(() => {
    // Add click outside listener for dropdown
    document.addEventListener('click', closeDropdownOnClickOutside);

    // Add event listeners for item-changed and section-changed events
    window.addEventListener('item-changed', handleItemChanged);
    window.addEventListener('section-changed', handleSectionChanged);

    // Check for user preference for dark mode
    if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
        darkMode.value = true;
        document.documentElement.classList.add('dark');
    }

    // Listen for changes in color scheme preference
    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (event) => {
        darkMode.value = event.matches;
        if (darkMode.value) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    });

    // Check if there's an item ID in the URL and initialize if present
    const urlParams = new URLSearchParams(window.location.search);
    const sectionId = urlParams.get('sectionId');
    const itemId = urlParams.get('itemId');
    const childId = urlParams.get('childId');

    if (sectionId) {
        currentItem.value = sectionId;
        showAllItems.value = false;
    } else if (itemId) {
        currentItem.value = itemId;
        showAllItems.value = false;
    } else if (childId) {
        currentItem.value = childId;
        showAllItems.value = false;
    }

    // Clean up event listeners on component unmount
    return () => {
        document.removeEventListener('click', closeDropdownOnClickOutside);
        window.removeEventListener('item-changed', handleItemChanged);
        window.removeEventListener('section-changed', handleSectionChanged);
    };
});

function getInitial(name: string): string {
    if (!name) return '?';
    return name.charAt(0).toUpperCase(); // Returns first initial of the user's name
}
</script>

<template>
    <!-- Mobile sidebar toggle button - fixed to bottom right on small screens -->
    <button
        @click="toggleSidebar"
        class="hover:bg-primary-700 fixed bottom-4 right-4 z-50 rounded-full bg-primary-600 p-3 text-white shadow-lg md:hidden"
    >
        <svg v-if="sidebarOpen" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
        <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
        </svg>
    </button>
    <!-- Main sidebar container with responsive behavior -->
    <aside
        :class="[
            'w-64 bg-white shadow-lg transition-all duration-300 ease-in-out dark:bg-gray-800',
            'fixed inset-y-0 left-0 z-40 md:relative',
            sidebarOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0',
        ]"
    >
        <!-- Scrollable sidebar content wrapper -->
        <div class="flex h-full flex-col overflow-hidden">
            <!-- Sidebar header with portal title - fixed at top -->
            <div class="flex flex-shrink-0 items-center justify-between border-b p-4 dark:border-gray-700">
                <div class="flex items-center space-x-2">
                    <div class="rounded bg-primary-600 p-1.5">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path d="M12 14l9-5-9-5-9 5 9 5z" />
                            <path
                                d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998a12.078 12.078 0 01.665-6.479L12 14z"
                            />
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998a12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222"
                            />
                        </svg>
                    </div>
                    <h1 class="text-lg font-bold">{{ portalTitle }}</h1>
                </div>
                <button @click="toggleSidebar" class="md:hidden">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- User info section - displays current user's avatar and role -->
            <div class="flex-shrink-0 border-b p-4 dark:border-gray-700">
                <div class="flex items-center space-x-3">
                    <!-- Avatar or Initial -->
                    <img v-if="user.avatar" :src="`/${user.avatar}`" alt="User Avatar" class="h-10 w-10 rounded-full" />

                    <div v-else class="flex h-10 w-10 items-center justify-center rounded-full bg-gray-400 text-sm font-bold text-white">
                        {{ getInitial(user.name) }}
                    </div>

                    <!-- Name and Role -->
                    <div>
                        <h3 class="font-medium">{{ user.name || 'User' }}</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ userRole }}</p>
                    </div>
                </div>
            </div>

            <!-- Scrollable content area -->
            <div class="scrollbar-thin flex-1 overflow-y-auto">
                <!-- User Items Section - Unified for all user types -->
                <div class="border-b p-4 dark:border-gray-700">
                    <div class="mb-2 flex items-center justify-between">
                        <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ getSectionTitle() }}</h3>
                        <!-- Only show add button for professors -->
                        <button
                            v-if="userType === 'prof'"
                            @click="openAddClassModal"
                            class="rounded-full p-1 text-primary-600 hover:bg-gray-100 dark:hover:bg-gray-700"
                            title="Add Item"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                        </button>
                    </div>

                    <!-- "All Children" button for parent view -->
                    <div v-if="userType === 'parent'" class="space-y-2">
                        <button
                            @click="selectAllItems"
                            :class="[
                                'flex w-full items-center rounded-md p-2 transition-colors',
                                showAllItems
                                    ? 'bg-primary-50 dark:text-primary-400 dark:border-primary-400 border-l-4 border-primary-600 text-primary-600 dark:bg-gray-700'
                                    : 'hover:bg-gray-100 dark:hover:bg-gray-700',
                            ]"
                        >
                            <div
                                class="bg-primary-100 dark:bg-primary-900 dark:text-primary-400 mr-3 flex h-8 w-8 items-center justify-center rounded-full text-primary-600"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"
                                    />
                                </svg>
                            </div>
                            <div class="text-left">
                                <p class="font-medium">All Children</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Overview</p>
                            </div>
                        </button>
                    </div>

                    <!-- User Items List with scrollbar -->
                    <div class="scrollbar-thin max-h-[250px] space-y-2 overflow-y-auto pr-1">
                        <div
                            v-for="item in userItems"
                            :key="item.id"
                            class="relative"
                        >
                            <!-- Class item with three dots menu -->
                            <div
                                :class="[
                                    'flex w-full items-center justify-between rounded-md p-2 text-left text-sm',
                                    currentItem === item.id
                                        ? userType === 'parent'
                                            ? 'bg-primary-50 dark:text-primary-400 dark:border-primary-400 border-l-4 border-primary-600 text-primary-600 dark:bg-gray-700'
                                            : 'bg-primary-50 dark:text-primary-400 text-primary-600 dark:bg-gray-700'
                                        : 'hover:bg-gray-100 dark:hover:bg-gray-700',
                                ]"
                            >
                                <!-- Item content (clickable) -->
                                <div
                                    class="flex items-center flex-grow cursor-pointer"
                                    @click="selectItem(item)"
                                >
                                    <!-- Different display for parent vs professor/student -->
                                    <div v-if="userType === 'parent'" class="flex items-center">
                                        <img v-if="item.avatar" :src="`/${user.avatar}`" :alt="`${item.name} Avatar`" class="mr-3 h-8 w-8 rounded-full" />
                                        <div
                                            v-else
                                            class="bg-primary-100 dark:bg-primary-900 dark:text-primary-400 mr-3 flex h-8 w-8 items-center justify-center rounded-full text-primary-600"
                                        >
                                            <span class="text-lg">{{ item.icon }}</span>
                                        </div>
                                        <div class="text-left">
                                            <p class="font-medium">{{ item.name }}</p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ item.term }}</p>
                                        </div>
                                    </div>
                                    <div v-else class="flex items-center space-x-2">
                                        <span class="text-lg">{{ item.icon }}</span>
                                        <span>{{ item.name }}</span>
                                    </div>

                                    <!-- Alert badge -->
                                    <span v-if="item.alerts" class="rounded-full bg-red-500 px-2 py-0.5 text-xs font-bold text-white">
                                        {{ item.alerts }}
                                    </span>
                                </div>

                                <!-- Three dots menu button (only for professors) -->
                                <button
                                    v-if="userType === 'prof'"
                                    @click.stop="toggleDropdown(item.id)"
                                    class="dropdown-trigger ml-2 p-1 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300"
                                >
                                    ⋮
                                </button>
                            </div>

                            <!-- Dropdown menu -->
                            <div
                                v-if="openDropdownId === item.id"
                                class="dropdown-menu absolute right-0 mt-1 w-32 rounded-md border border-gray-200 bg-white shadow-md z-10 dark:bg-gray-700 dark:border-gray-600"
                            >
                                <button
                                    class="block w-full text-left px-4 py-2 text-sm hover:bg-gray-100 dark:hover:bg-gray-600"
                                    @click="editClass(item)"
                                >
                                    Edit
                                </button>
                                <button
                                    class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/20"
                                    @click="deleteClass(item.id)"
                                >
                                    Delete
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Main navigation items - common for all user types -->
                <div v-bind="$attrs" class="scrollbar-thin max-h-[calc(100vh-400px)] overflow-y-auto">
                    <nav class="p-2">
                        <ul class="space-y-1">
                            <li v-for="(item, index) in mainNavItems" :key="index">
                                <a
                                    href="#"
                                    @click.prevent="updateActivePage(item.href)"
                                    :class="[
                                        'flex items-center space-x-3 rounded-lg p-3 transition-colors',
                                        activePage === item.href
                                            ? 'bg-primary-50 dark:text-primary-400 text-primary-600 dark:bg-gray-700'
                                            : 'hover:bg-gray-100 dark:hover:bg-gray-700',
                                    ]"
                                    :aria-label="item.title"
                                >
                                    <span>{{ item.icon }}</span>
                                    <span>{{ item.title }}</span>
                                </a>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>

            <!-- Sidebar Footer with User Dropdown - fixed at bottom -->
            <div class="flex-shrink-0 border-t p-4 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <!-- User Dropdown -->
                    <div class="relative">
                        <button
                            id="user-dropdown-trigger"
                            @click.stop="toggleUserDropdown"
                            class="flex items-center space-x-2 text-gray-500 hover:text-gray-700 dark:hover:text-gray-300"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                                />
                            </svg>
                            <span>Account</span>
                        </button>

                        <!-- Dropdown Menu -->
                        <div
                            id="user-dropdown"
                            v-if="showUserDropdown"
                            class="absolute bottom-full left-0 z-50 mb-2 w-56 rounded-md border border-gray-200 bg-white shadow-lg dark:border-gray-700 dark:bg-gray-800"
                        >
                            <!-- User Info -->
                            <div class="border-b border-gray-200 p-3 dark:border-gray-700">
                                <div class="flex items-center gap-2">
                                    <img v-if="user.avatar" :src="`/${user.avatar}`" class="h-10 w-10 rounded-full" />
                                    <div
                                        v-else
                                        class="flex h-10 w-10 items-center justify-center rounded-full bg-gray-400 text-sm font-bold text-white"
                                    >
                                        {{ getInitial(user.name) }}
                                    </div>
                                    <div>
                                        <p class="font-medium">{{ user.name }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ user.email || 'user@example.com' }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Settings Link -->
                            <div class="p-1">
                                <Link
                                    :href="route('profile.edit')"
                                    as="button"
                                    class="flex w-full items-center rounded-md px-3 py-2 text-sm hover:bg-gray-100 dark:hover:bg-gray-700"
                                >
                                    <svg
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
                                            d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"
                                        />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    Settings
                                </Link>
                            </div>

                            <!-- Separator -->
                            <div class="my-1 border-t border-gray-200 dark:border-gray-700"></div>

                            <!-- Logout Button -->
                            <div class="p-1">
                                <button
                                    @click="confirmLogout"
                                    class="flex w-full items-center rounded-md px-3 py-2 text-sm text-red-600 hover:bg-gray-100 dark:text-red-400 dark:hover:bg-gray-700"
                                >
                                    <svg
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
                                            d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"
                                        />
                                    </svg>
                                    Log out
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </aside>

    <!-- Logout Confirmation Modal -->
    <div v-if="showLogoutModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
        <div class="mx-4 w-full max-w-md rounded-lg bg-white p-6 shadow-lg dark:bg-gray-800">
            <h3 class="mb-4 text-lg font-medium">Confirm Logout</h3>
            <p class="mb-6 text-gray-600 dark:text-gray-300">Are you sure you want to log out of your account?</p>
            <div class="flex justify-end space-x-3">
                <button
                    @click="cancelLogout"
                    class="rounded-md border border-gray-300 px-4 py-2 hover:bg-gray-100 dark:border-gray-600 dark:hover:bg-gray-700"
                >
                    Cancel
                </button>
                <button @click="proceedWithLogout" class="rounded-md bg-red-600 px-4 py-2 text-white hover:bg-red-700">Logout</button>
            </div>
        </div>
    </div>

    <!-- Add Class Modal -->
    <!-- Add Class Modal - Redesigned -->
    <div v-if="showAddClassModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
        <div class="max-h-[90vh] w-full max-w-lg overflow-y-auto rounded-lg bg-white p-6 shadow-lg dark:bg-gray-800">
            <div class="mb-6 flex items-center justify-between">
                <h3 class="text-xl font-semibold text-gray-900 dark:text-white">
                    {{ isEditing ? 'Edit Class' : 'Add New Class' }}
                </h3>
                <button @click="showAddClassModal = false" class="text-gray-500 transition-colors hover:text-gray-700 dark:hover:text-gray-300">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form @submit.prevent="addClass" class="space-y-5">
                <!-- Basic Information Section -->
                <div class="space-y-4">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Class Name*</label>
                            <input type="text" v-model="newClass.name" required placeholder="e.g., Mathematics 101" class="input-style" />
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Term</label>
                            <input type="text" v-model="newClass.term" placeholder="e.g., Fall 2023" class="input-style" />
                        </div>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
                        <textarea v-model="newClass.description" placeholder="Brief description of the class" rows="2" class="input-style"></textarea>
                    </div>
                </div>

                <!-- Icon Selection -->
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Class Icon</label>
                    <div class="max-h-40 overflow-y-auto pr-1">
                        <div class="grid grid-cols-6 gap-2">
                            <button
                                v-for="icon in availableIcons"
                                :key="icon"
                                type="button"
                                @click="newClass.icon = icon"
                                :class="[
                                    'flex h-10 w-10 items-center justify-center rounded-md text-lg transition-all',
                                    newClass.icon === icon
                                        ? 'bg-primary-100 dark:bg-primary-900 scale-110 transform border-2 border-primary-500'
                                        : 'bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600',
                                ]"
                            >
                                {{ icon }}
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Schedule Section -->
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Class Schedule</label>

                    <div
                        v-for="(entry, index) in scheduleEntries"
                        :key="index"
                        class="dark:bg-gray-750 mb-3 flex flex-wrap items-center gap-2 rounded-md bg-gray-50 p-3"
                    >
                        <!-- Day Selection -->
                        <div class="w-full sm:w-auto">
                            <select v-model="entry.day" class="input-style px-2 py-1">
                                <option v-for="day in scheduleDays" :key="day" :value="day">{{ day }}</option>
                            </select>
                        </div>

                        <!-- Time Selection -->
                        <div class="flex flex-1 items-center gap-2">
                            <select
                                v-model="parseTimeRange(entry.time).startTime"
                                @change="updateScheduleTime(index, $event.target.value, parseTimeRange(entry.time).endTime)"
                                class="input-style px-2 py-1"
                            >
                                <option v-for="time in timeSlots" :key="time" :value="time">{{ time }}</option>
                            </select>
                            <span class="text-gray-500">to</span>
                            <select
                                v-model="parseTimeRange(entry.time).endTime"
                                @change="updateScheduleTime(index, parseTimeRange(entry.time).startTime, $event.target.value)"
                                class="input-style px-2 py-1"
                            >
                                <option v-for="time in timeSlots" :key="time" :value="time">{{ time }}</option>
                            </select>
                        </div>

                        <!-- Remove Button -->
                        <button
                            type="button"
                            @click="removeScheduleEntry(index)"
                            class="ml-auto p-1 text-red-500 hover:text-red-700"
                            :disabled="scheduleEntries.length <= 1"
                            :class="{ 'cursor-not-allowed opacity-50': scheduleEntries.length <= 1 }"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                                />
                            </svg>
                        </button>
                    </div>

                    <!-- Add Schedule Button -->
                    <button type="button" @click="addScheduleEntry" class="hover:text-primary-700 mt-1 flex items-center text-sm text-primary-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="mr-1 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Add Another Time Slot
                    </button>
                </div>

                <!-- Additional Details -->
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Room</label>
                        <input type="text" v-model="newClass.room" placeholder="e.g., Room 205" class="input-style" />
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Max Students</label>
                        <input type="number" v-model="newClass.max_students" placeholder="e.g., 30" class="input-style" />
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="mt-4 flex justify-end space-x-3 border-t border-gray-200 pt-4 dark:border-gray-700">
                    <button
                        type="button"
                        @click="showAddClassModal = false"
                        class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium transition-colors hover:bg-gray-100 dark:border-gray-600 dark:hover:bg-gray-700"
                    >
                        Cancel
                    </button>
                    <button
                        type="submit"
                        class="hover:bg-primary-700 rounded-md bg-primary-600 px-4 py-2 text-sm font-medium text-white transition-colors"
                    >
                    {{ isEditing ? 'Update Class' : 'Create Class' }}
                    </button>
                </div>
            </form>
            <!-- Add this below each input field to display errors -->
        </div>
    </div>

    <slot />
</template>

<style>
/* Enhanced scrollbar hiding for all browsers */
/* Base scrollbar hiding class */
.scrollbar-thin {
    -ms-overflow-style: none; /* IE and Edge */
    scrollbar-width: none; /* Firefox */
}

/* Hide scrollbar for Chrome, Safari and Opera */
.scrollbar-thin::-webkit-scrollbar {
    display: none;
}

/* Global scrollbar hiding for all elements */
html,
body {
    scrollbar-width: none; /* Firefox */
    -ms-overflow-style: none; /* IE and Edge */
}

html::-webkit-scrollbar,
body::-webkit-scrollbar {
    display: none; /* Chrome, Safari, Opera */
}

/* Target all scrollable elements */
.overflow-y-auto,
.overflow-x-auto,
.overflow-auto {
    scrollbar-width: none;
    -ms-overflow-style: none;
}

.overflow-y-auto::-webkit-scrollbar,
.overflow-x-auto::-webkit-scrollbar,
.overflow-auto::-webkit-scrollbar {
    display: none;
}

/* Input styles */
.input-style {
    @apply w-full rounded-md border border-gray-300 bg-white px-3 py-2 shadow-sm focus:border-primary-600 focus:outline-none focus:ring-2 focus:ring-primary-600 dark:border-gray-600 dark:bg-gray-700;
}

/* Fix for mobile Safari overscroll behavior */
@supports (-webkit-touch-callout: none) {
    .overflow-y-auto {
        -webkit-overflow-scrolling: touch;
    }

    /* Additional fix for iOS momentum scrolling */
    * {
        -webkit-overflow-scrolling: touch;
    }
}

/* Dark mode enhancement */
.dark .bg-gray-750 {
    background-color: rgba(55, 65, 81, 0.5);
}

/* Three dots menu button styling */
.dropdown-trigger {
    font-size: 1.25rem;
    line-height: 1;
    cursor: pointer;
    transition: all 0.2s;
}

.dropdown-trigger:hover {
    background-color: rgba(0, 0, 0, 0.05);
    border-radius: 50%;
}

.dark .dropdown-trigger:hover {
    background-color: rgba(255, 255, 255, 0.1);
}

/* Dropdown menu animation */
.dropdown-menu {
    animation: fadeIn 0.2s ease-out;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-5px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>
