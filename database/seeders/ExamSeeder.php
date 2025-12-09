<?php

namespace Database\Seeders;

use App\Models\ClassModel;
use App\Models\Exam;
use App\Models\Question;
use App\Models\QuestionAnswer;
use App\Models\QuestionOption;
use App\Models\QuestionImage;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Seeder;

class ExamSeeder extends Seeder
{
    public function run(): void
    {
        $teacher = User::where('email', 'prof.math@evaly.com')->first();

        if (!$teacher) {
            $this->command->error('Teacher prof.math@evaly.com not found');
            return;
        }

        $mathSubject = Subject::where('code', 'MATH')->first();
        $physicsSubject = Subject::where('code', 'PC')->first();
        $frSubject = Subject::where('code', 'FR')->first();
        $ts1Class = ClassModel::where('code', 'TS1')->first();

        if (!$mathSubject || !$ts1Class) {
            $this->command->error('Missing required data (subject or class)');
            return;
        }

        // Examen 1: Mathématiques - Algèbre
        $this->createAlgebraExam($teacher, $mathSubject, $ts1Class);

        // Examen 2: Mathématiques - Géométrie
        $this->createGeometryExam($teacher, $mathSubject, $ts1Class);

        // Examen 3: Physique-Chimie (ou Math si PC n'existe pas)
        $subject3 = $physicsSubject ?? $mathSubject;
        $this->createPhysicsExam($teacher, $subject3, $ts1Class);

        $this->command->info('3 exams with 10 questions each created successfully!');
    }

