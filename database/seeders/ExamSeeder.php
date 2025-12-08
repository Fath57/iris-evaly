<?php

namespace Database\Seeders;

use App\Models\ClassModel;
use App\Models\Exam;
use App\Models\Question;
use App\Models\QuestionAnswer;
use App\Models\QuestionOption;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Seeder;

class ExamSeeder extends Seeder
{
    public function run(): void
    {
        $teacher = User::whereHas('roles', function ($q) {
            $q->where('name', 'teacher');
        })->first();

        $mathSubject = Subject::where('code', 'MATH')->first();
        $frSubject = Subject::where('code', 'FR')->first();
        $ts1Class = ClassModel::where('code', 'TS1')->first();

        if (!$teacher || !$mathSubject || !$ts1Class) {
            $this->command->error('Missing required data (teacher, subject, or class)');
            return;
        }

        // Examen de Mathématiques
        $mathExam = Exam::firstOrCreate(
            ['title' => 'Examen de Mathématiques - Chapitre 1'],
            [
                'description' => 'Examen sur les fonctions et les limites',
                'instructions' => 'Répondez à toutes les questions. Utilisez une calculatrice si nécessaire.',
                'subject_id' => $mathSubject->id,
                'class_id' => $ts1Class->id,
                'created_by' => $teacher->id,
                'duration_minutes' => 60,
                'total_points' => 20,
                'passing_score' => 10,
                'start_date' => now()->subDay(),
                'end_date' => now()->addDays(7),
                'status' => 'published',
                'shuffle_questions' => false,
                'shuffle_options' => true,
                'show_results_immediately' => true,
                'allow_review' => true,
                'show_correct_answers' => true,
                'max_attempts' => 2,
            ]
        );

        // Questions pour l'examen de maths
        $this->createMathQuestions($mathExam);

        // Examen de Français si le sujet existe
        if ($frSubject) {
            $frenchExam = Exam::firstOrCreate(
                ['title' => 'Contrôle de Français - Grammaire'],
                [
                    'description' => 'Contrôle sur la conjugaison et la grammaire',
                    'instructions' => 'Lisez attentivement chaque question avant de répondre.',
                    'subject_id' => $frSubject->id,
                    'class_id' => $ts1Class->id,
                    'created_by' => $teacher->id,
                    'duration_minutes' => 45,
                    'total_points' => 20,
                    'passing_score' => 10,
                    'start_date' => now()->addDays(1),
                    'end_date' => now()->addDays(14),
                    'status' => 'published',
                    'shuffle_questions' => true,
                    'shuffle_options' => true,
                    'show_results_immediately' => false,
                    'allow_review' => false,
                    'show_correct_answers' => false,
                    'max_attempts' => 1,
                ]
            );

            $this->createFrenchQuestions($frenchExam);
        }

        $this->command->info('Exams created successfully!');
    }

    private function createMathQuestions(Exam $exam): void
    {
        if ($exam->questions()->count() > 0) {
            return;
        }

        $questions = [
            [
                'type' => 'multiple_choice',
                'question_text' => 'Quelle est la limite de f(x) = (x² - 1)/(x - 1) quand x tend vers 1 ?',
                'points' => 4,
                'order' => 1,
                'explanation' => 'En factorisant, f(x) = (x-1)(x+1)/(x-1) = x+1 pour x ≠ 1. Donc la limite est 2.',
                'difficulty_level' => 'medium',
                'options' => [
                    ['text' => '0', 'key' => 'A', 'is_correct' => false],
                    ['text' => '1', 'key' => 'B', 'is_correct' => false],
                    ['text' => '2', 'key' => 'C', 'is_correct' => true],
                    ['text' => 'La limite n\'existe pas', 'key' => 'D', 'is_correct' => false],
                ],
            ],
            [
                'type' => 'multiple_choice',
                'question_text' => 'Soit f(x) = 3x² - 6x + 2. Quelle est la dérivée f\'(x) ?',
                'points' => 4,
                'order' => 2,
                'explanation' => 'La dérivée de ax² est 2ax, donc f\'(x) = 6x - 6.',
                'difficulty_level' => 'easy',
                'options' => [
                    ['text' => '6x - 6', 'key' => 'A', 'is_correct' => true],
                    ['text' => '3x - 6', 'key' => 'B', 'is_correct' => false],
                    ['text' => '6x² - 6', 'key' => 'C', 'is_correct' => false],
                    ['text' => '6x + 2', 'key' => 'D', 'is_correct' => false],
                ],
            ],
            [
                'type' => 'true_false',
                'question_text' => 'La fonction f(x) = x² est croissante sur ℝ.',
                'points' => 2,
                'order' => 3,
                'explanation' => 'f(x) = x² est décroissante sur ]-∞, 0] et croissante sur [0, +∞[.',
                'difficulty_level' => 'easy',
                'options' => [
                    ['text' => 'Vrai', 'key' => 'A', 'is_correct' => false],
                    ['text' => 'Faux', 'key' => 'B', 'is_correct' => true],
                ],
            ],
            [
                'type' => 'multiple_choice',
                'question_text' => 'Quelle est la solution de l\'équation 2x + 5 = 11 ?',
                'points' => 3,
                'order' => 4,
                'explanation' => '2x = 11 - 5 = 6, donc x = 3.',
                'difficulty_level' => 'easy',
                'options' => [
                    ['text' => 'x = 2', 'key' => 'A', 'is_correct' => false],
                    ['text' => 'x = 3', 'key' => 'B', 'is_correct' => true],
                    ['text' => 'x = 4', 'key' => 'C', 'is_correct' => false],
                    ['text' => 'x = 8', 'key' => 'D', 'is_correct' => false],
                ],
            ],
            [
                'type' => 'multiple_choice',
                'question_text' => 'Quel est le discriminant de l\'équation x² - 5x + 6 = 0 ?',
                'points' => 4,
                'order' => 5,
                'explanation' => 'Δ = b² - 4ac = 25 - 24 = 1.',
                'difficulty_level' => 'medium',
                'options' => [
                    ['text' => '-1', 'key' => 'A', 'is_correct' => false],
                    ['text' => '0', 'key' => 'B', 'is_correct' => false],
                    ['text' => '1', 'key' => 'C', 'is_correct' => true],
                    ['text' => '49', 'key' => 'D', 'is_correct' => false],
                ],
            ],
            [
                'type' => 'true_false',
                'question_text' => 'Le nombre π (pi) est un nombre rationnel.',
                'points' => 3,
                'order' => 6,
                'explanation' => 'π est un nombre irrationnel, il ne peut pas s\'écrire sous forme de fraction.',
                'difficulty_level' => 'easy',
                'options' => [
                    ['text' => 'Vrai', 'key' => 'A', 'is_correct' => false],
                    ['text' => 'Faux', 'key' => 'B', 'is_correct' => true],
                ],
            ],
        ];

        $this->insertQuestions($exam, $questions);
    }

