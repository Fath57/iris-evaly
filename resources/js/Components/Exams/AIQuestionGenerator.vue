<template>
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-2xl font-bold text-gray-900 mb-6">Génération Automatique de Questions par IA</h2>

        <div v-if="!generating && !generatedQuestions.length" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Fournisseur IA</label>
                <select 
                    v-model="form.ai_provider"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
                >
                    <option value="chatgpt">ChatGPT (OpenAI)</option>
                    <option value="gemini">Gemini (Google)</option>
                    <option value="perplexity">Perplexity AI</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Sujet / Thème</label>
                <input 
                    v-model="form.subject_theme"
                    type="text"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
                    placeholder="Ex: Les équations du second degré, La grammaire française..."
                />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Niveau de Difficulté</label>
                    <select 
                        v-model="form.difficulty_level"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
                    >
                        <option value="easy">Facile</option>
                        <option value="medium">Moyen</option>
                        <option value="hard">Difficile</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Type de Questions</label>
                    <select 
                        v-model="form.question_type"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
                    >
                        <option value="multiple_choice">QCM</option>
                        <option value="true_false">Vrai/Faux</option>
                        <option value="short_answer">Questions Courtes</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nombre de Questions</label>
                    <input 
                        v-model.number="form.questions_requested"
                        type="number"
                        min="1"
                        max="20"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
                    />
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Prompt Personnalisé (optionnel)</label>
                <textarea 
                    v-model="form.custom_prompt"
                    rows="4"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
                    placeholder="Vous pouvez personnaliser le prompt pour affiner la génération..."
                ></textarea>
            </div>

            <div class="flex justify-end gap-2">
                <button 
                    @click="$emit('cancel')"
                    type="button"
                    class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300"
                >
                    Annuler
                </button>
                <button 
                    @click="generateQuestions"
                    type="button"
                    :disabled="!canGenerate"
                    class="px-4 py-2 bg-primary text-white rounded-md hover:bg-primary-600 disabled:bg-gray-300 disabled:cursor-not-allowed"
                >
                    Générer les Questions
                </button>
            </div>
        </div>

        <div v-if="generating" class="text-center py-12">
                            <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-primary"></div>
            <p class="mt-4 text-gray-600">Génération des questions en cours...</p>
            <p class="text-sm text-gray-500">Cela peut prendre quelques instants</p>
        </div>

        <div v-if="!generating && generatedQuestions.length" class="space-y-4">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-900">
                    Questions Générées ({{ generatedQuestions.length }})
                </h3>
                <div class="flex gap-2">
                    <button 
                        @click="selectAll"
                        type="button"
                        class="px-3 py-1 text-sm bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300"
                    >
                        Tout sélectionner
                    </button>
                    <button 
                        @click="deselectAll"
                        type="button"
                        class="px-3 py-1 text-sm bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300"
                    >
                        Tout désélectionner
                    </button>
                </div>
            </div>

            <div 
                v-for="(question, index) in generatedQuestions" 
                :key="index"
                class="border border-gray-200 rounded-lg p-4"
                :class="{ 'bg-blue-50 border-blue-300': selectedQuestions.includes(index) }"
            >
                <div class="flex items-start gap-3">
                    <input 
                        type="checkbox"
                        :checked="selectedQuestions.includes(index)"
                        @change="toggleQuestionSelection(index)"
                        class="mt-1 w-4 h-4 text-primary focus:ring-primary rounded"
                    />
                    <div class="flex-1">
                        <p class="font-medium text-gray-900 mb-2">{{ index + 1 }}. {{ question.question_text }}</p>
                        
                        <div v-if="question.options" class="space-y-1 ml-4 mb-2">
                            <div 
                                v-for="(option, optIndex) in question.options" 
                                :key="optIndex"
                                class="flex items-center gap-2 text-sm"
                                :class="{ 'text-green-600 font-medium': option.is_correct }"
                            >
                                <span>{{ option.option_key }}.</span>
                                <span>{{ option.option_text }}</span>
                                <span v-if="option.is_correct" class="text-xs">(Correct)</span>
                            </div>
                        </div>

                        <div class="flex gap-4 text-xs text-gray-500">
                            <span>Points: {{ question.points }}</span>
                            <span>Difficulté: {{ question.difficulty_level }}</span>
                            <span v-if="question.category">Catégorie: {{ question.category }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-2 mt-6">
                <button 
                    @click="resetForm"
                    type="button"
                    class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300"
                >
                    Générer d'autres Questions
                </button>
                <button 
                    @click="acceptSelected"
                    type="button"
                    :disabled="!selectedQuestions.length"
                    class="px-4 py-2 bg-primary text-white rounded-md hover:bg-primary-600 disabled:bg-gray-300 disabled:cursor-not-allowed"
                >
                    Accepter les Questions Sélectionnées ({{ selectedQuestions.length }})
                </button>
            </div>
        </div>

        <div v-if="error" class="mt-4 p-4 bg-red-50 border border-red-200 rounded-md">
            <p class="text-sm text-red-800">{{ error }}</p>
        </div>
    </div>
</template>

<script>
export default {
    name: 'AIQuestionGenerator',
    props: {
        examId: {
            type: Number,
            default: null,
        },
    },
    data() {
        return {
            form: {
                ai_provider: 'chatgpt',
                subject_theme: '',
                difficulty_level: 'medium',
                question_type: 'multiple_choice',
                questions_requested: 5,
                custom_prompt: '',
            },
            generating: false,
            generatedQuestions: [],
            selectedQuestions: [],
            historyId: null,
            error: null,
        };
    },
    computed: {
        canGenerate() {
            return this.form.subject_theme.trim().length > 0 && this.form.questions_requested > 0;
        },
    },
    methods: {
        async generateQuestions() {
            if (!this.canGenerate) return;

            this.generating = true;
            this.error = null;

            try {
                const response = await this.$inertia.post('/admin/ai/generate', {
                    ...this.form,
                    exam_id: this.examId,
                });

                if (response.success) {
                    this.generatedQuestions = response.data.questions;
                    this.historyId = response.data.history.id;
                    this.selectedQuestions = this.generatedQuestions.map((_, index) => index);
                }
            } catch (error) {
                this.error = error.response?.data?.message || 'Erreur lors de la génération des questions';
                console.error('Generation error:', error);
            } finally {
                this.generating = false;
            }
        },
        toggleQuestionSelection(index) {
            const idx = this.selectedQuestions.indexOf(index);
            if (idx > -1) {
                this.selectedQuestions.splice(idx, 1);
            } else {
                this.selectedQuestions.push(index);
            }
        },
        selectAll() {
            this.selectedQuestions = this.generatedQuestions.map((_, index) => index);
        },
        deselectAll() {
            this.selectedQuestions = [];
        },
        async acceptSelected() {
            if (!this.selectedQuestions.length) return;

            try {
                const questionIds = this.selectedQuestions.map(index => 
                    this.generatedQuestions[index].id
                );

                await this.$inertia.post(`/admin/ai/history/${this.historyId}/accept`, {
                    question_ids: questionIds,
                    exam_id: this.examId,
                });

                this.$emit('questions-accepted', questionIds);
                this.resetForm();
            } catch (error) {
                this.error = error.response?.data?.message || 'Erreur lors de l\'acceptation des questions';
                console.error('Accept error:', error);
            }
        },
        resetForm() {
            this.generatedQuestions = [];
            this.selectedQuestions = [];
            this.historyId = null;
            this.error = null;
        },
    },
};
</script>

