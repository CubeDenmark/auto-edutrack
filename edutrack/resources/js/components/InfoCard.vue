<script setup lang="ts">
import { defineProps } from 'vue';

defineProps({
  label: String,
  value: String | Number,
});
</script>

<template>
  <div class="bg-white p-4 shadow rounded-lg">
    <h3 class="text-lg font-semibold">{{ label }}</h3>
    <p class="text-gray-600">{{ value }}</p>
  </div>
</template>