    private function createFrenchQuestions(Exam $exam): void
    {
        if ($exam->questions()->count() > 0) {
            return;
        }

        $questions = [
            [
                'type' => 'multiple_choice',
                'question_text' => 'Quel est le participe passé du verbe "prendre" ?',
                'points' => 4,
                'order' => 1,
                'explanation' => 'Le participe passé de "prendre" est "pris".',
                'difficulty_level' => 'easy',
                'options' => [
                    ['text' => 'prendu', 'key' => 'A', 'is_correct' => false],
                    ['text' => 'pris', 'key' => 'B', 'is_correct' => true],
                    ['text' => 'prenu', 'key' => 'C', 'is_correct' => false],
                    ['text' => 'prendre', 'key' => 'D', 'is_correct' => false],
                ],
            ],
            [
                'type' => 'multiple_choice',
                'question_text' => 'Dans la phrase "Les enfants jouent dans le jardin", quel est le sujet ?',
                'points' => 4,
                'order' => 2,
                'explanation' => 'Le sujet est "Les enfants" car c\'est le groupe qui fait l\'action de jouer.',
                'difficulty_level' => 'easy',
                'options' => [
                    ['text' => 'jouent', 'key' => 'A', 'is_correct' => false],
                    ['text' => 'le jardin', 'key' => 'B', 'is_correct' => false],
                    ['text' => 'Les enfants', 'key' => 'C', 'is_correct' => true],
                    ['text' => 'dans', 'key' => 'D', 'is_correct' => false],
                ],
            ],
            [
                'type' => 'true_false',
                'question_text' => 'Le mot "rapidement" est un adverbe.',
                'points' => 3,
                'order' => 3,
                'explanation' => 'Les mots en "-ment" formés à partir d\'adjectifs sont généralement des adverbes.',
                'difficulty_level' => 'easy',
                'options' => [
                    ['text' => 'Vrai', 'key' => 'A', 'is_correct' => true],
                    ['text' => 'Faux', 'key' => 'B', 'is_correct' => false],
                ],
            ],
            [
                'type' => 'multiple_choice',
                'question_text' => 'Conjuguez le verbe "finir" à la 3ème personne du pluriel au passé composé.',
                'points' => 5,
                'order' => 4,
                'explanation' => 'Au passé composé, "finir" donne "ont fini" à la 3ème personne du pluriel.',
                'difficulty_level' => 'medium',
                'options' => [
                    ['text' => 'ils finissent', 'key' => 'A', 'is_correct' => false],
                    ['text' => 'ils ont fini', 'key' => 'B', 'is_correct' => true],
                    ['text' => 'ils finissaient', 'key' => 'C', 'is_correct' => false],
                    ['text' => 'ils finirent', 'key' => 'D', 'is_correct' => false],
                ],
            ],
            [
                'type' => 'multiple_choice',
                'question_text' => 'Quel est le synonyme de "content" ?',
                'points' => 4,
                'order' => 5,
                'explanation' => '"Heureux" est synonyme de "content", ils expriment tous deux un état de satisfaction.',
                'difficulty_level' => 'easy',
                'options' => [
                    ['text' => 'triste', 'key' => 'A', 'is_correct' => false],
                    ['text' => 'heureux', 'key' => 'B', 'is_correct' => true],
                    ['text' => 'fâché', 'key' => 'C', 'is_correct' => false],
                    ['text' => 'fatigué', 'key' => 'D', 'is_correct' => false],
                ],
            ],
        ];

        $this->insertQuestions($exam, $questions);
    }

    private function insertQuestions(Exam $exam, array $questionsData): void
    {
        foreach ($questionsData as $questionData) {
            $options = $questionData['options'];
            unset($questionData['options']);

            $question = Question::create([
                'exam_id' => $exam->id,
                'type' => $questionData['type'],
                'question_text' => $questionData['question_text'],
                'points' => $questionData['points'],
                'order' => $questionData['order'],
                'explanation' => $questionData['explanation'] ?? null,
                'difficulty_level' => $questionData['difficulty_level'] ?? 'medium',
            ]);

            foreach ($options as $index => $optionData) {
                $option = QuestionOption::create([
                    'question_id' => $question->id,
                    'option_text' => $optionData['text'],
                    'option_key' => $optionData['key'],
                    'order' => $index + 1,
                ]);

                // Créer la réponse
                QuestionAnswer::create([
                    'question_id' => $question->id,
                    'option_id' => $option->id,
                    'answer_text' => $optionData['text'],
                    'is_correct' => $optionData['is_correct'],
                ]);
            }
        }
    }
}
