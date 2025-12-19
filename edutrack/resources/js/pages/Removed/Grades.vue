<script setup lang="ts">
import AppLayout from "@/layouts/AppLayout.vue";
import { type BreadcrumbItem } from "@/types";
import { Head } from "@inertiajs/vue3";
import InfoCard from '@/components/InfoCard.vue';


const breadcrumbs: BreadcrumbItem[] = [
  {
    title: "Grades",
    href: "/grades",
  },
];

defineProps<{
  name?: string;
}>();

const infoCards = [
  { label: 'GWA', value: '1.75' },
  { label: 'Student Number', value: '20241001' },
  { label: 'Course', value: 'Bachelor of Science in IT' },
  { label: 'Year Level', value: '4th Year' },
  { label: 'Status', value: 'Enrolled' }

]
</script>

<template>
  <Head title="Grades" />

  <AppLayout :breadcrumbs="breadcrumbs">

     <!-- Grades List -->
     <div class="bg-background rounded-lg shadow border">
            <div class="p-4 border-b flex justify-between items-center">
              <h3 class="font-medium">Recent Grades</h3>
              <button class="text-xs bg-primary text-primary-foreground px-2 py-1 rounded">Add Grade</button>
            </div>
            <div class="p-4">
              <div class="overflow-x-auto">
                <table class="w-full">
                  <thead>
                    <tr class="text-left text-xs font-medium text-muted-foreground border-b">
                      <th class="pb-2 pl-4">Student</th>
                      <th class="pb-2">Subject</th>
                      <th class="pb-2">Test Type</th>
                      <th class="pb-2">Score</th>
                      <th class="pb-2">Date</th>
                      <th class="pb-2 pr-4">Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr class="border-b hover:bg-muted/20">
                      <td class="py-3 pl-4 text-sm">
                        <div class="flex items-center">
                          <img src="https://ui.shadcn.com/avatars/03.png" alt="Student" class="h-6 w-6 rounded-full mr-2" />
                          <span>Alex Johnson</span>
                        </div>
                      </td>
                      <td class="py-3 text-sm">Mathematics</td>
                      <td class="py-3 text-sm">Mid-term Exam</td>
                      <td class="py-3 text-sm">
                        <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">92%</span>
                      </td>
                      <td class="py-3 text-sm">May 15, 2023</td>
                      <td class="py-3 pr-4 text-sm">
                        <button class="p-1 rounded hover:bg-muted">
                          <FileText class="h-4 w-4" />
                        </button>
                      </td>
                    </tr>
                    <tr class="border-b hover:bg-muted/20">
                      <td class="py-3 pl-4 text-sm">
                        <div class="flex items-center">
                          <img src="https://ui.shadcn.com/avatars/05.png" alt="Student" class="h-6 w-6 rounded-full mr-2" />
                          <span>Samantha Lee</span>
                        </div>
                      </td>
                      <td class="py-3 text-sm">Science</td>
                      <td class="py-3 text-sm">Lab Report</td>
                      <td class="py-3 text-sm">
                        <span class="px-2 py-1 text-xs rounded-full bg-amber-100 text-amber-800">78%</span>
                      </td>
                      <td class="py-3 text-sm">May 12, 2023</td>
                      <td class="py-3 pr-4 text-sm">
                        <button class="p-1 rounded hover:bg-muted">
                          <FileText class="h-4 w-4" />
                        </button>
                      </td>
                    </tr>
                    <tr class="border-b hover:bg-muted/20">
                      <td class="py-3 pl-4 text-sm">
                        <div class="flex items-center">
                          <img src="https://ui.shadcn.com/avatars/02.png" alt="Student" class="h-6 w-6 rounded-full mr-2" />
                          <span>Michael Chen</span>
                        </div>
                      </td>
                      <td class="py-3 text-sm">History</td>
                      <td class="py-3 text-sm">Essay</td>
                      <td class="py-3 text-sm">
                        <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">88%</span>
                      </td>
                      <td class="py-3 text-sm">May 10, 2023</td>
                      <td class="py-3 pr-4 text-sm">
                        <button class="p-1 rounded hover:bg-muted">
                          <FileText class="h-4 w-4" />
                        </button>
                      </td>
                    </tr>
                    <tr class="hover:bg-muted/20">
                      <td class="py-3 pl-4 text-sm">
                        <div class="flex items-center">
                          <img src="https://ui.shadcn.com/avatars/04.png" alt="Student" class="h-6 w-6 rounded-full mr-2" />
                          <span>Emily Rodriguez</span>
                        </div>
                      </td>
                      <td class="py-3 text-sm">English</td>
                      <td class="py-3 text-sm">Presentation</td>
                      <td class="py-3 text-sm">
                        <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800">65%</span>
                      </td>
                      <td class="py-3 text-sm">May 8, 2023</td>
                      <td class="py-3 pr-4 text-sm">
                        <button class="p-1 rounded hover:bg-muted">
                          <FileText class="h-4 w-4" />
                        </button>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
    <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
      <!-- Chart Grid -->
      <!-- <div class="grid auto-rows-min gap-4 md:grid-cols-3">


        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
             <InfoCard v-for="card in infoCards" :key="card.label" v-bind="card" />
        </div>
      </div> -->
    </div>
  </AppLayout>
</template>
