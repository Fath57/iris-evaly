<template>
  <Modal :show="show" @close="$emit('close')" size="2xl">
    <div class="p-6">
      <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-3">
          <div class="p-3 bg-gradient-to-br from-[#FAB133] to-[#F19F0D] rounded-xl">
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
            </svg>
          </div>
          <div>
            <h3 class="text-2xl font-bold text-gray-900">🤖 Génération par IA</h3>
            <p class="text-sm text-gray-600">Créez des questions intelligentes en quelques secondes</p>
          </div>
        </div>
        <button @click="$emit('close')" class="text-gray-400 hover:text-gray-600 transition-colors">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
          </svg>
        </button>
      </div>

      <!-- Messages d'erreur -->
      <div v-if="error" class="mb-4 p-4 bg-red-50 border-l-4 border-red-500 rounded animate-shake">
        <div class="flex items-start">
          <svg class="h-5 w-5 text-red-400 mt-0.5" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
          </svg>
          <div class="ml-3">
            <p class="text-sm text-red-800">{{ error }}</p>
          </div>
        </div>
      </div>

      <!-- Formulaire -->
      <form @submit.prevent="generateQuestions" class="space-y-6">
        <!-- Sujet et Thème -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">
              📚 Sujet <span class="text-red-500">*</span>
            </label>
            <input
              v-model="form.subject"
              type="text"
              required
              class="w-full px-4 py-2.5 border-2 border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#FAB133] focus:border-transparent transition-all duration-300"
              placeholder="Ex: Mathématiques, Histoire..."
            />
          </div>

          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">
              🎯 Thème spécifique
            </label>
            <input
              v-model="form.topic"
              type="text"
              class="w-full px-4 py-2.5 border-2 border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#FAB133] focus:border-transparent transition-all duration-300"
              placeholder="Ex: Théorème de Pythagore..."
            />
          </div>
        </div>

        <!-- Type et Difficulté -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">
              📝 Type de question <span class="text-red-500">*</span>
            </label>
            <select
              v-model="form.question_type"
              required
              class="w-full px-4 py-2.5 bg-gradient-to-r from-gray-50 to-gray-100 border-2 border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#FAB133] focus:border-transparent transition-all duration-300 cursor-pointer"
            >
              <option value="multiple_choice">QCM (choix unique)</option>
              <option value="multiple_answers">Choix multiples</option>
              <option value="true_false">Vrai/Faux</option>
              <option value="short_answer">Question ouverte courte</option>
              <option value="essay">Question ouverte longue</option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">
              ⚡ Difficulté <span class="text-red-500">*</span>
            </label>
            <select
              v-model="form.difficulty"
              required
              class="w-full px-4 py-2.5 bg-gradient-to-r from-gray-50 to-gray-100 border-2 border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#FAB133] focus:border-transparent transition-all duration-300 cursor-pointer"
            >
              <option value="easy">Facile</option>
              <option value="medium">Moyen</option>
              <option value="hard">Difficile</option>
            </select>
          </div>
        </div>

        <!-- Nombre de questions -->
        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-2">
            🔢 Nombre de questions <span class="text-red-500">*</span>
          </label>
          <div class="flex items-center gap-4">
            <input
              v-model.number="form.number_of_questions"
              type="range"
              min="1"
              max="20"
              class="flex-1 h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-[#FAB133]"
            />
            <span class="px-4 py-2 bg-[#FAB133] text-white font-bold rounded-lg min-w-[60px] text-center">
              {{ form.number_of_questions }}
            </span>
          </div>
          <p class="mt-2 text-xs text-gray-500">Entre 1 et 20 questions</p>
        </div>

        <!-- Provider d'IA -->
        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-2">
            🤖 Provider d'IA
          </label>
          <div class="flex gap-3">
            <label class="flex-1 relative">
              <input
                type="radio"
                v-model="form.ai_provider"
                value="openai"
                class="sr-only peer"
              />
              <div class="p-4 border-2 border-gray-300 rounded-xl cursor-pointer peer-checked:border-[#FAB133] peer-checked:bg-[#FEF7EC] transition-all duration-300 hover:border-[#FAB133]">
                <div class="flex items-center justify-center gap-2">
                  <svg class="w-8 h-8 text-gray-700" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M22.2819 9.8211a5.9847 5.9847 0 0 0-.5157-4.9108 6.0462 6.0462 0 0 0-6.5098-2.9A6.0651 6.0651 0 0 0 4.9807 4.1818a5.9847 5.9847 0 0 0-3.9977 2.9 6.0462 6.0462 0 0 0 .7427 7.0966 5.98 5.98 0 0 0 .511 4.9107 6.051 6.051 0 0 0 6.5146 2.9001A5.9847 5.9847 0 0 0 13.2599 24a6.0557 6.0557 0 0 0 5.7718-4.2058 5.9894 5.9894 0 0 0 3.9977-2.9001 6.0557 6.0557 0 0 0-.7475-7.0729zm-9.022 12.6081a4.4755 4.4755 0 0 1-2.8764-1.0408l.1419-.0804 4.7783-2.7582a.7948.7948 0 0 0 .3927-.6813v-6.7369l2.02 1.1686a.071.071 0 0 1 .038.052v5.5826a4.504 4.504 0 0 1-4.4945 4.4944zm-9.6607-4.1254a4.4708 4.4708 0 0 1-.5346-3.0137l.142-.0852 4.783-2.7582a.7712.7712 0 0 0 .7806 0l5.8428 3.3685v2.3324a.0804.0804 0 0 1-.0332.0615L9.74 19.9502a4.4992 4.4992 0 0 1-6.1408-1.6464zM2.3408 7.8956a4.485 4.485 0 0 1 2.3655-1.9728V11.6a.7664.7664 0 0 0 .3879.6765l5.8144 3.3543-2.0201 1.1685a.0757.0757 0 0 1-.071 0l-4.8303-2.7865A4.504 4.504 0 0 1 2.3408 7.872zm16.5963 3.8558L13.1038 8.364 15.1192 7.2a.0757.0757 0 0 1 .071 0l4.8303 2.7913a4.4944 4.4944 0 0 1-.6765 8.1042v-5.6772a.79.79 0 0 0-.407-.667zm2.0107-3.0231l-.142-.0852-4.7735-2.7818a.7759.7759 0 0 0-.7854 0L9.409 9.2297V6.8974a.0662.0662 0 0 1 .0284-.0615l4.8303-2.7866a4.4992 4.4992 0 0 1 6.6802 4.66zM8.3065 12.863l-2.02-1.1638a.0804.0804 0 0 1-.038-.0567V6.0742a4.4992 4.4992 0 0 1 7.3757-3.4537l-.142.0805L8.704 5.459a.7948.7948 0 0 0-.3927.6813zm1.0976-2.3654l2.602-1.4998 2.6069 1.4998v2.9994l-2.5974 1.4997-2.6067-1.4997Z"/>
                  </svg>
                  <div class="text-center">
                    <div class="font-semibold text-gray-700">OpenAI</div>
                    <div class="text-xs text-gray-500">GPT-3.5 Turbo</div>
                  </div>
                </div>
              </div>
            </label>
            <label class="flex-1 relative">
              <input
                type="radio"
                v-model="form.ai_provider"
                value="gemini"
                class="sr-only peer"
              />
              <div class="p-4 border-2 border-gray-300 rounded-xl cursor-pointer peer-checked:border-[#FAB133] peer-checked:bg-[#FEF7EC] transition-all duration-300 hover:border-[#FAB133]">
                <div class="flex items-center justify-center gap-2">
                  <svg class="w-8 h-8" viewBox="0 0 24 24" fill="none">
                    <path d="M12 2L13.09 8.26L20 9L13.09 9.74L12 16L10.91 9.74L4 9L10.91 8.26L12 2Z" fill="#4285F4"/>
                    <path d="M12 8L13.09 14.26L20 15L13.09 15.74L12 22L10.91 15.74L4 15L10.91 14.26L12 8Z" fill="#EA4335"/>
                    <path d="M12 2L13.09 8.26L20 9L13.09 9.74L12 16L10.91 9.74L4 9L10.91 8.26L12 2Z" fill="#FBBC04" opacity="0.5"/>
                  </svg>
                  <div class="text-center">
                    <div class="font-semibold text-gray-700">Gemini</div>
                    <div class="text-xs text-gray-500">Flash 1.5</div>
                  </div>
                </div>
              </div>
            </label>
          </div>
        </div>

        <!-- Langue -->
        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-2">
            🌍 Langue
          </label>
          <div class="flex gap-3">
            <label class="flex-1 relative">
              <input
                type="radio"
                v-model="form.language"
                value="fr"
                class="sr-only peer"
              />
              <div class="p-4 border-2 border-gray-300 rounded-xl cursor-pointer peer-checked:border-[#FAB133] peer-checked:bg-[#FEF7EC] transition-all duration-300 hover:border-[#FAB133]">
                <div class="flex items-center justify-center gap-2">
                  <span class="text-2xl">🇫🇷</span>
                  <span class="font-semibold text-gray-700">Français</span>
                </div>
              </div>
            </label>
            <label class="flex-1 relative">
              <input
                type="radio"
                v-model="form.language"
                value="en"
                class="sr-only peer"
              />
              <div class="p-4 border-2 border-gray-300 rounded-xl cursor-pointer peer-checked:border-[#FAB133] peer-checked:bg-[#FEF7EC] transition-all duration-300 hover:border-[#FAB133]">
                <div class="flex items-center justify-center gap-2">
                  <span class="text-2xl">🇬🇧</span>
                  <span class="font-semibold text-gray-700">English</span>
                </div>
              </div>
            </label>
          </div>
        </div>

        <!-- Boutons d'action -->
        <div class="flex gap-3 pt-4 border-t-2 border-gray-100">
          <button
            type="submit"
            :disabled="isGenerating"
            class="flex-1 px-6 py-3 bg-gradient-to-r from-[#FAB133] to-[#F19F0D] text-white font-semibold rounded-xl hover:from-[#F19F0D] hover:to-[#C17F0A] focus:outline-none focus:ring-4 focus:ring-[#F9CF8D] transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none"
          >
            <span v-if="isGenerating" class="flex items-center justify-center">
              <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
              </svg>
              Génération en cours...
            </span>
            <span v-else class="flex items-center justify-center gap-2">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
              </svg>
              Générer avec l'IA
            </span>
          </button>
          <button
            type="button"
            @click="$emit('close')"
            class="px-6 py-3 bg-gray-200 text-gray-700 font-semibold rounded-xl hover:bg-gray-300 transition-all duration-300 shadow-md hover:shadow-lg"
          >
            Annuler
          </button>
        </div>
      </form>

      <!-- Note informative -->
      <div class="mt-6 p-4 bg-blue-50 border-l-4 border-blue-400 rounded">
        <div class="flex">
          <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
          </svg>
          <div class="ml-3">
            <p class="text-sm text-blue-700">
              <strong>💡 Astuce :</strong> Plus votre description est précise, meilleures seront les questions générées. Vous pourrez modifier les questions générées avant de les ajouter à votre examen.
            </p>
          </div>
        </div>
      </div>
    </div>
  </Modal>
