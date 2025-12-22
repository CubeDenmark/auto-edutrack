<script setup lang="ts">
import { ref, computed, onMounted, watch, inject } from 'vue';
import AppLayout from "@/layouts/AppLayout.vue";
import { type BreadcrumbItem } from "@/types";
import { Head, usePage, Link } from "@inertiajs/vue3";

// Breadcrumbs
const breadcrumbs: BreadcrumbItem[] = [
  {
    title: "Notifications",
    href: "/notifications",
  },
];

// Get the page props to access any data passed from the server
const page = usePage();

// Inject the current item ID from the sidebar
const currentItemId = inject('currentItemId', ref(null));

// State variables
const currentSection = ref<any>(null);

// General notifications (used when no class is selected)
const notifications = [
  {
    icon: '📝',
    iconBg: 'bg-blue-100 dark:bg-blue-900',
    iconColor: 'text-blue-600 dark:text-blue-400',
    title: 'New Assignment Posted',
    message: 'History research paper due in two weeks. Check the class portal for details.',
    time: '2 hours ago',
    unread: true
  },
  {
    icon: '🎓',
    iconBg: 'bg-green-100 dark:bg-green-900',
    iconColor: 'text-green-600 dark:text-green-400',
    title: 'Grade Updated',
    message: 'Your Science quiz has been graded. You received an A-.',
    time: '5 hours ago',
    unread: true
  },
  {
    icon: '📅',
    iconBg: 'bg-purple-100 dark:bg-purple-900',
    iconColor: 'text-purple-600 dark:text-purple-400',
    title: 'Event Reminder',
    message: 'Career Day is scheduled for next Friday. Don\'t forget to prepare your questions.',
    time: '1 day ago',
    unread: true
  },
  {
    icon: '📢',
    iconBg: 'bg-yellow-100 dark:bg-yellow-900',
    iconColor: 'text-yellow-600 dark:text-yellow-400',
    title: 'School Announcement',
    message: 'Parent-Teacher conferences will be held next week. Schedule your appointment online.',
    time: '2 days ago',
    unread: false
  },
  {
    icon: '🏆',
    iconBg: 'bg-red-100 dark:bg-red-900',
    iconColor: 'text-red-600 dark:text-red-400',
    title: 'Academic Achievement',
    message: 'Congratulations! You\'ve been nominated for the Student of the Month award.',
    time: '3 days ago',
    unread: false
  },
];

// Class sections data - same as in the other views
const classSections = ref([
  {
    id: 'sci101a',
    name: 'Science 101 - Section A',
    icon: '🧪',
    term: 'Fall Semester 2023',
    teacher: 'Dr. Johnson',
    room: 'SCI-201',
    notifications: [
      {
        icon: '📝',
        iconBg: 'bg-blue-100 dark:bg-blue-900',
        iconColor: 'text-blue-600 dark:text-blue-400',
        title: 'Lab Report Due',
        message: 'Your lab report on chemical reactions is due this Friday.',
        time: '1 hour ago',
        unread: true
      },
      {
        icon: '🔬',
        iconBg: 'bg-green-100 dark:bg-green-900',
        iconColor: 'text-green-600 dark:text-green-400',
        title: 'Lab Equipment Update',
        message: 'New microscopes have arrived. We will be using them in next week\'s lab.',
        time: '1 day ago',
        unread: false
      },
      {
        icon: '📚',
        iconBg: 'bg-purple-100 dark:bg-purple-900',
        iconColor: 'text-purple-600 dark:text-purple-400',
        title: 'Reading Assignment',
        message: 'Please read Chapter 5 before next class for discussion.',
        time: '2 days ago',
        unread: false
      }
    ]
  },
  {
    id: 'sci101b',
    name: 'Science 101 - Section B',
    icon: '🧪',
    term: 'Fall Semester 2023',
    teacher: 'Dr. Smith',
    room: 'SCI-202',
    notifications: [
      {
        icon: '🔬',
        iconBg: 'bg-blue-100 dark:bg-blue-900',
        iconColor: 'text-blue-600 dark:text-blue-400',
        title: 'Research Project Update',
        message: 'Project proposals have been reviewed. Check your feedback.',
        time: '3 hours ago',
        unread: true
      },
      {
        icon: '📅',
        iconBg: 'bg-yellow-100 dark:bg-yellow-900',
        iconColor: 'text-yellow-600 dark:text-yellow-400',
        title: 'Class Rescheduled',
        message: 'Thursday\'s class will be held in room SCI-301 due to maintenance.',
        time: '2 days ago',
        unread: false
      }
    ]
  },
  {
    id: 'bio202',
    name: 'Biology 202',
    icon: '🧬',
    term: 'Fall Semester 2023',
    teacher: 'Prof. Williams',
    room: 'BIO-101',
    notifications: [
      {
        icon: '🧬',
        iconBg: 'bg-green-100 dark:bg-green-900',
        iconColor: 'text-green-600 dark:text-green-400',
        title: 'DNA Model Project',
        message: 'Your DNA model project is due in two weeks. Materials list posted online.',
        time: '4 hours ago',
        unread: true
      },
      {
        icon: '📊',
        iconBg: 'bg-purple-100 dark:bg-purple-900',
        iconColor: 'text-purple-600 dark:text-purple-400',
        title: 'Ecosystem Presentation',
        message: 'Presentation schedule has been posted. Your group presents on Wednesday.',
        time: '1 day ago',
        unread: true
      },
      {
        icon: '📝',
        iconBg: 'bg-blue-100 dark:bg-blue-900',
        iconColor: 'text-blue-600 dark:text-blue-400',
        title: 'Quiz Results',
        message: 'Genetics quiz results have been posted. Class average was 85%.',
        time: '3 days ago',
        unread: false
      }
    ]
  },
  {
    id: 'chem101',
    name: 'Chemistry 101',
    icon: '⚗️',
    term: 'Fall Semester 2023',
    teacher: 'Dr. Brown',
    room: 'CHEM-305',
    notifications: [
      {
        icon: '⚗️',
        iconBg: 'bg-yellow-100 dark:bg-yellow-900',
        iconColor: 'text-yellow-600 dark:text-yellow-400',
        title: 'Lab Safety Reminder',
        message: 'Remember to bring your safety goggles for tomorrow\'s experiment.',
        time: '5 hours ago',
        unread: true
      },
      {
        icon: '📚',
        iconBg: 'bg-blue-100 dark:bg-blue-900',
        iconColor: 'text-blue-600 dark:text-blue-400',
        title: 'Study Guide Available',
        message: 'The study guide for the periodic table quiz is now available online.',
        time: '2 days ago',
        unread: false
      }
    ]
  }
]);

