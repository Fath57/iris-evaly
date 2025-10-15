<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AIPrompt;
use App\Models\User;

class AIPromptsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the first admin user, or create one if none exists
        $admin = User::role('admin')->first();
        
        if (!$admin) {
            $admin = User::role('teacher')->first();
        }
        
        if (!$admin) {
            $admin = User::first();
        }

        if (!$admin) {
            $this->command->warn('No users found. Please create users first.');
            return;
        }

        $prompts = [
            // ChatGPT Prompts
            [
                'name' => 'QCM Standard - ChatGPT',
                'description' => 'Template standard pour générer des questions à choix multiples',
                'ai_provider' => 'chatgpt',
                'question_type' => 'multiple_choice',
                'difficulty_level' => 'medium',
                'is_default' => true,
                'prompt_template' => 'Créez {questions_count} questions à choix multiples sur le thème "{subject_theme}" avec un niveau de difficulté {difficulty_level}.

Pour chaque question, fournissez :
- Une question claire et précise
- 4 options de réponse (A, B, C, D)
- Une seule réponse correcte
- Une explication de la réponse correcte
- Une catégorie/sous-thème

Format de réponse attendu en JSON :
{
  "questions": [
    {
      "question_text": "Texte de la question",
      "type": "multiple_choice",
      "difficulty_level": "{difficulty_level}",
      "points": 1,
      "category": "Catégorie",
      "options": [
        {"option_text": "Option A", "option_key": "A", "is_correct": false},
        {"option_text": "Option B", "option_key": "B", "is_correct": true},
        {"option_text": "Option C", "option_key": "C", "is_correct": false},
        {"option_text": "Option D", "option_key": "D", "is_correct": false}
      ],
      "explanation": "Explication de la réponse correcte"
    }
  ]
}',
                'created_by' => $admin->id,
                'is_active' => true,
            ],
            [
                'name' => 'Vrai/Faux - ChatGPT',
                'description' => 'Template pour générer des questions Vrai/Faux',
                'ai_provider' => 'chatgpt',
                'question_type' => 'true_false',
                'difficulty_level' => 'easy',
                'is_default' => true,
                'prompt_template' => 'Créez {questions_count} questions de type Vrai/Faux sur le thème "{subject_theme}" avec un niveau de difficulté {difficulty_level}.

Pour chaque question, fournissez :
- Une affirmation claire
- La réponse correcte (Vrai ou Faux)
- Une explication détaillée

Format de réponse attendu en JSON :
{
  "questions": [
    {
      "question_text": "Affirmation à évaluer",
      "type": "true_false",
      "difficulty_level": "{difficulty_level}",
      "points": 1,
      "category": "Catégorie",
      "options": [
        {"option_text": "Vrai", "option_key": "A", "is_correct": true},
        {"option_text": "Faux", "option_key": "B", "is_correct": false}
      ],
      "explanation": "Explication de pourquoi c\'est vrai ou faux"
    }
  ]
}',
                'created_by' => $admin->id,
                'is_active' => true,
            ],
            
            // Gemini Prompts
            [
                'name' => 'QCM Standard - Gemini',
                'description' => 'Template standard pour Gemini',
                'ai_provider' => 'gemini',
                'question_type' => 'multiple_choice',
                'difficulty_level' => 'medium',
                'is_default' => true,
                'prompt_template' => 'En tant qu\'expert pédagogique, générez {questions_count} questions à choix multiples de niveau {difficulty_level} sur le sujet : {subject_theme}

Critères :
- Questions précises et sans ambiguïté
- 4 options dont une seule correcte
- Distracteurs plausibles
- Explication pédagogique de la réponse

Répondez au format JSON suivant :
{
  "questions": [
    {
      "question_text": "Question",
      "type": "multiple_choice",
      "difficulty_level": "{difficulty_level}",
      "points": 1,
      "category": "Sous-thème",
      "options": [
        {"option_text": "Option A", "option_key": "A", "is_correct": false},
        {"option_text": "Option B", "option_key": "B", "is_correct": true},
        {"option_text": "Option C", "option_key": "C", "is_correct": false},
        {"option_text": "Option D", "option_key": "D", "is_correct": false}
      ],
      "explanation": "Pourquoi cette réponse est correcte"
    }
  ]
}',
                'created_by' => $admin->id,
                'is_active' => true,
            ],
            
            // Perplexity Prompts
            [
                'name' => 'QCM Avancé - Perplexity',
                'description' => 'Template avancé pour Perplexity avec contexte étendu',
                'ai_provider' => 'perplexity',
                'question_type' => 'multiple_choice',
                'difficulty_level' => 'hard',
                'is_default' => true,
                'prompt_template' => 'Utilisez vos connaissances les plus récentes pour créer {questions_count} questions à choix multiples de haut niveau (difficulté : {difficulty_level}) sur : {subject_theme}

Exigences :
- Questions qui testent la compréhension approfondie
- Options réalistes basées sur des erreurs communes
- Contexte actuel et pertinent
- Explications détaillées avec références si possible

Format JSON requis :
{
  "questions": [
    {
      "question_text": "Question élaborée",
      "type": "multiple_choice",
      "difficulty_level": "{difficulty_level}",
      "points": 2,
      "category": "Catégorie spécifique",
      "options": [
        {"option_text": "Option A", "option_key": "A", "is_correct": false},
        {"option_text": "Option B", "option_key": "B", "is_correct": true},
        {"option_text": "Option C", "option_key": "C", "is_correct": false},
        {"option_text": "Option D", "option_key": "D", "is_correct": false}
      ],
      "explanation": "Explication détaillée avec contexte"
    }
  ]
}',
                'created_by' => $admin->id,
                'is_active' => true,
            ],
            
            // Questions ouvertes
            [
                'name' => 'Questions Courtes - ChatGPT',
                'description' => 'Template pour questions à réponse courte',
                'ai_provider' => 'chatgpt',
                'question_type' => 'short_answer',
                'difficulty_level' => 'medium',
                'is_default' => true,
                'prompt_template' => 'Créez {questions_count} questions à réponse courte sur "{subject_theme}" de niveau {difficulty_level}.

Chaque question doit :
- Demander une réponse précise (mot, phrase courte, calcul)
- Avoir une réponse claire et vérifiable
- Inclure des critères de correction

Format JSON :
{
  "questions": [
    {
      "question_text": "Question nécessitant une réponse courte",
      "type": "short_answer",
      "difficulty_level": "{difficulty_level}",
      "points": 2,
      "category": "Catégorie",
      "correct_answer_text": "Réponse attendue",
      "explanation": "Critères d\'acceptation de la réponse"
    }
  ]
}',
                'created_by' => $admin->id,
                'is_active' => true,
            ],
        ];

        foreach ($prompts as $prompt) {
            AIPrompt::create($prompt);
        }

        $this->command->info('AI Prompts seeded successfully!');
        $this->command->info('Created ' . count($prompts) . ' default prompts.');
    }
}
