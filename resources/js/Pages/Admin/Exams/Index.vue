<template>
    <AppLayout>
        <div class="p-4 sm:p-6 lg:p-8">
            <div class="mb-6">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    <div>
                        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Gestion des Examens</h1>
                        <p class="mt-1 text-sm text-gray-600">Créez et gérez vos examens</p>
                    </div>
                    <div class="flex gap-2">
                        <!--<button 
                            @click="showAIGenerator = true"
                            class="px-4 py-2 bg-purple-600 text-white text-sm rounded-md hover:bg-purple-700 flex items-center gap-2"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                            Générer avec IA
                        </button>-->
                        <button 
                            @click="createExam"
                            class="px-4 py-2 bg-primary text-white text-sm rounded-md hover:bg-primary-600 flex items-center gap-2"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Nouvel Examen
                        </button>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-white rounded-lg shadow-md p-4 mb-6">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Statut</label>
                        <select 
                            v-model="filters.status"
                            @change="applyFilters"
                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
                        >
                            <option value="">Tous</option>
                            <option value="draft">Brouillon</option>
                            <option value="published">Publié</option>
                            <option value="ongoing">En cours</option>
                            <option value="completed">Terminé</option>
                            <option value="archived">Archivé</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Matière</label>
                        <select 
                            v-model="filters.subject_id"
                            @change="applyFilters"
                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
                        >
                            <option value="">Toutes les matières</option>
                            <option v-for="subject in subjects" :key="subject.id" :value="subject.id">
                                {{ subject.name }}
                            </option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Classe</label>
                        <select 
                            v-model="filters.class_id"
                            @change="applyFilters"
                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
                        >
                            <option value="">Toutes les classes</option>
                            <option v-for="classItem in classes" :key="classItem.id" :value="classItem.id">
                                {{ classItem.name }}
                            </option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Rechercher</label>
                        <input 
                            v-model="filters.search"
                            @input="applyFilters"
                            type="text"
                            placeholder="Titre de l'examen..."
                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
                        />
                    </div>
                </div>
            </div>

            <!-- Exams Grid -->
            <div v-if="filteredExams.length" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <ExamCard 
                    v-for="exam in filteredExams" 
                    :key="exam.id"
                    :exam="exam"
                    :user-role="userRole"
                    @view="viewExam"
                    @edit="editExam"
                />
            </div>

            <div v-else class="bg-white rounded-lg shadow-md p-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">Aucun examen</h3>
                <p class="mt-1 text-sm text-gray-500">Commencez par créer un nouvel examen.</p>
                <div class="mt-6">
                    <button 
                        @click="createExam"
                        class="px-4 py-2 bg-primary text-white text-sm rounded-md hover:bg-primary-600"
                    >
                        Créer un Examen
                    </button>
                </div>
            </div>

            <!-- AI Generator Modal -->
            <Modal v-if="showAIGenerator" @close="showAIGenerator = false">
                <AIQuestionGenerator 
                    @questions-accepted="handleQuestionsAccepted"
                    @cancel="showAIGenerator = false"
                />
            </Modal>
        </div>
    </AppLayout>
</template>

<script>
import AppLayout from '@/Layouts/AppLayout.vue';
import ExamCard from '@/Components/Exams/ExamCard.vue';
import AIQuestionGenerator from '@/Components/Exams/AIQuestionGenerator.vue';
import Modal from '@/Components/Modal.vue';

export default {
    name: 'ExamsIndex',
    components: {
        AppLayout,
        ExamCard,
        AIQuestionGenerator,
        Modal,
    },
    props: {
        exams: {
            type: Array,
            default: () => [],
        },
        subjects: {
            type: Array,
            default: () => [],
        },
        classes: {
            type: Array,
            default: () => [],
        },
        userRole: {
            type: String,
            default: 'teacher',
        },
    },
    data() {
        return {
            filters: {
                status: '',
                subject_id: '',
                class_id: '',
                search: '',
            },
            showAIGenerator: false,
        };
    },
    computed: {
        filteredExams() {
            let filtered = this.exams;

            if (this.filters.status) {
                filtered = filtered.filter(exam => exam.status === this.filters.status);
            }

            if (this.filters.subject_id) {
                filtered = filtered.filter(exam => exam.subject_id === parseInt(this.filters.subject_id));
            }

            if (this.filters.class_id) {
                filtered = filtered.filter(exam => exam.class_id === parseInt(this.filters.class_id));
            }

            if (this.filters.search) {
                const search = this.filters.search.toLowerCase();
                filtered = filtered.filter(exam => 
                    exam.title.toLowerCase().includes(search) ||
                    exam.description?.toLowerCase().includes(search)
                );
            }

            return filtered;
        },
    },
    methods: {
        createExam() {
            this.$inertia.visit('/admin/exams/create');
        },
        viewExam(exam) {
            this.$inertia.visit(`/admin/exams/${exam.id}`);
        },
        editExam(exam) {
            this.$inertia.visit(`/admin/exams/${exam.id}/edit`);
        },
        applyFilters() {
            // Filters are applied via computed property
        },
        handleQuestionsAccepted(questionIds) {
            this.showAIGenerator = false;
            // Refresh the page or show success message
            this.$inertia.reload();
        },
    },
};
</script>

