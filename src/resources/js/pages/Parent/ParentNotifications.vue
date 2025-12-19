<script setup lang="ts">
import { ref, computed, onMounted, watch, inject } from 'vue';
import AppLayout from "@/layouts/AppLayout.vue";
import { type BreadcrumbItem } from "@/types";
import { Head, usePage, Link } from "@inertiajs/vue3";

// Breadcrumbs
const breadcrumbs: BreadcrumbItem[] = [
  {
    title: "Notifications",
    href: "/parent/notifications",
  },
];

// Get the page props to access any data passed from the server
const page = usePage();

// Inject the current child ID from the parent component
const injectedChildId = inject('currentChildId', ref(null));
const injectedSectionId = inject('currentSectionId', ref(null));
// Inject the current item ID from the sidebar
const injectedItemId = inject('currentItemId', ref(null));

// State variables
const currentChild = ref<any>(null);
const isMobile = ref(false);
const notificationFilter = ref('all');

// Children data
const children = ref([
  {
    id: 'child1',
    name: 'Alex Johnson',
    grade: 'Grade 10',
    avatar: 'https://i.pravatar.cc/150?img=3'
  },
  {
    id: 'child2',
    name: 'Emma Johnson',
    grade: 'Grade 8',
    avatar: 'https://i.pravatar.cc/150?img=5'
  },
  {
    id: 'child3',
    name: 'Noah Johnson',
    grade: 'Grade 6',
    avatar: 'https://i.pravatar.cc/150?img=8'
  }
]);

// Notifications data
const notifications = ref([
  {
    id: 1,
    icon: '📝',
    iconBg: 'bg-blue-100 dark:bg-blue-900',
    iconColor: 'text-blue-600 dark:text-blue-400',
    title: 'New Assignment Posted',
    message: 'History research paper due in two weeks. Check the class portal for details.',
    time: '2 hours ago',
    unread: true,
    forChild: 'Alex Johnson',
    childId: 'child1'
  },
  {
    id: 2,
    icon: '🎓',
    iconBg: 'bg-green-100 dark:bg-green-900',
    iconColor: 'text-green-600 dark:text-green-400',
    title: 'Grade Updated',
    message: 'Science quiz has been graded. Your child received an A-.',
    time: '5 hours ago',
    unread: true,
    forChild: 'Emma Johnson',
    childId: 'child2'
  },
  {
    id: 3,
    icon: '📅',
    iconBg: 'bg-purple-100 dark:bg-purple-900',
    iconColor: 'text-purple-600 dark:text-purple-400',
    title: 'Event Reminder',
    message: 'Career Day is scheduled for next Friday. Parents are encouraged to participate.',
    time: '1 day ago',
    unread: true,
    forChild: 'All Children',
    childId: 'all'
  },
  {
    id: 4,
    icon: '📢',
    iconBg: 'bg-yellow-100 dark:bg-yellow-900',
    iconColor: 'text-yellow-600 dark:text-yellow-400',
    title: 'School Announcement',
    message: 'Parent-Teacher conferences will be held next week. Schedule your appointment online.',
    time: '2 days ago',
    unread: false,
    forChild: 'All Children',
    childId: 'all'
  },
  {
    id: 5,
    icon: '🏆',
    iconBg: 'bg-red-100 dark:bg-red-900',
    iconColor: 'text-red-600 dark:text-red-400',
    title: 'Academic Achievement',
    message: 'Congratulations! Your child has been nominated for the Student of the Month award.',
    time: '3 days ago',
    unread: false,
    forChild: 'Noah Johnson',
    childId: 'child3'
  },
  {
    id: 6,
    icon: '📚',
    iconBg: 'bg-blue-100 dark:bg-blue-900',
    iconColor: 'text-blue-600 dark:text-blue-400',
    title: 'Library Book Due',
    message: 'Please remind your child to return the library book by Friday.',
    time: '3 days ago',
    unread: false,
    forChild: 'Alex Johnson',
    childId: 'child1'
  },
  {
    id: 7,
    icon: '🚌',
    iconBg: 'bg-yellow-100 dark:bg-yellow-900',
    iconColor: 'text-yellow-600 dark:text-yellow-400',
    title: 'Field Trip Permission',
    message: 'Permission slip for the museum field trip is due by Monday.',
    time: '4 days ago',
    unread: false,
    forChild: 'Emma Johnson',
    childId: 'child2'
  },
  {
    id: 8,
    icon: '🏫',
    iconBg: 'bg-purple-100 dark:bg-purple-900',
    iconColor: 'text-purple-600 dark:text-purple-400',
    title: 'School Closure',
    message: 'School will be closed next Monday for staff development day.',
    time: '5 days ago',
    unread: false,
    forChild: 'All Children',
    childId: 'all'
  }
]);

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
    notificationFilter.value = 'all';
  } else {
    const child = findChildById(childId);
    if (child) {
      currentChild.value = child;
      notificationFilter.value = child.id;
    }
  }

  // Dispatch event for other components to listen to
  window.dispatchEvent(new CustomEvent('child-changed', {
    detail: {
      childId: childId,
      source: 'notifications'
    }
  }));
};

