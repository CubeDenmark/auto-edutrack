<script setup lang="ts">
import AppLayout from "@/layouts/AppLayout.vue";
import { type BreadcrumbItem } from "@/types";
import { Head, usePage } from "@inertiajs/vue3";
import { ref, computed, onMounted, inject, watch } from "vue";

const breadcrumbs: BreadcrumbItem[] = [
{
  title: "Behavior Reports",
  href: "/behavior-reports",
}
];

defineProps<{
name?: string;
sectionId?: string;
}>();

// Get the page props to access any data passed from the server
// const page = usePage();

// Inject the current item ID from the parent component (from sidebar)
const currentItemId = inject('currentItemId', ref(null));

// State variables
const currentSection = ref<any>(null);
const showAddReportModal = ref(false);
const filterType = ref('all');

// Form states for new report
const newReport = ref({
student: '',
type: 'positive',
title: '',
description: '',
date: new Date().toISOString().split('T')[0]
});


const page = usePage();
const classSections = ref(page.props.sections); // Changed from classSections to sections
// // Class sections data
// const classSections = ref([
// {
//   id: 'sci101a',
//   name: 'Science 101 - Section A',
//   icon: '🧪',
//   term: 'Fall Semester 2023',
//   studentCount: 28,
//   behaviorSummary: {
//     positive: 12,
//     warnings: 5,
//     incidents: 2
//   },
//   students: [
//     { id: '2023001', name: 'Emma Thompson', email: 'emma.t@school.edu' },
//     { id: '2023002', name: 'Liam Johnson', email: 'liam.j@school.edu' },
//     { id: '2023003', name: 'Olivia Davis', email: 'olivia.d@school.edu' },
//     { id: '2023004', name: 'Noah Wilson', email: 'noah.w@school.edu' },
//     { id: '2023005', name: 'Ava Martinez', email: 'ava.m@school.edu' },
//     { id: '2023006', name: 'Ethan Brown', email: 'ethan.b@school.edu' },
//     { id: '2023007', name: 'Sophia Taylor', email: 'sophia.t@school.edu' },
//     { id: '2023008', name: 'Mason Anderson', email: 'mason.a@school.edu' },
//     { id: '2023009', name: 'Isabella Thomas', email: 'isabella.t@school.edu' },
//     { id: '2023010', name: 'James Jackson', email: 'james.j@school.edu' },
//   ],
//   behaviorReports: [
//     {
//       student: 'Emma Thompson',
//       icon: '🌟',
//       iconBg: 'bg-green-100 dark:bg-green-900',
//       iconColor: 'text-green-600 dark:text-green-400',
//       type: 'positive',
//       typeClass: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
//       description: 'Emma has been consistently participating in class discussions and providing thoughtful insights.',
//       date: 'Oct 10, 2023'
//     },
//     {
//       student: 'Noah Wilson',
//       icon: '⚠️',
//       iconBg: 'bg-yellow-100 dark:bg-yellow-900',
//       iconColor: 'text-yellow-600 dark:text-yellow-400',
//       type: 'warning',
//       typeClass: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
//       description: 'Noah did not complete the assigned homework for today\'s class.',
//       date: 'Oct 5, 2023'
//     },
//     {
//       student: 'Ava Martinez',
//       icon: '🏆',
//       iconBg: 'bg-blue-100 dark:bg-blue-900',
//       iconColor: 'text-blue-600 dark:text-blue-400',
//       type: 'positive',
//       typeClass: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
//       description: 'Ava\'s science project was selected as one of the top three in the class.',
//       date: 'Sep 28, 2023'
//     },
//     {
//       student: 'Liam Johnson',
//       icon: '❌',
//       iconBg: 'bg-red-100 dark:bg-red-900',
//       iconColor: 'text-red-600 dark:text-red-400',
//       type: 'incident',
//       typeClass: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
//       description: 'Liam was disruptive during the lab session and refused to follow safety protocols.',
//       date: 'Sep 22, 2023'
//     },
//   ]
// },
// {
//   id: 'sci101b',
//   name: 'Science 101 - Section B',
//   icon: '🧪',
//   term: 'Fall Semester 2023',
//   studentCount: 25,
//   behaviorSummary: {
//     positive: 8,
//     warnings: 3,
//     incidents: 1
//   },
//   students: [
//     { id: '2023011', name: 'William Garcia', email: 'william.g@school.edu' },
//     { id: '2023012', name: 'Charlotte Rodriguez', email: 'charlotte.r@school.edu' },
//     { id: '2023013', name: 'Benjamin Lee', email: 'benjamin.l@school.edu' },
//     { id: '2023014', name: 'Amelia Hernandez', email: 'amelia.h@school.edu' },
//     { id: '2023015', name: 'Lucas Gonzalez', email: 'lucas.g@school.edu' },
//     { id: '2023016', name: 'Mia Wilson', email: 'mia.w@school.edu' },
//     { id: '2023017', name: 'Henry Lopez', email: 'henry.l@school.edu' },
//     { id: '2023018', name: 'Evelyn Clark', email: 'evelyn.c@school.edu' },
//     { id: '2023019', name: 'Alexander Lewis', email: 'alexander.l@school.edu' },
//     { id: '2023020', name: 'Harper Young', email: 'harper.y@school.edu' },
//   ],
//   behaviorReports: [
//     {
//       student: 'Benjamin Lee',
//       icon: '🌟',
//       iconBg: 'bg-green-100 dark:bg-green-900',
//       iconColor: 'text-green-600 dark:text-green-400',
//       type: 'positive',
//       typeClass: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
//       description: 'Benjamin helped other students understand difficult concepts during the lab session.',
//       date: 'Oct 8, 2023'
//     },
//     {
//       student: 'Charlotte Rodriguez',
//       icon: '⚠️',
//       iconBg: 'bg-yellow-100 dark:bg-yellow-900',
//       iconColor: 'text-yellow-600 dark:text-yellow-400',
//       type: 'warning',
//       typeClass: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
//       description: 'Charlotte was using her phone during class despite multiple reminders.',
//       date: 'Oct 3, 2023'
//     },
//     {
//       student: 'Harper Young',
//       icon: '❌',
//       iconBg: 'bg-red-100 dark:bg-red-900',
//       iconColor: 'text-red-600 dark:text-red-400',
//       type: 'incident',
//       typeClass: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
//       description: 'Harper mishandled lab equipment, causing minor damage.',
//       date: 'Sep 25, 2023'
//     },
//   ]
// },
// {
//   id: 'bio202',
//   name: 'Biology 202',
//   icon: '🧬',
//   term: 'Fall Semester 2023',
//   studentCount: 22,
//   behaviorSummary: {
//     positive: 10,
//     warnings: 2,
//     incidents: 0
//   },
//   students: [
//     { id: '2023021', name: 'Daniel Moore', email: 'daniel.m@school.edu' },
//     { id: '2023022', name: 'Scarlett White', email: 'scarlett.w@school.edu' },
//     { id: '2023023', name: 'Michael Harris', email: 'michael.h@school.edu' },
//     { id: '2023024', name: 'Victoria Martin', email: 'victoria.m@school.edu' },
//     { id: '2023025', name: 'Joseph Thompson', email: 'joseph.t@school.edu' },
//     { id: '2023026', name: 'Penelope Garcia', email: 'penelope.g@school.edu' },
//     { id: '2023027', name: 'David Martinez', email: 'david.m@school.edu' },
//     { id: '2023028', name: 'Elizabeth Robinson', email: 'elizabeth.r@school.edu' },
//   ],
//   behaviorReports: [
//     {
//       student: 'Scarlett White',
//       icon: '🌟',
//       iconBg: 'bg-green-100 dark:bg-green-900',
//       iconColor: 'text-green-600 dark:text-green-400',
//       type: 'positive',
//       typeClass: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
//       description: 'Scarlett has been exceptionally helpful during lab sessions and assists other students.',
//       date: 'Oct 7, 2023'
//     },
//     {
//       student: 'Michael Harris',
//       icon: '⚠️',
//       iconBg: 'bg-yellow-100 dark:bg-yellow-900',
//       iconColor: 'text-yellow-600 dark:text-yellow-400',
//       type: 'warning',
//       typeClass: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
//       description: 'Michael has been consistently late in submitting assignments.',
//       date: 'Oct 2, 2023'
//     },
//   ]
// },
// {
//   id: 'chem101',
//   name: 'Chemistry 101',
//   icon: '⚗️',
//   term: 'Fall Semester 2023',
//   studentCount: 24,
//   behaviorSummary: {
//     positive: 7,
//     warnings: 4,
//     incidents: 1
//   },
//   students: [
//     { id: '2023031', name: 'Samuel Walker', email: 'samuel.w@school.edu' },
//     { id: '2023032', name: 'Grace Allen', email: 'grace.a@school.edu' },
//     { id: '2023033', name: 'Andrew Scott', email: 'andrew.s@school.edu' },
//     { id: '2023034', name: 'Zoe Green', email: 'zoe.g@school.edu' },
//     { id: '2023035', name: 'Christopher Baker', email: 'christopher.b@school.edu' },
//     { id: '2023036', name: 'Lily Nelson', email: 'lily.n@school.edu' },
//     { id: '2023037', name: 'Matthew Carter', email: 'matthew.c@school.edu' },
//     { id: '2023038', name: 'Chloe Mitchell', email: 'chloe.m@school.edu' },
//   ],
//   behaviorReports: [
//     {
//       student: 'Andrew Scott',
//       icon: '🌟',
//       iconBg: 'bg-green-100 dark:bg-green-900',
//       iconColor: 'text-green-600 dark:text-green-400',
//       type: 'positive',
//       typeClass: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
//       description: 'Andrew demonstrated excellent lab technique and helped maintain safety standards.',
//       date: 'Oct 5, 2023'
//     },
//     {
//       student: 'Grace Allen',
//       icon: '⚠️',
//       iconBg: 'bg-yellow-100 dark:bg-yellow-900',
//       iconColor: 'text-yellow-600 dark:text-yellow-400',
//       type: 'warning',
//       typeClass: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
//       description: 'Grace needs to be more careful with lab equipment and follow procedures more closely.',
//       date: 'Oct 1, 2023'
//     },
//     {
//       student: 'Zoe Green',
//       icon: '❌',
//       iconBg: 'bg-red-100 dark:bg-red-900',
//       iconColor: 'text-red-600 dark:text-red-400',
//       type: 'incident',
//       typeClass: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
//       description: 'Zoe caused a minor chemical spill by not following proper procedures.',
//       date: 'Sep 24, 2023'
//     },
//   ]
// }
// ]);

