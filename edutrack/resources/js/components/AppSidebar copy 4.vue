<script setup lang="ts">
import { Link, router, usePage } from '@inertiajs/vue3';
import { ref, computed, watch, onMounted, provide } from 'vue';
import type { SharedData, User } from '@/types';

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
  parent: 'Parent Portal'
};

const userRoles = {
  prof: 'Professor/Admin',
  student: 'Student',
  parent: 'Parent/Guardian'
};

const portalTitle = computed(() => portalTitles[userType] || 'Portal');
const userRole = computed(() => userRoles[userType] || 'User');

// ==========================================
// UI STATE MANAGEMENT
// ==========================================

const sidebarOpen = ref(false);
const toggleSidebar = () => (sidebarOpen.value = !sidebarOpen.value);

const showUserDropdown = ref(false);
const showLogoutModal = ref(false);
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
    data: { itemId: currentItem.value }
  });
};

watch(() => props.activePage, (newValue) => {
  if (newValue) emit('update:activePage', newValue);
}, { immediate: true });

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
  parent: 'YOUR CHILDREN'
};

const getSectionTitle = () => sectionTitles[userType] || 'MY ITEMS';


onMounted(() => {
  document.addEventListener('click', closeDropdownOnClickOutside);
  if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
    darkMode.value = true;
    document.documentElement.classList.add('dark');
  }
  window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', event => {
    darkMode.value = event.matches;
    document.documentElement.classList.toggle('dark', darkMode.value);
  });
});

// Unified data structure for all user types
// const userItems = ref([]);

// Define the type explicitly
const userItems = ref<
  Array<{
    id: string;
    name: string;
    icon: string;
  }>
>([]);

// Initialize user items based on user type
onMounted(() => {
  if (userType === 'prof') {
    userItems.value = [
      {
        id: 'sci101a',
        name: 'Science 101 - Section A',
        icon: '🧪',
        // Other properties...
      },
      {
        id: 'sci101b',
        name: 'Science 101 - Section B',
        icon: '🧪',
        // Other properties...
      },
      {
        id: 'bio202',
        name: 'Biology 202',
        icon: '🧬',
        // Other properties...
      },
      {
        id: 'chem101',
        name: 'Chemistry 101',
        icon: '⚗️',
        // Other properties...
      }
    ];
  } else if (userType === 'parent') {
    userItems.value = [
      {
        id: 'child1',
        name: 'Alex Johnson',
        icon: '👦',
      },
      {
        id: 'child2',
        name: 'Emma Johnson',
        icon: '👧',
      },
      {
        id: 'child3',
        name: 'Noah Johnson',
        icon: '👦',
      }
    ];
  } else if (userType === 'student') {
    userItems.value = [
      {
        id: 'sci101a',
        name: 'Science 101 - Section A',
        icon: '🧪',
        // Other properties...
      },
      {
        id: 'sci101b',
        name: 'Science 101 - Section B',
        icon: '🧪',
        // Other properties...
      },
      {
        id: 'bio202',
        name: 'Biology 202',
        icon: '🧬',
        // Other properties...
      },
      {
        id: 'chem101',
        name: 'Chemistry 101',
        icon: '⚗️',
        // Other properties...
      }
    ];
  }
});

// Function to open the add item modal (placeholder)
const openAddItemModal = () => {
  // This would be implemented to add a new item
  console.log('Open add item modal');
};

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
  window.dispatchEvent(new CustomEvent('item-changed', {
    detail: {
      itemId: item.id,
      itemType: itemType,
      source: 'sidebar'
    }
  }));
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
  window.dispatchEvent(new CustomEvent('item-changed', {
    detail: {
      itemId: 'all',
      itemType: 'overview',
      source: 'sidebar'
    }
  }));
};

// ==========================================
// LIFECYCLE HOOKS AND INITIALIZATION
// ==========================================

// Setup event listeners and initialize state on component mount
onMounted(() => {
  // Add click outside listener for dropdown
  document.addEventListener('click', closeDropdownOnClickOutside);

  // Check for user preference for dark mode
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

  // Check if there's an item ID in the URL and initialize if present
  const urlParams = new URLSearchParams(window.location.search);
  const sectionId = urlParams.get('sectionId');
  const childId = urlParams.get('childId');

  if (sectionId) {
    currentItem.value = sectionId;
    showAllItems.value = false;
  } else if (childId) {
    currentItem.value = childId;
    showAllItems.value = false;
  }
});
</script>

<template>
  <!-- Mobile sidebar toggle button - fixed to bottom right on small screens -->
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

  <!-- Main sidebar container with responsive behavior -->
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

    <!-- User info section - displays current user's avatar and role -->
    <div class="p-4 border-b dark:border-gray-700">
      <div class="flex items-center space-x-3">
        <img src="https://i.pravatar.cc/100" alt="User Avatar" class="w-10 h-10 rounded-full" />
        <div>
          <h3 class="font-medium">{{ user.name || 'User' }}</h3>
          <p class="text-sm text-gray-500 dark:text-gray-400">{{ userRole }}</p>
        </div>
      </div>
    </div>

    <!-- User Items Section - Unified for all user types -->
    <div class="p-4 border-b dark:border-gray-700">
      <div class="flex items-center justify-between mb-2">
        <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ getSectionTitle() }}</h3>
        <!-- Only show add button for professors -->
        <button
          v-if="userType === 'prof'"
          @click="openAddItemModal"
          class="p-1 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700 text-primary-600"
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
            'w-full flex items-center p-2 rounded-md transition-colors',
            showAllItems
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
      </div>

      <!-- User Items List -->
      <div class="space-y-2">
        <button
          v-for="item in userItems"
          :key="item.id"
          @click="selectItem(item)"
          :class="[
            'w-full flex items-center justify-between p-2 rounded-md text-left text-sm',
            currentItem === item.id
              ? userType === 'parent'
                ? 'bg-primary-50 text-primary-600 dark:bg-gray-700 dark:text-primary-400 border-l-4 border-primary-600 dark:border-primary-400'
                : 'bg-primary-50 text-primary-600 dark:bg-gray-700 dark:text-primary-400'
              : 'hover:bg-gray-100 dark:hover:bg-gray-700'
          ]"
        >
          <!-- Different display for parent vs professor/student -->
          <div v-if="userType === 'parent'" class="flex items-center">
            <img v-if="item.avatar" :src="item.avatar" :alt="`${item.name} Avatar`" class="w-8 h-8 rounded-full mr-3" />
            <div v-else class="w-8 h-8 rounded-full bg-primary-100 dark:bg-primary-900 text-primary-600 dark:text-primary-400 flex items-center justify-center mr-3">
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
          <span
            v-if="item.alerts"
            class="bg-red-500 text-white text-xs font-bold px-2 py-0.5 rounded-full"
          >
            {{ item.alerts }}
          </span>
        </button>
      </div>
    </div>

    <!-- Main navigation items - common for all user types -->
    <!-- <div v-bind="$attrs">
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
    </div> -->
    <!-- Main navigation items - common for all user types -->
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
