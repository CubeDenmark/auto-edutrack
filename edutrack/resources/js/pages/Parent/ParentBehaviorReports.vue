<script setup lang="ts">
import { ref, computed, onMounted, watch, inject } from 'vue';
import AppLayout from "@/layouts/AppLayout.vue";
import { type BreadcrumbItem } from "@/types";
import { Head, usePage, Link } from "@inertiajs/vue3";

// Breadcrumbs
const breadcrumbs: BreadcrumbItem[] = [
  {
    title: "Behavior Reports",
    href: "/parent/behavior-reports",
  },
];

// Get the page props to access data passed from the server
const page = usePage();

// Inject the current child ID from the parent component
const injectedChildId = inject('currentChildId', ref(null));
const injectedSectionId = inject('currentSectionId', ref(null));
// Inject the current item ID from the sidebar
const injectedItemId = inject('currentItemId', ref(null));

// State variables
const currentChild = ref<any>(null);
const isMobile = ref(false);

// Get children data from page props instead of static data
const children = ref(page.props.children || []);

// Check if we're on mobile
const checkMobile = () => {
  isMobile.value = window.innerWidth < 768;
};

// Methods
function findChildById(id) {
  if (!id) return null;
  return children.value.find(child => child.id === id || child.id === Number(id)) || null;
}

// Select a child and notify other components
const selectChild = (childId) => {
  if (childId === 'all') {
    currentChild.value = null;
  } else {
    const child = findChildById(childId);
    if (child) {
      currentChild.value = child;
    }
  }

  // Dispatch event for other components to listen to
  window.dispatchEvent(new CustomEvent('child-changed', {
    detail: {
      childId: childId,
      source: 'behavior-reports'
    }
  }));
};

// Handle item-changed event from sidebar
const handleItemChanged = (event) => {
  if (event.detail) {
    const { itemId, itemType, source } = event.detail;

    console.log('Item changed event received:', itemId, itemType, source);

    if (source === 'sidebar') {
      if (itemId === 'all') {
        // Handle "All Children" selection
        currentChild.value = null;
      } else {
        // Handle specific child selection
        const child = findChildById(itemId);
        if (child) {
          currentChild.value = child;
        }
      }
    }
  }
};

// Handle child-changed event from other components
const handleChildChanged = (event) => {
  if (event.detail && event.detail.source !== 'behavior') {
    const { childId } = event.detail;

    if (childId === 'all') {
      currentChild.value = null;
    } else {
      const child = findChildById(childId);
      if (child) {
        currentChild.value = child;
      }
    }
  }
};

// Handle section-changed event (for backward compatibility)
const handleSectionChanged = (event) => {
  if (event.detail) {
    const { sectionId, source } = event.detail;

    if (source === 'sidebar') {
      if (sectionId === 'all') {
        currentChild.value = null;
      } else {
        const child = findChildById(sectionId);
        if (child) {
          currentChild.value = child;
        }
      }
    }
  }
};

// Watch for changes in the injected child ID
watch(injectedChildId, (newChildId) => {
  if (newChildId && newChildId !== 'all') {
    const child = findChildById(newChildId);
    if (child) {
      currentChild.value = child;
    }
  } else if (newChildId === 'all') {
    currentChild.value = null;
  }
}, { immediate: true });

// Watch for changes in the injected section ID
watch(injectedSectionId, (newSectionId) => {
  if (newSectionId && newSectionId !== 'all') {
    const child = findChildById(newSectionId);
    if (child) {
      currentChild.value = child;
    }
  } else if (newSectionId === 'all') {
    currentChild.value = null;
  }
}, { immediate: true });

// Watch for changes in the injected item ID from sidebar
watch(injectedItemId, (newItemId) => {
  console.log('Injected item ID changed:', newItemId);
  if (newItemId && newItemId !== 'all') {
    const child = findChildById(newItemId);
    if (child) {
      currentChild.value = child;
    }
  } else if (newItemId === 'all') {
    currentChild.value = null;
  }
}, { immediate: true });

