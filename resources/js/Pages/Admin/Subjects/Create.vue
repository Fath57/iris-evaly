<template>
    <AppLayout>
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-gray-900">Créer une matière</h1>
                <p class="mt-1 text-sm text-gray-600">Ajoutez une nouvelle matière à la plateforme</p>
            </div>

            <!-- Form -->
            <form @submit.prevent="submit" class="space-y-6">
                <div class="bg-white shadow rounded-lg p-6">
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <FormInput
                            id="name"
                            v-model="form.name"
                            label="Nom de la matière"
                            placeholder="Ex: Mathématiques"
                            required
                            :error="form.errors.name"
                        />

                        <FormInput
                            id="code"
                            v-model="form.code"
                            label="Code"
                            placeholder="Ex: MATH"
                            required
                            :error="form.errors.code"
                            helper="Code unique pour identifier la matière"
                        />

                        <div class="sm:col-span-2">
                            <label for="description" class="block text-sm font-medium text-gray-700 mb-1">
                                Description
                            </label>
                            <textarea
                                id="description"
                                v-model="form.description"
                                rows="3"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                                placeholder="Description de la matière..."
                            ></textarea>
                        </div>

                        <div>
                            <label for="color" class="block text-sm font-medium text-gray-700 mb-1">
                                Couleur
                            </label>
                            <div class="flex items-center gap-3">
                                <input
                                    id="color"
                                    v-model="form.color"
                                    type="color"
                                    class="h-10 w-20 border border-gray-300 rounded-lg cursor-pointer"
                                />
                                <input
                                    v-model="form.color"
                                    type="text"
                                    class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                                    placeholder="#FF5E0E"
                                />
                            </div>
                            <p class="mt-1 text-sm text-gray-500">
                                Couleur pour identifier visuellement la matière
                            </p>
                        </div>

                        <div class="flex items-center">
                            <input
                                id="is_active"
                                v-model="form.is_active"
                                type="checkbox"
                                class="h-4 w-4 text-orange-600 focus:ring-orange-500 border-gray-300 rounded"
                            />
                            <label for="is_active" class="ml-2 block text-sm text-gray-900">
                                Matière active
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex justify-end gap-3 pb-6">
                    <Button
                        type="button"
                        variant="secondary"
                        @click="router.visit(route('admin.subjects.index'))"
                    >
                        Annuler
                    </Button>
                    <Button
                        type="submit"
                        :disabled="form.processing"
                    >
                        <span v-if="form.processing">Création...</span>
                        <span v-else>Créer la matière</span>
                    </Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>

<script setup>
import { useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Button from '@/Components/Button.vue';
import FormInput from '@/Components/FormInput.vue';

const form = useForm({
    name: '',
    code: '',
    description: '',
    color: '#FF5E0E',
    is_active: true,
});

const submit = () => {
    form.post(route('admin.subjects.store'));
};
</script>









