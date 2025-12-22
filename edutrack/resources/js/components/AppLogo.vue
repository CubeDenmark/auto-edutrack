<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';

interface Props {
    class?: string;
}

defineProps<Props>();
</script>

<template>
    <div class=" bg-white flex aspect-square size-10 items-center justify-center rounded-md bg-sidebar-primary text-sidebar-primary-foreground">
        <AppLogoIcon class="size-10 fill-current text-white dark:text-black" />
    </div>
    <div class="ml-1 grid flex-1 text-left text-xl">
        <span class="mb-0.5 truncate font-semibold leading-none">EduTrack</span>
    </div>
</template>