</template>

<script setup>
import { ref, reactive } from 'vue';
import { router } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';

const props = defineProps({
  show: Boolean,
  examId: {
    type: [String, Number],
    required: true,
  },
});

const emit = defineEmits(['close', 'questionsGenerated']);

const form = reactive({
  subject: '',
  topic: '',
  question_type: 'multiple_choice',
  difficulty: 'medium',
  number_of_questions: 5,
  language: 'fr',
  ai_provider: 'openai', // Nouveau champ pour le provider d'IA
});

const isGenerating = ref(false);
const error = ref(null);

async function generateQuestions() {
  isGenerating.value = true;
  error.value = null;

  // Utiliser router.post d'Inertia au lieu de fetch
  router.post(`/admin/exams/${props.examId}/questions/generate-ai`, {
    exam_id: props.examId,
    ...form,
  }, {
    preserveState: true,
    preserveScroll: true,
    onSuccess: (page) => {
      isGenerating.value = false;

        console.log(page.props.flash);
      // Récupérer les données depuis flash (méthode correcte avec Inertia)
      const flash = page.props.flash || {};

      // Vérifier si nous avons des questions générées
      if (flash.generatedQuestions && Array.isArray(flash.generatedQuestions)) {
        const questions = flash.generatedQuestions;

        if (questions.length > 0) {
          emit('questionsGenerated', questions);
          emit('close');
          console.log(`${questions.length} questions générées avec succès !`);

          // Afficher un message de succès si disponible
          if (flash.success) {
            console.log(flash.success);
          }
        } else {
          error.value = 'Aucune question n\'a été générée. Veuillez réessayer.';
        }
      } else if (flash.error) {
        // Si nous avons une erreur dans flash
        error.value = flash.error;
      } else {
        error.value = 'La réponse du serveur est invalide. Veuillez réessayer.';
      }
    },
    onError: (errors) => {
      isGenerating.value = false;
      // Gérer les erreurs de validation et autres erreurs du serveur
      if (errors.message) {
        error.value = errors.message;
      } else if (typeof errors === 'object') {
        // Si c'est un objet d'erreurs de validation, prendre la première erreur
        const firstError = Object.values(errors)[0];
        error.value = Array.isArray(firstError) ? firstError[0] : firstError;
      } else {
        error.value = 'Erreur lors de la génération des questions. Veuillez réessayer.';
      }
      console.error('Error generating questions:', errors);
    },
    onFinish: () => {
      isGenerating.value = false;
    },
  });
}
</script>

<style scoped>
@keyframes shake {
  0%, 100% { transform: translateX(0); }
  10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
  20%, 40%, 60%, 80% { transform: translateX(5px); }
}

.animate-shake {
  animation: shake 0.5s;
}
</style>