// Find section by ID
function findSectionById(id) {
  if (!classSections.value) return null;
  return classSections.value.find(section => section.id === id) || null;
}

// Update the current section
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

// Open add report modal
function openAddReportModal() {
if (!currentSection.value) return;

// Reset form
newReport.value = {
  student: currentSection.value.students[0]?.id || '',
  type: 'positive',
  title: '',
  description: '',
  date: new Date().toISOString().split('T')[0]
};

showAddReportModal.value = true;
}
// Add a new behavior report
// Function to add a behavior report
// Add a new behavior report
async function addReport() {
  if (!currentSection.value || !newReport.value.student) return;

  // Find the selected student
  const student = currentSection.value.students.find(s => s.id === newReport.value.student);
  if (!student) return;

  // Create the report object
  const report = {
    student: student.name,
    student_id: student.id,
    class_id: currentSection.value.id,
    description: newReport.value.description,
    date: new Date(newReport.value.date).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' }),
    type: newReport.value.type,
    typeClass: '',
    icon: '',
    iconBg: '',
    iconColor: ''
  };

  // Set the appropriate icon and classes based on the report type
  if (newReport.value.type === 'positive') {
    report.icon = '🌟';
    report.iconBg = 'bg-green-100 dark:bg-green-900';
    report.iconColor = 'text-green-600 dark:text-green-400';
    report.typeClass = 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300';
    currentSection.value.behaviorSummary.positive += 1;
  } else if (newReport.value.type === 'warning') {
    report.icon = '⚠️';
    report.iconBg = 'bg-yellow-100 dark:bg-yellow-900';
    report.iconColor = 'text-yellow-600 dark:text-yellow-400';
    report.typeClass = 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300';
    currentSection.value.behaviorSummary.warnings += 1;
  } else if (newReport.value.type === 'incident') {
    report.icon = '❌';
    report.iconBg = 'bg-red-100 dark:bg-red-900';
    report.iconColor = 'text-red-600 dark:text-red-400';
    report.typeClass = 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300';
    currentSection.value.behaviorSummary.incidents += 1;
  }

  // Add the report to the current section
  currentSection.value.behaviorReports.unshift(report);

  showAddReportModal.value = false;

  // Log the report data
  console.log('Report:', report);

  // Get CSRF token
  const csrfTokenMetaTag = document.querySelector('meta[name="csrf-token"]');
  const csrfToken = csrfTokenMetaTag ? csrfTokenMetaTag.getAttribute('content') : null;

  if (!csrfToken) {
    console.error('CSRF token not found!');
    // Optionally display an error message to the user
    return;
  }

  // Send the report data to the server
  try {
    const formattedDate = new Date(newReport.value.date).toISOString().split('T')[0];
    const response = await fetch('/prof/behavior_reports', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken, // Use the CSRF token here
      },
      body: JSON.stringify({
        student_id: student.id,
        class_id: currentSection.value.id,
        description: newReport.value.description, // This should match the controller's validation key
        date: formattedDate,
        type: newReport.value.type,
        title:newReport.value.title,
        // `${newReport.value.type.charAt(0).toUpperCase() + newReport.value.type.slice(1)} report for ${student.name}` // Generate a title based on the report type and student name
      })
    });

    if (!response.ok) {
      const errorData = await response.json();
      console.error('Server error:', errorData);
      throw new Error(errorData.error || 'Failed to save behavior report');
    }

    const result = await response.json();
    console.log('Report saved successfully:', result);

    // Show success notification
    const notification = document.createElement('div');
    notification.className = 'fixed bottom-4 right-4 bg-emerald-500 text-white px-6 py-3 rounded-lg shadow-lg transform transition-all duration-500 flex items-center';
    notification.innerHTML = `
      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
      </svg>
      Behavior report saved successfully!
    `;
    document.body.appendChild(notification);

    setTimeout(() => {
      notification.style.opacity = '0';
      setTimeout(() => {
        document.body.removeChild(notification);
      }, 500);
    }, 3000);

  } catch (error) {
    console.error('Error saving behavior report:', error);

    // Show error notification
    const notification = document.createElement('div');
    notification.className = 'fixed bottom-4 right-4 bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg transform transition-all duration-500 flex items-center';
    notification.innerHTML = `
      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
      </svg>
      Error saving behavior report. Please try again.
    `;
    document.body.appendChild(notification);

    setTimeout(() => {
      notification.style.opacity = '0';
      setTimeout(() => {
        document.body.removeChild(notification);
      }, 500);
    }, 3000);
  }
}

