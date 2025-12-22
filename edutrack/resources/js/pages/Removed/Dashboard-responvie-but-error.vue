<script setup lang="ts">
import AppLayout from "@/layouts/AppLayout.vue";
import { type BreadcrumbItem } from "@/types";
import { Head } from "@inertiajs/vue3";

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: "Dashboard",
    href: "/dashboard",
  },
];

defineProps<{
  name?: string;
}>();

import { ref } from "vue";

import {
  Users,
  BookOpen,
  Award,
  Clock,
} from 'lucide-vue-next'

const activeMenuItem = ref("Dashboard");


const gradeDistribution = ref([
  { label: "A", percentage: 40 },
  { label: "B", percentage: 30 },
  { label: "C", percentage: 20 },
  { label: "D", percentage: 5 },
  { label: "F", percentage: 5 }
]);

const recentActivities = ref([
  { student: "John Doe", class: "Math 101", activity: "Submitted Homework", date: "2025-03-08", status: "Completed" },
  { student: "Jane Smith", class: "Science 102", activity: "Missed Quiz", date: "2025-03-07", status: "Failed" },
  { student: "Alice Johnson", class: "History 103", activity: "Attended Lecture", date: "2025-03-06", status: "Completed" },
  { student: "Bob Williams", class: "English 104", activity: "Submitted Project", date: "2025-03-05", status: "Pending" }
]);


// Dashboard stats
const stats = [
  { name: 'Total Students', value: '1,234', icon: Users, color: 'blue' },
  { name: 'Average Attendance', value: '85%', icon: Clock, color: 'green' },
  { name: 'Average Grade', value: 'B+', icon: Award, color: 'yellow' },
  { name: 'Active Courses', value: '24', icon: BookOpen, color: 'purple' }
]

</script>
<style>
@tailwind base;
@tailwind components;
@tailwind utilities;

:root {
  --primary: 222 47% 50%;
  --primary-foreground: 210 40% 98%;
}

.dark {
  --primary: 217 91% 60%;
  --primary-foreground: 210 40% 98%;
}

/* Add smooth scrolling */
html {
  scroll-behavior: smooth;
}

/* Improve mobile responsiveness */
@media (max-width: 640px) {
  .table-responsive {
    display: block;
    width: 100%;
    overflow-x: auto;
  }
}

/* Add transitions for better UX */
.transition-all {
  transition-property: all;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 300ms;
}
</style>
<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
      <div class="flex-1 overflow-y-auto p-4 md:p-6 bg-gray-100 dark:bg-gray-900">
        <div v-if="activeMenuItem === 'Dashboard'" class="space-y-6">

          <!-- Stats Grid -->
          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6">
            <div
              v-for="(stat, index) in stats"
              :key="index"
              class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 md:p-6 flex items-center"
            >
              <div
                class="rounded-full p-3 mr-4"
                :class="`bg-${stat.color}-100 dark:bg-${stat.color}-900/30 text-${stat.color}-600 dark:text-${stat.color}-400`"
              >
                <component :is="stat.icon" class="h-6 w-6" />
              </div>
              <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ stat.name }}</p>
                <p class="text-2xl font-semibold text-gray-800 dark:text-gray-200">
                  {{ stat.value }}
                </p>
              </div>
            </div>
          </div>

          <!-- Attendance & Grade Distribution -->
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
              <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">Attendance Overview</h2>
              <div class="h-64 flex items-center justify-center">
                <div class="relative w-40 h-40">
                  <div class="absolute inset-0 flex items-center justify-center">
                    <p class="text-2xl font-bold text-gray-800 dark:text-gray-200">85%</p>
                  </div>
                  <svg class="w-full h-full" viewBox="0 0 100 100">
                    <circle
                      class="text-gray-200 dark:text-gray-700 stroke-current"
                      stroke-width="10"
                      cx="50"
                      cy="50"
                      r="40"
                      fill="transparent"
                    ></circle>
                    <circle
                      class="text-primary stroke-current"
                      stroke-width="10"
                      stroke-linecap="round"
                      stroke-dasharray="251.2"
                      stroke-dashoffset="37.68"
                      cx="50"
                      cy="50"
                      r="40"
                      fill="transparent"
                      transform="rotate(-90 50 50)"
                    ></circle>
                  </svg>
                </div>
              </div>
              <div class="grid grid-cols-3 gap-4 mt-4">
                <div class="text-center">
                  <p class="text-sm text-gray-500 dark:text-gray-400">Present</p>
                  <p class="text-lg font-semibold text-gray-800 dark:text-gray-200">85%</p>
                </div>
                <div class="text-center">
                  <p class="text-sm text-gray-500 dark:text-gray-400">Absent</p>
                  <p class="text-lg font-semibold text-gray-800 dark:text-gray-200">10%</p>
                </div>
                <div class="text-center">
                  <p class="text-sm text-gray-500 dark:text-gray-400">Late</p>
                  <p class="text-lg font-semibold text-gray-800 dark:text-gray-200">5%</p>
                </div>
              </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
              <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">Grade Distribution</h2>
              <div class="h-64 flex items-center justify-center">
                <div class="w-full h-full flex flex-col space-y-2">
                  <div v-for="(grade, index) in gradeDistribution" :key="index" class="flex items-center">
                    <span class="w-8 text-sm text-gray-600 dark:text-gray-400">{{ grade.label }}</span>
                    <div class="flex-1 h-6 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden ml-2">
                      <div
                        class="h-full rounded-full bg-primary"
                        :style="{ width: `${grade.percentage}%` }"
                      ></div>
                    </div>
                    <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">{{ grade.percentage }}%</span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Recent Activities Table -->
          <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
            <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">Recent Activities</h2>
            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead>
                  <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Student</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Activity</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Date</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                  </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                  <tr v-for="(activity, index) in recentActivities" :key="index">
                    <td class="px-4 py-4 whitespace-nowrap">
                      <div class="flex items-center">
                        <div class="flex-shrink-0 h-10 w-10">
                          <img
                            :src="`/placeholder.svg?height=40&width=40&text=${activity.student.charAt(0)}`"
                            alt=""
                            class="h-10 w-10 rounded-full"
                          />
                        </div>
                        <div class="ml-4">
                          <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ activity.student }}</div>
                          <div class="text-sm text-gray-500 dark:text-gray-400">{{ activity.class }}</div>
                        </div>
                      </div>
                    </td>
                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ activity.activity }}</td>
                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ activity.date }}</td>
                    <td class="px-4 py-4 whitespace-nowrap">
                      <span
                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                        :class="{
                          'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400': activity.status === 'Completed',
                          'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400': activity.status === 'Pending',
                          'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400': activity.status === 'Failed'
                        }"
                      >
                        {{ activity.status }}
                      </span>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

        </div>
      </div>
    </AppLayout>
  </template>
