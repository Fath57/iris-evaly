<template>
    <AppLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Rôles</h1>
                    <p class="mt-1 text-sm text-gray-600">Gestion des rôles et permissions</p>
                </div>
                <Link
                    v-if="can('create roles')"
                    :href="route('admin.roles.create')"
                    class="inline-flex items-center px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white font-medium rounded-lg transition-colors"
                >
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Nouveau rôle
                </Link>
            </div>

            <!-- Roles Grid -->
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                <div v-for="role in roles" :key="role.id" class="bg-white overflow-hidden shadow rounded-lg hover:shadow-lg transition-shadow">
                    <div class="px-6 py-5">
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="text-lg font-semibold text-gray-900">{{ role.name }}</h3>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                {{ role.permissions.length }} permissions
                            </span>
                        </div>

                        <p v-if="role.description" class="text-sm text-gray-600 mb-4 line-clamp-2">
                            {{ role.description }}
                        </p>
                        <p v-else class="text-sm text-gray-400 mb-4 italic">
                            Aucune description
                        </p>

                        <div v-if="can('edit roles') || can('delete roles')" class="mt-4 pt-4 border-t border-gray-200 flex gap-2">
                            <Link
                                v-if="can('edit roles')"
                                :href="route('admin.roles.edit', role.id)"
                                class="flex-1 inline-flex justify-center items-center px-3 py-2 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 transition-colors"
                            >
                                Modifier
                            </Link>
                            <button
                                v-if="can('delete roles') && role.name !== 'super-admin'"
                                @click="deleteRole(role.id)"
                                class="px-3 py-2 border border-red-300 text-sm font-medium rounded-lg text-red-700 bg-white hover:bg-red-50 transition-colors"
                            >
                                Supprimer
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div v-if="roles.length === 0" class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">Aucun rôle</h3>
                <p class="mt-1 text-sm text-gray-500">Commencez par créer un nouveau rôle</p>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { Link, router, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    roles: Array,
});

const page = usePage();

const can = (permission) => {
    return page.props.auth.user.permissions.includes(permission);
};

const deleteRole = (id) => {
    if (confirm('Êtes-vous sûr de vouloir supprimer ce rôle ?')) {
        router.delete(route('admin.roles.destroy', id));
    }
};
</script>

