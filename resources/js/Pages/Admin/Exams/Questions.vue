<template>
  <AppLayout>
    <div class="p-4 sm:p-6 lg:p-8">
      <div class="mb-6 flex justify-between items-center">
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Gestion des questions</h1>
        <div class="flex gap-3">
          <Button @click="showAIModal = true" variant="success">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
            </svg>
            Générer avec l'IA
          </Button>
          <Button @click="$inertia.visit(`/admin/exams/${examId}`)" variant="secondary">
            Retour à l'examen
          </Button>
        </div>
      </div>

      <!-- Liste des questions -->
      <div v-if="questions.length" class="mb-8 space-y-4">
        <div v-for="(question, index) in questions" :key="question.id" class="bg-white rounded-lg shadow p-6">
          <div class="flex items-start justify-between">
            <div class="flex-1">
              <div class="flex items-center gap-4 mb-2">
                <Badge>{{ getTypeLabel(question.type) }}</Badge>
                <span class="text-sm text-gray-600">{{ question.points }} point(s)</span>
              </div>
              <div class="mt-2" v-html="formatText(question.question_text)" />
              <div v-if="question.image_path" class="mt-4">
                <img :src="`/storage/${question.image_path}`" :alt="question.alt_text" class="max-w-md rounded-lg shadow" />
              </div>
              <div v-if="question.explanation" class="mt-3 p-3 bg-blue-50 rounded text-sm text-gray-700">
                <span class="font-medium">Feedback :</span> {{ question.explanation }}
              </div>
            </div>
            <div class="flex gap-2 ml-4">
              <Button @click="editQuestion(question)" variant="secondary" size="sm">Éditer</Button>
              <Button @click="confirmDelete(question.id)" variant="danger" size="sm">Supprimer</Button>
            </div>
          </div>
        </div>
      </div>

      <div v-else class="bg-white rounded-lg shadow p-8 text-center text-gray-500 mb-8">
        <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <p class="text-lg font-medium mb-2">Aucune question pour le moment</p>
        <p class="text-sm mb-4">Commencez par créer une question ou utilisez l'IA pour en générer automatiquement</p>
        <Button @click="showAIModal = true" variant="primary">
          <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
          </svg>
          Générer avec l'IA
        </Button>
      </div>

      <!-- Formulaire de création/édition -->
      <QuestionForm
        :question="selectedQuestion"
        :examId="examId"
        @saved="handleSaved"
        @cancel="selectedQuestion = null"
      />

      <!-- Modal de confirmation -->
      <ConfirmModal
        :show="showDeleteModal"
        title="Supprimer cette question ?"
        message="Cette action est irréversible."
        @confirm="deleteQuestion"
        @cancel="showDeleteModal = false"
      />

      <!-- Modal de génération IA -->
      <AIQuestionGenerator
        :show="showAIModal"
        :examId="examId"
        @close="showAIModal = false"
        @questionsGenerated="handleAIQuestions"
      />

      <!-- Indicateur de création des questions IA -->
      <div v-if="isCreatingAIQuestions" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 max-w-md mx-4">
          <div class="flex items-center justify-center mb-4">
            <svg class="animate-spin -ml-1 mr-3 h-8 w-8 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span class="text-lg font-medium text-gray-900">Création des questions en cours...</span>
          </div>
          <p class="text-sm text-gray-600 text-center">
            Veuillez patienter pendant que nous créons toutes les questions générées par l'IA.
          </p>
        </div>
      </div>

      <!-- Option de création en masse pour les questions IA -->
      <div v-if="showBulkCreateOption" class="mt-4 p-4 bg-white rounded-lg shadow">
        <p class="text-sm text-gray-700 mb-2">
          Vous avez des questions générées par l'IA prêtes à être créées. Voulez-vous les créer toutes en une fois ?
        </p>
        <div class="flex gap-2">
          <Button @click="createAllAIQuestions" variant="primary" class="flex-1">
            Créer toutes les questions
          </Button>
          <Button @click="showBulkCreateOption = false" variant="secondary" class="flex-1">
            Non, merci
          </Button>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import QuestionForm from './QuestionForm.vue';
import Button from '@/Components/Button.vue';
import Badge from '@/Components/Badge.vue';
import ConfirmModal from '@/Components/ConfirmModal.vue';
import AIQuestionGenerator from '@/Components/AIQuestionGenerator.vue';

