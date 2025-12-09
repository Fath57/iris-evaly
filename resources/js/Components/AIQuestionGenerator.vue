<template>
  <Modal :show="show" @close="$emit('close')" size="xl" backdrop="blur">
    <div class="relative overflow-hidden">
      <!-- Gradient background decoration -->
      <div class="absolute -top-24 -right-24 w-48 h-48 bg-gradient-to-br from-amber-400/20 to-orange-500/20 rounded-full blur-3xl"></div>
      <div class="absolute -bottom-24 -left-24 w-48 h-48 bg-gradient-to-tr from-amber-400/10 to-yellow-500/10 rounded-full blur-3xl"></div>

      <div class="relative p-6">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-amber-400 to-orange-500 flex items-center justify-center shadow-lg shadow-amber-500/25">
              <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
              </svg>
            </div>
            <div>
              <h3 class="text-lg font-semibold text-gray-900">Génération IA</h3>
              <p class="text-xs text-gray-500">Créez des questions automatiquement</p>
            </div>
          </div>
          <button @click="$emit('close')" class="w-8 h-8 rounded-lg bg-gray-100 hover:bg-gray-200 flex items-center justify-center transition-colors">
            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>

        <!-- Error message -->
        <Transition
          enter-active-class="transition ease-out duration-200"
          enter-from-class="opacity-0 -translate-y-2"
          enter-to-class="opacity-100 translate-y-0"
          leave-active-class="transition ease-in duration-150"
          leave-from-class="opacity-100 translate-y-0"
          leave-to-class="opacity-0 -translate-y-2"
        >
          <div v-if="error" class="mb-5 p-3 bg-red-50 border border-red-100 rounded-xl">
            <div class="flex items-center gap-2">
              <div class="w-5 h-5 rounded-full bg-red-100 flex items-center justify-center flex-shrink-0">
                <svg class="w-3 h-3 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                </svg>
              </div>
              <p class="text-sm text-red-700">{{ error }}</p>
            </div>
          </div>
        </Transition>

        <!-- Form -->
        <form @submit.prevent="generateQuestions" class="space-y-4">
          <!-- Subject & Topic -->
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-medium text-gray-600 mb-1.5">Sujet *</label>
              <input
                v-model="form.subject"
                type="text"
                required
                class="w-full px-3 py-2.5 text-sm bg-gray-50 border-0 rounded-xl focus:outline-none focus:ring-2 focus:ring-amber-500/50 placeholder-gray-400 transition-all"
                placeholder="Ex: Mathématiques"
              />
            </div>
            <div>
              <label class="block text-xs font-medium text-gray-600 mb-1.5">Thème</label>
              <input
                v-model="form.topic"
                type="text"
                class="w-full px-3 py-2.5 text-sm bg-gray-50 border-0 rounded-xl focus:outline-none focus:ring-2 focus:ring-amber-500/50 placeholder-gray-400 transition-all"
                placeholder="Ex: Pythagore"
              />
            </div>
          </div>

          <!-- Type, Difficulty, Number -->
          <div class="grid grid-cols-3 gap-3">
            <div>
              <label class="block text-xs font-medium text-gray-600 mb-1.5">Type *</label>
              <select
                v-model="form.question_type"
                required
                class="w-full px-3 py-2.5 text-sm bg-gray-50 border-0 rounded-xl focus:outline-none focus:ring-2 focus:ring-amber-500/50 transition-all appearance-none cursor-pointer"
              >
                <option value="multiple_choice">QCM</option>
                <option value="multiple_answers">Choix multiples</option>
                <option value="true_false">Vrai/Faux</option>
                <option value="short_answer">Réponse courte</option>
                <option value="essay">Rédaction</option>
              </select>
            </div>
            <div>
              <label class="block text-xs font-medium text-gray-600 mb-1.5">Difficulté *</label>
              <select
                v-model="form.difficulty"
                required
                class="w-full px-3 py-2.5 text-sm bg-gray-50 border-0 rounded-xl focus:outline-none focus:ring-2 focus:ring-amber-500/50 transition-all appearance-none cursor-pointer"
              >
                <option value="easy">Facile</option>
                <option value="medium">Moyen</option>
                <option value="hard">Difficile</option>
              </select>
            </div>
            <div>
              <label class="block text-xs font-medium text-gray-600 mb-1.5">Nombre *</label>
              <input
                v-model.number="form.number_of_questions"
                type="number"
                min="1"
                max="20"
                class="w-full px-3 py-2.5 text-sm bg-gray-50 border-0 rounded-xl focus:outline-none focus:ring-2 focus:ring-amber-500/50 transition-all"
              />
            </div>
          </div>

          <!-- Provider & Language -->
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-medium text-gray-600 mb-1.5">Provider IA</label>
              <select
                v-model="form.ai_provider"
                class="w-full px-3 py-2.5 text-sm bg-gray-50 border-0 rounded-xl focus:outline-none focus:ring-2 focus:ring-amber-500/50 transition-all appearance-none cursor-pointer"
              >
                <option value="openai">OpenAI GPT</option>
                <option value="gemini">Google Gemini</option>
              </select>
            </div>
            <div>
              <label class="block text-xs font-medium text-gray-600 mb-1.5">Langue</label>
              <select
                v-model="form.language"
                class="w-full px-3 py-2.5 text-sm bg-gray-50 border-0 rounded-xl focus:outline-none focus:ring-2 focus:ring-amber-500/50 transition-all appearance-none cursor-pointer"
              >
                <option value="fr">Français</option>
                <option value="en">English</option>
              </select>
            </div>
          </div>

          <!-- Action Buttons -->
          <div class="flex gap-3 pt-4">
            <button
              type="button"
              @click="$emit('close')"
              class="px-4 py-2.5 text-sm font-medium text-gray-600 bg-gray-100 rounded-xl hover:bg-gray-200 transition-colors"
            >
              Annuler
            </button>
            <button
              type="submit"
              :disabled="isGenerating"
              class="flex-1 px-4 py-2.5 text-sm font-medium text-white bg-gradient-to-r from-amber-500 to-orange-500 rounded-xl hover:from-amber-600 hover:to-orange-600 focus:outline-none focus:ring-2 focus:ring-amber-500/50 focus:ring-offset-2 transition-all disabled:opacity-50 disabled:cursor-not-allowed shadow-lg shadow-amber-500/25"
            >
              <span v-if="isGenerating" class="flex items-center justify-center gap-2">
                <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
                </svg>
                Génération...
              </span>
              <span v-else class="flex items-center justify-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
                Générer
              </span>
            </button>
          </div>
        </form>

        <!-- Info note -->
        <p class="mt-4 text-xs text-center text-gray-400">
          Plus votre description est précise, meilleures seront les questions.
        </p>
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
  ai_provider: 'openai',
});

