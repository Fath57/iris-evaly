<template>
  <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
    <!-- Formulaire -->
    <div class="bg-white rounded-lg shadow-lg p-6 transition-all duration-300">
      <!-- Bandeau spécial pour les questions générées par l'IA -->
      <div v-if="question?.is_from_ai" class="mb-6 p-4 bg-gradient-to-r from-[#FAB133] to-[#F19F0D] rounded-xl text-white">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-3">
            <div class="p-2 bg-white/20 rounded-lg">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
              </svg>
            </div>
            <div>
              <div class="font-bold text-lg">Question générée par l'IA</div>
              <div class="text-sm opacity-90">
                Question {{ question.ai_question_number }} sur {{ question.ai_total_questions }}
              </div>
            </div>
          </div>
          <div class="text-right">
            <div class="text-2xl font-bold">{{ question.ai_question_number }}/{{ question.ai_total_questions }}</div>
            <div class="text-xs opacity-90">Progression</div>
          </div>
        </div>
        <div class="mt-3 pt-3 border-t border-white/20">
          <p class="text-sm opacity-90">
            💡 Revoyez et modifiez cette question avant de l'enregistrer.
            Après sauvegarde, la question suivante sera automatiquement chargée.
          </p>
        </div>
      </div>

      <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-gray-900">
          {{ form.id ? '✏️ Modifier la question' : '➕ Nouvelle question' }}
        </h2>
        <button
          type="button"
          @click="togglePreview"
          class="lg:hidden px-4 py-2 bg-[#FAB133] text-white rounded-lg hover:bg-[#F19F0D] transition-colors"
        >
          {{ showPreview ? '📝 Formulaire' : '👁️ Aperçu' }}
        </button>
      </div>

      <!-- Messages d'erreur globaux -->
      <div v-if="errors && Object.keys(errors).length > 0" class="mb-4 p-4 bg-red-50 border-l-4 border-red-500 rounded animate-shake">
        <div class="flex items-start">
          <div class="flex-shrink-0">
            <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
            </svg>
          </div>
          <div class="ml-3">
            <h3 class="text-sm font-medium text-red-800">Erreurs de validation</h3>
            <ul class="mt-2 text-sm text-red-700 list-disc list-inside">
              <li v-for="(error, field) in errors" :key="field">{{ error }}</li>
            </ul>
          </div>
        </div>
      </div>

      <form @submit.prevent="handleSubmit" class="space-y-5">
        <!-- Type et Points -->
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">
              Type de question <span class="text-red-500">*</span>
            </label>
            <select
              v-model="form.type"
              :class="['w-full px-4 py-2.5 bg-gradient-to-r from-gray-50 to-gray-100 border-2 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#FAB133] focus:border-transparent transition-all duration-300 cursor-pointer hover:shadow-md', errors?.type ? 'border-red-300' : 'border-gray-300']"
            >
              <option v-for="opt in typeOptions" :key="opt.value" :value="opt.value">
                {{ opt.label }}
              </option>
            </select>
            <p v-if="errors?.type" class="mt-1 text-xs text-red-600 animate-fadeIn">{{ errors.type }}</p>
          </div>

          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">
              Points <span class="text-red-500">*</span>
            </label>
            <input
              v-model.number="form.points"
              type="number"
              min="1"
              :class="['w-full px-4 py-2.5 bg-gradient-to-r from-gray-50 to-gray-100 border-2 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#FAB133] focus:border-transparent transition-all duration-300', errors?.points ? 'border-red-300' : 'border-gray-300']"
              placeholder="Ex: 5"
            />
            <p v-if="errors?.points" class="mt-1 text-xs text-red-600 animate-fadeIn">{{ errors.points }}</p>
          </div>
        </div>

        <!-- Énoncé -->
        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-2">
            📝 Énoncé <span class="text-red-500">*</span>
            <span class="text-xs font-normal text-gray-500">(Supporte *italique* et **gras**)</span>
          </label>
          <textarea
            v-model="form.question_text"
            rows="4"
            :class="['w-full px-4 py-3 border-2 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#FAB133] focus:border-transparent transition-all duration-300 resize-none', errors?.question_text ? 'border-red-300' : 'border-gray-300']"
            placeholder="Ex: Quelle est la capitale de la France ?"
          />
          <p v-if="errors?.question_text" class="mt-1 text-xs text-red-600 animate-fadeIn">{{ errors.question_text }}</p>
        </div>

        <!-- Image et Alt Text -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">
              🖼️ Image (optionnel)
            </label>
            <div class="relative">
              <input
                type="file"
                accept="image/jpeg,image/png,image/gif,image/svg+xml"
                @change="handleImageUpload"
                class="hidden"
                ref="imageInput"
              />
              <button
                type="button"
                @click="$refs.imageInput.click()"
                class="w-full px-4 py-3 border-2 border-dashed border-gray-300 rounded-xl hover:border-[#FAB133] hover:bg-[#FEF7EC] transition-all duration-300 text-gray-600 hover:text-[#C17F0A] font-medium"
              >
                {{ form.image || form.image_path ? '✓ Image ajoutée' : '+ Ajouter une image' }}
              </button>
            </div>
            <div v-if="imagePreview || form.image_path" class="mt-3 relative group">
              <img
                :src="imagePreview || `/storage/${form.image_path}`"
                :alt="form.alt_text || 'Aperçu'"
                class="w-full h-32 object-cover rounded-lg shadow-md transition-transform group-hover:scale-105"
              />
              <button
                type="button"
                @click="removeImage"
                class="absolute top-2 right-2 p-1 bg-red-500 text-white rounded-full opacity-0 group-hover:opacity-100 transition-opacity hover:bg-red-600"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
              </button>
            </div>
          </div>

          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">
              ♿ Texte alternatif
            </label>
            <input
              v-model="form.alt_text"
              type="text"
              class="w-full px-4 py-2.5 border-2 border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#FAB133] focus:border-transparent transition-all duration-300"
              placeholder="Description de l'image"
            />
            <p class="mt-1 text-xs text-gray-500">Pour l'accessibilité</p>
          </div>
        </div>

        <!-- Feedback -->
        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-2">
            💡 Feedback (optionnel)
          </label>
          <textarea
            v-model="form.explanation"
            rows="2"
            class="w-full px-4 py-2.5 border-2 border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#FAB133] focus:border-transparent transition-all duration-300 resize-none"
            placeholder="Explication pour l'étudiant..."
          />
        </div>

        <!-- Options pour QCM -->
        <div v-if="showOptions" class="space-y-3">
          <div class="flex justify-between items-center">
            <label class="block text-sm font-semibold text-gray-700">
              ✓ Options de réponse
            </label>
            <button
              type="button"
              @click="addOption"
              class="px-3 py-1.5 bg-gradient-to-r from-[#FAB133] to-[#F19F0D] text-white text-sm rounded-lg hover:from-[#F19F0D] hover:to-[#FAB133]-700 transition-all duration-300 shadow-md hover:shadow-lg transform hover:-translate-y-0.5"
            >
              + Ajouter
            </button>
          </div>

          <div v-for="(option, idx) in form.options" :key="idx" class="bg-gradient-to-br from-gray-50 to-gray-100 p-4 rounded-xl border-2 border-gray-200 hover:border-[#F9CF8D] transition-all duration-300 animate-slideIn">
            <div class="flex items-start gap-3 mb-2">
              <label class="relative flex items-center cursor-pointer group">
                <input
                  type="checkbox"
                  v-model="option.is_correct"
                  class="sr-only peer"
                />
                <div class="w-6 h-6 bg-white border-2 border-gray-300 rounded-md peer-checked:bg-gradient-to-r peer-checked:from-green-400 peer-checked:to-green-500 peer-checked:border-green-500 transition-all duration-300 flex items-center justify-center">
                  <svg v-if="option.is_correct" class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                  </svg>
                </div>
              </label>

              <input
                v-model="option.option_text"
                type="text"
                placeholder="Texte de l'option"
                class="flex-1 px-3 py-2 border-2 border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#FAB133] focus:border-transparent transition-all duration-300"
              />

              <button
                type="button"
                @click="removeOption(idx)"
                class="p-2 text-red-500 hover:bg-red-50 rounded-lg transition-all duration-300 hover:scale-110"
              >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
              </button>
            </div>

            <!-- Image pour option -->
            <div class="ml-9">
              <input
                type="file"
                accept="image/*"
                @change="e => handleOptionImage(e, idx)"
                :ref="`optionImage${idx}`"
                class="hidden"
              />
              <button
                type="button"
                @click="$refs[`optionImage${idx}`][0].click()"
                class="text-xs px-3 py-1.5 border border-gray-300 rounded-lg hover:border-[#FAB133] hover:bg-[#FEF7EC] transition-all duration-300 text-gray-600 hover:text-[#C17F0A]"
              >
                {{ option.image || option.image_path ? '✓ Image' : '+ Image' }}
              </button>
              <div v-if="option.imagePreview || option.image_path" class="mt-2 relative group inline-block">
                <img
                  :src="option.imagePreview || `/storage/${option.image_path}`"
                  alt="Option"
                  class="h-20 rounded-lg shadow transition-transform group-hover:scale-105"
                />
                <button
                  type="button"
                  @click="removeOptionImage(idx)"
                  class="absolute -top-2 -right-2 p-1 bg-red-500 text-white rounded-full opacity-0 group-hover:opacity-100 transition-opacity"
                >
                  <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                  </svg>
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Réponse correcte pour questions ouvertes -->
        <div v-if="form.type === 'short_answer' || form.type === 'essay'" class="animate-fadeIn">
          <label class="block text-sm font-semibold text-gray-700 mb-2">
            ✍️ Exemple de réponse correcte
          </label>
          <textarea
            v-model="form.correct_answer"
            rows="3"
            class="w-full px-4 py-2.5 border-2 border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#FAB133] focus:border-transparent transition-all duration-300 resize-none"
            placeholder="Exemple de réponse attendue..."
          />
        </div>

        <!-- Boutons d'action -->
        <div class="flex gap-3 pt-6 border-t-2 border-gray-100">
          <button
            type="submit"
            :disabled="isSubmitting"
            class="flex-1 px-6 py-3 bg-gradient-to-r from-[#FAB133] to-[#F19F0D] text-white font-semibold rounded-xl hover:from-[#F19F0D] hover:to-[#FAB133]-700 focus:outline-none focus:ring-4 focus:ring-[#F9CF8D] transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none"
          >
            <span v-if="isSubmitting" class="flex items-center justify-center">
              <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
              </svg>
              Enregistrement...
            </span>
            <span v-else>{{ form.id ? '💾 Mettre à jour' : '✨ Créer la question' }}</span>
          </button>
          <button
            v-if="form.id"
            type="button"
            @click="handleCancel"
            class="px-6 py-3 bg-gray-200 text-gray-700 font-semibold rounded-xl hover:bg-gray-300 transition-all duration-300 shadow-md hover:shadow-lg"
          >
            Annuler
          </button>
        </div>
      </form>
    </div>

    <!-- Prévisualisation -->
    <div :class="['bg-gradient-to-br from-[#FEF7EC] to-[#FDEFD9] rounded-lg shadow-lg p-6 transition-all duration-300 sticky top-6 h-fit', {'hidden lg:block': !showPreview, 'block': showPreview}]">
      <h3 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
        <svg class="w-6 h-6 mr-2 text-[#F19F0D]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
        </svg>
        Aperçu en temps réel
      </h3>

      <div class="bg-white rounded-xl p-5 shadow-inner space-y-4">
        <div class="flex items-start justify-between">
          <div class="flex-1">
            <div class="flex items-center gap-3 mb-3">
              <span class="px-3 py-1 bg-gradient-to-r from-[#FAB133] to-[#F19F0D] text-white text-xs font-semibold rounded-full shadow">
                {{ getTypeLabel(form.type) }}
              </span>
              <span class="px-3 py-1 bg-gradient-to-r from-green-500 to-teal-500 text-white text-xs font-semibold rounded-full shadow">
                {{ form.points }} pt{{ form.points > 1 ? 's' : '' }}
              </span>
            </div>
            <div v-if="form.question_text" v-html="formatText(form.question_text)" class="text-gray-900 text-base leading-relaxed"/>
            <p v-else class="text-gray-400 italic">Saisissez une question...</p>
          </div>
        </div>

        <div v-if="imagePreview || form.image_path" class="animate-fadeIn">
          <img
            :src="imagePreview || `/storage/${form.image_path}`"
            :alt="form.alt_text || 'Image de la question'"
            class="w-full max-w-md mx-auto rounded-lg shadow-lg"
          />
          <p v-if="form.alt_text" class="text-xs text-gray-500 mt-2 text-center italic">{{ form.alt_text }}</p>
        </div>

        <div v-if="showOptions && form.options.length > 0" class="space-y-2 animate-fadeIn">
          <p class="text-sm font-semibold text-gray-700 mb-2">Options :</p>
          <div v-for="(option, idx) in form.options" :key="idx" :class="['p-3 rounded-lg border-2 transition-all duration-300', option.is_correct ? 'bg-green-50 border-green-400' : 'bg-gray-50 border-gray-200']">
            <div class="flex items-center gap-2">
              <span v-if="option.is_correct" class="text-green-600 font-bold">✓</span>
              <span :class="[option.is_correct ? 'font-semibold text-green-800' : 'text-gray-700']">
                {{ option.option_text || `Option ${idx + 1}` }}
              </span>
            </div>
            <div v-if="option.imagePreview || option.image_path" class="mt-2">
              <img
                :src="option.imagePreview || `/storage/${option.image_path}`"
                alt="Option"
                class="h-20 rounded-lg shadow"
              />
            </div>
          </div>
        </div>

        <div v-if="form.correct_answer && (form.type === 'short_answer' || form.type === 'essay')" class="p-3 bg-blue-50 border-l-4 border-blue-400 rounded animate-fadeIn">
          <p class="text-xs font-semibold text-blue-800 mb-1">Exemple de réponse :</p>
          <p class="text-sm text-blue-700">{{ form.correct_answer }}</p>
        </div>

        <div v-if="form.explanation" class="p-3 bg-[#FEF7EC] border-l-4 border-[#FAB133] rounded animate-fadeIn">
          <p class="text-xs font-semibold text-[#916008] mb-1">💡 Feedback :</p>
          <p class="text-sm text-[#C17F0A]">{{ form.explanation }}</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
  question: Object,
  examId: {
    type: [String, Number],
    required: true,
  },
});