    private function createAlgebraExam($teacher, $subject, $class): void
    {
        $exam = Exam::firstOrCreate(
            ['title' => 'Examen d\'Algèbre - Équations et Fonctions'],
            [
                'description' => 'Examen complet sur les équations du second degré, les fonctions et les dérivées',
                'instructions' => 'Répondez à toutes les questions. Vous avez 90 minutes. Calculatrice autorisée.',
                'subject_id' => $subject->id,
                'class_id' => $class->id,
                'created_by' => $teacher->id,
                'duration_minutes' => 90,
                'total_points' => 40,
                'passing_score' => 20,
                'start_date' => now()->subDays(2),
                'end_date' => now()->addDays(14),
                'status' => 'published',
                'shuffle_questions' => true,
                'shuffle_options' => true,
                'show_results_immediately' => true,
                'allow_review' => true,
                'show_correct_answers' => true,
                'max_attempts' => 3,
            ]
        );

        if ($exam->questions()->count() > 0) {
            return;
        }

        $questions = [
            // Question 1: QCM avec image
            [
                'type' => 'multiple_choice',
                'question_text' => 'Observez le graphique ci-dessous. Quelle est l\'équation de cette parabole ?',
                'points' => 4,
                'order' => 1,
                'explanation' => 'La parabole passe par l\'origine et s\'ouvre vers le haut, donc f(x) = x².',
                'difficulty_level' => 'medium',
                'question_image' => 'questions/graph_function.png',
                'options' => [
                    ['text' => 'f(x) = x²', 'key' => 'A', 'is_correct' => true],
                    ['text' => 'f(x) = 2x', 'key' => 'B', 'is_correct' => false],
                    ['text' => 'f(x) = x³', 'key' => 'C', 'is_correct' => false],
                    ['text' => 'f(x) = √x', 'key' => 'D', 'is_correct' => false],
                ],
            ],
            // Question 2: Vrai/Faux
            [
                'type' => 'true_false',
                'question_text' => 'Le discriminant de l\'équation x² - 4x + 4 = 0 est égal à zéro.',
                'points' => 3,
                'order' => 2,
                'explanation' => 'Δ = b² - 4ac = 16 - 16 = 0. L\'équation a une racine double.',
                'difficulty_level' => 'easy',
                'options' => [
                    ['text' => 'Vrai', 'key' => 'A', 'is_correct' => true],
                    ['text' => 'Faux', 'key' => 'B', 'is_correct' => false],
                ],
            ],
            // Question 3: QCM
            [
                'type' => 'multiple_choice',
                'question_text' => 'Quelle est la dérivée de f(x) = 3x³ - 2x² + 5x - 1 ?',
                'points' => 4,
                'order' => 3,
                'explanation' => 'f\'(x) = 9x² - 4x + 5 en appliquant les règles de dérivation.',
                'difficulty_level' => 'medium',
                'options' => [
                    ['text' => 'f\'(x) = 9x² - 4x + 5', 'key' => 'A', 'is_correct' => true],
                    ['text' => 'f\'(x) = 9x² - 4x', 'key' => 'B', 'is_correct' => false],
                    ['text' => 'f\'(x) = 3x² - 2x + 5', 'key' => 'C', 'is_correct' => false],
                    ['text' => 'f\'(x) = 9x³ - 4x² + 5x', 'key' => 'D', 'is_correct' => false],
                ],
            ],
            // Question 4: QCM
            [
                'type' => 'multiple_choice',
                'question_text' => 'Résoudre l\'équation : 2x² - 8x + 6 = 0',
                'points' => 4,
                'order' => 4,
                'explanation' => 'Δ = 64 - 48 = 16. x = (8 ± 4) / 4, donc x = 3 ou x = 1.',
                'difficulty_level' => 'medium',
                'options' => [
                    ['text' => 'x = 1 ou x = 3', 'key' => 'A', 'is_correct' => true],
                    ['text' => 'x = 2 ou x = 4', 'key' => 'B', 'is_correct' => false],
                    ['text' => 'x = -1 ou x = -3', 'key' => 'C', 'is_correct' => false],
                    ['text' => 'Pas de solution', 'key' => 'D', 'is_correct' => false],
                ],
            ],
            // Question 5: Vrai/Faux
            [
                'type' => 'true_false',
                'question_text' => 'La fonction f(x) = ln(x) est définie pour tout x réel.',
                'points' => 3,
                'order' => 5,
                'explanation' => 'ln(x) n\'est défini que pour x > 0.',
                'difficulty_level' => 'easy',
                'options' => [
                    ['text' => 'Vrai', 'key' => 'A', 'is_correct' => false],
                    ['text' => 'Faux', 'key' => 'B', 'is_correct' => true],
                ],
            ],
            // Question 6: QCM avec image
            [
                'type' => 'multiple_choice',
                'question_text' => 'Utilisez la calculatrice pour trouver la valeur de sin(30°).',
                'points' => 4,
                'order' => 6,
                'explanation' => 'sin(30°) = 1/2 = 0.5',
                'difficulty_level' => 'easy',
                'question_image' => 'questions/calculator.png',
                'options' => [
                    ['text' => '0.5', 'key' => 'A', 'is_correct' => true],
                    ['text' => '0.866', 'key' => 'B', 'is_correct' => false],
                    ['text' => '1', 'key' => 'C', 'is_correct' => false],
                    ['text' => '0.707', 'key' => 'D', 'is_correct' => false],
                ],
            ],
            // Question 7: QCM
            [
                'type' => 'multiple_choice',
                'question_text' => 'Quelle est la limite de (x² - 1)/(x - 1) quand x tend vers 1 ?',
                'points' => 4,
                'order' => 7,
                'explanation' => 'En factorisant : (x-1)(x+1)/(x-1) = x+1, donc la limite est 2.',
                'difficulty_level' => 'medium',
                'options' => [
                    ['text' => '2', 'key' => 'A', 'is_correct' => true],
                    ['text' => '0', 'key' => 'B', 'is_correct' => false],
                    ['text' => '1', 'key' => 'C', 'is_correct' => false],
                    ['text' => 'La limite n\'existe pas', 'key' => 'D', 'is_correct' => false],
                ],
            ],
            // Question 8: Réponse courte
            [
                'type' => 'short_answer',
                'question_text' => 'Calculez la somme des racines de l\'équation x² - 7x + 12 = 0. (Répondez par un nombre)',
                'points' => 4,
                'order' => 8,
                'explanation' => 'Par les relations de Viète, S = -b/a = 7. Les racines sont 3 et 4.',
                'difficulty_level' => 'medium',
                'correct_answer' => '7',
            ],
            // Question 9: Vrai/Faux
            [
                'type' => 'true_false',
                'question_text' => 'e^(ln(5)) = 5',
                'points' => 3,
                'order' => 9,
                'explanation' => 'exp et ln sont des fonctions réciproques, donc e^(ln(x)) = x.',
                'difficulty_level' => 'easy',
                'options' => [
                    ['text' => 'Vrai', 'key' => 'A', 'is_correct' => true],
                    ['text' => 'Faux', 'key' => 'B', 'is_correct' => false],
                ],
            ],
            // Question 10: Essai
            [
                'type' => 'essay',
                'question_text' => 'Expliquez en quelques phrases la méthode pour résoudre une équation du second degré ax² + bx + c = 0. Donnez un exemple.',
                'points' => 7,
                'order' => 10,
                'explanation' => 'Calculer le discriminant Δ = b² - 4ac, puis appliquer la formule x = (-b ± √Δ) / 2a.',
                'difficulty_level' => 'hard',
            ],
        ];

        $this->insertQuestions($exam, $questions);
    }

