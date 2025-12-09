<template>
  <Modal :show="show" @close="$emit('close')" size="2xl">
    <div class="p-6">
      <div class="flex items-center justify-between mb-6">
        <div>
          <h3 class="text-xl font-bold text-gray-900">Génération par IA</h3>
          <p class="text-sm text-gray-500">Créez des questions automatiquement</p>
        </div>
        <button @click="$emit('close')" class="text-gray-400 hover:text-gray-600 transition-colors">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
      <form @submit.prevent="generateQuestions" class="space-y-5">
        <!-- Sujet et Thème -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              Sujet <span class="text-red-500">*</span>
            </label>
            <input
              v-model="form.subject"
              type="text"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#FAB133] focus:border-transparent"
              placeholder="Ex: Mathématiques, Histoire..."
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              Thème spécifique
            </label>
            <input
              v-model="form.topic"
              type="text"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#FAB133] focus:border-transparent"
              placeholder="Ex: Théorème de Pythagore..."
            />
          </div>
        </div>

        <!-- Type, Difficulté et Nombre -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              Type de question <span class="text-red-500">*</span>
            </label>
            <select
              v-model="form.question_type"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#FAB133] focus:border-transparent"
            >
              <option value="multiple_choice">QCM (choix unique)</option>
              <option value="multiple_answers">Choix multiples</option>
              <option value="true_false">Vrai/Faux</option>
              <option value="short_answer">Question ouverte courte</option>
              <option value="essay">Question ouverte longue</option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              Difficulté <span class="text-red-500">*</span>
            </label>
            <select
              v-model="form.difficulty"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#FAB133] focus:border-transparent"
            >
              <option value="easy">Facile</option>
              <option value="medium">Moyen</option>
              <option value="hard">Difficile</option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              Nombre de questions <span class="text-red-500">*</span>
            </label>
            <input
              v-model.number="form.number_of_questions"
              type="number"
              min="1"
              max="20"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#FAB133] focus:border-transparent"
            />
          </div>
        </div>

        <!-- Provider d'IA et Langue -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              Provider IA
            </label>
            <select
              v-model="form.ai_provider"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#FAB133] focus:border-transparent"
            >
              <option value="openai">OpenAI (GPT-3.5)</option>
              <option value="gemini">Google Gemini</option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              Langue
            </label>
            <select
              v-model="form.language"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#FAB133] focus:border-transparent"
            >
              <option value="fr">Français</option>
              <option value="en">English</option>
            </select>
          </div>
        </div>

        <!-- Boutons d'action -->
        <div class="flex gap-3 pt-4 border-t border-gray-200">
          <button
            type="button"
            @click="$emit('close')"
            class="px-4 py-2 bg-gray-100 text-gray-700 font-medium rounded-lg hover:bg-gray-200 transition-colors"
          >
            Annuler
          </button>
          <button
            type="submit"
            :disabled="isGenerating"
            class="flex-1 px-4 py-2 bg-[#FAB133] text-white font-medium rounded-lg hover:bg-[#E9A020] focus:outline-none focus:ring-2 focus:ring-[#FAB133] focus:ring-offset-2 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <span v-if="isGenerating" class="flex items-center justify-center">
              <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
              </svg>
              Génération...
            </span>
            <span v-else>Générer les questions</span>
          </button>
        </div>
      </form>

      <!-- Note informative -->
      <p class="mt-4 text-xs text-gray-500">
        Plus votre description est précise, meilleures seront les questions générées.
      </p>
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