const emit = defineEmits(['saved', 'cancel']);

const typeOptions = [
  { value: 'multiple_choice', label: 'QCM (choix unique)' },
  { value: 'multiple_answers', label: 'Choix multiples' },
  { value: 'short_answer', label: 'Question ouverte courte' },
  { value: 'true_false', label: 'Vrai/Faux' },
  { value: 'essay', label: 'Question ouverte longue' },
];

const form = ref({
  id: null,
  type: 'multiple_choice',
  question_text: '',
  points: 1,
  explanation: '',
  alt_text: '',
  image: null,
  image_path: '',
  correct_answer: '',
  options: [],
});

const imagePreview = ref(null);
const isSubmitting = ref(false);
const showPreview = ref(true);
const errors = ref({});

const showOptions = computed(() => {
  return ['multiple_choice', 'multiple_answers', 'true_false'].includes(form.value.type);
});

watch(() => props.question, (val) => {
  if (val) {
    form.value = {
      ...val,
      options: val.options || [],
      image: null,
    };
    imagePreview.value = null;
  } else {
    resetForm();
  }
}, { immediate: true });

watch(() => form.value.type, (newType) => {
  if (newType === 'true_false' && form.value.options.length === 0) {
    form.value.options = [
      { option_text: 'Vrai', is_correct: false, option_key: 'true' },
      { option_text: 'Faux', is_correct: false, option_key: 'false' },
    ];
  }
});

