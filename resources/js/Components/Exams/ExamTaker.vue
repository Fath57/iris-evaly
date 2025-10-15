<template>
    <div class="max-w-4xl mx-auto p-4 sm:p-6">
        <!-- Exam Header -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">{{ exam.title }}</h1>
                    <p class="text-sm text-gray-600 mt-1">{{ exam.subject?.name }}</p>
                </div>
                <div class="text-right">
                    <div class="text-2xl font-bold text-primary">{{ timeRemaining }}</div>
                    <div class="text-xs text-gray-600">Temps restant</div>
                </div>
            </div>

            <div class="flex gap-4 text-sm text-gray-600">
                <span>Question {{ currentQuestionIndex + 1 }} / {{ questions.length }}</span>
                <span>•</span>
                <span>{{ answeredCount }} / {{ questions.length }} réponses</span>
            </div>

            <!-- Progress Bar -->
            <div class="mt-4 w-full bg-gray-200 rounded-full h-2">
                <div 
                    class="bg-primary h-2 rounded-full transition-all duration-300"
                    :style="{ width: `${progress}%` }"
                ></div>
            </div>
        </div>

        <!-- Question Card -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <div class="mb-4">
                <div class="flex justify-between items-start mb-2">
                    <h2 class="text-lg font-semibold text-gray-900">
                        Question {{ currentQuestionIndex + 1 }}
                    </h2>
                    <span class="px-3 py-1 text-xs font-semibold bg-blue-100 text-blue-800 rounded-full">
                        {{ currentQuestion.points }} point(s)
                    </span>
                </div>
                <p class="text-gray-700 leading-relaxed">{{ currentQuestion.question_text }}</p>
            </div>

            <!-- Question Images -->
            <div v-if="currentQuestion.images?.length" class="mb-4">
                <img 
                    v-for="image in currentQuestion.images" 
                    :key="image.id"
                    :src="image.image_url"
                    :alt="image.alt_text"
                    class="max-w-full h-auto rounded-lg shadow-sm"
                />
            </div>

            <!-- Multiple Choice Options -->
            <div v-if="currentQuestion.type === 'multiple_choice' || currentQuestion.type === 'true_false'" class="space-y-3">
                <div 
                    v-for="option in currentQuestion.options" 
                    :key="option.id"
                    @click="selectOption(option.id)"
                    class="p-4 border-2 rounded-lg cursor-pointer transition-all"
                    :class="isSelected(option.id) ? 'border-primary bg-primary-50' : 'border-gray-200 hover:border-gray-300'"
                >
                    <div class="flex items-start gap-3">
                        <input 
                            type="radio"
                            :name="`question-${currentQuestion.id}`"
                            :checked="isSelected(option.id)"
                            class="mt-1 w-4 h-4 text-primary focus:ring-primary"
                            @click.stop
                        />
                        <div class="flex-1">
                            <div class="flex items-center gap-2">
                                <span class="font-medium text-gray-900">{{ option.option_key }}.</span>
                                <span class="text-gray-700">{{ option.option_text }}</span>
                            </div>
                            <div v-if="option.images?.length" class="mt-2">
                                <img 
                                    v-for="image in option.images" 
                                    :key="image.id"
                                    :src="image.image_url"
                                    :alt="image.alt_text"
                                    class="max-w-xs h-auto rounded-lg shadow-sm"
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Short Answer / Essay -->
            <div v-else class="space-y-3">
                <textarea 
                    v-model="answers[currentQuestion.id]"
                    rows="6"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
                    :placeholder="currentQuestion.type === 'essay' ? 'Rédigez votre réponse ici...' : 'Votre réponse...'"
                ></textarea>
            </div>
        </div>

        <!-- Navigation -->
        <div class="flex justify-between items-center">
            <button 
                v-if="currentQuestionIndex > 0"
                @click="previousQuestion"
                class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 flex items-center gap-2"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Précédent
            </button>

            <div class="flex gap-2">
                <button 
                    @click="showQuestionList = !showQuestionList"
                    class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300"
                >
                    Liste des Questions
                </button>

                <button 
                    v-if="currentQuestionIndex < questions.length - 1"
                    @click="nextQuestion"
                    class="px-4 py-2 bg-primary text-white rounded-md hover:bg-primary-600 flex items-center gap-2"
                >
                    Suivant
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </button>

                <button 
                    v-else
                    @click="confirmSubmit"
                    class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700"
                >
                    Terminer l'Examen
                </button>
            </div>
        </div>

        <!-- Question List Modal -->
        <Modal v-if="showQuestionList" @close="showQuestionList = false">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Liste des Questions</h3>
                <div class="grid grid-cols-5 gap-2">
                    <button 
                        v-for="(question, index) in questions"
                        :key="question.id"
                        @click="goToQuestion(index)"
                        class="p-3 text-center rounded-lg border-2 transition-all"
                        :class="getQuestionButtonClass(index)"
                    >
                        {{ index + 1 }}
                    </button>
                </div>
                <div class="mt-4 flex gap-4 text-sm">
                    <div class="flex items-center gap-2">
                        <div class="w-4 h-4 bg-green-500 rounded"></div>
                        <span>Répondue</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-4 h-4 bg-gray-300 rounded"></div>
                        <span>Non répondue</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-4 h-4 bg-primary rounded"></div>
                        <span>Question actuelle</span>
                    </div>
                </div>
            </div>
        </Modal>

        <!-- Confirm Submit Modal -->
        <ConfirmModal 
            v-if="showConfirmSubmit"
            title="Terminer l'Examen"
            message="Êtes-vous sûr de vouloir terminer l'examen ? Cette action est irréversible."
            @confirm="submitExam"
            @cancel="showConfirmSubmit = false"
        />
    </div>