    private function createGeometryExam($teacher, $subject, $class): void
    {
        $exam = Exam::firstOrCreate(
            ['title' => 'Examen de Géométrie - Figures et Théorèmes'],
            [
                'description' => 'Examen sur la géométrie plane, les triangles, cercles et le théorème de Pythagore',
                'instructions' => 'Répondez à toutes les questions. Durée: 60 minutes. Règle et compas autorisés.',
                'subject_id' => $subject->id,
                'class_id' => $class->id,
                'created_by' => $teacher->id,
                'duration_minutes' => 60,
                'total_points' => 40,
                'passing_score' => 20,
                'start_date' => now()->subDay(),
                'end_date' => now()->addDays(10),
                'status' => 'published',
                'shuffle_questions' => false,
                'shuffle_options' => true,
                'show_results_immediately' => true,
                'allow_review' => true,
                'show_correct_answers' => true,
                'max_attempts' => 2,
            ]
        );

        if ($exam->questions()->count() > 0) {
            return;
        }

        $questions = [
            // Question 1: QCM avec image de triangle
            [
                'type' => 'multiple_choice',
                'question_text' => 'Quelle est la nature de ce triangle ?',
                'points' => 4,
                'order' => 1,
                'explanation' => 'Un triangle équilatéral a trois côtés égaux.',
                'difficulty_level' => 'easy',
                'question_image' => 'questions/triangle.png',
                'options' => [
                    ['text' => 'Triangle équilatéral', 'key' => 'A', 'is_correct' => true],
                    ['text' => 'Triangle isocèle', 'key' => 'B', 'is_correct' => false],
                    ['text' => 'Triangle rectangle', 'key' => 'C', 'is_correct' => false],
                    ['text' => 'Triangle scalène', 'key' => 'D', 'is_correct' => false],
                ],
            ],
            // Question 2: QCM avec image du théorème de Pythagore
            [
                'type' => 'multiple_choice',
                'question_text' => 'Dans un triangle rectangle, si a=3 et b=4, quelle est la longueur de l\'hypoténuse c ?',
                'points' => 4,
                'order' => 2,
                'explanation' => 'c² = a² + b² = 9 + 16 = 25, donc c = 5.',
                'difficulty_level' => 'medium',
                'question_image' => 'questions/pythagore.png',
                'options' => [
                    ['text' => '5', 'key' => 'A', 'is_correct' => true],
                    ['text' => '7', 'key' => 'B', 'is_correct' => false],
                    ['text' => '6', 'key' => 'C', 'is_correct' => false],
                    ['text' => '12', 'key' => 'D', 'is_correct' => false],
                ],
            ],
            // Question 3: Vrai/Faux
            [
                'type' => 'true_false',
                'question_text' => 'La somme des angles d\'un triangle est égale à 180°.',
                'points' => 3,
                'order' => 3,
                'explanation' => 'C\'est une propriété fondamentale des triangles dans la géométrie euclidienne.',
                'difficulty_level' => 'easy',
                'options' => [
                    ['text' => 'Vrai', 'key' => 'A', 'is_correct' => true],
                    ['text' => 'Faux', 'key' => 'B', 'is_correct' => false],
                ],
            ],
            // Question 4: QCM avec image de cercle
            [
                'type' => 'multiple_choice',
                'question_text' => 'Quelle est l\'aire d\'un cercle de rayon r = 3 cm ?',
                'points' => 4,
                'order' => 4,
                'explanation' => 'A = πr² = π × 9 = 9π cm².',
                'difficulty_level' => 'medium',
                'question_image' => 'questions/circle.png',
                'options' => [
                    ['text' => '9π cm²', 'key' => 'A', 'is_correct' => true],
                    ['text' => '6π cm²', 'key' => 'B', 'is_correct' => false],
                    ['text' => '3π cm²', 'key' => 'C', 'is_correct' => false],
                    ['text' => '12π cm²', 'key' => 'D', 'is_correct' => false],
                ],
            ],
            // Question 5: QCM
            [
                'type' => 'multiple_choice',
                'question_text' => 'Quel est le périmètre d\'un carré de côté 5 cm ?',
                'points' => 4,
                'order' => 5,
                'explanation' => 'P = 4 × côté = 4 × 5 = 20 cm.',
                'difficulty_level' => 'easy',
                'question_image' => 'questions/square.png',
                'options' => [
                    ['text' => '20 cm', 'key' => 'A', 'is_correct' => true],
                    ['text' => '25 cm', 'key' => 'B', 'is_correct' => false],
                    ['text' => '10 cm', 'key' => 'C', 'is_correct' => false],
                    ['text' => '15 cm', 'key' => 'D', 'is_correct' => false],
                ],
            ],
            // Question 6: Vrai/Faux
            [
                'type' => 'true_false',
                'question_text' => 'Deux droites parallèles ne se croisent jamais.',
                'points' => 3,
                'order' => 6,
                'explanation' => 'Par définition, des droites parallèles ne se croisent jamais.',
                'difficulty_level' => 'easy',
                'options' => [
                    ['text' => 'Vrai', 'key' => 'A', 'is_correct' => true],
                    ['text' => 'Faux', 'key' => 'B', 'is_correct' => false],
                ],
            ],
            // Question 7: QCM avec options images
            [
                'type' => 'multiple_choice',
                'question_text' => 'Quelle figure a exactement 4 côtés égaux et 4 angles droits ?',
                'points' => 4,
                'order' => 7,
                'explanation' => 'Un carré a 4 côtés égaux et 4 angles de 90°.',
                'difficulty_level' => 'easy',
                'question_image' => 'questions/geometry.png',
                'options' => [
                    ['text' => 'Carré', 'key' => 'A', 'is_correct' => true],
                    ['text' => 'Rectangle', 'key' => 'B', 'is_correct' => false],
                    ['text' => 'Losange', 'key' => 'C', 'is_correct' => false],
                    ['text' => 'Trapèze', 'key' => 'D', 'is_correct' => false],
                ],
            ],
            // Question 8: QCM
            [
                'type' => 'multiple_choice',
                'question_text' => 'Quel est le volume d\'un cube de côté 4 cm ?',
                'points' => 4,
                'order' => 8,
                'explanation' => 'V = côté³ = 4³ = 64 cm³.',
                'difficulty_level' => 'medium',
                'options' => [
                    ['text' => '64 cm³', 'key' => 'A', 'is_correct' => true],
                    ['text' => '16 cm³', 'key' => 'B', 'is_correct' => false],
                    ['text' => '48 cm³', 'key' => 'C', 'is_correct' => false],
                    ['text' => '12 cm³', 'key' => 'D', 'is_correct' => false],
                ],
            ],
            // Question 9: Réponse courte
            [
                'type' => 'short_answer',
                'question_text' => 'Calculez l\'aire d\'un rectangle de longueur 8 cm et largeur 5 cm. (Répondez en cm²)',
                'points' => 4,
                'order' => 9,
                'explanation' => 'A = L × l = 8 × 5 = 40 cm².',
                'difficulty_level' => 'easy',
                'correct_answer' => '40',
            ],
            // Question 10: Essai
            [
                'type' => 'essay',
                'question_text' => 'Démontrez le théorème de Pythagore en utilisant un exemple concret avec des valeurs numériques.',
                'points' => 6,
                'order' => 10,
                'explanation' => 'Dans un triangle rectangle, le carré de l\'hypoténuse égale la somme des carrés des deux autres côtés.',
                'difficulty_level' => 'hard',
            ],
        ];

        $this->insertQuestions($exam, $questions);
    }

