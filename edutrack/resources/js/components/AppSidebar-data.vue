<script setup lang="ts">
import { Link, router, usePage } from '@inertiajs/vue3';
import { ref, computed, watch, onMounted, provide } from 'vue';
import type { SharedData, User } from '@/types';

const props = defineProps<{
  mainNavItems: { title: string; href: string; icon: string }[];
  activePage: string;
}>();

// Define the emit to update the activePage
const emit = defineEmits<{
  'update:activePage': [value: string];
  'update:content': [type: string, id: string | number];
}>();

// Access user directly from Inertia page props
const page = usePage<SharedData>();
const user = page.props.auth.user as User;
const userType = user.user_type;

// Portal titles and roles based on user type
const portalTitles = {
  prof: 'Professor Portal',
  student: 'Student Portal',
  parent: 'Parent Portal'
};

const userRoles = {
  prof: 'Professor/Admin',
  student: 'Student',
  parent: 'Parent/Guardian'
};

// Computed properties for dynamic portal title and user role
const portalTitle = computed(() => {
  return portalTitles[userType] || 'Portal';
});

const userRole = computed(() => {
  return userRoles[userType] || 'User';
});

const sidebarOpen = ref(false);
const showLogoutModal = ref(false);
const showUserDropdown = ref(false);

// Methods
const toggleSidebar = () => {
  sidebarOpen.value = !sidebarOpen.value;
};

// Toggle user dropdown
const toggleUserDropdown = () => {
  showUserDropdown.value = !showUserDropdown.value;
};

// Close dropdown when clicking outside
const closeDropdownOnClickOutside = (event) => {
  const dropdown = document.getElementById('user-dropdown');
  const trigger = document.getElementById('user-dropdown-trigger');

  if (dropdown && trigger && !dropdown.contains(event.target) && !trigger.contains(event.target)) {
    showUserDropdown.value = false;
  }
};

// Add click outside listener
onMounted(() => {
  document.addEventListener('click', closeDropdownOnClickOutside);
});

// Logout confirmation
const confirmLogout = () => {
  showLogoutModal.value = true;
};

const cancelLogout = () => {
  showLogoutModal.value = false;
};

const proceedWithLogout = () => {
  // Close the modal
  showLogoutModal.value = false;

  // Proceed with logout
  router.post(route('logout'));
};

// Method to update active page without navigation
const updateActivePage = (href: string) => {
  emit('update:activePage', href);

  // Use Inertia's visit with only option to update the URL without a full page reload
  router.visit(href, {
    preserveState: true, // Keep the current state
    only: ['content'], // Only refresh the content data from the server
    replace: true, // Replace the current history entry instead of adding a new one
    data: {
      sectionId: currentSection.value // Pass the current section ID if one is selected
    }
  });
};

// Class sections data
const classSections = ref([
  {
    id: 'sci101a',
    name: 'Science 101 - Section A',
    icon: '🧪',
    term: 'Fall Semester 2023',
    alerts: 2,
    studentCount: 28,
    presentCount: 26,
    attendanceSummary: {
      present: 26,
      absent: 1,
      late: 1,
      excused: 0
    },
    // Other properties...
  },
  {
    id: 'sci101b',
    name: 'Science 101 - Section B',
    icon: '🧪',
    term: 'Fall Semester 2023',
    alerts: 0,
    studentCount: 25,
    presentCount: 23,
    attendanceSummary: {
      present: 23,
      absent: 1,
      late: 1,
      excused: 0
    },
    // Other properties...
  },
  {
    id: 'bio202',
    name: 'Biology 202',
    icon: '🧬',
    term: 'Fall Semester 2023',
    alerts: 1,
    studentCount: 22,
    presentCount: 20,
    attendanceSummary: {
      present: 20,
      absent: 1,
      late: 0,
      excused: 1
    },
    // Other properties...
  },
  {
    id: 'chem101',
    name: 'Chemistry 101',
    icon: '⚗️',
    term: 'Fall Semester 2023',
    alerts: 0,
    studentCount: 24,
    presentCount: 22,
    attendanceSummary: {
      present: 22,
      absent: 1,
      late: 1,
      excused: 0
    },
    // Other properties...
  }
]);