</template>

<script>
import Modal from '@/Components/Modal.vue';
import ConfirmModal from '@/Components/ConfirmModal.vue';

export default {
    name: 'ExamTaker',
    components: {
        Modal,
        ConfirmModal,
    },
    props: {
        exam: {
            type: Object,
            required: true,
        },
        questions: {
            type: Array,
            required: true,
        },
        attemptId: {
            type: Number,
            required: true,
        },
    },
    data() {
        return {
            currentQuestionIndex: 0,
            answers: {},
            startTime: Date.now(),
            timeLeft: this.exam.duration_minutes * 60,
            showQuestionList: false,
            showConfirmSubmit: false,
        };
    },
    computed: {
        currentQuestion() {
            return this.questions[this.currentQuestionIndex];
        },
        answeredCount() {
            return Object.keys(this.answers).filter(key => {
                const answer = this.answers[key];
                return answer && (answer.option_id || answer.answer_text);
            }).length;
        },
        progress() {
            return (this.answeredCount / this.questions.length) * 100;
        },
        timeRemaining() {
            const minutes = Math.floor(this.timeLeft / 60);
            const seconds = this.timeLeft % 60;
            return `${minutes}:${seconds.toString().padStart(2, '0')}`;
        },
    },
    methods: {
        isSelected(optionId) {
            return this.answers[this.currentQuestion.id]?.option_id === optionId;
        },
        selectOption(optionId) {
            this.$set(this.answers, this.currentQuestion.id, {
                question_id: this.currentQuestion.id,
                option_id: optionId,
            });
            this.saveAnswer();
        },
        async saveAnswer() {
            const answer = this.answers[this.currentQuestion.id];
            if (!answer) return;

            try {
                await this.$inertia.post(`/student/attempts/${this.attemptId}/answer`, answer, {
                    preserveState: true,
                    preserveScroll: true,
                });
            } catch (error) {
                console.error('Error saving answer:', error);
            }
        },
        nextQuestion() {
            if (this.currentQuestionIndex < this.questions.length - 1) {
                this.currentQuestionIndex++;
            }
        },
        previousQuestion() {
            if (this.currentQuestionIndex > 0) {
                this.currentQuestionIndex--;
            }
        },
        goToQuestion(index) {
            this.currentQuestionIndex = index;
            this.showQuestionList = false;
        },
        getQuestionButtonClass(index) {
            if (index === this.currentQuestionIndex) {
                return 'bg-primary text-white border-primary';
            }
            if (this.answers[this.questions[index].id]) {
                return 'bg-green-500 text-white border-green-500';
            }
            return 'bg-white text-gray-700 border-gray-300 hover:border-gray-400';
        },
        confirmSubmit() {
            this.showConfirmSubmit = true;
        },
        async submitExam() {
            try {
                const timeSpent = Math.floor((Date.now() - this.startTime) / 1000);
                await this.$inertia.post(`/student/attempts/${this.attemptId}/complete`, {
                    time_spent_seconds: timeSpent,
                });
            } catch (error) {
                console.error('Error submitting exam:', error);
            }
        },
    },
    mounted() {
        // Timer
        this.timer = setInterval(() => {
            if (this.timeLeft > 0) {
                this.timeLeft--;
            } else {
                this.submitExam();
            }
        }, 1000);

        // Auto-save on page unload
        window.addEventListener('beforeunload', this.saveAnswer);
    },
    beforeUnmount() {
        clearInterval(this.timer);
        window.removeEventListener('beforeunload', this.saveAnswer);
    },
};
</script>