function resetForm() {
  form.value = {
    id: null,
    type: 'multiple_choice',
    question_text: '',
    points: 1,
    explanation: '',
    alt_text: '',
    image: null,
    image_path: '',
    correct_answer: '',
    options: [],
  };
  imagePreview.value = null;
}

function handleImageUpload(e) {
  const file = e.target.files[0];
  if (file) {
    form.value.image = file;
    const reader = new FileReader();
    reader.onload = (ev) => {
      imagePreview.value = ev.target.result;
    };
    reader.readAsDataURL(file);
  }
}

function handleOptionImage(e, idx) {
  const file = e.target.files[0];
  if (file) {
    form.value.options[idx].image = file;
    const reader = new FileReader();
    reader.onload = (ev) => {
      form.value.options[idx].imagePreview = ev.target.result;
    };
    reader.readAsDataURL(file);
  }
}

function addOption() {
  form.value.options.push({
    option_text: '',
    is_correct: false,
    image: null,
    imagePreview: null,
  });
}

function removeOption(idx) {
  form.value.options.splice(idx, 1);
}

function handleCancel() {
  resetForm();
  emit('cancel');
}

function togglePreview() {
  showPreview.value = !showPreview.value;
}

function removeImage() {
  form.value.image = null;
  form.value.image_path = '';
  imagePreview.value = null;
}

