<template>
    <AppLayout>
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-gray-900">Créer un rôle</h1>
                <p class="mt-1 text-sm text-gray-600">Définissez un nouveau rôle avec ses permissions</p>
            </div>

            <!-- Form -->
            <form @submit.prevent="submit" class="space-y-6">
                <!-- Basic Info Card -->
                <div class="bg-white shadow rounded-lg p-6">
                    <h2 class="text-lg font-medium text-gray-900 mb-4">Informations générales</h2>
                    
                    <div class="space-y-4">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                                Nom du rôle *
                            </label>
                            <input
                                id="name"
                                v-model="form.name"
                                type="text"
                                required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                                placeholder="Ex: manager, coordinateur..."
                            />
                            <div v-if="form.errors.name" class="mt-1 text-sm text-red-600">
                                {{ form.errors.name }}
                            </div>
                        </div>

                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700 mb-1">
                                Description
                            </label>
                            <textarea
                                id="description"
                                v-model="form.description"
                                rows="3"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                                placeholder="Décrivez les responsabilités de ce rôle..."
                            ></textarea>
                            <div v-if="form.errors.description" class="mt-1 text-sm text-red-600">
                                {{ form.errors.description }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Permissions by Module -->
                <div class="bg-white shadow rounded-lg p-6">
                    <h2 class="text-lg font-medium text-gray-900 mb-4">Permissions par module</h2>
                    <div v-if="form.errors.permissions" class="mb-4 text-sm text-red-600">
                        {{ form.errors.permissions }}
                    </div>

                    <div class="space-y-6">
                        <div v-for="module in modules" :key="module.id" class="border border-gray-200 rounded-lg p-4">
                            <div class="flex items-center justify-between mb-3">
                                <div>
                                    <h3 class="text-base font-medium text-gray-900">{{ module.name }}</h3>
                                    <p v-if="module.description" class="text-sm text-gray-500">{{ module.description }}</p>
                                </div>
                                <button
                                    type="button"
                                    @click="toggleModule(module.id)"
                                    class="text-sm text-orange-600 hover:text-orange-700 font-medium"
                                >
                                    {{ isModuleFullySelected(module.id) ? 'Tout désélectionner' : 'Tout sélectionner' }}
                                </button>
                            </div>

                            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                <div v-for="permission in module.permissions" :key="permission.id" class="flex items-start">
                                    <input
                                        :id="`permission-${permission.id}`"
                                        v-model="form.permissions"
                                        :value="permission.id"
                                        type="checkbox"
                                        class="h-4 w-4 text-orange-600 focus:ring-orange-500 border-gray-300 rounded mt-1"
                                    />
                                    <label :for="`permission-${permission.id}`" class="ml-3 block text-sm cursor-pointer">
                                        <span class="font-medium text-gray-900">{{ permission.name }}</span>
                                        <span v-if="permission.description" class="block text-gray-500">{{ permission.description }}</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex justify-end gap-3 pb-6">
                    <Link
                        :href="route('admin.roles.index')"
                        class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors"
                    >
                        Annuler
                    </Link>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <span v-if="form.processing">Création...</span>
                        <span v-else>Créer le rôle</span>
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>

<script setup>
import { useForm, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    modules: Array,
});

const form = useForm({
    name: '',
    description: '',
    permissions: [],
});

const submit = () => {
    form.post(route('admin.roles.store'));
};

const isModuleFullySelected = (moduleId) => {
    const module = props.modules.find(m => m.id === moduleId);
    if (!module) return false;
    
    const permissionIds = module.permissions.map(p => p.id);
    return permissionIds.every(id => form.permissions.includes(id));
};

const toggleModule = (moduleId) => {
    const module = props.modules.find(m => m.id === moduleId);
    if (!module) return;
    
    const permissionIds = module.permissions.map(p => p.id);
    const allSelected = isModuleFullySelected(moduleId);
    
    if (allSelected) {
        // Remove all module permissions
        form.permissions = form.permissions.filter(id => !permissionIds.includes(id));
    } else {
        // Add all module permissions
        const newPermissions = permissionIds.filter(id => !form.permissions.includes(id));
        form.permissions = [...form.permissions, ...newPermissions];
    }
};
</script>

