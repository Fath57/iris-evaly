<template>
    <AppLayout>
        <div class="p-4 sm:p-6 lg:p-8">
            <!-- Header -->
            <div class="mb-6 flex justify-between items-start">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">{{ exam.title }}</h1>
                    <p class="mt-1 text-sm text-gray-600">{{ exam.subject?.name }} - {{ exam.class?.name }}</p>
                </div>
                <div class="flex gap-2">
                    <button
                        @click="$inertia.visit(`/admin/exams/${exam.id}/edit`)"
                        class="px-4 py-2 bg-blue-500 text-white text-sm rounded-md hover:bg-blue-600"
                    >
                        Modifier
                    </button>
                    <button
                        v-if="exam.status === 'draft'"
                        @click="publishExam"
                        class="px-4 py-2 bg-green-500 text-white text-sm rounded-md hover:bg-green-600"
                    >
                        Publier
                    </button>
                    <button
                        v-if="canReleaseResults"
                        @click="releaseResults"
                        :disabled="isReleasingResults"
                        class="px-4 py-2 bg-purple-500 text-white text-sm rounded-md hover:bg-purple-600 disabled:opacity-50"
                    >
                        <span v-if="isReleasingResults">Publication...</span>
                        <span v-else>Publier les résultats</span>
                    </button>
                </div>
            </div>

            <!-- Status Badges -->
            <div class="mb-6 flex flex-wrap gap-2">
                <span
                    :class="statusClass"
                    class="px-3 py-1 text-xs font-semibold rounded-full"
                >
                    {{ statusText }}
                </span>
                <span
                    v-if="exam.results_released_at"
                    class="px-3 py-1 text-xs font-semibold rounded-full bg-purple-100 text-purple-800"
                >
                    Résultats publiés le {{ formatDate(exam.results_released_at) }}
                </span>
                <span
                    v-else-if="exam.show_results_immediately"
                    class="px-3 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800"
                >
                    Résultats immédiats
                </span>
                <span
                    v-else-if="exam.status !== 'draft'"
                    class="px-3 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800"
                >
                    Résultats en attente de publication
                </span>
            </div>

            <!-- Exam Details -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Informations Générales</h2>
                    <dl class="space-y-3">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Description</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ exam.description || 'N/A' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Durée</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ exam.duration_minutes }} minutes</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Note de Passage</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ exam.passing_score }}%</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Tentatives Autorisées</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ exam.max_attempts }}</dd>
                        </div>
                    </dl>
                </div>

                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Dates et Horaires</h2>
                    <dl class="space-y-3">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Début</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ formatDate(exam.start_date) }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Fin</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ formatDate(exam.end_date) }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Créé le</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ formatDate(exam.created_at) }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Instructions -->
            <div v-if="exam.instructions" class="bg-white rounded-lg shadow-md p-6 mb-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Instructions</h2>
                <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ exam.instructions }}</p>
            </div>

            <!-- Questions Section -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-lg font-semibold text-gray-900">
                        Questions ({{ exam.questions?.length || 0 }})
                    </h2>
                    <button
                        @click="addQuestion"
                        class="px-4 py-2 bg-[#FAB133] text-white text-sm rounded-md hover:bg-primary-600"
                    >
                        Ajouter une Question
                    </button>
                </div>

                <div v-if="exam.questions && exam.questions.length" class="space-y-4">
                    <div
                        v-for="(question, index) in exam.questions"
                        :key="question.id"
                        class="border border-gray-200 rounded-lg p-4"
                    >
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <h3 class="font-medium text-gray-900 mb-2">
                                    {{ index + 1 }}. {{ question.question_text }}
                                </h3>
                                <div class="flex gap-4 text-xs text-gray-500">
                                    <span>{{ question.points }} point(s)</span>
                                    <span>{{ question.type }}</span>
                                    <span>{{ question.difficulty_level }}</span>
                                </div>
                            </div>
                            <button
                                @click="editQuestion(question.id)"
                                class="text-blue-600 hover:text-blue-800 text-sm"
                            >
                                Modifier
                            </button>
                        </div>
                    </div>
                </div>

                <div v-else class="text-center py-8 text-gray-500">
                    Aucune question pour le moment. Commencez par en ajouter une.
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script>
import AppLayout from '@/Layouts/AppLayout.vue';
import axios from 'axios';

export default {
    name: 'ShowExam',
    components: {
        AppLayout,
    },
    props: {
        exam: {
            type: Object,
            required: true,
        },
    },
    data() {
        return {
            isReleasingResults: false,
        };
    },
    computed: {
        canReleaseResults() {
            return this.exam.status !== 'draft'
                && !this.exam.show_results_immediately
                && !this.exam.results_released_at;
        },
        statusClass() {
            const classes = {
                draft: 'bg-gray-200 text-gray-700',
                published: 'bg-green-100 text-green-800',
                ongoing: 'bg-blue-100 text-blue-800',
                completed: 'bg-purple-100 text-purple-800',
                archived: 'bg-red-100 text-red-800',
            };
            return classes[this.exam.status] || 'bg-gray-200 text-gray-700';
        },
        statusText() {
            const statuses = {
                draft: 'Brouillon',
                published: 'Publié',
                ongoing: 'En cours',
                completed: 'Terminé',
                archived: 'Archivé',
            };
            return statuses[this.exam.status] || this.exam.status;
        },
    },
    methods: {
        formatDate(date) {
            if (!date) return 'N/A';
            return new Date(date).toLocaleString('fr-FR', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
            });
        },
        addQuestion() {
            this.$inertia.visit(`/admin/exams/${this.exam.id}/questions`);
        },
        editQuestion(questionId) {
            this.$inertia.visit(`/admin/exams/${this.exam.id}/questions?edit=${questionId}`);
        },
        publishExam() {
            if (confirm('Êtes-vous sûr de vouloir publier cet examen ?')) {
                this.$inertia.post(`/admin/exams/${this.exam.id}/publish`);
            }
        },
        async releaseResults() {
            if (!confirm('Êtes-vous sûr de vouloir publier les résultats ? Les étudiants seront notifiés.')) {
                return;
            }

            this.isReleasingResults = true;
            try {
                const response = await axios.post(`/admin/exams/${this.exam.id}/release-results`);
                if (response.data.success) {
                    alert(`Résultats publiés ! ${response.data.data.students_notified} étudiant(s) notifié(s).`);
                    this.$inertia.reload();
                }
            } catch (error) {
                alert(error.response?.data?.message || 'Erreur lors de la publication des résultats.');
            } finally {
                this.isReleasingResults = false;
            }
        },
    },
};
</script>