// Watch for changes in the URL query parameters
watch(() => window.location.search, (newSearch) => {
  const params = new URLSearchParams(newSearch);
  const childId = params.get('childId');
  const sectionId = params.get('sectionId');
  const itemId = params.get('itemId');

  console.log('URL params changed:', { childId, sectionId, itemId });

  // Check for childId first
  if (childId) {
    const child = findChildById(childId);
    if (child) {
      currentChild.value = child;
    }
  }
  // Then check for sectionId or itemId (which could be a child ID for parents)
  else if (sectionId || itemId) {
    const id = sectionId || itemId;
    const child = findChildById(id);
    if (child) {
      currentChild.value = child;
    }
  }
}, { immediate: true });

// Lifecycle hooks
onMounted(() => {
  console.log('ParentBehaviorReports component mounted');

  // Check if we're on mobile
  checkMobile();
  window.addEventListener('resize', checkMobile);

  // Add event listeners for various events
  window.addEventListener('item-changed', handleItemChanged);
  window.addEventListener('child-changed', handleChildChanged);
  window.addEventListener('section-changed', handleSectionChanged);

  // Initialize currentChild based on URL parameters or props
  const initializeCurrentChild = () => {
    const urlParams = new URLSearchParams(window.location.search);
    const childIdFromUrl = urlParams.get('childId');
    const sectionId = urlParams.get('sectionId');
    const itemId = urlParams.get('itemId');
    const childIdFromProps = page.props.childId;

    console.log('URL params on mount:', { childIdFromUrl, sectionId, itemId, childIdFromProps });

    let childId = childIdFromProps || childIdFromUrl || sectionId || itemId;

    if (childId) {
      const child = findChildById(childId);
      if (child) {
        currentChild.value = child;
      }
    }
  };

  initializeCurrentChild();

  // Clean up event listeners on component unmount
  return () => {
    window.removeEventListener('resize', checkMobile);
    window.removeEventListener('item-changed', handleItemChanged);
    window.removeEventListener('child-changed', handleChildChanged);
    window.removeEventListener('section-changed', handleSectionChanged);
  };
});

// Computed properties for behavior summary with defensive coding
const positiveReports = computed(() => {
  if (!currentChild.value || !currentChild.value.behaviorReports) return 0;
  return currentChild.value.behaviorReports.filter(report => report.type === 'positive').length;
});

const warningReports = computed(() => {
  if (!currentChild.value || !currentChild.value.behaviorReports) return 0;
  return currentChild.value.behaviorReports.filter(report => report.type === 'warning').length;
});

const incidentReports = computed(() => {
  if (!currentChild.value || !currentChild.value.behaviorReports) return 0;
  return currentChild.value.behaviorReports.filter(report => report.type === 'incident').length;
});

// Get behavior reports for the current child or all children
const behaviorReports = computed(() => {
  if (currentChild.value && currentChild.value.behaviorReports) {
    return currentChild.value.behaviorReports;
  } else {
    // Combine all children's behavior reports and sort by date (most recent first)
    const allReports = [];
    children.value.forEach(child => {
      if (child.behaviorReports) {
        child.behaviorReports.forEach(report => {
          allReports.push({
            ...report,
            childName: child.name,
            childId: child.id
          });
        });
      }
    });

    // Sort by date (assuming date is in format "MMM DD, YYYY")
    return allReports.sort((a, b) => {
      const dateA = new Date(a.date);
      const dateB = new Date(b.date);
      return dateB - dateA;
    });
  }
});

// Helper function to safely get behavior status
const getBehaviorStatus = (child) => {
  if (child && child.stats && child.stats.behavior) {
    return child.stats.behavior;
  }
  return 'Good'; // Default value
};
</script>