// Mark a notification as read
const markAsRead = (notificationId) => {
  const notification = notifications.value.find(n => n.id === notificationId);
  if (notification) {
    notification.unread = false;
  }
};

// Mark all notifications as read
const markAllAsRead = () => {
  notifications.value.forEach(notification => {
    notification.unread = false;
  });
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
        notificationFilter.value = 'all';
      } else {
        // Handle specific child selection
        const child = findChildById(itemId);
        if (child) {
          currentChild.value = child;
          notificationFilter.value = child.id;
        }
      }
    }
  }
};

// Handle child-changed event from other components
const handleChildChanged = (event) => {
  if (event.detail && event.detail.source !== 'notifications') {
    const { childId } = event.detail;

    if (childId === 'all') {
      currentChild.value = null;
      notificationFilter.value = 'all';
    } else {
      const child = findChildById(childId);
      if (child) {
        currentChild.value = child;
        notificationFilter.value = child.id;
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
        notificationFilter.value = 'all';
      } else {
        const child = findChildById(sectionId);
        if (child) {
          currentChild.value = child;
          notificationFilter.value = child.id;
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
      notificationFilter.value = child.id;
    }
  } else if (newChildId === 'all') {
    currentChild.value = null;
    notificationFilter.value = 'all';
  }
}, { immediate: true });

// Watch for changes in the injected section ID
watch(injectedSectionId, (newSectionId) => {
  if (newSectionId && newSectionId !== 'all') {
    const child = findChildById(newSectionId);
    if (child) {
      currentChild.value = child;
      notificationFilter.value = child.id;
    }
  } else if (newSectionId === 'all') {
    currentChild.value = null;
    notificationFilter.value = 'all';
  }
}, { immediate: true });

// Watch for changes in the injected item ID from sidebar
watch(injectedItemId, (newItemId) => {
  console.log('Injected item ID changed:', newItemId);
  if (newItemId && newItemId !== 'all') {
    const child = findChildById(newItemId);
    if (child) {
      currentChild.value = child;
      notificationFilter.value = child.id;
    }
  } else if (newItemId === 'all') {
    currentChild.value = null;
    notificationFilter.value = 'all';
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
      notificationFilter.value = child.id;
    }
  }
  // Then check for sectionId or itemId (which could be a child ID for parents)
  else if (sectionId || itemId) {
    const id = sectionId || itemId;
    const child = findChildById(id);
    if (child) {
      currentChild.value = child;
      notificationFilter.value = child.id;
    }
  }
}, { immediate: true });