    private function createPhysicsExam($teacher, $subject, $class): void
    {
        $exam = Exam::firstOrCreate(
            ['title' => 'Examen de Physique - Mécanique et Énergie'],
            [
                'description' => 'Examen sur les principes de mécanique, les forces et l\'énergie',
                'instructions' => 'Répondez à toutes les questions. Durée: 75 minutes. Formulaire autorisé.',
                'subject_id' => $subject->id,
                'class_id' => $class->id,
                'created_by' => $teacher->id,
                'duration_minutes' => 75,
                'total_points' => 40,
                'passing_score' => 20,
                'start_date' => now()->addDays(1),
                'end_date' => now()->addDays(21),
                'status' => 'published',
                'shuffle_questions' => true,
                'shuffle_options' => true,
                'show_results_immediately' => false,
                'allow_review' => false,
                'show_correct_answers' => false,
                'max_attempts' => 1,
            ]
        );

        if ($exam->questions()->count() > 0) {
            return;
        }

        $questions = [
            // Question 1: QCM
            [
                'type' => 'multiple_choice',
                'question_text' => 'Quelle est l\'unité de mesure de la force dans le système international ?',
                'points' => 4,
                'order' => 1,
                'explanation' => 'L\'unité SI de la force est le Newton (N).',
                'difficulty_level' => 'easy',
                'options' => [
                    ['text' => 'Newton (N)', 'key' => 'A', 'is_correct' => true],
                    ['text' => 'Joule (J)', 'key' => 'B', 'is_correct' => false],
                    ['text' => 'Watt (W)', 'key' => 'C', 'is_correct' => false],
                    ['text' => 'Pascal (Pa)', 'key' => 'D', 'is_correct' => false],
                ],
            ],
            // Question 2: QCM avec image
            [
                'type' => 'multiple_choice',
                'question_text' => 'D\'après la formule E = mc², que représente c ?',
                'points' => 4,
                'order' => 2,
                'explanation' => 'c représente la vitesse de la lumière dans le vide (≈ 3×10⁸ m/s).',
                'difficulty_level' => 'medium',
                'question_image' => 'questions/equation.png',
                'options' => [
                    ['text' => 'La vitesse de la lumière', 'key' => 'A', 'is_correct' => true],
                    ['text' => 'Une constante quelconque', 'key' => 'B', 'is_correct' => false],
                    ['text' => 'La charge électrique', 'key' => 'C', 'is_correct' => false],
                    ['text' => 'La capacité thermique', 'key' => 'D', 'is_correct' => false],
                ],
            ],
            // Question 3: Vrai/Faux
            [
                'type' => 'true_false',
                'question_text' => 'La masse d\'un objet change selon l\'endroit où il se trouve.',
                'points' => 3,
                'order' => 3,
                'explanation' => 'La masse est constante. C\'est le poids (P = mg) qui varie avec g.',
                'difficulty_level' => 'medium',
                'options' => [
                    ['text' => 'Vrai', 'key' => 'A', 'is_correct' => false],
                    ['text' => 'Faux', 'key' => 'B', 'is_correct' => true],
                ],
            ],
            // Question 4: QCM
            [
                'type' => 'multiple_choice',
                'question_text' => 'Un objet de masse 10 kg est soumis à une force de 50 N. Quelle est son accélération ?',
                'points' => 4,
                'order' => 4,
                'explanation' => 'F = ma, donc a = F/m = 50/10 = 5 m/s².',
                'difficulty_level' => 'medium',
                'options' => [
                    ['text' => '5 m/s²', 'key' => 'A', 'is_correct' => true],
                    ['text' => '500 m/s²', 'key' => 'B', 'is_correct' => false],
                    ['text' => '0.2 m/s²', 'key' => 'C', 'is_correct' => false],
                    ['text' => '60 m/s²', 'key' => 'D', 'is_correct' => false],
                ],
            ],
            // Question 5: Vrai/Faux
            [
                'type' => 'true_false',
                'question_text' => 'L\'énergie cinétique dépend de la vitesse au carré.',
                'points' => 3,
                'order' => 5,
                'explanation' => 'Ec = ½mv², donc l\'énergie cinétique est proportionnelle à v².',
                'difficulty_level' => 'easy',
                'options' => [
                    ['text' => 'Vrai', 'key' => 'A', 'is_correct' => true],
                    ['text' => 'Faux', 'key' => 'B', 'is_correct' => false],
                ],
            ],
            // Question 6: QCM
            [
                'type' => 'multiple_choice',
                'question_text' => 'Quelle est l\'énergie potentielle gravitationnelle d\'un objet de 2 kg à 10 m de hauteur ? (g = 10 m/s²)',
                'points' => 4,
                'order' => 6,
                'explanation' => 'Ep = mgh = 2 × 10 × 10 = 200 J.',
                'difficulty_level' => 'medium',
                'options' => [
                    ['text' => '200 J', 'key' => 'A', 'is_correct' => true],
                    ['text' => '20 J', 'key' => 'B', 'is_correct' => false],
                    ['text' => '100 J', 'key' => 'C', 'is_correct' => false],
                    ['text' => '2000 J', 'key' => 'D', 'is_correct' => false],
                ],
            ],
            // Question 7: QCM
            [
                'type' => 'multiple_choice',
                'question_text' => 'Selon la troisième loi de Newton, si A exerce une force sur B, alors :',
                'points' => 4,
                'order' => 7,
                'explanation' => 'Principe d\'action-réaction : B exerce une force égale et opposée sur A.',
                'difficulty_level' => 'medium',
                'options' => [
                    ['text' => 'B exerce une force égale et opposée sur A', 'key' => 'A', 'is_correct' => true],
                    ['text' => 'B n\'exerce aucune force sur A', 'key' => 'B', 'is_correct' => false],
                    ['text' => 'B exerce une force double sur A', 'key' => 'C', 'is_correct' => false],
                    ['text' => 'B exerce une force dans la même direction sur A', 'key' => 'D', 'is_correct' => false],
                ],
            ],
            // Question 8: Réponse courte
            [
                'type' => 'short_answer',
                'question_text' => 'Calculez la puissance d\'une machine qui effectue un travail de 500 J en 10 secondes. (Répondez en W)',
                'points' => 4,
                'order' => 8,
                'explanation' => 'P = W/t = 500/10 = 50 W.',
                'difficulty_level' => 'medium',
                'correct_answer' => '50',
            ],
            // Question 9: Vrai/Faux
            [
                'type' => 'true_false',
                'question_text' => 'Dans le vide, tous les objets tombent à la même vitesse, quelle que soit leur masse.',
                'points' => 3,
                'order' => 9,
                'explanation' => 'Sans résistance de l\'air, l\'accélération est g pour tous les objets.',
                'difficulty_level' => 'medium',
                'options' => [
                    ['text' => 'Vrai', 'key' => 'A', 'is_correct' => true],
                    ['text' => 'Faux', 'key' => 'B', 'is_correct' => false],
                ],
            ],
            // Question 10: Essai
            [
                'type' => 'essay',
                'question_text' => 'Expliquez le principe de conservation de l\'énergie mécanique et donnez un exemple concret de sa mise en application.',
                'points' => 7,
                'order' => 10,
                'explanation' => 'Em = Ec + Ep = constante si les forces non conservatives sont nulles.',
                'difficulty_level' => 'hard',
            ],
        ];

        $this->insertQuestions($exam, $questions);
    }

