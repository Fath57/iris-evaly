<template>
    <AppLayout>
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-gray-900">Assigner des classes</h1>
                <div class="mt-2 flex items-center gap-2">
                    <div class="h-10 w-10 rounded-full bg-orange-100 flex items-center justify-center">
                        <span class="text-sm font-medium text-orange-800">
                            {{ teacher.name.charAt(0).toUpperCase() }}
                        </span>
                    </div>
                    <div>
                        <p class="font-medium text-gray-900">{{ teacher.name }}</p>
                        <p class="text-sm text-gray-500">{{ teacher.email }}</p>
                    </div>
                </div>
            </div>

            <!-- Form -->
            <form @submit.prevent="submit" class="space-y-6">
                <div class="bg-white shadow rounded-lg p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-lg font-medium text-gray-900">Sélection des classes</h2>
                        <Button
                            type="button"
                            variant="secondary"
                            size="sm"
                            @click="addAssignment"
                        >
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Ajouter une classe
                        </Button>
                    </div>

                    <div v-if="form.errors.assignments" class="mb-4 text-sm text-red-600">
                        {{ form.errors.assignments }}
                    </div>

                    <div class="space-y-4">
                        <div
                            v-for="(assignment, index) in form.assignments"
                            :key="index"
                            class="border border-gray-200 rounded-lg p-4"
                        >
                            <div class="flex items-start justify-between mb-4">
                                <h3 class="text-sm font-medium text-gray-900">Assignation {{ index + 1 }}</h3>
                                <button
                                    type="button"
                                    @click="removeAssignment(index)"
                                    class="text-red-600 hover:text-red-700"
                                >
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>

                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <FormSelect
                                    :id="`class-${index}`"
                                    v-model="assignment.class_id"
                                    label="Classe"
                                    placeholder="Sélectionner une classe"
                                    required
                                    :options="classOptions"
                                    :error="form.errors[`assignments.${index}.class_id`]"
                                />

                                <FormSelect
                                    :id="`subject-${index}`"
                                    v-model="assignment.subject_id"
                                    label="Matière (optionnel)"
                                    placeholder="Toutes les matières"
                                    :options="subjectOptions"
                                    :error="form.errors[`assignments.${index}.subject_id`]"
                                />
                            </div>

                            <div class="mt-3">
                                <label class="flex items-center">
                                    <input
                                        v-model="assignment.is_main_teacher"
                                        type="checkbox"
                                        class="h-4 w-4 text-orange-600 focus:ring-orange-500 border-gray-300 rounded"
                                    />
                                    <span class="ml-2 text-sm text-gray-900">
                                        Professeur principal de cette classe
                                    </span>
                                </label>
                            </div>
                        </div>

                        <div v-if="form.assignments.length === 0" class="text-center py-8 border-2 border-dashed border-gray-300 rounded-lg">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                            </svg>
                            <p class="mt-2 text-sm text-gray-500">Aucune classe assignée</p>
                            <Button
                                type="button"
                                variant="secondary"
                                size="sm"
                                class="mt-3"
                                @click="addAssignment"
                            >
                                Ajouter une première classe
                            </Button>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex justify-end gap-3 pb-6">
                    <Button
                        type="button"
                        variant="secondary"
                        @click="router.visit(route('admin.users.index'))"
                    >
                        Annuler
                    </Button>
                    <Button
                        type="submit"
                        :disabled="form.processing || form.assignments.length === 0"
                    >
                        <span v-if="form.processing">Enregistrement...</span>
                        <span v-else>Enregistrer les assignations</span>
                    </Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>

<script setup>
import { computed } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Button from '@/Components/Button.vue';
import FormSelect from '@/Components/FormSelect.vue';

const props = defineProps({
    teacher: Object,
    classes: Array,
    subjects: Array,
    currentAssignments: Array,
});

const form = useForm({
    assignments: props.currentAssignments.length > 0 
        ? props.currentAssignments 
        : [],
});

const classOptions = computed(() => {
    return props.classes.map(classItem => ({
        value: classItem.id,
        label: `${classItem.name} (${classItem.code})`,
    }));
});

const subjectOptions = computed(() => {
    return props.subjects.map(subject => ({
        value: subject.id,
        label: subject.name,
    }));
});

const addAssignment = () => {
    form.assignments.push({
        class_id: '',
        subject_id: '',
        is_main_teacher: false,
    });
};

const removeAssignment = (index) => {
    form.assignments.splice(index, 1);
};

const submit = () => {
    form.put(route('admin.users.assign-classes.update', props.teacher.id));
};
</script>