// Student's enrolled classes
const enrolledClasses = computed(() => {
  // In a real app, this would be filtered based on the student's enrollment
  return classSections.value;
});

// Methods
function findSectionById(id) {
  return classSections.value.find(section => section.id === id) || null;
}

// Select a section
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
}

// Listen for item changes from the sidebar
const handleItemChange = (event) => {
  if (event.detail && event.detail.source === 'sidebar') {
    const itemId = event.detail.itemId;
    const itemType = event.detail.itemType;

    // For students, the itemType will be 'section' or undefined
    if (itemType === 'section' || !itemType) {
      const section = findSectionById(itemId);
      if (section && (!currentSection.value || currentSection.value.id !== section.id)) {
        // Only update if it's a different section to avoid infinite loops
        currentSection.value = section;
      }
    }
  }
};

// For backward compatibility, also listen for section-changed events
const handleSectionChange = (event) => {
  const sectionId = event.detail.sectionId;
  if (sectionId) {
    const section = findSectionById(sectionId);
    if (section && (!currentSection.value || currentSection.value.id !== section.id)) {
      currentSection.value = section;
    }
  }
};

// Watch for changes in the injected item ID
watch(currentItemId, (newItemId) => {
  if (newItemId) {
    const section = findSectionById(newItemId);
    if (section && (!currentSection.value || currentSection.value.id !== section.id)) {
      // Only update if it's a different section
      currentSection.value = section;
    }
  }
}, { immediate: true });

// Watch for changes in the URL query parameters
watch(() => window.location.search, (newSearch) => {
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
}, { immediate: true });

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

  // First check for sectionId from URL or props
  if (sectionIdFromUrl || sectionIdFromProps) {
    const sectionId = sectionIdFromProps || sectionIdFromUrl;
    const section = findSectionById(sectionId);
    if (section) {
      currentSection.value = section;
      return;
    }
  }
  // Then check for itemId (which could be a section ID from sidebar)
  else if (itemIdFromUrl) {
    const section = findSectionById(itemIdFromUrl);
    if (section) {
      currentSection.value = section;
      return;
    }
  }
  // Finally check for injected item ID
  else if (currentItemId.value) {
    const section = findSectionById(currentItemId.value);
    if (section) {
      currentSection.value = section;
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
  <Head title="Student Notifications" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-6 pt-20">
      <!-- No Section Selected Message -->
      <div v-if="!currentSection" class="flex flex-col items-center justify-center h-[calc(100vh-10rem)] text-center">
        <div class="bg-gray-100 dark:bg-gray-800 p-6 rounded-full mb-4">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z" />
          </svg>
        </div>
        <h2 class="text-2xl font-bold mb-2">Select a Class Section</h2>
        <p class="text-gray-500 dark:text-gray-400 mb-6 max-w-md">Please select a class section from the sidebar to view class-specific notifications.</p>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
          <button
            v-for="section in classSections.slice(0, 4)"
            :key="section.id"
            @click="selectSection(section)"
            class="flex flex-col items-center p-4 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700"
          >
            <span class="text-3xl mb-2">{{ section.icon }}</span>
            <span class="text-sm font-medium">{{ section.name }}</span>
          </button>
        </div>
      </div>

      <!-- Class-specific notifications content -->
      <div v-if="currentSection" class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
        <div class="p-6 border-b dark:border-gray-700">
          <div class="flex items-center justify-between">
            <h3 class="font-medium">{{ currentSection.name }} Notifications</h3>
            <button class="text-sm text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300">
              Mark all as read
            </button>
          </div>
        </div>
        <div class="divide-y divide-gray-200 dark:divide-gray-700">
          <div v-for="(notification, index) in currentSection.notifications" :key="index"
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
              </div>
            </div>
          </div>
        </div>
        <div class="p-6 border-t dark:border-gray-700 text-center">
          <button class="text-sm text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300">
            Load more notifications
          </button>
        </div>
      </div>

      <!-- General notifications content (when no class is selected) -->
      <div v-if="!currentSection" class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden mt-6">
        <div class="p-6 border-b dark:border-gray-700">
          <div class="flex items-center justify-between">
            <h3 class="font-medium">Notifications</h3>
            <button class="text-sm text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300">
              Mark all as read
            </button>
          </div>
        </div>
        <div class="divide-y divide-gray-200 dark:divide-gray-700">
          <div v-for="(notification, index) in notifications" :key="index"
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
              </div>
            </div>
          </div>
        </div>
        <div class="p-6 border-t dark:border-gray-700 text-center">
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
