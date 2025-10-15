<template>
    <AppLayout>
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-gray-900">Créer une classe</h1>
                <p class="mt-1 text-sm text-gray-600">Ajoutez une nouvelle classe à la plateforme</p>
            </div>

            <!-- Form -->
            <form @submit.prevent="submit" class="space-y-6">
                <div class="bg-white shadow rounded-lg p-6">
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                                Nom de la classe *
                            </label>
                            <input
                                id="name"
                                v-model="form.name"
                                type="text"
                                required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                                placeholder="Ex: Terminale S - Groupe A"
                            />
                            <div v-if="form.errors.name" class="mt-1 text-sm text-red-600">
                                {{ form.errors.name }}
                            </div>
                        </div>

                        <div>
                            <label for="code" class="block text-sm font-medium text-gray-700 mb-1">
                                Code de la classe *
                            </label>
                            <input
                                id="code"
                                v-model="form.code"
                                type="text"
                                required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                                placeholder="Ex: TS-A"
                            />
                            <div v-if="form.errors.code" class="mt-1 text-sm text-red-600">
                                {{ form.errors.code }}
                            </div>
                        </div>

                        <div>
                            <label for="academic_year" class="block text-sm font-medium text-gray-700 mb-1">
                                Année académique *
                            </label>
                            <input
                                id="academic_year"
                                v-model="form.academic_year"
                                type="number"
                                required
                                min="2000"
                                max="2100"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                                placeholder="2024"
                            />
                            <div v-if="form.errors.academic_year" class="mt-1 text-sm text-red-600">
                                {{ form.errors.academic_year }}
                            </div>
                        </div>

                        <div>
                            <label for="level" class="block text-sm font-medium text-gray-700 mb-1">
                                Niveau *
                            </label>
                            <select
                                id="level"
                                v-model="form.level"
                                required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                            >
                                <option value="">Sélectionner un niveau</option>
                                <option value="primaire">Primaire</option>
                                <option value="college">Collège</option>
                                <option value="lycee">Lycée</option>
                                <option value="universite">Université</option>
                            </select>
                            <div v-if="form.errors.level" class="mt-1 text-sm text-red-600">
                                {{ form.errors.level }}
                            </div>
                        </div>

                        <div class="sm:col-span-2">
                            <label for="description" class="block text-sm font-medium text-gray-700 mb-1">
                                Description
                            </label>
                            <textarea
                                id="description"
                                v-model="form.description"
                                rows="3"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                                placeholder="Description de la classe..."
                            ></textarea>
                        </div>

                        <div class="flex items-center">
                            <input
                                id="is_active"
                                v-model="form.is_active"
                                type="checkbox"
                                class="h-4 w-4 text-orange-600 focus:ring-orange-500 border-gray-300 rounded"
                            />
                            <label for="is_active" class="ml-2 block text-sm text-gray-900">
                                Classe active
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex justify-end gap-3 pb-6">
                    <Link
                        :href="route('admin.classes.index')"
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
                        <span v-else>Créer la classe</span>
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
    teachers: Array,
});

const form = useForm({
    name: '',
    code: '',
    description: '',
    academic_year: new Date().getFullYear(),
    level: 'lycee',
    is_active: true,
});

const submit = () => {
    form.post(route('admin.classes.store'));
};
</script>
