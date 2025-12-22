
<!-- Working redesign 1.1 -->
<script setup lang="ts">
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import { Mail, User, Send, Loader } from 'lucide-vue-next'

const form = ref({
  name: '',
  email: '',
  message: ''
})

const loading = ref(false)
const showSuccess = ref(false)

const submitForm = () => {
  loading.value = true

  router.post('/send-email', form.value, {
    onSuccess: () => {
      loading.value = false
      showSuccess.value = true
      form.value = { name: '', email: '', message: '' }

      // Hide success message after 5 seconds
      setTimeout(() => {
        showSuccess.value = false
      }, 5000)
    },
    onError: (errors) => {
      loading.value = false
      console.error(errors)
    }
  })
}
</script>

<template>
  <div class="max-w-md mx-auto p-6 bg-white rounded-lg shadow-lg">
    <h2 class="text-2xl font-bold text-gray-800 mb-6 flex items-center justify-center">
      <Mail class="mr-2 h-6 w-6 text-emerald-600" />
      Contact Us
    </h2>

    <!-- Success notification -->
    <div v-if="showSuccess" class="mb-6 p-4 bg-emerald-50 border border-emerald-200 rounded-md">
      <p class="text-emerald-700 font-medium flex items-center">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
        </svg>
        Email sent successfully!
      </p>
    </div>

    <form @submit.prevent="submitForm" class="space-y-4">
      <div class="space-y-2">
        <label for="name" class="block text-sm font-medium text-gray-700">Your Name</label>
        <div class="relative">
          <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <User class="h-5 w-5 text-gray-400" />
          </div>
          <input
            id="name"
            v-model="form.name"
            type="text"
            required
            class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
            placeholder="John Doe"
          />
        </div>
      </div>

      <div class="space-y-2">
        <label for="email" class="block text-sm font-medium text-gray-700">Your Email</label>
        <div class="relative">
          <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <Mail class="h-5 w-5 text-gray-400" />
          </div>
          <input
            id="email"
            v-model="form.email"
            type="email"
            required
            class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
            placeholder="john@example.com"
          />
        </div>
      </div>

      <div class="space-y-2">
        <label for="message" class="block text-sm font-medium text-gray-700">Your Message</label>
        <textarea
          id="message"
          v-model="form.message"
          rows="4"
          required
          class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
          placeholder="How can we help you?"
        ></textarea>
      </div>

      <button
        type="submit"
        :disabled="loading"
        class="w-full flex justify-center items-center py-2 px-4 border border-transparent rounded-md shadow-sm text-white bg-emerald-600 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 disabled:opacity-75 transition-colors duration-200"
      >
        <Loader v-if="loading" class="animate-spin mr-2 h-5 w-5" />
        <Send v-else class="mr-2 h-5 w-5" />
        {{ loading ? 'Sending...' : 'Send Message' }}
      </button>
    </form>
  </div>
</template>

<!-- Working code 1.0 -->
<!-- <script setup lang="ts">
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'

const form = ref({
  name: '',
  email: '',
  message: ''
})

const submitForm = () => {
  router.post('/send-email', form.value, {
    onSuccess: () => {
      alert('Email sent successfully!')
      form.value = { name: '', email: '', message: '' }
    },
    onError: (errors) => {
      console.error(errors)
    }
  })
}
</script>

<template>
  <div>
    <h2>Contact Us</h2>
    <form @submit.prevent="submitForm">
      <input v-model="form.name" placeholder="Your Name" required />
      <input v-model="form.email" type="email" placeholder="Your Email" required />
      <textarea v-model="form.message" placeholder="Your Message" required></textarea>
      <button type="submit">Send Email</button>
    </form>
  </div>
</template> -->