const isGenerating = ref(false);
const error = ref(null);

async function generateQuestions() {
  isGenerating.value = true;
  error.value = null;

  router.post(`/admin/exams/${props.examId}/questions/generate-ai`, {
    exam_id: props.examId,
    ...form,
  }, {
    preserveState: true,
    preserveScroll: true,
    onSuccess: (page) => {
      isGenerating.value = false;
      const flash = page.props.flash || {};

      if (flash.generatedQuestions && Array.isArray(flash.generatedQuestions)) {
        const questions = flash.generatedQuestions;

        if (questions.length > 0) {
          emit('questionsGenerated', questions);
          emit('close');
        } else {
          error.value = 'Aucune question générée. Réessayez.';
        }
      } else if (flash.error) {
        error.value = flash.error;
      } else {
        error.value = 'Réponse invalide du serveur.';
      }
    },
    onError: (errors) => {
      isGenerating.value = false;
      if (errors.message) {
        error.value = errors.message;
      } else if (typeof errors === 'object') {
        const firstError = Object.values(errors)[0];
        error.value = Array.isArray(firstError) ? firstError[0] : firstError;
      } else {
        error.value = 'Erreur lors de la génération.';
      }
    },
    onFinish: () => {
      isGenerating.value = false;
    },
  });
}
</script>
