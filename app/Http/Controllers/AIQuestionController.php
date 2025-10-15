<?php

namespace App\Http\Controllers;

use App\Services\AIQuestionGeneratorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class AIQuestionController extends Controller
{
    protected $aiService;

    public function __construct(AIQuestionGeneratorService $aiService)
    {
        $this->aiService = $aiService;
    }

    /**
     * Génère des questions par IA
     */
    public function generateQuestions(Request $request)
    {
        $validated = $request->validate([
            'exam_id' => 'required|exists:exams,id',
            'subject' => 'required|string|max:255',
            'topic' => 'nullable|string|max:500',
            'question_type' => 'required|in:multiple_choice,multiple_answers,true_false,short_answer,essay',
            'difficulty' => 'required|in:easy,medium,hard',
            'number_of_questions' => 'required|integer|min:1|max:20',
            'language' => 'nullable|in:fr,en',
            'ai_provider' => 'nullable|in:openai,gemini',
        ]);

        try {
            $result = $this->aiService->generateQuestions($validated);

            if ($result['success']) {
                $message = 'Questions générées avec succès';
                if (isset($result['provider'])) {
                    $providerName = $result['provider'] === 'openai' ? 'OpenAI' : 'Gemini';
                    $message .= " avec {$providerName}";
                }
                if (isset($result['demo_mode']) && $result['demo_mode']) {
                    $message = 'Questions d\'exemple générées (mode démo)';
                }

                // Mettre les données directement dans la session flash
                session()->flash('generatedQuestions', $result['questions']);
                session()->flash('tokensUsed', $result['tokens_used'] ?? 0);
                session()->flash('provider', $result['provider'] ?? 'demo');
                session()->flash('success', $message);

                return redirect()->back();
            }

            // Gestion des erreurs
            $errorMessage = 'Erreur lors de la génération des questions';
            if (isset($result['error'])) {
                if (strpos($result['error'], 'insufficient_quota') !== false) {
                    $errorMessage = 'Quota OpenAI épuisé. Le système va essayer avec Gemini automatiquement.';
                } elseif (strpos($result['error'], 'model_not_found') !== false) {
                    $errorMessage = 'Modèle IA non accessible. Vérifiez votre configuration ou utilisez un autre provider.';
                } else {
                    $errorMessage .= ': ' . $result['error'];
                }
            }

            session()->flash('error', $errorMessage);
            return redirect()->back();

        } catch (\Exception $e) {
            Log::error('AI Question Generation Controller Error: ' . $e->getMessage());
            session()->flash('error', 'Erreur système lors de la génération des questions. Veuillez réessayer.');
            return redirect()->back();
        }
    }
}
