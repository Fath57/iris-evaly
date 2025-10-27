<template>
    <div class="min-h-screen bg-gray-100 flex">
        <!-- Sidebar vertical -->
        <div class="w-64 bg-white shadow-sm border-r border-gray-200 flex flex-col">
            <!-- Logo -->
            <div class="flex items-center justify-center h-16 border-b border-gray-200">
                <Link :href="route('admin.dashboard')" class="flex items-center gap-2">
                    <img src="/images/logo.png" alt="Evaly" class="h-10 w-auto" />
                    <span class="text-xl font-bold text-primary">Evaly</span>
                </Link>
            </div>

            <!-- Navigation Links -->
            <nav class="flex-1 px-4 py-6 space-y-2">
                <Link
                    :href="route('admin.dashboard')"
                    :class="route().current('admin.dashboard') ? 'bg-primary/10 border-primary text-primary' : 'border-transparent text-gray-600 hover:bg-gray-50 hover:text-gray-900'"
                    class="group flex items-center px-3 py-2 text-sm font-medium border-l-4 transition-colors"
                >
                    <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z" />
                    </svg>
                    Dashboard
                </Link>

                <Link
                    v-if="can('view users')"
                    :href="route('admin.users.index')"
                    :class="route().current('admin.users.*') ? 'bg-primary/10 border-primary text-primary' : 'border-transparent text-gray-600 hover:bg-gray-50 hover:text-gray-900'"
                    class="group flex items-center px-3 py-2 text-sm font-medium border-l-4 transition-colors"
                >
                    <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    Utilisateurs
                </Link>
                <Link
                    v-if="can('view students')"
                    :href="route('admin.students.index')"
                    :class="route().current('admin.students.*') ? 'bg-primary/10 border-primary text-primary' : 'border-transparent text-gray-600 hover:bg-gray-50 hover:text-gray-900'"
                    class="group flex items-center px-3 py-2 text-sm font-medium border-l-4 transition-colors"
                >
                    <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    Étudiants
                </Link>


                <Link
                    v-if="can('view roles')"
                    :href="route('admin.roles.index')"
                    :class="route().current('admin.roles.*') ? 'bg-primary/10 border-primary text-primary' : 'border-transparent text-gray-600 hover:bg-gray-50 hover:text-gray-900'"
                    class="group flex items-center px-3 py-2 text-sm font-medium border-l-4 transition-colors"
                >
                    <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                    Rôles
                </Link>

                <Link
                    v-if="can('view classes')"
                    :href="route('admin.classes.index')"
                    :class="route().current('admin.classes.*') ? 'bg-primary/10 border-primary text-primary' : 'border-transparent text-gray-600 hover:bg-gray-50 hover:text-gray-900'"
                    class="group flex items-center px-3 py-2 text-sm font-medium border-l-4 transition-colors"
                >
                    <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                    Classes
                </Link>

                <Link
                    v-if="can('view subjects')"
                    :href="route('admin.subjects.index')"
                    :class="route().current('admin.subjects.*') ? 'bg-primary/10 border-primary text-primary' : 'border-transparent text-gray-600 hover:bg-gray-50 hover:text-gray-900'"
                    class="group flex items-center px-3 py-2 text-sm font-medium border-l-4 transition-colors"
                >
                    <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                    Matières
                </Link>

                <Link
                    :href="route('admin.exams.index')"
                    :class="route().current('admin.exams.*') ? 'bg-primary/10 border-primary text-primary' : 'border-transparent text-gray-600 hover:bg-gray-50 hover:text-gray-900'"
                    class="group flex items-center px-3 py-2 text-sm font-medium border-l-4 transition-colors"
                >
                    <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Examens
                </Link>
            </nav>

            <!-- User Menu -->
            <div class="border-t border-gray-200 p-4">
                <div class="relative">
                    <button
                        @click="showUserMenu = !showUserMenu"
                        class="w-full flex items-center text-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary rounded-lg px-3 py-2 hover:bg-gray-50 transition-colors"
                    >
                        <div class="flex-1 text-left">
                            <p class="text-sm font-medium text-gray-700">{{ $page.props.auth.user.name }}</p>
                            <p class="text-xs text-gray-500">{{ $page.props.auth.user.roles.join(', ') }}</p>
                        </div>
                        <svg class="h-5 w-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>

                    <div
                        v-show="showUserMenu"
                        @click="showUserMenu = false"
                        class="origin-bottom-left absolute bottom-full left-0 mb-2 w-full rounded-lg shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-10"
                    >
                        <div class="py-1">
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

        <!-- Main content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Flash Messages -->
            <div v-if="$page.props.flash.success" class="bg-green-50 border border-green-200 text-green-800 px-4 py-3">
                {{ $page.props.flash.success }}
            </div>

            <div v-if="$page.props.flash.error" class="bg-red-50 border border-red-200 text-red-800 px-4 py-3">
                {{ $page.props.flash.error }}
            </div>

            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto py-6">
                <slot />
            </main>
        </div>
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
