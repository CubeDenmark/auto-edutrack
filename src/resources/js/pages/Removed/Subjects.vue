<script setup lang="ts">
import AppLayout from "@/layouts/AppLayout.vue";
import { type BreadcrumbItem } from "@/types";
import { Head } from "@inertiajs/vue3";
import  SubjectComponents from '@/components/SubjectComponents.vue';

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: "Subjects",
    href: "/subjects",
  },
];

defineProps<{
  name?: string;
}>();
</script>

<template>
  <Head title="Grades" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">


        <SubjectComponents />

      <!-- Chart Grid -->
      <div class="grid auto-rows-min gap-4 md:grid-cols-3">



      </div>
    </div>
  </AppLayout>
</template>
