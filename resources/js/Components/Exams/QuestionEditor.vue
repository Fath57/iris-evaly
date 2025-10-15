<template>
    <div class="bg-white rounded-lg shadow-md p-6 mb-4">
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Type de Question</label>
            <select 
                v-model="localQuestion.type" 
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
            >
                <option value="multiple_choice">QCM (Choix Unique)</option>
                <option value="true_false">Vrai/Faux</option>
                <option value="short_answer">Question Courte</option>
                <option value="essay">Question Ouverte</option>
            </select>
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Énoncé de la Question</label>
            <textarea 
                v-model="localQuestion.question_text"
                rows="4"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
                placeholder="Saisir l'énoncé de la question..."
            ></textarea>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Points</label>
                <input 
                    v-model.number="localQuestion.points"
                    type="number"
                    min="1"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
                />
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Difficulté</label>
                <select 
                    v-model="localQuestion.difficulty_level"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
                >
                    <option value="easy">Facile</option>
                    <option value="medium">Moyen</option>
                    <option value="hard">Difficile</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Catégorie</label>
                <input 
                    v-model="localQuestion.category"
                    type="text"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
                    placeholder="Ex: Algèbre, Grammaire..."
                />
            </div>
        </div>

        <!-- Options for Multiple Choice -->
        <div v-if="localQuestion.type === 'multiple_choice' || localQuestion.type === 'true_false'" class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Options de Réponse</label>
            
            <div v-for="(option, index) in localQuestion.options" :key="index" class="flex items-center gap-2 mb-2">
                <input 
                    type="radio"
                    :name="`correct-answer-${localQuestion.id || 'new'}`"
                    :checked="option.is_correct"
                    @change="selectCorrectAnswer(index)"
                    class="w-4 h-4 text-primary focus:ring-primary"
                />
                <input 
                    v-model="option.option_text"
                    type="text"
                    class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
                    :placeholder="`Option ${String.fromCharCode(65 + index)}`"
                />
                <button 
                    v-if="localQuestion.options.length > 2"
                    @click="removeOption(index)"
                    type="button"
                    class="px-3 py-2 bg-red-500 text-white rounded-md hover:bg-red-600"
                >
                    Supprimer
                </button>
            </div>

            <button 
                @click="addOption"
                type="button"
                class="mt-2 px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300"
            >
                + Ajouter une Option
            </button>
        </div>

        <!-- Correct Answer for Short Answer -->
        <div v-if="localQuestion.type === 'short_answer'" class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Réponse Correcte</label>
            <input 
                v-model="localQuestion.correct_answer_text"
                type="text"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
                placeholder="Réponse attendue..."
            />
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Explication (optionnel)</label>
            <textarea 
                v-model="localQuestion.explanation"
                rows="3"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
                placeholder="Explication de la réponse..."
            ></textarea>
        </div>

        <div class="flex items-center gap-2 mb-4">
            <input 
                v-model="localQuestion.is_in_bank"
                type="checkbox"
                class="w-4 h-4 text-primary focus:ring-primary rounded"
            />
            <label class="text-sm text-gray-700">Ajouter à la banque de questions</label>
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
                @click="saveQuestion"
                type="button"
                class="px-4 py-2 bg-primary text-white rounded-md hover:bg-primary-600"
            >
                Enregistrer
            </button>
        </div>
    </div>
</template>

<script>
export default {
    name: 'QuestionEditor',
    props: {
        question: {
            type: Object,
            default: () => ({
                type: 'multiple_choice',
                question_text: '',
                points: 1,
                difficulty_level: 'medium',
                category: '',
                options: [
                    { option_text: '', option_key: 'A', is_correct: false },
                    { option_text: '', option_key: 'B', is_correct: false },
                    { option_text: '', option_key: 'C', is_correct: false },
                    { option_text: '', option_key: 'D', is_correct: false },
                ],
                correct_answer_text: '',
                explanation: '',
                is_in_bank: false,
            }),
        },
    },
    data() {
        return {
            localQuestion: { ...this.question },
        };
    },
    methods: {
        addOption() {
            const nextKey = String.fromCharCode(65 + this.localQuestion.options.length);
            this.localQuestion.options.push({
                option_text: '',
                option_key: nextKey,
                is_correct: false,
            });
        },
        removeOption(index) {
            this.localQuestion.options.splice(index, 1);
        },
        selectCorrectAnswer(index) {
            this.localQuestion.options.forEach((option, i) => {
                option.is_correct = i === index;
            });
        },
        saveQuestion() {
            this.$emit('save', this.localQuestion);
        },
    },
    watch: {
        question: {
            handler(newVal) {
                this.localQuestion = { ...newVal };
            },
            deep: true,
        },
    },
};
</script>

