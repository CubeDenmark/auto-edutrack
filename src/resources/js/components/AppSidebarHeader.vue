<script setup lang="ts">
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import type { BreadcrumbItemType } from '@/types';
import { ref, computed } from 'vue';

// Define props to receive from parent
const props = defineProps<{
  breadcrumbs?: BreadcrumbItemType[];
  mainNavItems: { title: string; href: string; icon: string }[];
  activePage: string;
}>();

// State for notifications dropdown
const showNotifications = ref(false);

// Toggle notifications dropdown
const toggleNotifications = () => {
  showNotifications.value = !showNotifications.value;
};

// Computed property to get the current page title
const currentPageTitle = computed(() => {
  const currentItem = props.mainNavItems.find(item => item.href === props.activePage);
  return currentItem ? currentItem.title : 'Dashboard';
});
</script>

<template>
  <header
    class="bg-white dark:bg-gray-800 shadow-sm fixed top-0 right-0 left-0 md:left-64 z-30"
  >
    <div class="flex items-center justify-between p-4">
      <!-- Dynamic page title based on active page -->
      <h1 class="text-xl font-semibold">{{ currentPageTitle }}</h1>

      <div class="flex items-center space-x-4">
        <!-- Notifications Dropdown -->
        <div class="relative">
          <!-- <button
            @click="toggleNotifications"
            class="relative p-1 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
            </svg>
            <span class="absolute top-0 right-0 h-2 w-2 bg-red-500 rounded-full"></span>
          </button> -->

          <!-- Notifications dropdown panel -->
          <div v-if="showNotifications" class="absolute right-0 mt-2 w-80 bg-white dark:bg-gray-800 rounded-lg shadow-lg border dark:border-gray-700 z-50">
            <div class="p-3 border-b dark:border-gray-700">
              <h3 class="font-medium">Notifications</h3>
            </div>
            <div class="max-h-64 overflow-y-auto">
              <div class="p-3 border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700">
                <p class="text-sm">New user registered</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">5 minutes ago</p>
              </div>
              <div class="p-3 border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700">
                <p class="text-sm">System update completed</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">1 hour ago</p>
              </div>
            </div>
            <div class="p-2 text-center">
              <button class="text-sm text-primary-600 hover:underline">View all notifications</button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </header>
</template>