const childrenSections = ref([
  {
    id: 'child1',
    name: 'Alex Johnson',
    icon: '👦',
    term: 'Grade 10',
    alerts: 2,
    studentCount: 1,
    presentCount: 1,
    attendanceSummary: {
      present: 95,
      absent: 5,
      late: 0,
      excused: 0
    },
    stats: {
      gpa: 3.7,
      behavior: 'Good'
    },
    recentActivity: 'Received an A on Math quiz and submitted Science project.'
  },
  {
    id: 'child2',
    name: 'Emma Johnson',
    icon: '👧',
    term: 'Grade 8',
    alerts: 1,
    studentCount: 1,
    presentCount: 1,
    attendanceSummary: {
      present: 98,
      absent: 2,
      late: 0,
      excused: 0
    },
    stats: {
      gpa: 3.9,
      behavior: 'Excellent'
    },
    recentActivity: 'Won first place in the science fair and joined the debate team.'
  },
  {
    id: 'child3',
    name: 'Noah Johnson',
    icon: '👦',
    term: 'Grade 6',
    alerts: 0,
    studentCount: 1,
    presentCount: 1,
    attendanceSummary: {
      present: 92,
      absent: 8,
      late: 0,
      excused: 0
    },
    stats: {
      gpa: 3.5,
      behavior: 'Good'
    },
    recentActivity: 'Completed all homework assignments and participated in class discussions.'
  }
]);

const currentSection = ref<string | null>(null);

const currentChild = ref<string | null>(null);

// Provide the current section ID to child components
provide('currentSectionId', currentSection);

// Provide the current Child ID to child components
provide('currentChildId', currentChild);

// Select section without changing the current page
const selectSection = (section) => {
  // Prevent re-selecting the same section to avoid infinite loops
  if (currentSection.value === section.id) {
    return;
  }

  const selectSection = (section) => {
  // Prevent re-selecting the same section to avoid infinite loops
  if (currentSection.value === section.id) {
    return;
  }

  const selectaChild = (child) => {
  // Prevent re-selecting the same section to avoid infinite loops
  if (currentChild.value === child.id) {
    return;
  }

  currentSection.value = section.id;

  currentChild.value = child.id;

  // Emit the section ID to the parent component
  emit('update:content', 'section', section.id);

  // Update the URL with the section ID without navigating
  const url = new URL(window.location.href);
  url.searchParams.set('sectionId', section.id);
  window.history.replaceState({}, '', url.toString());

  // Dispatch a custom event that components can listen for
  // We'll use a flag to prevent infinite loops
  window.dispatchEvent(new CustomEvent('section-changed', {
    detail: {
      sectionId: section.id,
      source: 'sidebar' // Add a source identifier
    }
  }));
};

// Function to open the add class modal (placeholder)
const openAddClassModal = () => {
  // This would be implemented to add a new class
  console.log('Open add class modal');
};

// Parent-specific data and methods
const children = ref([
  {
    id: 1,
    name: 'John Doe',
    grade: 'Grade 10',
    avatar: 'https://i.pravatar.cc/100?img=1',
    alerts: 2,
    url: '/children/1'
  },
  {
    id: 2,
    name: 'Jane Doe',
    grade: 'Grade 8',
    avatar: 'https://i.pravatar.cc/100?img=5',
    alerts: 0,
    url: '/children/2'
  },
  {
    id: 3,
    name: 'James Doe',
    grade: 'Grade 6',
    avatar: 'https://i.pravatar.cc/100?img=8',
    alerts: 1,
    url: '/children/3'
  },
]);

const selectedChild = ref<string | number>('all');

// Select child without page navigation
const selectChild = (childId) => {
  selectedChild.value = childId;
  emit('update:content', 'child', childId);

  const url = childId === 'all' ? '/children/overview' :
    children.value.find(c => c.id === childId)?.url || '/children/overview';

  // Use Inertia's visit with preserveState to update URL without full reload
  router.visit(url, {
    preserveState: true,
    only: ['childData'],
    replace: true
  });
};

// Watch for changes in props.activePage to update local state
watch(() => props.activePage, (newValue) => {
  // If activePage changes from parent component, update local state
  if (newValue) {
    // You might need additional logic here to determine if this is a section or child
    // For now, just update the activePage
    emit('update:activePage', newValue);
  }
}, { immediate: true });

// Replace data property with ref
const darkMode = ref(false);

const toggleDarkMode = () => {
  darkMode.value = !darkMode.value;
  if (darkMode.value) {
    document.documentElement.classList.add('dark');
  } else {
    document.documentElement.classList.remove('dark');
  }
};

// Replace mounted lifecycle hook with onMounted
onMounted(() => {
  // Check for user preference
  if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
    darkMode.value = true;
    document.documentElement.classList.add('dark');
  }

  // Listen for changes in color scheme preference
  window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', event => {
    darkMode.value = event.matches;
    if (darkMode.value) {
      document.documentElement.classList.add('dark');
    } else {
      document.documentElement.classList.remove('dark');
    }
  });

  // Check if there's a section ID in the URL
  const urlParams = new URLSearchParams(window.location.search);
  const sectionId = urlParams.get('sectionId');
  const childId = urlParams.get('childId');

  if (sectionId) {
    currentSection.value = sectionId;
  }
   if (childId) {
    currentChild.value = childId;
  }
});
</script>