function removeOptionImage(idx) {
  form.value.options[idx].image = null;
  form.value.options[idx].image_path = '';
  form.value.options[idx].imagePreview = null;
}

function getTypeLabel(type) {
  const labels = {
    multiple_choice: 'QCM (choix unique)',
    multiple_answers: 'Choix multiples',
    short_answer: 'Question ouverte courte',
    true_false: 'Vrai/Faux',
    essay: 'Question ouverte longue',
  };
  return labels[type] || type;
}

function formatText(text) {
  if (!text) return '';
  return text
    .replace(/\*\*(.*?)\*\*/g, '<strong class="font-bold text-gray-900">$1</strong>')
    .replace(/\*(.*?)\*/g, '<em class="italic text-gray-700">$1</em>');
}

function handleSubmit() {
  isSubmitting.value = true;
  errors.value = {}; // Réinitialiser les erreurs

  const formData = new FormData();
  formData.append('exam_id', props.examId);
  formData.append('type', form.value.type);
  formData.append('question_text', form.value.question_text);
  formData.append('points', form.value.points);
  formData.append('explanation', form.value.explanation || '');
  formData.append('alt_text', form.value.alt_text || '');

  if (form.value.image) {
    formData.append('image', form.value.image);
  }

  if (form.value.correct_answer) {
    formData.append('correct_answer', form.value.correct_answer);
  }

  if (showOptions.value && form.value.options.length > 0) {
    formData.append('options', JSON.stringify(
      form.value.options.map(opt => ({
        option_text: opt.option_text,
        is_correct: opt.is_correct,
        option_key: opt.option_key || null,
      }))
    ));

    // Ajouter les images des options si présentes
    form.value.options.forEach((opt, idx) => {
      if (opt.image) {
        formData.append(`option_image_${idx}`, opt.image);
      }
    });
  }

  const url = form.value.id
    ? `/admin/questions/${form.value.id}`
    : `/admin/exams/${props.examId}/questions`;

  if (form.value.id) {
    formData.append('_method', 'PUT');
  }

  router.post(url, formData, {
    forceFormData: true,
    preserveState: true,
    preserveScroll: true,
    onSuccess: () => {
      isSubmitting.value = false;
      errors.value = {};
      resetForm();
      emit('saved');
    },
    onError: (responseErrors) => {
      isSubmitting.value = false;
      errors.value = responseErrors;
      console.error('Erreur lors de la sauvegarde:', responseErrors);
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

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(-10px); }
  to { opacity: 1; transform: translateY(0); }
}

@keyframes slideIn {
  from { opacity: 0; transform: translateX(-20px); }
  to { opacity: 1; transform: translateX(0); }
}

.animate-shake {
  animation: shake 0.5s;
}

.animate-fadeIn {
  animation: fadeIn 0.3s ease-out;
}

.animate-slideIn {
  animation: slideIn 0.3s ease-out;
}
</style>
