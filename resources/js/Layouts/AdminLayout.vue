<template>
    <div class="min-h-screen bg-gray-100">
        <!-- Navigation -->
        <nav class="bg-white shadow-sm border-b border-gray-200">
            <div class="mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex">
                        <!-- Logo -->
                        <div class="flex-shrink-0 flex items-center">
                            <Link :href="route('admin.dashboard')" class="flex items-center gap-2">
                                <img src="/images/logo.png" alt="Evaly" class="h-10 w-auto" />
                                <span class="text-xl font-bold text-primary">Evaly</span>
                            </Link>
                        </div>

                        <!-- Navigation Links -->
                        <div class="hidden sm:ml-8 sm:flex sm:space-x-4">
                            <Link
                                :href="route('admin.dashboard')"
                                :class="route().current('admin.dashboard') ? 'border-primary text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'"
                                class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium transition-colors"
                            >
                                Dashboard
                            </Link>
                            
                            <Link
                                v-if="can('view users')"
                                :href="route('admin.users.index')"
                                :class="route().current('admin.users.*') ? 'border-primary text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'"
                                class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium transition-colors"
                            >
                                Utilisateurs
                            </Link>
                            
                            <Link
                                v-if="can('view roles')"
                                :href="route('admin.roles.index')"
                                :class="route().current('admin.roles.*') ? 'border-primary text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'"
                                class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium transition-colors"
                            >
                                Rôles
                            </Link>
                            
                            <Link
                                v-if="can('view classes')"
                                :href="route('admin.classes.index')"
                                :class="route().current('admin.classes.*') ? 'border-primary text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'"
                                class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium transition-colors"
                            >
                                Classes
                            </Link>
                            
                            <Link
                                v-if="can('view subjects')"
                                :href="route('admin.subjects.index')"
                                :class="route().current('admin.subjects.*') ? 'border-primary text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'"
                                class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium transition-colors"
                            >
                                Matières
                            </Link>
                            
                            <Link
                                :href="route('admin.exams.index')"
                                :class="route().current('admin.exams.*') ? 'border-primary text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'"
                                class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium transition-colors"
                            >
                                Examens
                            </Link>
                        </div>
                    </div>

                    <!-- Right Side -->
                    <div class="hidden sm:ml-6 sm:flex sm:items-center space-x-4">
                        <!-- User Dropdown -->
                        <div class="relative">
                            <button
                                @click="showUserMenu = !showUserMenu"
                                class="flex items-center text-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary rounded-lg px-3 py-2 hover:bg-gray-50 transition-colors"
                            >
                                <span class="mr-2 text-gray-700">{{ $page.props.auth.user.name }}</span>
                                <svg class="h-5 w-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>

                            <div
                                v-show="showUserMenu"
                                @click="showUserMenu = false"
                                class="origin-top-right absolute right-0 mt-2 w-48 rounded-lg shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-10"
                            >
                            <div class="py-1">
                                <div class="px-4 py-2 text-xs text-gray-500 border-b border-gray-100">
                                    <p class="font-semibold text-gray-700">{{ $page.props.auth.user.email }}</p>
                                    <p class="mt-1">Rôles: {{ $page.props.auth.user.roles.join(', ') }}</p>
                                </div>
                                <button
                                    @click="toggleMenuLayout"
                                    class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors"
                                >
                                    {{ $page.props.auth.user.menu_layout === 'horizontal' ? 'Menu vertical' : 'Menu horizontal' }}
                                </button>
                                <Link
                                    :href="route('logout')"
                                    method="post"
                                    as="button"
                                    class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors"
                                >
                                    Déconnexion
                                </Link>
                            </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Page Content -->
        <main class="py-6">
            <!-- Flash Messages -->
            <div v-if="$page.props.flash.success" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-4">
                <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg">
                    {{ $page.props.flash.success }}
                </div>
            </div>
            
            <div v-if="$page.props.flash.error" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-4">
                <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg">
                    {{ $page.props.flash.error }}
                </div>
            </div>

            <slot />
        </main>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';

const showUserMenu = ref(false);
const page = usePage();

const can = (permission) => {
    return page.props.auth.user.permissions.includes(permission);
};

const toggleMenuLayout = () => {
    const newLayout = page.props.auth.user.menu_layout === 'horizontal' ? 'vertical' : 'horizontal';
    
    router.patch(route('profile.update-preferences'), {
        menu_layout: newLayout
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};
</script>