<template>
  <!-- Rest of the template remains the same -->
  <button
    @click="toggleSidebar"
    class="fixed z-50 bottom-4 right-4 md:hidden bg-primary-600 hover:bg-primary-700 text-white p-3 rounded-full shadow-lg"
  >
    <svg v-if="sidebarOpen" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
    </svg>
    <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
    </svg>
  </button>

  <aside
    :class="[
      'w-64 bg-white dark:bg-gray-800 shadow-lg transition-all duration-300 ease-in-out',
      'fixed inset-y-0 left-0 z-40 md:relative',
      sidebarOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0'
    ]"
  >
    <!-- Sidebar header with portal title -->
    <div class="flex items-center justify-between p-4 border-b dark:border-gray-700">
      <div class="flex items-center space-x-2">
        <div class="bg-primary-600 p-1.5 rounded">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path d="M12 14l9-5-9-5-9 5 9 5z" />
            <path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998a12.078 12.078 0 01.665-6.479L12 14z" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998a12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222" />
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

    <!-- User info section -->
    <div class="p-4 border-b dark:border-gray-700">
      <div class="flex items-center space-x-3">
        <img src="https://i.pravatar.cc/100" alt="User Avatar" class="w-10 h-10 rounded-full" />
        <div>
          <h3 class="font-medium">{{ user.name || 'User' }}</h3>
          <p class="text-sm text-gray-500 dark:text-gray-400">{{ userRole }}</p>
        </div>
      </div>
    </div>

    <!-- Professor-specific: Class Sections -->
    <div v-if="userType === 'prof'" class="p-4 border-b dark:border-gray-700">
      <div class="flex items-center justify-between mb-2">
        <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">MY CLASSES</h3>
        <button
          @click="openAddClassModal"
          class="p-1 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700 text-primary-600"
          title="Add Class"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
        </button>
      </div>
      <div class="space-y-2">
        <button
          v-for="section in classSections"
          :key="section.id"
          @click="selectSection(section)"
          :class="[
            'w-full flex items-center justify-between p-2 rounded-md text-left text-sm',
            currentSection === section.id
              ? 'bg-primary-50 text-primary-600 dark:bg-gray-700 dark:text-primary-400'
              : 'hover:bg-gray-100 dark:hover:bg-gray-700'
          ]"
        >
          <div class="flex items-center space-x-2">
            <span class="text-lg">{{ section.icon }}</span>
            <span>{{ section.name }}</span>
          </div>
          <span
            v-if="section.alerts"
            class="bg-red-500 text-white text-xs font-bold px-2 py-0.5 rounded-full"
          >
            {{ section.alerts }}
          </span>
        </button>
      </div>
    </div>

     <!-- Class for Student Sections -->
    <div  v-if="userType === 'student'" class="p-4 border-b dark:border-gray-700">
        <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">MY CLASSES</h3>
        <div class="space-y-2">
            <button
            v-for="section in classSections"
            :key="section.id"
            @click="selectSection(section)"
            :class="[
                'w-full flex items-center justify-between p-2 rounded-md text-left text-sm',
                currentSection === section.id
                ? 'bg-primary-50 text-primary-600 dark:bg-gray-700 dark:text-primary-400'
                : 'hover:bg-gray-100 dark:hover:bg-gray-700'
            ]"
            >
            <div class="flex items-center space-x-2">
                <span class="text-lg">{{ section.icon }}</span>
                <span>{{ section.name }}</span>
            </div>
            <span
                v-if="section.alerts"
                class="bg-red-500 text-white text-xs font-bold px-2 py-0.5 rounded-full"
            >
                {{ section.alerts }}
            </span>
            </button>
        </div>
    </div>


     <!-- Child Selector for Parent/Guardian -->
     <div  v-if="userType === 'parent'" class="p-4 border-b dark:border-gray-700">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Your Children</label>
            <div class="space-y-2">
              <button
                 @click="selectaChild(child)"
                :class="[
                  'w-full flex items-center p-2 rounded-md transition-colors',
                  selectedChild === 'all'
                    ? 'bg-primary-50 text-primary-600 dark:bg-gray-700 dark:text-primary-400 border-l-4 border-primary-600 dark:border-primary-400'
                    : 'hover:bg-gray-100 dark:hover:bg-gray-700'
                ]"
              >
                <div class="w-8 h-8 rounded-full bg-primary-100 dark:bg-primary-900 text-primary-600 dark:text-primary-400 flex items-center justify-center mr-3">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                  </svg>
                </div>
                <div class="text-left">
                  <p class="font-medium">All Children</p>
                  <p class="text-xs text-gray-500 dark:text-gray-400">Overview</p>
                </div>
              </button>

              <button
                v-for="child in children"
                :key="child.id"
                @click="selectaChild(child)"
                :class="[
                  'w-full flex items-center p-2 rounded-md transition-colors',
                  selectedChild === child.id
                    ? 'bg-primary-50 text-primary-600 dark:bg-gray-700 dark:text-primary-400 border-l-4 border-primary-600 dark:border-primary-400'
                    : 'hover:bg-gray-100 dark:hover:bg-gray-700'
                ]"
              >
                <img :src="child.avatar" :alt="`${child.name} Avatar`" class="w-8 h-8 rounded-full mr-3" />
                <div class="text-left">
                  <p class="font-medium">{{ child.name }}</p>
                  <p class="text-xs text-gray-500 dark:text-gray-400">{{ child.grade }}</p>
                </div>
                <div v-if="child.alerts" class="ml-auto bg-red-500 text-white text-xs font-bold w-5 h-5 flex items-center justify-center rounded-full">
                  {{ child.alerts }}
                </div>
              </button>
            </div>
          </div>


    <!-- Main navigation items -->
    <div v-bind="$attrs">
      <nav class="p-2">
        <ul class="space-y-1">
          <li v-for="(item, index) in mainNavItems" :key="index">
            <a
              href="#"
              @click.prevent="updateActivePage(item.href)"
              :class="[
                'flex items-center space-x-3 p-3 rounded-lg transition-colors',
                activePage === item.href
                  ? 'bg-primary-50 text-primary-600 dark:bg-gray-700 dark:text-primary-400'
                  : 'hover:bg-gray-100 dark:hover:bg-gray-700'
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

    <!-- Sidebar Footer with User Dropdown -->
    <div class="absolute bottom-0 w-full p-4 border-t dark:border-gray-700">
      <div class="flex items-center justify-between">
        <!-- User Dropdown -->
        <div class="relative">
          <button
            id="user-dropdown-trigger"
            @click.stop="toggleUserDropdown"
            class="flex items-center space-x-2 text-gray-500 hover:text-gray-700 dark:hover:text-gray-300"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
            </svg>
            <span>Account</span>
          </button>

          <!-- Dropdown Menu -->
          <div
            id="user-dropdown"
            v-if="showUserDropdown"
            class="absolute bottom-full left-0 mb-2 w-56 rounded-md bg-white dark:bg-gray-800 shadow-lg border border-gray-200 dark:border-gray-700 z-50"
          >
            <!-- User Info -->
            <div class="p-3 border-b border-gray-200 dark:border-gray-700">
              <div class="flex items-center gap-2">
                <img :src="user.avatar || 'https://i.pravatar.cc/100'" alt="User Avatar" class="w-10 h-10 rounded-full" />
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
                class="flex w-full items-center px-3 py-2 text-sm rounded-md hover:bg-gray-100 dark:hover:bg-gray-700"
              >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                Settings
              </Link>
            </div>

            <!-- Separator -->
            <div class="border-t border-gray-200 dark:border-gray-700 my-1"></div>

            <!-- Logout Button -->
            <div class="p-1">
              <button
                @click="confirmLogout"
                class="flex w-full items-center px-3 py-2 text-sm text-red-600 dark:text-red-400 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700"
              >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg>
                Log out
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </aside>

  <!-- Logout Confirmation Modal -->
  <div v-if="showLogoutModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 max-w-md w-full mx-4">
      <h3 class="text-lg font-medium mb-4">Confirm Logout</h3>
      <p class="text-gray-600 dark:text-gray-300 mb-6">Are you sure you want to log out of your account?</p>
      <div class="flex justify-end space-x-3">
        <button
          @click="cancelLogout"
          class="px-4 py-2 rounded-md border border-gray-300 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700"
        >
          Cancel
        </button>
        <button
          @click="proceedWithLogout"
          class="px-4 py-2 rounded-md bg-red-600 text-white hover:bg-red-700"
        >
          Logout
        </button>
      </div>
    </div>
  </div>

  <slot />
</template>