const props = defineProps({
  questions: {
    type: Array,
    default: () => [],
  },
  examId: {
    type: [String, Number],
    required: true,
  },
});

const selectedQuestion = ref(null);
const showDeleteModal = ref(false);
const showAIModal = ref(false);
const questionToDelete = ref(null);
const isCreatingAIQuestions = ref(false);
const aiGeneratedQuestions = ref([]);
const currentAIQuestionIndex = ref(0);
const showBulkCreateOption = ref(false);

function formatText(text) {
  // Formatage basique : gras **texte**, italique *texte*
  return text
    .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
    .replace(/\*(.*?)\*/g, '<em>$1</em>');
}

function getTypeLabel(type) {
  const labels = {
    multiple_choice: 'QCM (choix unique)',
    multiple_answers: 'Choix multiples',
    short_answer: 'Question ouverte courte',
    true_false: 'Vrai/Faux',
    essay: 'Question ouverte',
  };
  return labels[type] || type;
}

function editQuestion(question) {
  selectedQuestion.value = { ...question };
}

function confirmDelete(id) {
  questionToDelete.value = id;
  showDeleteModal.value = true;
}

function deleteQuestion() {
  if (questionToDelete.value) {
    router.delete(`/admin/questions/${questionToDelete.value}`, {
      onSuccess: () => {
        showDeleteModal.value = false;
        questionToDelete.value = null;
      },
    });
  }
}

function handleSaved() {
  selectedQuestion.value = null;
  router.reload({ only: ['questions'] });
}

async function handleAIQuestions(generatedQuestions) {
  // Fermer le modal de génération AI
  showAIModal.value = false;

  // Si pas de questions générées, arrêter
  if (!generatedQuestions || generatedQuestions.length === 0) {
    return;
  }

  // Stocker toutes les questions générées pour pré-remplissage
  aiGeneratedQuestions.value = generatedQuestions.map((question, index) => {
    // Déterminer le type de question
    let questionType = 'multiple_choice';
    if (question.options) {
      const correctCount = question.options.filter(opt => opt.is_correct).length;
      if (question.options.length === 2) {
        questionType = 'true_false';
      } else if (correctCount > 1) {
        questionType = 'multiple_answers';
      }
    } else if (question.correct_answer) {
      questionType = question.correct_answer.length > 100 ? 'essay' : 'short_answer';
    }

    return {
      question_text: question.question_text,
      type: questionType,
      points: question.points || 1,
      explanation: question.explanation || '',
      correct_answer: question.correct_answer || '',
      options: question.options || [],
      is_from_ai: true,
      ai_question_number: index + 1,
      ai_total_questions: generatedQuestions.length,
    };
  });

  currentAIQuestionIndex.value = 0;

  // Pré-remplir le formulaire avec la première question
  if (aiGeneratedQuestions.value.length > 0) {
    selectedQuestion.value = { ...aiGeneratedQuestions.value[0] };

    // Ajouter un bouton pour créer toutes les questions en une fois
    showBulkCreateOption.value = true;
  }
}

// Fonction pour créer toutes les questions IA en une fois
async function createAllAIQuestions() {
  if (!aiGeneratedQuestions.value || aiGeneratedQuestions.value.length === 0) {
    return;
  }

  isCreatingAIQuestions.value = true;
  showBulkCreateOption.value = false;

  try {
    for (const question of aiGeneratedQuestions.value) {
      const questionData = {
        exam_id: props.examId,
        question_text: question.question_text,
        type: question.type,
        points: question.points,
        explanation: question.explanation,
        correct_answer: question.correct_answer,
        options: question.options,
      };

      await router.post(`/admin/exams/${props.examId}/questions`, questionData, {
        preserveState: true,
        preserveScroll: true,
        onError: (errors) => {
          console.error('Erreur lors de la création de la question:', errors);
        }
      });
    }

    // Nettoyer les données IA
    aiGeneratedQuestions.value = [];
    currentAIQuestionIndex.value = 0;
    selectedQuestion.value = null;

    // Recharger la liste des questions
    router.reload({ only: ['questions'] });
  } catch (error) {
    console.error('Erreur lors de la création des questions IA:', error);
  } finally {
    isCreatingAIQuestions.value = false;
  }
}
</script>
