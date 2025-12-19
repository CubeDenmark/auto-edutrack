<script setup lang="ts">
import AppContent from '@/components/AppContent.vue';
import AppShell from '@/components/AppShell.vue';
import AppSidebar from '@/components/AppSidebar.vue';
import AppSidebarHeader from '@/components/AppSidebarHeader.vue';
import { type BreadcrumbItem, type SharedData, type User } from '@/types';
import { ref, computed, onMounted, watch } from 'vue';
import { usePage, router } from '@inertiajs/vue3';

// Props
interface Props {
  breadcrumbs?: BreadcrumbItem[];
}

// Set default values for props
const props = withDefaults(defineProps<Props>(), {
  breadcrumbs: () => [],
});

// Access user directly from Inertia page props
const page = usePage<SharedData>();
const user = page.props.auth.user as User;
const userType = user.user_type;

console.log('User type:', userType);

// Shared state
const activePage = ref('prof/dashboard');
const currentSectionId = ref<string | null>(null);

// Navigation items for different user types
const profNavItems = [
  { title: 'Dashboard', href: '/prof/dashboard', icon: '📊' },
  { title: 'Students', href: '/prof/students', icon: '👨‍🎓' }, // Added new students tab
  { title: 'Attendance', href: '/prof/attendance', icon: '✅' },
  { title: 'Grades', href: '/prof/grades', icon: '🏆' },
  { title: 'Assessment', href: '/prof/assessment', icon: '🏆' },
  { title: 'Assessment Records', href: '/prof/assess_records', icon: '🏆' },
  { title: 'Behavior Reports', href: '/prof/behavior_reports', icon: '📜' },
];

const studentNavItems = [
  { title: 'Dashboard', href: '/student/dashboard', icon: '📊' },
  { title: 'Attendance', href: '/student/attendance', icon: '📅' },
  { title: 'Grades', href: '/student/grades', icon: '🎓' },
  { title: 'Behavior Reports', href: '/student/behavior-reports', icon: '📜' },
//   { title: 'Notifications', href: '/student/notifications', icon: '🔔' }
];

const parentNavItems = [
  { title: 'Dashboard', href: '/parent/dashboard', icon: '📊' },
  { title: 'Grades', href: '/parent/grades', icon: '🎓' },
  { title: 'Attendance', href: '/parent/attendance', icon: '📅' },
  { title: 'Behavior Reports', href: '/parent/behavior-reports', icon: '📜' },
//   { title: 'Notifications', href: '/parent/notifications', icon: '🔔' },
];

// Computed property to get the appropriate navigation items based on user type
const mainNavItems = computed(() => {
  switch (userType) {
    case 'prof':
      return profNavItems;
    case 'student':
      return studentNavItems;
    case 'parent':
      return parentNavItems;
    default:
      return studentNavItems; // Fallback to admin if type is unknown
  }
});

// Computed property to get the current page
const currentPath = computed(() => {
  return page.url;
});

// Handle content updates from the sidebar
const handleContentUpdate = (type: string, id: string | number) => {
  if (type === 'section') {
    currentSectionId.value = id as string;

    // Update the URL with the section ID without navigating
    const url = new URL(window.location.href);
    url.searchParams.set('sectionId', id as string);
    window.history.replaceState({}, '', url.toString());

    // If we need to refresh data from the server, we can use Inertia's visit
    router.reload({
      only: ['sectionData'],
      data: { sectionId: id },
      preserveState: true
    });
  }
};

// Set the active page based on the current URL when the component mounts
onMounted(() => {
  const path = currentPath.value;
  // Find the matching nav item or default to dashboard
  const matchingItem = mainNavItems.value.find(item => path.startsWith(item.href));
  if (matchingItem) {
    activePage.value = matchingItem.href;
  }

  // Check if there's a section ID in the URL
  const urlParams = new URLSearchParams(window.location.search);
  const sectionId = urlParams.get('sectionId');

  if (sectionId) {
    currentSectionId.value = sectionId;
  }
});

// Watch for changes in the URL to update the active page and section
watch(() => window.location.href, (newUrl) => {
  const url = new URL(newUrl);
  const path = url.pathname;

  // Update active page if it changed
  const matchingItem = mainNavItems.value.find(item => path.startsWith(item.href));
  if (matchingItem) {
    activePage.value = matchingItem.href;
  }

  // Update section ID if it changed
  const sectionId = url.searchParams.get('sectionId');
  if (sectionId !== currentSectionId.value) {
    currentSectionId.value = sectionId;
  }
}, { immediate: true });
</script>

<template>
  <AppShell variant="sidebar" class="h-screen flex overflow-hidden">
    <!-- Sidebar -->
    <AppSidebar
      class="h-full overflow-hidden flex-shrink-0"
      :mainNavItems="mainNavItems"
      :activePage="activePage"
      @update:activePage="activePage = $event"
      @update:content="handleContentUpdate"
    />

    <!-- Main Content -->
    <AppContent variant="sidebar" class="h-full overflow-y-auto">
      <AppSidebarHeader
        :breadcrumbs="breadcrumbs"
        :mainNavItems="mainNavItems"
        :activePage="activePage"
      />
      <slot />
    </AppContent>
  </AppShell>
</template>