    private function insertQuestions(Exam $exam, array $questionsData): void
    {
        foreach ($questionsData as $questionData) {
            $options = $questionData['options'] ?? [];
            $questionImage = $questionData['question_image'] ?? null;
            $correctAnswer = $questionData['correct_answer'] ?? null;

            unset($questionData['options'], $questionData['question_image'], $questionData['correct_answer']);

            $question = Question::create([
                'exam_id' => $exam->id,
                'type' => $questionData['type'],
                'question_text' => $questionData['question_text'],
                'points' => $questionData['points'],
                'order' => $questionData['order'],
                'explanation' => $questionData['explanation'] ?? null,
                'difficulty_level' => $questionData['difficulty_level'] ?? 'medium',
            ]);

            // Ajouter l'image de la question si présente
            if ($questionImage) {
                QuestionImage::create([
                    'question_id' => $question->id,
                    'option_id' => null,
                    'image_path' => $questionImage,
                    'image_type' => 'question',
                    'alt_text' => 'Image de la question ' . $question->id,
                    'order' => 1,
                ]);
            }

            // Pour les questions à choix multiples ou vrai/faux
            if (in_array($questionData['type'], ['multiple_choice', 'true_false'])) {
                foreach ($options as $index => $optionData) {
                    $option = QuestionOption::create([
                        'question_id' => $question->id,
                        'option_text' => $optionData['text'],
                        'option_key' => $optionData['key'],
                        'order' => $index + 1,
                    ]);

                    // Ajouter l'image de l'option si présente
                    if (isset($optionData['image'])) {
                        QuestionImage::create([
                            'question_id' => $question->id,
                            'option_id' => $option->id,
                            'image_path' => $optionData['image'],
                            'image_type' => 'option',
                            'alt_text' => 'Option ' . $optionData['key'],
                            'order' => 1,
                        ]);
                    }

                    // Créer la réponse
                    QuestionAnswer::create([
                        'question_id' => $question->id,
                        'option_id' => $option->id,
                        'answer_text' => $optionData['text'],
                        'is_correct' => $optionData['is_correct'],
                    ]);
                }
            }

            // Pour les questions à réponse courte
            if ($questionData['type'] === 'short_answer' && $correctAnswer) {
                QuestionAnswer::create([
                    'question_id' => $question->id,
                    'option_id' => null,
                    'answer_text' => $correctAnswer,
                    'is_correct' => true,
                ]);
            }

            // Pour les essais, pas de réponse correcte prédéfinie
        }

        // Mettre à jour le total des points de l'examen
        $totalPoints = $exam->questions()->sum('points');
        $exam->update(['total_points' => $totalPoints]);
    }
}
