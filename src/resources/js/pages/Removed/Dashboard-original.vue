<script setup lang="ts">
import AppLayout from "@/layouts/AppLayout.vue";
import { type BreadcrumbItem } from "@/types";
import { Head } from "@inertiajs/vue3";
import AreaChart from "@/components/AreaChart.vue";
// import BarChart from "@/components/BarChart.vue";
import ChartLine from "@/components/ChartLine.vue";
import DonutChart from "@/components/DonutChart.vue";
import {
  User,
  GraduationCap,
  Users,
  CalendarCheck,
  TrendingUp,
  TrendingDown,
  AlertTriangle,
  BarChart,
 } from "lucide-vue-next";

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: "Dashboard",
    href: "/dashboard",
  },
];

defineProps<{
  name?: string;
}>();
</script>

<template>
  <Head title="Dashboard" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
      <!-- Chart Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
          <div class="bg-background rounded-lg shadow p-4 border">
            <div class="flex items-center">
              <div class="p-2 rounded-full bg-primary/10 text-primary mr-4">
                <Users class="h-6 w-6" />
              </div>
              <div>
                <p class="text-sm text-muted-foreground">Total Students</p>
                <h3 class="text-2xl font-bold">1,248</h3>
              </div>
            </div>
            <div class="mt-4 text-xs text-muted-foreground flex items-center">
              <TrendingUp class="h-3 w-3 text-green-500 mr-1" />
              <span class="text-green-500 font-medium">+2.5%</span>
              <span class="ml-1">from last month</span>
            </div>
          </div>

          <div class="bg-background rounded-lg shadow p-4 border">
            <div class="flex items-center">
              <div class="p-2 rounded-full bg-green-100 text-green-600 mr-4">
                <CalendarCheck class="h-6 w-6" />
              </div>
              <div>
                <p class="text-sm text-muted-foreground">Attendance Rate</p>
                <h3 class="text-2xl font-bold">94.2%</h3>
              </div>
            </div>
            <div class="mt-4 text-xs text-muted-foreground flex items-center">
              <TrendingDown class="h-3 w-3 text-red-500 mr-1" />
              <span class="text-red-500 font-medium">-0.8%</span>
              <span class="ml-1">from last week</span>
            </div>
          </div>

          <div class="bg-background rounded-lg shadow p-4 border">
            <div class="flex items-center">
              <div class="p-2 rounded-full bg-blue-100 text-blue-600 mr-4">
                <GraduationCap class="h-6 w-6" />
              </div>
              <div>
                <p class="text-sm text-muted-foreground">Average GPA</p>
                <h3 class="text-2xl font-bold">3.42</h3>
              </div>
            </div>
            <div class="mt-4 text-xs text-muted-foreground flex items-center">
              <TrendingUp class="h-3 w-3 text-green-500 mr-1" />
              <span class="text-green-500 font-medium">+0.3</span>
              <span class="ml-1">from last semester</span>
            </div>
          </div>

          <div class="bg-background rounded-lg shadow p-4 border">
            <div class="flex items-center">
              <div class="p-2 rounded-full bg-amber-100 text-amber-600 mr-4">
                <AlertTriangle class="h-6 w-6" />
              </div>
              <div>
                <p class="text-sm text-muted-foreground">Behavior Incidents</p>
                <h3 class="text-2xl font-bold">24</h3>
              </div>
            </div>
            <div class="mt-4 text-xs text-muted-foreground flex items-center">
              <TrendingDown class="h-3 w-3 text-green-500 mr-1" />
              <span class="text-green-500 font-medium">-12%</span>
              <span class="ml-1">from last month</span>
            </div>
          </div>
        </div>

      <div class="grid auto-rows-min gap-4 md:grid-cols-3">

        <div className="p-6">
            <div className="bg-white p-4 rounded-md border shadow-sm">
              <div className="flex items-start gap-3">
                <div className="p-2 bg-gray-100 rounded-md">
                  <User :size="20" stroke="blue" className="text-blue-900" />
                </div>
                <div>
                  <div className="text-sm text-gray-500 uppercase">STUDENT NUMBER</div>
                  <div className="font-medium">2021300766</div>
                </div>
              </div>
            </div>
        </div>

        <div class="p-6">
            <div className="bg-white p-4 rounded-md border shadow-sm">
              <div className="flex items-start gap-3">
                <div className="p-2 bg-gray-100 rounded-md">
                  <GraduationCap :size="20" stroke="blue" className="text-blue-600" />
                </div>
                <div>
                  <div className="text-sm text-gray-500 uppercase">PROGRAM</div>
                  <div className="font-medium">Bachelor of Science in Information Technology</div>
                </div>
              </div>
            </div>
        </div>

        <div className="p-6">
            <div className="bg-white p-4 rounded-md border shadow-sm">
              <div className="flex items-start gap-3">
                <div className="p-2 bg-gray-100 rounded-md">
                  <User :size="20" stroke="blue" className="text-blue-600" />
                </div>
                <div>
                  <div className="text-sm text-gray-500 uppercase">STUDENT NUMBER</div>
                  <div className="font-medium">2021300766</div>
                </div>
              </div>
            </div>
        </div>

        <div class="p-6">
            <div className="bg-white p-4 rounded-md border shadow-sm">
              <div className="flex items-start gap-3">
                <div className="p-2 bg-gray-100 rounded-md">
                  <GraduationCap :size="20" stroke="blue" className="text-blue-600" />
                </div>
                <div>
                  <div className="text-sm text-gray-500 uppercase">PROGRAM</div>
                  <div className="font-medium">Bachelor of Science in Information Technology</div>
                </div>
              </div>
            </div>
        </div>

        <div className="p-6">
            <div className="bg-white p-4 rounded-md border shadow-sm">
              <div className="flex items-start gap-3">
                <div className="p-2 bg-gray-100 rounded-md">
                  <User :size="20" stroke="blue" className="text-blue-600" />
                </div>
                <div>
                  <div className="text-sm text-gray-500 uppercase">STUDENT NUMBER</div>
                  <div className="font-medium">2021300766</div>
                </div>
              </div>
            </div>
        </div>

        <div class="p-6">
            <div className="bg-white p-4 rounded-md border shadow-sm">
              <div className="flex items-start gap-3">
                <div className="p-2 bg-gray-100 rounded-md">
                  <GraduationCap :size="20" stroke="blue" className="text-blue-600" />
                </div>
                <div>
                  <div className="text-sm text-gray-500 uppercase">PROGRAM</div>
                  <div className="font-medium">Bachelor of Science in Information Technology</div>
                </div>
              </div>
            </div>
        </div>

        <div className="p-6">
            <div className="bg-white p-4 rounded-md border shadow-sm">
              <div className="flex items-start gap-3">
                <div className="p-2 bg-gray-100 rounded-md">
                  <User :size="20" stroke="blue" className="text-blue-600" />
                </div>
                <div>
                  <div className="text-sm text-gray-500 uppercase">STUDENT NUMBER</div>
                  <div className="font-medium">2021300766</div>
                </div>
              </div>
            </div>
        </div>

        <div class="p-6">
            <div className="bg-white p-4 rounded-md border shadow-sm">
              <div className="flex items-start gap-3">
                <div className="p-2 bg-gray-100 rounded-md">
                  <GraduationCap :size="20" stroke="blue" className="text-blue-600" />
                </div>
                <div>
                  <div className="text-sm text-gray-500 uppercase">PROGRAM</div>
                  <div className="font-medium">Bachelor of Science in Information Technology</div>
                </div>
              </div>
            </div>
        </div>




        <!-- Line Chart -->
        <div class="relative min-h-[50vh] w-full overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
          <div class="w-full overflow-x-auto">
            <ChartLine class="w-full max-w-full" />
          </div>
        </div>

        <!-- Bar Chart -->
        <div class="relative min-h-[50vh] w-full overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
          <div class="w-full overflow-x-auto">
            <BarChart class="w-full max-w-full" />
          </div>
        </div>

        <!-- Donut Chart -->
        <div class="relative min-h-[50vh] w-full overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
          <div class="w-full overflow-x-auto">
            <DonutChart class="w-full max-w-full" />
          </div>
        </div>
      </div>

      <!-- Area Chart (Full Width) -->
      <div class="relative min-h-[100vh] flex-1 rounded-xl border border-sidebar-border/70 dark:border-sidebar-border md:min-h-min">
        <div class="w-full overflow-x-auto">
          <AreaChart class="w-full max-w-full" />
        </div>
      </div>
    </div>
  </AppLayout>
</template>
