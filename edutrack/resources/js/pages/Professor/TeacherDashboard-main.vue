<script setup lang="ts">
import AppLayout from "@/layouts/AppLayout.vue";
import { type BreadcrumbItem } from "@/types";
import { Head } from "@inertiajs/vue3";

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: "Dashboard",
    href: "teacher/dashboard",
  },
];

defineProps<{
  name?: string;
}>();

import { ref } from "vue";


const recentActivities = ref([
    {
      icon: '👤',
      iconBg: 'bg-blue-100 dark:bg-blue-900',
      iconColor: 'text-blue-500',
      title: 'New User Registration',
      description: 'John Smith registered as a new student.',
      time: '2h ago'
    },
    {
      icon: '🏫',
      iconBg: 'bg-green-100 dark:bg-green-900',
      iconColor: 'text-green-500',
      title: 'Department Added',
      description: 'College of Fine Arts was added to the system.',
      time: '5h ago'
    },
    {
      icon: '📚',
      iconBg: 'bg-yellow-100 dark:bg-yellow-900',
      iconColor: 'text-yellow-500',
      title: 'Course Updated',
      description: 'BSIT curriculum was updated with new subjects.',
      time: '1d ago'
    },
    {
      icon: '📜',
      iconBg: 'bg-purple-100 dark:bg-purple-900',
      iconColor: 'text-purple-500',
      title: 'Report Generated',
      description: 'Performance report for College of Engineering was generated.',
      time: '2d ago'
    },
  ]);

</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 pt-20">
            <!-- Dashboard Page -->
            <div>
              <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                <!-- Users Card -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                  <div class="flex items-center justify-between mb-4">
                    <h3 class="font-medium">Total Users</h3>
                  </div>
                  <div class="flex items-center space-x-4">
                    <div class="w-16 h-16 rounded-full flex items-center justify-center bg-blue-100 dark:bg-blue-900 text-blue-500">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                      </svg>
                    </div>
                    <div>
                      <p class="text-sm text-gray-500 dark:text-gray-400">Registered Users</p>
                      <p class="text-2xl font-bold">1,254</p>
                    </div>
                  </div>
                </div>

                <!-- Departments Card -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                  <div class="flex items-center justify-between mb-4">
                    <h3 class="font-medium">Departments</h3>
                  </div>
                  <div class="flex items-center space-x-4">
                    <div class="w-16 h-16 rounded-full flex items-center justify-center bg-green-100 dark:bg-green-900 text-green-500">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                      </svg>
                    </div>
                    <div>
                      <p class="text-sm text-gray-500 dark:text-gray-400">Active Departments</p>
                      <p class="text-2xl font-bold">8</p>
                    </div>
                  </div>
                </div>

                <!-- Courses Card -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                  <div class="flex items-center justify-between mb-4">
                    <h3 class="font-medium">Courses</h3>
                  </div>
                  <div class="flex items-center space-x-4">
                    <div class="w-16 h-16 rounded-full flex items-center justify-center bg-purple-100 dark:bg-purple-900 text-purple-500">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path d="M12 14l9-5-9-5-9 5 9 5z" />
                        <path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998a12.078 12.078 0 01.665-6.479L12 14z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998a12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222" />
                      </svg>
                    </div>
                    <div>
                      <p class="text-sm text-gray-500 dark:text-gray-400">Total Courses</p>
                      <p class="text-2xl font-bold">24</p>
                    </div>
                  </div>
                </div>

                <!-- Sections Card -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                  <div class="flex items-center justify-between mb-4">
                    <h3 class="font-medium">Sections</h3>
                  </div>
                  <div class="flex items-center space-x-4">
                    <div class="w-16 h-16 rounded-full flex items-center justify-center bg-yellow-100 dark:bg-yellow-900 text-yellow-500">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                      </svg>
                    </div>
                    <div>
                      <p class="text-sm text-gray-500 dark:text-gray-400">Active Sections</p>
                      <p class="text-2xl font-bold">42</p>
                    </div>
                  </div>
                </div>
              </div>

              <!-- User Statistics Chart -->
              <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-6">
                <h3 class="font-medium mb-4">User Statistics</h3>
                <div class="h-80 w-full">
                  <!-- Chart would be rendered here with a chart library -->
                  <div class="h-full w-full flex items-center justify-center bg-gray-100 dark:bg-gray-700 rounded-lg">
                    <div class="space-y-6 w-full px-8">
                      <h4 class="text-center text-gray-500 dark:text-gray-400">Students per Department</h4>
                      <!-- College of Computer Studies -->
                      <div>
                        <div class="flex justify-between mb-1">
                          <span class="text-sm font-medium">College of Computer Studies</span>
                          <span class="text-sm font-medium">450</span>
                        </div>
                        <div class="w-full bg-gray-200 dark:bg-gray-600 rounded-full h-2.5">
                          <div class="bg-blue-600 h-2.5 rounded-full" style="width: 90%"></div>
                        </div>
                      </div>

                      <!-- College of Engineering -->
                      <div>
                        <div class="flex justify-between mb-1">
                          <span class="text-sm font-medium">College of Engineering</span>
                          <span class="text-sm font-medium">380</span>
                        </div>
                        <div class="w-full bg-gray-200 dark:bg-gray-600 rounded-full h-2.5">
                          <div class="bg-green-600 h-2.5 rounded-full" style="width: 76%"></div>
                        </div>
                      </div>

                      <!-- College of Business -->
                      <div>
                        <div class="flex justify-between mb-1">
                          <span class="text-sm font-medium">College of Business</span>
                          <span class="text-sm font-medium">320</span>
                        </div>
                        <div class="w-full bg-gray-200 dark:bg-gray-600 rounded-full h-2.5">
                          <div class="bg-yellow-600 h-2.5 rounded-full" style="width: 64%"></div>
                        </div>
                      </div>

                      <!-- College of Arts and Sciences -->
                      <div>
                        <div class="flex justify-between mb-1">
                          <span class="text-sm font-medium">College of Arts and Sciences</span>
                          <span class="text-sm font-medium">280</span>
                        </div>
                        <div class="w-full bg-gray-200 dark:bg-gray-600 rounded-full h-2.5">
                          <div class="bg-purple-600 h-2.5 rounded-full" style="width: 56%"></div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Recent Activity -->
              <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h3 class="font-medium mb-4">Recent System Activity</h3>
                <div class="space-y-4">
                  <div v-for="(activity, index) in recentActivities" :key="index" class="flex items-start space-x-4 pb-4 border-b dark:border-gray-700 last:border-0 last:pb-0">
                    <div :class="`w-10 h-10 rounded-full flex items-center justify-center ${activity.iconBg} ${activity.iconColor}`">
                      <span class="text-lg">{{ activity.icon }}</span>
                    </div>
                    <div class="flex-1">
                      <p class="font-medium">{{ activity.title }}</p>
                      <p class="text-sm text-gray-500 dark:text-gray-400">{{ activity.description }}</p>
                    </div>
                    <span class="text-xs text-gray-500 dark:text-gray-400">{{ activity.time }}</span>
                  </div>
                </div>
              </div>
            </div>
            </div>
    </AppLayout>
  </template>