// Delete a behavior report
function deleteReport(index) {
if (!currentSection.value) return;

const report = currentSection.value.behaviorReports[index];

// Update the behavior summary counts
if (report.type === 'positive') {
  currentSection.value.behaviorSummary.positive = Math.max(0, currentSection.value.behaviorSummary.positive - 1);
} else if (report.type === 'warning') {
  currentSection.value.behaviorSummary.warnings = Math.max(0, currentSection.value.behaviorSummary.warnings - 1);
} else if (report.type === 'incident') {
  currentSection.value.behaviorSummary.incidents = Math.max(0, currentSection.value.behaviorSummary.incidents - 1);
}

// Remove the report
currentSection.value.behaviorReports.splice(index, 1);
}

// Computed property for filtered reports
const filteredReports = computed(() => {
if (!currentSection.value) return [];

if (filterType.value === 'all') {
  return currentSection.value.behaviorReports;
}

return currentSection.value.behaviorReports.filter(report => report.type === filterType.value);
});

// Listen for item changes from the sidebar
const handleItemChange = (event) => {
if (event.detail && event.detail.source === 'sidebar') {
  const itemId = event.detail.itemId;
  const itemType = event.detail.itemType;

  // For professors, the itemType will be 'section' or undefined
  if (itemType === 'section' || !itemType) {
    const section = findSectionById(itemId);
    if (section && (!currentSection.value || currentSection.value.id !== section.id)) {
      // Only update if it's a different section to avoid infinite loops
      currentSection.value = section;
    }
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
const handleSectionChange = (event) => {
  if (event.detail && event.detail.sectionId) {
    const section = findSectionById(event.detail.sectionId);
    if (section && (!currentSection.value || currentSection.value.id !== section.id)) {
      // Only update if it's a different section
      currentSection.value = section;
    }
  }
};

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
<Head title="Behavior Reports" />

<AppLayout :breadcrumbs="breadcrumbs">
  <div class="p-6 pt-20">
    <!-- No Section Selected Message -->
    <div v-if="!currentSection" class="flex h-[calc(100vh-10rem)] flex-col items-center justify-center text-center">
        <div class="mb-4 rounded-full bg-gray-100 p-6 dark:bg-gray-800">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"
                />
            </svg>
        </div>
        <h2 class="mb-2 text-2xl font-bold">No Selected Class</h2>
        <p class="mb-6 max-w-md text-gray-500 dark:text-gray-400">
            Please select a class from the sidebar to view class-specific information. You can also add class from the sidebar, just click the '+' button.
        </p>
        <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
            <button
                v-for="section in classSections.slice(0, 4)"
                :key="section.id"
                @click="selectSection(section)"
                class="flex flex-col items-center rounded-lg border border-gray-200 bg-white p-4 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:hover:bg-gray-700"
            >
                <span class="mb-2 text-3xl">{{ section.icon }}</span>
                <span class="text-sm font-medium">{{ section.name }}</span>
            </button>
        </div>
    </div>

   <!-- Behavior Reports Page -->
    <div v-if="currentSection">
     <!-- Header with stats -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
          <div class="flex items-center justify-between mb-4">
            <h3 class="font-medium">Positive Reports</h3>
          </div>
          <div class="flex items-center space-x-4">
            <div class="w-16 h-16 rounded-full flex items-center justify-center bg-green-100 dark:bg-green-900 text-green-500">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
              </svg>
            </div>
            <div>
              <p class="text-sm text-gray-500 dark:text-gray-400">Positive Behaviors</p>
              <p class="text-2xl font-bold">{{ currentSection.behaviorSummary.positive }}</p>
            </div>
          </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
          <div class="flex items-center justify-between mb-4">
            <h3 class="font-medium">Warnings</h3>
          </div>
          <div class="flex items-center space-x-4">
            <div class="w-16 h-16 rounded-full flex items-center justify-center bg-yellow-100 dark:bg-yellow-900 text-yellow-500">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
              </svg>
            </div>
            <div>
              <p class="text-sm text-gray-500 dark:text-gray-400">Behavior Warnings</p>
              <p class="text-2xl font-bold">{{ currentSection.behaviorSummary.warnings }}</p>
            </div>
          </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
          <div class="flex items-center justify-between mb-4">
            <h3 class="font-medium">Incidents</h3>
          </div>
          <div class="flex items-center space-x-4">
            <div class="w-16 h-16 rounded-full flex items-center justify-center bg-red-100 dark:bg-red-900 text-red-500">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <div>
              <p class="text-sm text-gray-500 dark:text-gray-400">Serious Incidents</p>
              <p class="text-2xl font-bold">{{ currentSection.behaviorSummary.incidents }}</p>
            </div>
          </div>
        </div>
      </div>

     <!-- Behavior Reports List -->
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-6">
        <div class="flex justify-between items-center mb-6">
          <h3 class="font-medium">Behavior Reports</h3>
          <button @click="openAddReportModal"   class="hover:bg-primary-700 flex items-center gap-2 rounded-lg bg-primary-600 px-4 py-2.5 text-sm font-medium text-white transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            New Report
          </button>
        </div>

       <!-- Filter Controls -->
        <div class="flex flex-wrap gap-2 mb-4">
          <button
            @click="filterType = 'all'"
            :class="[
              'px-3 py-1 rounded-md text-sm',
              filterType === 'all'
                ? 'bg-primary-600 text-white'
                : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300'
            ]"
          >
            All Reports
          </button>
          <button
            @click="filterType = 'positive'"
            :class="[
              'px-3 py-1 rounded-md text-sm',
              filterType === 'positive'
                ? 'bg-green-600 text-white'
                : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300'
            ]"
          >
            Positive
          </button>
          <button
            @click="filterType = 'warning'"
            :class="[
              'px-3 py-1 rounded-md text-sm',
              filterType === 'warning'
                ? 'bg-yellow-600 text-white'
                : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300'
            ]"
          >
            Warnings
          </button>
          <button
            @click="filterType = 'incident'"
            :class="[
              'px-3 py-1 rounded-md text-sm',
              filterType === 'incident'
                ? 'bg-red-600 text-white'
                : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300'
            ]"
          >
            Incidents
          </button>
        </div>

        <div v-if="filteredReports.length === 0" class="text-center py-8">
          <p class="text-gray-500 dark:text-gray-400">No behavior reports found for the selected filter.</p>
        </div>

        <div v-else class="divide-y divide-gray-200 dark:divide-gray-700">
          <div v-for="(report, index) in filteredReports" :key="index" class="p-6">
            <div class="flex items-start space-x-4">
              <div :class="`w-10 h-10 rounded-full flex items-center justify-center ${report.iconBg} ${report.iconColor}`">
                <span class="text-lg">{{ report.icon }}</span>
              </div>
              <div class="flex-1">
                <div class="flex items-center justify-between mb-2">
                  <h4 class="font-medium">{{ report.student }}</h4>
                  <span :class="`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${report.typeClass}`">
                    {{ report.type.charAt(0).toUpperCase() + report.type.slice(1) }}
                  </span>
                </div>
                <p class="text-sm text-gray-600 dark:text-gray-300 mb-2">{{ report.description }}</p>
                <div class="flex items-center text-sm text-gray-500 dark:text-gray-400">
                  <p>{{ report.date }}</p>
                </div>
              </div>
              <div class="flex space-x-2">
                <!-- <button class="p-1 text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                  </svg>
                </button> -->
                <button @click="deleteReport(index)" class="p-1 text-gray-500 hover:text-red-600 dark:hover:text-red-400">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                  </svg>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

     <!-- Behavior Trends -->
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <h3 class="font-medium mb-4">Behavior Trends</h3>
        <div class="h-64 w-full">
          <div class="h-full w-full flex items-center justify-center bg-gray-100 dark:bg-gray-700 rounded-lg">
            <div class="grid grid-cols-3 gap-4 w-full px-8">
              <div class="flex flex-col items-center">
                <div class="h-32 w-12 bg-green-500 rounded-t-lg relative overflow-hidden">
                  <div class="absolute bottom-0 w-full bg-green-600" :style="`height: ${(currentSection.behaviorSummary.positive / (currentSection.behaviorSummary.positive + currentSection.behaviorSummary.warnings + currentSection.behaviorSummary.incidents) * 100) || 0}%`"></div>
                </div>
                <p class="mt-2 font-medium">Positive</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ currentSection.behaviorSummary.positive }} reports</p>
              </div>
              <div class="flex flex-col items-center">
                <div class="h-32 w-12 bg-yellow-500 rounded-t-lg relative overflow-hidden">
                  <div class="absolute bottom-0 w-full bg-yellow-600" :style="`height: ${(currentSection.behaviorSummary.warnings / (currentSection.behaviorSummary.positive + currentSection.behaviorSummary.warnings + currentSection.behaviorSummary.incidents) * 100) || 0}%`"></div>
                </div>
                <p class="mt-2 font-medium">Warnings</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ currentSection.behaviorSummary.warnings }} reports</p>
              </div>
              <div class="flex flex-col items-center">
                <div class="h-32 w-12 bg-red-500 rounded-t-lg relative overflow-hidden">
                  <div class="absolute bottom-0 w-full bg-red-600" :style="`height: ${(currentSection.behaviorSummary.incidents / (currentSection.behaviorSummary.positive + currentSection.behaviorSummary.warnings + currentSection.behaviorSummary.incidents) * 100) || 0}%`"></div>
                </div>
                <p class="mt-2 font-medium">Incidents</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ currentSection.behaviorSummary.incidents }} reports</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