// Lifecycle hooks
onMounted(() => {
  console.log('ParentNotifications component mounted');

  // Check if we're on mobile
  checkMobile();
  window.addEventListener('resize', checkMobile);

  // Add event listeners for various events
  window.addEventListener('item-changed', handleItemChanged);
  window.addEventListener('child-changed', handleChildChanged);
  window.addEventListener('section-changed', handleSectionChanged);

  // Check if there's an item ID in the URL and initialize if present
  const urlParams = new URLSearchParams(window.location.search);
  const childIdFromUrl = urlParams.get('childId');
  const sectionId = urlParams.get('sectionId');
  const itemId = urlParams.get('itemId');
  const childIdFromProps = page.props.childId;

  console.log('URL params on mount:', { childIdFromUrl, sectionId, itemId, childIdFromProps });

  // First check for childId from URL or props
  if (childIdFromUrl || childIdFromProps) {
    const childId = childIdFromProps || childIdFromUrl;
    const child = findChildById(childId);
    if (child) {
      currentChild.value = child;
      notificationFilter.value = child.id;
    }
  }
  // Then check for sectionId or itemId (which could be a child ID for parents)
  else if (sectionId || itemId) {
    const id = sectionId || itemId;
    const child = findChildById(id);
    if (child) {
      currentChild.value = child;
      notificationFilter.value = child.id;
    }
  }

  // Clean up event listeners on component unmount
  return () => {
    window.removeEventListener('resize', checkMobile);
    window.removeEventListener('item-changed', handleItemChanged);
    window.removeEventListener('child-changed', handleChildChanged);
    window.removeEventListener('section-changed', handleSectionChanged);
  };
});

// Computed properties
const filteredNotifications = computed(() => {
  if (notificationFilter.value === 'all') {
    return notifications.value;
  } else {
    const childName = findChildById(notificationFilter.value)?.name;
    return notifications.value.filter(notification =>
      notification.forChild === childName || notification.forChild === 'All Children'
    );
  }
});

const unreadCount = computed(() => {
  return filteredNotifications.value.filter(notification => notification.unread).length;
});
</script>

<template>
  <Head title="Parent Notifications" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-6 pt-20">
      <!-- Child selector (visible on mobile) -->
      <div v-if="isMobile" class="mb-6">
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
                <img :src="child.avatar" :alt="`${child.name} Avatar`" class="w-12 h-12 rounded-full mr-4" />
                <div>
                  <h4 class="font-medium">{{ child.name }}</h4>
                  <p class="text-sm text-gray-500 dark:text-gray-400">{{ child.grade }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Notifications Panel -->
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
        <div class="p-6 border-b dark:border-gray-700">
          <div class="flex items-center justify-between">
            <h3 class="font-medium">Notifications</h3>
            <div class="flex space-x-4">
              <select
                v-model="notificationFilter"
                class="text-sm rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100"
              >
                <option value="all">All Children</option>
                <option v-for="child in children" :key="child.id" :value="child.id">{{ child.name }}</option>
              </select>
              <button
                @click="markAllAsRead"
                class="text-sm text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300"
              >
                Mark all as read
              </button>
            </div>
          </div>
          <div v-if="unreadCount > 0" class="mt-2 text-sm text-gray-500 dark:text-gray-400">
            You have {{ unreadCount }} unread notification{{ unreadCount > 1 ? 's' : '' }}
          </div>
        </div>
        <div class="divide-y divide-gray-200 dark:divide-gray-700">
          <div v-for="notification in filteredNotifications" :key="notification.id"
            :class="[
              'p-6 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors',
              notification.unread ? 'bg-blue-50 dark:bg-blue-900/10' : ''
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
                <div class="mt-2 flex justify-end space-x-2">
                  <button
                    v-if="notification.unread"
                    @click="markAsRead(notification.id)"
                    class="text-xs text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300"
                  >
                    Mark as read
                  </button>
                  <button
                    v-if="notification.childId !== 'all'"
                    @click="selectChild(notification.childId)"
                    class="text-xs text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200"
                  >
                    View Child
                  </button>
                  <button class="text-xs text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300">
                    View details
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div v-if="filteredNotifications.length === 0" class="p-10 text-center text-gray-500 dark:text-gray-400">
          No notifications found for the selected filter.
        </div>
        <div v-else class="p-6 border-t dark:border-gray-700 text-center">
          <button class="text-sm text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300">
            Load more notifications
          </button>
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