<template>
  <Head title="Parent Behavior Reports" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-6 pt-20">
      <!-- Child selector (visible on mobile or when no child is selected) -->
      <div v-if="!currentChild || isMobile" class="mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
          <h3 class="font-medium mb-4">My Children</h3>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div
              v-for="(child, index) in children"
              :key="index"
              class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
              :class="{'ring-2 ring-primary-500 dark:ring-primary-400': currentChild && currentChild.id === child.id}"
              @click="selectChild(child.id)"
            >
            <div class="flex items-center">
  <div class="w-12 h-12 rounded-full mr-4 bg-gray-300 text-white flex items-center justify-center text-xl font-medium overflow-hidden">
    <img
      v-if="child.avatar"
      :src="`/${child.avatar}`"
      :alt="`${child.name} Avatar`"
      class="w-12 h-12 rounded-full object-cover"
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
          </div>
        </div>
      </div>

      <!-- Child-specific behavior content -->
      <div v-if="currentChild">
        <!-- Child header -->
        <div class="flex items-center mb-6">
  <div class="w-16 h-16 rounded-full mr-4 bg-gray-300 text-white flex items-center justify-center text-2xl font-medium overflow-hidden">
    <img
      v-if="currentChild.avatar"
      :src="`/${currentChild.avatar}`"
      :alt="`${currentChild.name} Avatar`"
      class="w-16 h-16 rounded-full object-cover"
    />
    <span v-else>
      {{ currentChild.name.charAt(0).toUpperCase() }}
    </span>
  </div>
  
  <div>
    <h2 class="text-xl font-semibold">{{ currentChild.name }}</h2>
    <p class="text-gray-500 dark:text-gray-400">{{ currentChild.grade }}</p>
    <div class="flex items-center mt-1">
      <span class="text-sm text-gray-500 dark:text-gray-400 mr-2">Behavior:</span>
      <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300">
        {{ getBehaviorStatus(currentChild) }}
      </span>
    </div>
  </div>
  
  <button
    @click="selectChild('all')"
    class="ml-auto text-sm text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300"
  >
    Back to Overview
  </button>
</div>


        <!-- Behavior Summary -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-6">
          <h3 class="font-medium mb-4">Behavior Summary</h3>
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="p-4 bg-green-50 dark:bg-green-900/20 rounded-lg border border-green-100 dark:border-green-800">
              <div class="flex items-center space-x-3">
                <div class="bg-green-100 dark:bg-green-800 p-2 rounded-full">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                  </svg>
                </div>
                <div>
                  <p class="text-sm text-gray-500 dark:text-gray-400">Positive Reports</p>
                  <p class="text-xl font-bold">{{ positiveReports }}</p>
                </div>
              </div>
            </div>
            <div class="p-4 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg border border-yellow-100 dark:border-yellow-800">
              <div class="flex items-center space-x-3">
                <div class="bg-yellow-100 dark:bg-yellow-800 p-2 rounded-full">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-yellow-600 dark:text-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                  </svg>
                </div>
                <div>
                  <p class="text-sm text-gray-500 dark:text-gray-400">Warnings</p>
                  <p class="text-xl font-bold">{{ warningReports }}</p>
                </div>
              </div>
            </div>
            <div class="p-4 bg-red-50 dark:bg-red-900/20 rounded-lg border border-red-100 dark:border-red-800">
              <div class="flex items-center space-x-3">
                <div class="bg-red-100 dark:bg-red-800 p-2 rounded-full">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                  </svg>
                </div>
                <div>
                  <p class="text-sm text-gray-500 dark:text-gray-400">Incidents</p>
                  <p class="text-xl font-bold">{{ incidentReports }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Behavior Reports -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
          <div class="p-6 border-b dark:border-gray-700">
            <h3 class="font-medium">Behavior Reports</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400">Current Academic Year</p>
          </div>
          <div class="divide-y divide-gray-200 dark:divide-gray-700">
            <div v-for="(report, index) in behaviorReports" :key="index" class="p-6">
              <div class="flex items-start space-x-4">
                <div :class="`w-10 h-10 rounded-full flex items-center justify-center ${report.iconBg} ${report.iconColor}`">
                  <span class="text-lg">{{ report.icon }}</span>
                </div>
                <div class="flex-1">
                  <div class="flex items-center justify-between mb-2">
                    <h4 class="font-medium">{{ report.title }}</h4>
                    <span :class="`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${report.typeClass}`">
                      {{ report.type }}
                    </span>
                  </div>
                  <p class="text-sm text-gray-600 dark:text-gray-300 mb-2">{{ report.description }}</p>
                  <!-- <div class="flex items-center justify-between">
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ report.teacher }} • {{ report.date }}</p>
                    <button class="text-sm text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300">
                      Respond
                    </button>
                  </div> -->
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- All children behavior reports -->
      <div v-else>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-6">
          <h3 class="font-medium mb-4">Behavior Summary - All Children</h3>
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div v-for="child in children" :key="child.id" class="bg-white dark:bg-gray-700 rounded-lg shadow p-4 border border-gray-200 dark:border-gray-600">
              <div class="flex items-center mb-3">
  <div class="w-10 h-10 rounded-full mr-3 bg-gray-300 text-white flex items-center justify-center text-sm font-medium overflow-hidden">
    <img
      v-if="child.avatar"
      :src="`/${child.avatar}`"
      
      class="w-10 h-10 rounded-full object-cover"
    />
    <span v-else>
      {{ child.name.charAt(0).toUpperCase() }}
    </span>
  </div>

  <div>
    <h4 class="font-medium">{{ child.name }}</h4>
    <p class="text-xs text-gray-500 dark:text-gray-400">{{ child.grade }}</p>
  </div>

  <button
    @click="selectChild(child.id)"
    class="ml-auto text-xs text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300"
  >
    View Details
  </button>