<!-- Improved Add Report Modal -->
<div v-if="showAddReportModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 backdrop-blur-sm">
  <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-2xl w-full max-w-lg p-8 relative">
    <!-- Modal Header -->
    <div class="flex justify-between items-center mb-6">
      <h3 class="text-2xl font-semibold text-gray-800 dark:text-white">Create New Behavior Report</h3>
      <button @click="showAddReportModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
      </button>
    </div>

    <!-- Modal Form -->
    <form @submit.prevent="addReport" class="space-y-5">

      <!-- Student Dropdown -->
      <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Student</label>
        <select
          v-model="newReport.student"
          required
          class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-primary-500 focus:outline-none"
        >
          <option value="">Select a student</option>
          <option v-for="student in currentSection.students" :key="student.id" :value="student.id">
            {{ student.name }}
          </option>
        </select>
      </div>

      <!-- Report Type Dropdown -->
      <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Report Type</label>
        <select
          v-model="newReport.type"
          class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-primary-500 focus:outline-none"
        >
          <option value="positive">Positive</option>
          <option value="warning">Warning</option>
          <option value="incident">Incident</option>
        </select>
      </div>

      <!-- Title Input -->
      <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Title</label>
        <input
          type="text"
          v-model="newReport.title"
          required
          placeholder="Report title"
          class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-primary-500 focus:outline-none"
        />
      </div>

      <!-- Description Textarea -->
      <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Description</label>
        <textarea
          v-model="newReport.description"
          rows="4"
          required
          placeholder="Describe the behavior..."
          class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-primary-500 focus:outline-none resize-none"
        ></textarea>
      </div>

      <!-- Date Input -->
      <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Date</label>
        <input
          type="date"
          v-model="newReport.date"
          required
          class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-primary-500 focus:outline-none"
        />
      </div>

      <!-- Buttons -->
      <div class="flex justify-end gap-3 pt-4">
        <button
          type="button"
          @click="showAddReportModal = false"
          class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition"
        >
          Cancel
        </button>
        <button
          type="submit"
          class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium shadow transition"
        >
          Submit Report
        </button>
      </div>
    </form>
  </div>
</div>

</AppLayout>
</template>

<!-- <style>
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
</style> -->
