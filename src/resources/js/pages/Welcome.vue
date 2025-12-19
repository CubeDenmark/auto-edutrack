<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { Link, usePage } from '@inertiajs/vue3';

const login = () => {
    // In a real app, this would navigate to your login page or dashboard
    // For this example, we'll just log a message
    console.log('Navigating to dashboard');
    // Uncomment this when you have router set up
    // router.push('/dashboard');
};

const scrollToSection = (section) => {
    const element = document.getElementById(section);
    if (element) {
        element.scrollIntoView({ behavior: 'smooth' });
    }
};

const page = usePage();
const name = page.props.name;
const quote = page.props.quote;

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div class="flex min-h-screen flex-col">
        <!-- Navigation -->
        <nav class="sticky top-0 z-50 bg-white shadow-sm">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 items-center justify-between">
                    <div class="flex items-center space-x-8">
                        <!-- Logo with White Background -->
                        <Link :href="route('home')" class="relative z-20 flex items-center text-lg font-medium">
                            <div class="rounded-lg bg-white p-2">
                                <AppLogoIcon class="size-8 fill-current text-black" />
                            </div>
                            <span class="ml-2 text-black">{{ name }}</span>
                        </Link>
                        <div class="hidden md:flex md:space-x-8">
                            <a
                                @click="scrollToSection('home')"
                                class="inline-flex cursor-pointer items-center px-1 pt-1 text-sm font-medium text-black hover:text-gray-700"
                            >
                                Home
                            </a>
                            <a
                                @click="scrollToSection('features')"
                                class="inline-flex cursor-pointer items-center px-1 pt-1 text-sm font-medium text-black hover:text-gray-700"
                            >
                                Features
                            </a>
                            <a
                                @click="scrollToSection('about')"
                                class="inline-flex cursor-pointer items-center px-1 pt-1 text-sm font-medium text-black hover:text-gray-700"
                            >
                                About
                            </a>
                        </div>
                    </div>
                    <div class="flex items-center">
                        <Link
                            :href="route('login')"
                            class="ml-4 rounded-md border border-transparent bg-blue-900 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-900 focus:ring-offset-2"
                        >
                            Login
                        </Link>
                        <Link
                            :href="route('register')"
                            class="ml-4 rounded-md border border-blue-900 px-4 py-2 text-sm font-medium text-blue-900 shadow-sm hover:bg-blue-100 hover:text-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-900 focus:ring-offset-2"
                        >
                            Register
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Mobile menu, show/hide based on menu state -->
            <div class="md:hidden">
                <div class="space-y-1 pb-3 pt-2">
                    <a
                        @click="scrollToSection('home')"
                        class="block cursor-pointer border-l-4 border-transparent py-2 pl-3 pr-4 text-base font-medium text-gray-600 hover:border-indigo-300 hover:bg-gray-50 hover:text-indigo-700"
                        >Home</a
                    >
                    <a
                        @click="scrollToSection('features')"
                        class="block cursor-pointer border-l-4 border-transparent py-2 pl-3 pr-4 text-base font-medium text-gray-600 hover:border-indigo-300 hover:bg-gray-50 hover:text-indigo-700"
                        >Features</a
                    >
                    <a
                        @click="scrollToSection('about')"
                        class="block cursor-pointer border-l-4 border-transparent py-2 pl-3 pr-4 text-base font-medium text-gray-600 hover:border-indigo-300 hover:bg-gray-50 hover:text-indigo-700"
                        >About</a
                    >
                </div>
            </div>
        </nav>

        <!-- Hero Section -->
        <div id="home" class="bg-gradient-to-r from-indigo-500 to-purple-600 py-16">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="lg:flex lg:items-center lg:justify-between">
                    <div class="lg:w-1/2">
                        <h1 class="text-4xl font-extrabold tracking-tight text-white sm:text-5xl md:text-6xl">
                            <span class="block">Student Monitoring</span>
                            <span class="block text-indigo-200">Made Simple</span>
                        </h1>
                        <p class="mt-3 text-base text-indigo-100 sm:mt-5 sm:text-xl lg:text-lg xl:text-xl">
                            Track student performance, monitor grades, and analyze academic progress with our comprehensive monitoring system.
                        </p>
                        <div class="mt-8 flex gap-4">
                            <!-- <button @click="login" class="px-8 py-3 border border-transparent text-base font-medium rounded-md text-indigo-700 bg-white hover:bg-indigo-50 md:py-4 md:text-lg md:px-10">
                  Get Started
                </button> -->
                            <Link
                                :href="route('login')"
                                class="rounded-md border border-transparent bg-white px-8 py-3 text-base font-medium text-indigo-700 hover:bg-indigo-50 md:px-10 md:py-4 md:text-lg"
                            >
                                Get Started
                            </Link>
                            <button
                                @click="scrollToSection('features')"
                                class="rounded-md border border-transparent bg-indigo-800 bg-opacity-60 px-8 py-3 text-base font-medium text-white hover:bg-opacity-70 md:px-10 md:py-4 md:text-lg"
                            >
                                Learn More
                            </button>
                        </div>
                    </div>
                    <div class="mt-12 lg:mt-0 lg:w-1/2">
                        <!-- <img class="w-full rounded-lg shadow-xl" src="https://picsum.photos/600/400" alt="Dashboard preview" /> -->
                        <img src="/systemimages/wellcome_page.jpeg" alt="Students Studying" class="w-full rounded-lg shadow-xl" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Features -->
        <div id="features" class="bg-white py-12">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="text-center">
                    <h2 class="text-base font-semibold uppercase tracking-wide text-indigo-600">Features</h2>
                    <p class="mt-2 text-3xl font-extrabold text-gray-900 sm:text-4xl">Everything you need to monitor student progress</p>
                </div>

                <div class="mt-10">
                    <div class="grid grid-cols-1 gap-10 sm:grid-cols-2 lg:grid-cols-3">
                        <div class="flex flex-col items-center">
                            <div class="flex h-12 w-12 items-center justify-center rounded-md bg-indigo-500 text-white">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"
                                    />
                                </svg>
                            </div>
                            <h3 class="mt-4 text-xl font-medium text-gray-900">Grade Tracking</h3>
                            <p class="mt-2 text-center text-base text-gray-500">
                                Monitor student grades across all Classes with detailed analytics and progress reports.
                            </p>
                        </div>

                        <div class="flex flex-col items-center">
                            <div class="flex h-12 w-12 items-center justify-center rounded-md bg-indigo-500 text-white">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"
                                    />
                                </svg>
                            </div>
                            <h3 class="mt-4 text-xl font-medium text-gray-900">Class Management</h3>
                            <p class="mt-2 text-center text-base text-gray-500">
                                Organize and manage classes, and academic requirements in one place.
                            </p>
                        </div>

                        <div class="flex flex-col items-center">
                            <div class="flex h-12 w-12 items-center justify-center rounded-md bg-indigo-500 text-white">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                                    />
                                </svg>
                            </div>
                            <h3 class="mt-4 text-xl font-medium text-gray-900">Performance Analytics</h3>
                            <p class="mt-2 text-center text-base text-gray-500">
                                Visualize student performance with interactive charts and comprehensive reports.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- About Section -->
        <div id="about" class="bg-gray-50 py-12">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="px-4 py-12 text-center">
                    <h2 class="text-base font-semibold uppercase tracking-wide text-indigo-600">About</h2>

                    <p class="mt-10 text-3xl font-extrabold text-gray-900 sm:text-4xl">Our Vision</p>
                    <p class="mx-auto mt-4 max-w-2xl text-xl text-gray-500">
                        To become the leading educational platform that transforms traditional grading and monitoring into a smart, analytics-powered
                        system that promotes academic excellence and equitable access to education.
                    </p>

                    <p class="mt-6 text-3xl font-extrabold text-gray-900 sm:text-4xl">Our Mission</p>
                    <p class="mx-auto mt-4 max-w-2xl text-xl text-gray-500">
                        To empower educators and students by providing an intuitive and data-driven platform that enhances academic performance,
                        encourages informed decision-making, and supports lifelong learning.
                    </p>
                </div>

                <div class="mt-10">
                    <div class="prose prose-indigo prose-lg mx-auto text-gray-500">
                        <p>
                            Edutrack Monitoring System was founded with a simple mission: to help Professors, Parents, and Students analyze,
                            and improve student performance through data-driven insights. Our platform provides educators with the tools they need to
                            identify trends, spot struggling students early, and implement targeted interventions.
                        </p>
                        <p>
                            Built on Laravel 12 and Vue.js, our system combines powerful backend processing with an intuitive, responsive frontend
                            interface. We believe that technology should enhance the educational experience, not complicate it.
                        </p>
                        <!-- <p>
                            Our team consists of educators, developers, and data scientists who are passionate about improving educational outcomes.
                            We work closely with schools and universities to ensure our platform meets the real-world needs of students and teachers.
                        </p> -->
                    </div>
                </div>

                <!-- <div class="mt-10">
                    <div class="prose prose-indigo prose-lg mx-auto text-gray-500">
                        <p>
                            CHCCI was founded with a simple mission: to help educational institutions track, analyze, and improve student
                            performance through data-driven insights. Our platform provides educators with the tools they need to identify trends,
                            spot struggling students early, and implement targeted interventions.
                        </p>
                        <p>
                            Built on Laravel 12 and Vue.js, our system combines powerful backend processing with an intuitive, responsive frontend
                            interface. We believe that technology should enhance the educational experience, not complicate it.
                        </p>
                        <p>
                            Our team consists of educators, developers, and data scientists who are passionate about improving educational outcomes.
                            We work closely with schools and universities to ensure our platform meets the real-world needs of students and teachers.
                        </p>
                    </div>
                </div> -->
            </div>
        </div>

        <!-- Footer -->
        <footer class="mt-auto bg-gray-800">
            <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
                <div class="text-center">
                    <p class="text-base text-gray-400">&copy; 2025 Edutrack. All rights reserved.</p>
                </div>
            </div>
        </footer>
    </div>
</template>