</div>

              <div class="grid grid-cols-3 gap-2 text-center text-sm">
                <div class="p-2 bg-green-50 dark:bg-green-900/20 rounded">
                  <p class="text-xs text-gray-500 dark:text-gray-400">Positive</p>
                  <p class="font-bold text-green-600 dark:text-green-400">
                    {{ child.behaviorReports ? child.behaviorReports.filter(r => r.type === 'positive').length : 0 }}
                  </p>
                </div>
                <div class="p-2 bg-yellow-50 dark:bg-yellow-900/20 rounded">
                  <p class="text-xs text-gray-500 dark:text-gray-400">Warnings</p>
                  <p class="font-bold text-yellow-600 dark:text-yellow-400">
                    {{ child.behaviorReports ? child.behaviorReports.filter(r => r.type === 'warning').length : 0 }}
                  </p>
                </div>
                <div class="p-2 bg-red-50 dark:bg-red-900/20 rounded">
                  <p class="text-xs text-gray-500 dark:text-gray-400">Incidents</p>
                  <p class="font-bold text-red-600 dark:text-red-400">
                    {{ child.behaviorReports ? child.behaviorReports.filter(r => r.type === 'incident').length : 0 }}
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Recent Behavior Reports -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
          <div class="p-6 border-b dark:border-gray-700">
            <h3 class="font-medium">Recent Behavior Reports</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400">All Children</p>
          </div>
          <div class="divide-y divide-gray-200 dark:divide-gray-700">
            <div v-for="(report, index) in behaviorReports" :key="index" class="p-6">
              <div class="flex items-start space-x-4">
                <div :class="`w-10 h-10 rounded-full flex items-center justify-center ${report.iconBg} ${report.iconColor}`">
                  <span class="text-lg">{{ report.icon }}</span>
                </div>
                <div class="flex-1">
                  <div class="flex items-center justify-between mb-2">
                    <h4 class="font-medium">{{ report.title }}</h4>
                    <span :class="`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${report.typeClass}`">
                      {{ report.type }}
                    </span>
                  </div>
                  <p class="text-sm text-gray-600 dark:text-gray-300 mb-2">{{ report.description }}</p>
                  <div class="flex items-center justify-between">
                    <div>
                      <p class="text-sm text-gray-500 dark:text-gray-400">{{ report.teacher }} • {{ report.date }}</p>
                      <p class="text-xs text-gray-500 dark:text-gray-400">Child: {{ report.childName }}</p>
                    </div>
                    <div class="flex space-x-2">
                      <button
                        @click="selectChild(report.childId)"
                        class="text-sm text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200"
                      >
                        View Child
                      </button>
                      <button class="text-sm text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300">
                        Respond
                      </button>
                    </div>
                  </div>
                </div>
              </div>
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

.bg-primary-50 { background-color: rgb(var(--primary-50)); }
.bg-primary-100 { background-color: rgb(var(--primary-100)); }
.bg-primary-600 { background-color: rgb(var(--primary-600)); }
.text-primary-600 { color: rgb(var(--primary-600)); }
.text-primary-400 { color: rgb(var(--primary-400)); }

/* Add Tailwind dark mode support */
.dark .dark\:bg-primary-900 { background-color: rgb(var(--primary-900)); }
.dark .dark\:text-primary-400 { color: rgb(var(--primary-400)); }
.dark .dark\:text-primary-300 { color: rgb(var(--primary-300)); }
</style>
