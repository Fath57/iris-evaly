<?php

namespace App\Services;

use App\Models\AIPrompt;
use App\Models\AIGenerationHistory;
use App\Models\Question;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AIQuestionGeneratorService
{
    protected $openaiApiKey;
    protected $geminiApiKey;
    protected $openaiUrl;
    protected $geminiUrl;
    protected $defaultProvider;

    public function __construct()
    {
        $this->openaiApiKey = config('services.openai.api_key');
        $this->geminiApiKey = config('services.gemini.api_key');
        $this->openaiUrl = 'https://api.openai.com/v1/chat/completions';
        $this->geminiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash-latest:generateContent';
        $this->defaultProvider = config('app.default_ai_provider', 'openai');
    }

    /**
     * Génère des questions à partir d'un sujet/thème
     */
    public function generateQuestions(array $data)
    {
        $subject = $data['subject'] ?? '';
        $topic = $data['topic'] ?? '';
        $questionType = $data['question_type'] ?? 'multiple_choice';
        $difficulty = $data['difficulty'] ?? 'medium';
        $numberOfQuestions = $data['number_of_questions'] ?? 5;
        $language = $data['language'] ?? 'fr';
        $provider = $data['ai_provider'] ?? $this->defaultProvider;

        // Vérifier si le provider sélectionné a une clé API configurée
        if (!$this->isProviderAvailable($provider)) {
            Log::warning("AI provider {$provider} not configured. Trying fallback or demo mode.");
            return $this->handleUnavailableProvider($data);
        }

        // Récupérer ou créer le prompt approprié
        $prompt = $this->getOrCreatePrompt($questionType, $difficulty, $provider);

        // Construire le prompt pour l'IA
        $systemPrompt = $this->buildSystemPrompt($questionType, $difficulty, $language);
        $userPrompt = $this->buildUserPrompt($subject, $topic, $numberOfQuestions, $questionType);

        try {
            $result = $this->callAIProvider($provider, $systemPrompt, $userPrompt);

            if ($result['success']) {
                // Parser la réponse
                $questions = $this->parseAIResponse($result['content'], $questionType);

                // Enregistrer l'historique de génération
                $this->recordGenerationHistory($prompt, $data, $questions, true, null, $provider, $result['tokens_used']);

                return [
                    'success' => true,
                    'questions' => $questions,
                    'tokens_used' => $result['tokens_used'],
                    'provider' => $provider,
                ];
            }

            throw new \Exception('API call failed: ' . $result['error']);

        } catch (\Exception $e) {
            Log::error("AI Question Generation Error ({$provider}): " . $e->getMessage());

            // Enregistrer l'échec
            $this->recordGenerationHistory($prompt, $data, [], false, $e->getMessage(), $provider);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'provider' => $provider,
            ];
        }
    }

    /**
     * Vérifie si un provider est disponible
     */
    protected function isProviderAvailable($provider)
    {
        switch ($provider) {
            case 'openai':
                return !empty($this->openaiApiKey);
            case 'gemini':
                return !empty($this->geminiApiKey);
            default:
                return false;
        }
    }

    /**
     * Gère le cas où le provider n'est pas disponible
     */
    protected function handleUnavailableProvider($data)
    {
        // Essayer les providers alternatifs
        $providers = ['gemini', 'openai']; // Gemini en premier car plus fiable
        $requestedProvider = $data['ai_provider'] ?? $this->defaultProvider;

        foreach ($providers as $provider) {
            if ($provider !== $requestedProvider && $this->isProviderAvailable($provider)) {
                Log::info("Switching to {$provider} as fallback from {$requestedProvider}");
                $data['ai_provider'] = $provider;
                return $this->generateQuestions($data);
            }
        }

        // Si aucun provider n'est disponible, utiliser le mode démo
        Log::warning('No AI provider configured. Using demo mode.');
        return $this->generateDemoQuestions($data);
    }

    /**
     * Appelle le provider d'IA sélectionné
     */
    protected function callAIProvider($provider, $systemPrompt, $userPrompt)
    {
        switch ($provider) {
            case 'openai':
                return $this->callOpenAI($systemPrompt, $userPrompt);
            case 'gemini':
                return $this->callGemini($systemPrompt, $userPrompt);
            default:
                throw new \Exception("Provider {$provider} not supported");
        }
    }

    /**
     * Appelle l'API OpenAI
     */
    protected function callOpenAI($systemPrompt, $userPrompt)
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->openaiApiKey,
            'Content-Type' => 'application/json',
        ])->timeout(60)->post($this->openaiUrl, [
            'model' => 'gpt-3.5-turbo',
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $userPrompt],
            ],
            'temperature' => 0.7,
            'max_tokens' => 2000,
        ]);

        if ($response->successful()) {
            $data = $response->json();
            return [
                'success' => true,
                'content' => $data['choices'][0]['message']['content'],
                'tokens_used' => $data['usage']['total_tokens'] ?? 0,
            ];
        }

        return [
            'success' => false,
            'error' => $response->body(),
        ];
    }

    /**
     * Appelle l'API Gemini
     */
    protected function callGemini($systemPrompt, $userPrompt)
    {
        $fullPrompt = $systemPrompt . "\n\n" . $userPrompt;

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->timeout(60)->post($this->geminiUrl . '?key=' . $this->geminiApiKey, [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $fullPrompt]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => 0.7,
                'maxOutputTokens' => 2000,
            ]
        ]);

        if ($response->successful()) {
            $data = $response->json();
            $content = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';

            return [
                'success' => true,
                'content' => $content,
                'tokens_used' => $data['usageMetadata']['totalTokenCount'] ?? 0,
            ];
        }

        return [
            'success' => false,
            'error' => $response->body(),
        ];
    }

    /**
     * Construit le prompt système
     */
    protected function buildSystemPrompt($questionType, $difficulty, $language)
    {
        $prompts = [
            'multiple_choice' => "Tu es un expert en création de QCM éducatifs. Tu génères des questions à choix unique de qualité avec 4 options dont une seule est correcte. Fournis toujours une explication claire pour la bonne réponse.",
            'multiple_answers' => "Tu es un expert en création de QCM éducatifs. Tu génères des questions à choix multiples où plusieurs réponses peuvent être correctes. Fournis toujours une explication claire.",
            'true_false' => "Tu es un expert en création de questions Vrai/Faux éducatives. Tu génères des affirmations claires qui peuvent être vraies ou fausses avec des explications.",
            'short_answer' => "Tu es un expert en création de questions ouvertes courtes. Tu génères des questions qui nécessitent une réponse courte et précise.",
            'essay' => "Tu es un expert en création de questions de dissertation. Tu génères des questions qui nécessitent une réponse développée et argumentée.",
        ];

        $difficultyText = [
            'easy' => 'facile',
            'medium' => 'moyen',
            'hard' => 'difficile',
        ];

        $basePrompt = $prompts[$questionType] ?? $prompts['multiple_choice'];
        $basePrompt .= "\n\nNiveau de difficulté : " . ($difficultyText[$difficulty] ?? 'moyen');
        $basePrompt .= "\nLangue : " . ($language === 'fr' ? 'français' : 'anglais');
        $basePrompt .= "\n\nRéponds UNIQUEMENT avec un JSON valide, sans texte avant ou après, au format suivant :";
        $basePrompt .= "\n" . $this->getJsonFormat($questionType);

        return $basePrompt;
    }

    /**
     * Construit le prompt utilisateur
     */
    protected function buildUserPrompt($subject, $topic, $numberOfQuestions, $questionType)
    {
        $prompt = "Génère {$numberOfQuestions} question(s)";

        if ($subject) {
            $prompt .= " sur le sujet : {$subject}";
        }

        if ($topic) {
            $prompt .= ", thème spécifique : {$topic}";
        }

        $prompt .= ".\n\nAssure-toi que :";
        $prompt .= "\n- Les questions sont claires et sans ambiguïté";
        $prompt .= "\n- Les options de réponse sont plausibles";
        $prompt .= "\n- Les explications sont pédagogiques";
        $prompt .= "\n- Le contenu est adapté au niveau demandé";

        return $prompt;
    }

    /**
     * Retourne le format JSON attendu
     */
    protected function getJsonFormat($questionType)
    {
        $formats = [
            'multiple_choice' => '{
  "questions": [
    {
      "question_text": "Texte de la question",
      "points": 1,
      "options": [
        {"option_text": "Option A", "is_correct": false},
        {"option_text": "Option B", "is_correct": true},
        {"option_text": "Option C", "is_correct": false},
        {"option_text": "Option D", "is_correct": false}
      ],
      "explanation": "Explication de la bonne réponse"
    }
  ]
}',
            'multiple_answers' => '{
  "questions": [
    {
      "question_text": "Texte de la question",
      "points": 2,
      "options": [
        {"option_text": "Option A", "is_correct": true},
        {"option_text": "Option B", "is_correct": true},
        {"option_text": "Option C", "is_correct": false},
        {"option_text": "Option D", "is_correct": false}
      ],
      "explanation": "Explication des bonnes réponses"
    }
  ]
}',
            'true_false' => '{
  "questions": [
    {
      "question_text": "Affirmation à évaluer",
      "points": 1,
      "options": [
        {"option_text": "Vrai", "is_correct": true},
        {"option_text": "Faux", "is_correct": false}
      ],
      "explanation": "Explication"
    }
  ]
}',
            'short_answer' => '{
  "questions": [
    {
      "question_text": "Question ouverte",
      "points": 2,
      "correct_answer": "Exemple de réponse attendue",
      "explanation": "Critères d\'évaluation"
    }
  ]
}',
        ];

        return $formats[$questionType] ?? $formats['multiple_choice'];
    }

    /**
     * Parse la réponse de l'IA
     */
    protected function parseAIResponse($content, $questionType)
    {
        // Nettoyer le contenu (enlever les ```json si présent)
        $content = preg_replace('/```json\s*/', '', $content);
        $content = preg_replace('/```\s*/', '', $content);
        $content = trim($content);

        // Correction spécifique pour Gemini : enlever les backslashes d'échappement incorrects
        // Gemini ajoute parfois des \" au lieu de " dans le JSON
        $content = str_replace('\"', '"', $content);

        // Nettoyer les doubles guillemets échappés de manière incorrecte
        $content = preg_replace('/\\\\"/', '"', $content);

        // 🔧 NOUVEAU : Remplacer les guillemets simples par des guillemets doubles dans les valeurs JSON
        // Gemini utilise parfois 'texte' au lieu de "texte"
        // On doit faire attention à ne pas remplacer les apostrophes dans le texte (comme "l'homme")
        // Pattern: remplace 'valeur' par "valeur" quand c'est une valeur JSON (après : ou dans un tableau)
        $content = preg_replace('/:\s*\'([^\']*)\'/u', ': "$1"', $content);
        $content = preg_replace('/\[\s*\'([^\']*)\'/u', '["$1"', $content);
        $content = preg_replace('/,\s*\'([^\']*)\'/u', ', "$1"', $content);

        try {
            $data = json_decode($content, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                // Log plus détaillé pour debugging
                Log::error('JSON decode error: ' . json_last_error_msg());
                Log::error('Response content after cleanup: ' . substr($content, 0, 1000) . '...');
                throw new \Exception('Invalid JSON response from AI: ' . json_last_error_msg());
            }

            return $data['questions'] ?? [];
        } catch (\Exception $e) {
            Log::error('Error parsing AI response: ' . $e->getMessage());

            // Tentative de réparation plus agressive du JSON
            $fixedContent = $this->aggressiveJsonFix($content);
            try {
                $data = json_decode($fixedContent, true);
                if (json_last_error() === JSON_ERROR_NONE && isset($data['questions'])) {
                    Log::info('JSON repaired successfully with aggressive fix');
                    return $data['questions'];
                }
            } catch (\Exception $e2) {
                Log::error('Aggressive fix also failed');
            }

            return [];
        }
    }

    /**
     * Tentative de réparation agressive du JSON mal formaté
     */
    protected function aggressiveJsonFix($content)
    {
        // Remplacer TOUS les guillemets simples par des doubles
        // mais préserver les apostrophes dans le texte

        // D'abord, protéger les apostrophes dans les contractions françaises
        $content = preg_replace("/([a-zàâäéèêëïîôùûüÿæœç])'([a-zàâäéèêëïîôùûüÿæœç])/ui", '$1APOSTROPHE$2', $content);

        // Remplacer tous les guillemets simples restants par des doubles
        $content = str_replace("'", '"', $content);

        // Restaurer les apostrophes
        $content = str_replace('APOSTROPHE', "'", $content);

        return $content;
    }

    /**
     * Récupère ou crée un prompt
     */
    protected function getOrCreatePrompt($questionType, $difficulty, $provider)
    {
        return AIPrompt::firstOrCreate(
            [
                'question_type' => $questionType,
                'difficulty_level' => $difficulty,
                'is_default' => true,
            ],
            [
                'name' => "Génération {$questionType} - {$difficulty}",
                'description' => "Prompt par défaut pour la génération de questions {$questionType} de niveau {$difficulty}",
                'prompt_template' => $this->buildSystemPrompt($questionType, $difficulty, 'fr'),
                'ai_provider' => $provider,
                'is_active' => true,
                'usage_count' => 0,
            ]
        );
    }

    /**
     * Enregistre l'historique de génération
     */
    protected function recordGenerationHistory($prompt, $inputData, $questions, $success, $error = null, $provider = 'openai', $tokensUsed = 0)
    {
        try {
            AIGenerationHistory::create([
                'prompt_id' => $prompt->id ?? null,
                'user_id' => auth()->id(),
                'ai_provider' => $provider,
                'subject_theme' => ($inputData['subject'] ?? '') . ($inputData['topic'] ? ' - ' . $inputData['topic'] : ''),
                'difficulty_level' => $inputData['difficulty'] ?? 'medium',
                'questions_requested' => $inputData['number_of_questions'] ?? 0,
                'questions_generated' => count($questions),
                'questions_accepted' => 0,
                'custom_prompt' => json_encode($inputData),
                'tokens_used' => $tokensUsed,
                'status' => $success ? 'completed' : 'failed',
                'error_message' => $error,
                'quality_rating' => null,
            ]);

            // Incrémenter le compteur d'utilisation du prompt
            if ($prompt) {
                $prompt->increment('usage_count');
            }
        } catch (\Exception $e) {
            Log::error('Error recording generation history: ' . $e->getMessage());
        }
    }

    /**
     * Génère des questions d'exemple en mode démo (sans API)
     */
    protected function generateDemoQuestions(array $data)
    {
        $subject = $data['subject'] ?? 'Sujet général';
        $topic = $data['topic'] ?? '';
        $questionType = $data['question_type'] ?? 'multiple_choice';
        $numberOfQuestions = min($data['number_of_questions'] ?? 3, 5);

        $questions = [];

        for ($i = 1; $i <= $numberOfQuestions; $i++) {
            switch ($questionType) {
                case 'multiple_choice':
                    $questions[] = [
                        'question_text' => "Question {$i} sur {$subject}" . ($topic ? " - {$topic}" : "") . " (Mode Démo)",
                        'points' => 1,
                        'options' => [
                            ['option_text' => 'Option A', 'is_correct' => true],
                            ['option_text' => 'Option B', 'is_correct' => false],
                            ['option_text' => 'Option C', 'is_correct' => false],
                            ['option_text' => 'Option D', 'is_correct' => false],
                        ],
                        'explanation' => "Ceci est une question d'exemple générée en mode démo. Configurez votre clé API OpenAI pour générer de vraies questions.",
                    ];
                    break;

                case 'multiple_answers':
                    $questions[] = [
                        'question_text' => "Question {$i} à choix multiples sur {$subject}" . ($topic ? " - {$topic}" : "") . " (Mode Démo)",
                        'points' => 2,
                        'options' => [
                            ['option_text' => 'Option A', 'is_correct' => true],
                            ['option_text' => 'Option B', 'is_correct' => true],
                            ['option_text' => 'Option C', 'is_correct' => false],
                            ['option_text' => 'Option D', 'is_correct' => false],
                        ],
                        'explanation' => "Exemple de question à choix multiples. Configurez l'API pour des vraies questions.",
                    ];
                    break;

                case 'true_false':
                    $questions[] = [
                        'question_text' => "Affirmation {$i} sur {$subject}" . ($topic ? " - {$topic}" : "") . " (Mode Démo)",
                        'points' => 1,
                        'options' => [
                            ['option_text' => 'Vrai', 'is_correct' => $i % 2 === 1],
                            ['option_text' => 'Faux', 'is_correct' => $i % 2 === 0],
                        ],
                        'explanation' => "Question Vrai/Faux d'exemple. Activez l'IA pour du contenu personnalisé.",
                    ];
                    break;

                case 'short_answer':
                    $questions[] = [
                        'question_text' => "Question ouverte {$i} sur {$subject}" . ($topic ? " - {$topic}" : "") . " (Mode Démo)",
                        'points' => 2,
                        'correct_answer' => "Exemple de réponse courte pour la question {$i}",
                        'explanation' => "Question ouverte d'exemple. Configurez OpenAI API pour générer de vraies questions.",
                    ];
                    break;

                case 'essay':
                    $questions[] = [
                        'question_text' => "Question de dissertation {$i} sur {$subject}" . ($topic ? " - {$topic}" : "") . " (Mode Démo)",
                        'points' => 5,
                        'correct_answer' => "Exemple de réponse développée pour la question {$i}",
                        'explanation' => "Question de dissertation d'exemple. Utilisez l'IA pour du contenu personnalisé.",
                    ];
                    break;
            }
        }

        return [
            'success' => true,
            'questions' => $questions,
            'tokens_used' => 0,
            'demo_mode' => true,
        ];
    }
}
