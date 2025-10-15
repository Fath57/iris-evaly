<template>
    <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-orange-50 to-orange-100 px-4 sm:px-6 lg:px-8">
        <div class="max-w-4xl w-full space-y-8 bg-white p-8 rounded-2xl shadow-xl">
            <div>
                <h2 class="text-center text-3xl font-bold text-gray-900">
                    Sélection des classes
                </h2>
                <p class="mt-2 text-center text-sm text-gray-600">
                    Choisissez les classes dans lesquelles vous intervenez
                </p>
            </div>
            
            <form class="mt-8 space-y-6" @submit.prevent="submit">
                <div v-if="Object.keys(form.errors).length" class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                    <ul class="list-disc list-inside text-sm">
                        <li v-for="(error, field) in form.errors" :key="field">{{ error }}</li>
                    </ul>
                </div>

                <div class="space-y-4">
                    <h3 class="text-lg font-medium text-gray-900">Classes disponibles</h3>
                    
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div v-for="classItem in classes" :key="classItem.id" class="border border-gray-200 rounded-lg p-4">
                            <div class="flex items-center mb-3">
                                <input
                                    :id="`class-${classItem.id}`"
                                    v-model="selectedClasses"
                                    :value="classItem.id"
                                    type="checkbox"
                                    class="h-4 w-4 text-orange-600 focus:ring-orange-500 border-gray-300 rounded"
                                />
                                <label :for="`class-${classItem.id}`" class="ml-3 block text-sm font-medium text-gray-900">
                                    {{ classItem.name }}
                                </label>
                            </div>
                            
                            <p class="text-sm text-gray-600 mb-3">{{ classItem.code }} - {{ classItem.level }}</p>
                            
                            <!-- Subject and main teacher selection for selected classes -->
                            <div v-if="selectedClasses.includes(classItem.id)" class="mt-3 pt-3 border-t border-gray-200 space-y-3">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        Matière enseignée (optionnel)
                                    </label>
                                    <select
                                        v-model="classSelections[classItem.id].subject_id"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                                    >
                                        <option value="">Toutes les matières</option>
                                        <option v-for="subject in subjects" :key="subject.id" :value="subject.id">
                                            {{ subject.name }}
                                        </option>
                                    </select>
                                </div>
                                
                                <div class="flex items-center">
                                    <input
                                        :id="`main-${classItem.id}`"
                                        v-model="classSelections[classItem.id].is_main_teacher"
                                        type="checkbox"
                                        class="h-4 w-4 text-orange-600 focus:ring-orange-500 border-gray-300 rounded"
                                    />
                                    <label :for="`main-${classItem.id}`" class="ml-2 block text-sm text-gray-900">
                                        Professeur principal de cette classe
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-3">
                    <button
                        type="button"
                        @click="skipSelection"
                        class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors"
                    >
                        Passer cette étape
                    </button>
                    <button
                        type="submit"
                        :disabled="form.processing || selectedClasses.length === 0"
                        class="px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <span v-if="form.processing">Enregistrement...</span>
                        <span v-else>Enregistrer mes classes</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { ref, watch } from 'vue';
import { useForm, router } from '@inertiajs/vue3';

const props = defineProps({
    classes: Array,
    subjects: Array,
});

const selectedClasses = ref([]);
const classSelections = ref({});

const form = useForm({
    selections: [],
});

// Watch for changes in selected classes
watch(selectedClasses, (newSelected, oldSelected) => {
    // Add new selections
    newSelected.forEach(classId => {
        if (!classSelections.value[classId]) {
            classSelections.value[classId] = {
                class_id: classId,
                subject_id: '',
                is_main_teacher: false,
            };
        }
    });
    
    // Remove unselected classes
    Object.keys(classSelections.value).forEach(classId => {
        if (!newSelected.includes(parseInt(classId))) {
            delete classSelections.value[classId];
        }
    });
}, { deep: true });

const submit = () => {
    form.selections = Object.values(classSelections.value);
    form.post(route('profile.setup.classes.store'));
};

const skipSelection = () => {
    router.visit(route('admin.dashboard'));
};
</script>
