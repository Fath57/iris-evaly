<template>
    <AppLayout>
        <div class="p-4 sm:p-6 lg:p-8 max-w-5xl mx-auto">
            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900">Créer un Nouvel Examen</h1>
                <p class="mt-2 text-gray-600">Suivez les étapes pour configurer votre examen</p>
            </div>

            <!-- Error Alert -->
            <div v-if="$page.props.errors.error" class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4">
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-red-600 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                    <div class="flex-1">
                        <h3 class="text-sm font-medium text-red-800">Erreur</h3>
                        <p class="mt-1 text-sm text-red-700">{{ $page.props.errors.error }}</p>
                    </div>
                </div>
            </div>

            <!-- Progress Steps -->
            <div class="mb-8">
                <div class="flex items-center justify-between">
                    <div 
                        v-for="(step, index) in steps" 
                        :key="index"
                        class="flex items-center flex-1"
                    >
                        <div class="flex flex-col items-center flex-1">
                            <div 
                                class="w-10 h-10 rounded-full flex items-center justify-center text-sm font-semibold transition-all duration-300"
                                :class="currentStep >= index ? 'bg-primary text-white' : 'bg-gray-200 text-gray-500'"
                            >
                                {{ index + 1 }}
                            </div>
                            <span 
                                class="mt-2 text-xs font-medium transition-all duration-300"
                                :class="currentStep >= index ? 'text-primary' : 'text-gray-500'"
                            >
                                {{ step }}
                            </span>
                        </div>
                        <div 
                            v-if="index < steps.length - 1"
                            class="h-1 flex-1 mx-2 transition-all duration-300"
                            :class="currentStep > index ? 'bg-primary' : 'bg-gray-200'"
                        ></div>
                    </div>
                </div>
            </div>

            <!-- Form -->
            <form @submit.prevent="submitForm" class="space-y-6">
                <!-- Step 1: Informations de Base -->
                <div v-show="currentStep === 0" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 animate-fade-in">
                    <h2 class="text-xl font-semibold text-gray-900 mb-6">Informations de Base</h2>
                    
                    <div class="space-y-5">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Titre de l'Examen <span class="text-red-500">*</span>
                            </label>
                            <input 
                                v-model="form.title"
                                type="text"
                                required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent transition-all"
                                placeholder="Ex: Examen Final - Mathématiques"
                            />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                            <textarea 
                                v-model="form.description"
                                rows="3"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent transition-all"
                                placeholder="Décrivez brièvement le contenu de l'examen..."
                            ></textarea>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Matière <span class="text-red-500">*</span>
                                </label>
                                <select 
                                    v-model="form.subject_id"
                                    required
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent transition-all"
                                >
                                    <option value="">Choisir une matière</option>
                                    <option v-for="subject in subjects" :key="subject.id" :value="subject.id">
                                        {{ subject.name }}
                                    </option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Classe <span class="text-red-500">*</span>
                                </label>
                                <select 
                                    v-model="form.class_id"
                                    required
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent transition-all"
                                >
                                    <option value="">Choisir une classe</option>
                                    <option v-for="classItem in classes" :key="classItem.id" :value="classItem.id">
                                        {{ classItem.name }}
                                    </option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 2: Dates et Durée -->
                <div v-show="currentStep === 1" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 animate-fade-in">
                    <h2 class="text-xl font-semibold text-gray-900 mb-6">Dates et Durée</h2>
                    
                    <div class="space-y-5">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Date de Début <span class="text-red-500">*</span>
                                </label>
                                <input 
                                    v-model="form.start_date"
                                    type="datetime-local"
                                    required
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent transition-all"
                                />
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Date de Fin <span class="text-red-500">*</span>
                                </label>
                                <input 
                                    v-model="form.end_date"
                                    type="datetime-local"
                                    required
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent transition-all"
                                />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Durée (minutes) <span class="text-red-500">*</span>
                                </label>
                                <div class="flex items-center gap-3">
                                    <input 
                                        v-model.number="form.duration_minutes"
                                        type="range"
                                        min="5"
                                        max="240"
                                        step="5"
                                        class="flex-1"
                                    />
                                    <span class="px-4 py-2 bg-gray-100 rounded-lg font-semibold text-primary min-w-[80px] text-center">
                                        {{ form.duration_minutes }} min
                                    </span>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Tentatives Autorisées
                                </label>
                                <div class="flex items-center gap-3">
                                    <input 
                                        v-model.number="form.max_attempts"
                                        type="range"
                                        min="1"
                                        max="10"
                                        class="flex-1"
                                    />
                                    <span class="px-4 py-2 bg-gray-100 rounded-lg font-semibold text-primary min-w-[80px] text-center">
                                        {{ form.max_attempts }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 3: Configuration -->
                <div v-show="currentStep === 2" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 animate-fade-in">
                    <h2 class="text-xl font-semibold text-gray-900 mb-6">Configuration de l'Examen</h2>
                    
                    <div class="space-y-5">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Note de Passage (%)
                            </label>
                            <div class="flex items-center gap-3">
                                <input 
                                    v-model.number="form.passing_score"
                                    type="range"
                                    min="0"
                                    max="100"
                                    step="5"
                                    class="flex-1"
                                />
                                <span class="px-4 py-2 bg-gray-100 rounded-lg font-semibold text-primary min-w-[80px] text-center">
                                    {{ form.passing_score }}%
                                </span>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-3">Options d'Affichage</label>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <label class="flex items-center gap-3 p-3 border-2 border-gray-200 rounded-lg hover:border-primary transition-all cursor-pointer">
                                    <input 
                                        v-model="form.shuffle_questions"
                                        type="checkbox"
                                        class="w-5 h-5 text-primary focus:ring-primary rounded"
                                    />
                                    <div class="flex-1">
                                        <div class="font-medium text-gray-900">Mélanger les questions</div>
                                        <div class="text-xs text-gray-500">Questions dans un ordre aléatoire</div>
                                    </div>
                                </label>

                                <label class="flex items-center gap-3 p-3 border-2 border-gray-200 rounded-lg hover:border-primary transition-all cursor-pointer">
                                    <input 
                                        v-model="form.shuffle_options"
                                        type="checkbox"
                                        class="w-5 h-5 text-primary focus:ring-primary rounded"
                                    />
                                    <div class="flex-1">
                                        <div class="font-medium text-gray-900">Mélanger les options</div>
                                        <div class="text-xs text-gray-500">Options de réponse aléatoires</div>
                                    </div>
                                </label>

                                <label class="flex items-center gap-3 p-3 border-2 border-gray-200 rounded-lg hover:border-primary transition-all cursor-pointer">
                                    <input 
                                        v-model="form.show_results_immediately"
                                        type="checkbox"
                                        class="w-5 h-5 text-primary focus:ring-primary rounded"
                                    />
                                    <div class="flex-1">
                                        <div class="font-medium text-gray-900">Résultats immédiats</div>
                                        <div class="text-xs text-gray-500">Afficher score après l'examen</div>
                                    </div>
                                </label>

                                <label class="flex items-center gap-3 p-3 border-2 border-gray-200 rounded-lg hover:border-primary transition-all cursor-pointer">
                                    <input 
                                        v-model="form.show_correct_answers"
                                        type="checkbox"
                                        class="w-5 h-5 text-primary focus:ring-primary rounded"
                                    />
                                    <div class="flex-1">
                                        <div class="font-medium text-gray-900">Afficher les corrections</div>
                                        <div class="text-xs text-gray-500">Montrer les bonnes réponses</div>
                                    </div>
                                </label>

                                <label class="flex items-center gap-3 p-3 border-2 border-gray-200 rounded-lg hover:border-primary transition-all cursor-pointer">
                                    <input 
                                        v-model="form.allow_navigation"
                                        type="checkbox"
                                        class="w-5 h-5 text-primary focus:ring-primary rounded"
                                    />
                                    <div class="flex-1">
                                        <div class="font-medium text-gray-900">Navigation libre</div>
                                        <div class="text-xs text-gray-500">Permettre d'aller-retour</div>
                                    </div>
                                </label>

                                <label class="flex items-center gap-3 p-3 border-2 border-gray-200 rounded-lg hover:border-primary transition-all cursor-pointer">
                                    <input 
                                        v-model="form.allow_review"
                                        type="checkbox"
                                        class="w-5 h-5 text-primary focus:ring-primary rounded"
                                    />
                                    <div class="flex-1">
                                        <div class="font-medium text-gray-900">Révision autorisée</div>
                                        <div class="text-xs text-gray-500">Revoir avant soumission</div>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Instructions Particulières</label>
                            <textarea 
                                v-model="form.instructions"
                                rows="4"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent transition-all"
                                placeholder="Consignes importantes pour les étudiants..."
                            ></textarea>
                        </div>
                    </div>
                </div>

                <!-- Navigation Buttons -->
                <div class="flex flex-col gap-4 pt-6 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex justify-start">
                        <Button
                            v-if="currentStep > 0"
                            type="button"
                            variant="secondary"
                            size="lg"
                            class="gap-2"
                            @click="currentStep--"
                        >
                            <span aria-hidden="true">←</span>
                            <span>Précédent</span>
                        </Button>
                    </div>

                    <div class="flex justify-end">
                        <Button
                            v-if="currentStep < steps.length - 1"
                            type="button"
                            variant="primary"
                            size="lg"
                            class="gap-2"
                            @click="nextStep"
                        >
                            <span>Suivant</span>
                            <span aria-hidden="true">→</span>
                        </Button>
                        <Button
                            v-else
                            type="submit"
                            variant="success"
                            size="lg"
                            :disabled="form.processing"
                            class="gap-2"
                        >
                            <span aria-hidden="true">✓</span>
                            <span>{{ form.processing ? 'Création...' : 'Créer l\'Examen' }}</span>
                        </Button>
                    </div>
                </div>
            </form>
        </div>
    </AppLayout>
</template>

<script>
import AppLayout from '@/Layouts/AppLayout.vue';
import Button from '@/Components/Button.vue';
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

export default {
    name: 'CreateExam',
    components: {
        AppLayout,
        Button,
    },
    props: {
        subjects: {
            type: Array,
            required: true,
        },
        classes: {
            type: Array,
            required: true,
        },
    },
    setup() {
        const currentStep = ref(0);
        const steps = ['Informations', 'Dates & Durée', 'Configuration'];

        const form = useForm({
            title: '',
            description: '',
            instructions: '',
            subject_id: '',
            class_id: '',
            duration_minutes: 60,
            passing_score: 50,
            start_date: '',
            end_date: '',
            shuffle_questions: false,
            shuffle_options: false,
            show_results_immediately: false,
            allow_review: true,
            show_correct_answers: true,
            allow_navigation: true,
            max_attempts: 1,
        });

        const nextStep = () => {
            // Validation basique avant de passer à l'étape suivante
            if (currentStep.value === 0) {
                if (!form.title || !form.subject_id || !form.class_id) {
                    alert('Veuillez remplir tous les champs obligatoires');
                    return;
                }
            }
            if (currentStep.value === 1) {
                if (!form.start_date || !form.end_date) {
                    alert('Veuillez définir les dates de début et de fin');
                    return;
                }
                if (new Date(form.end_date) <= new Date(form.start_date)) {
                    alert('La date de fin doit être après la date de début');
                    return;
                }
            }
            currentStep.value++;
        };

        const submitForm = () => {
            form.post('/admin/exams');
        };

        return {
            currentStep,
            steps,
            form,
            nextStep,
            submitForm,
        };
    },
};
</script>

<style scoped>
@keyframes fade-in {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-fade-in {
    animation: fade-in 0.3s ease-out;
}

/* Custom range input styling */
input[type="range"] {
    -webkit-appearance: none;
    appearance: none;
    height: 6px;
    border-radius: 3px;
    background: #e5e7eb;
    outline: none;
}

input[type="range"]::-webkit-slider-thumb {
    -webkit-appearance: none;
    appearance: none;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    background: #FAB133;
    cursor: pointer;
    transition: all 0.15s ease-in-out;
}

input[type="range"]::-webkit-slider-thumb:hover {
    transform: scale(1.2);
    box-shadow: 0 0 0 8px rgba(255, 94, 14, 0.1);
}

input[type="range"]::-moz-range-thumb {
    width: 20px;
    height: 20px;
    border-radius: 50%;
    background: #FAB133;
    cursor: pointer;
    border: none;
    transition: all 0.15s ease-in-out;
}

input[type="range"]::-moz-range-thumb:hover {
    transform: scale(1.2);
    box-shadow: 0 0 0 8px rgba(255, 94, 14, 0.1);
}
</style>
