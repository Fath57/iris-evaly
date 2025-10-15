# Configuration du Module d'Examens - Guide Rapide

## Étape 1 : Exécuter les migrations

```bash
php artisan migrate
```

Cette commande créera toutes les tables nécessaires :
- exams
- questions
- question_options
- question_answers
- question_images
- exam_attempts
- exam_attempt_answers
- ai_prompts
- ai_generation_history

## Étape 2 : Créer le lien symbolique pour les images

```bash
php artisan storage:link
```

Cela créera un lien symbolique de `public/storage` vers `storage/app/public` pour rendre les images accessibles publiquement.

## Étape 3 : Configurer les clés API IA

Ajoutez ces lignes à votre fichier `.env` :

```env
# OpenAI (ChatGPT) - Obtenir sur https://platform.openai.com/api-keys
OPENAI_API_KEY=sk-...
OPENAI_ORGANIZATION=org-...

# Google Gemini - Obtenir sur https://makersuite.google.com/app/apikey
GEMINI_API_KEY=...

# Perplexity AI - Obtenir sur https://www.perplexity.ai/settings/api
PERPLEXITY_API_KEY=pplx-...
```

**Note :** Les clés API sont optionnelles. Sans elles, seules les fonctionnalités de génération IA ne seront pas disponibles. Toutes les autres fonctionnalités fonctionneront normalement.

## Étape 4 : Créer les prompts IA par défaut

```bash
php artisan db:seed --class=AIPromptsSeeder
```

Cela créera 5 prompts par défaut :
- QCM Standard - ChatGPT
- Vrai/Faux - ChatGPT
- QCM Standard - Gemini
- QCM Avancé - Perplexity
- Questions Courtes - ChatGPT

## Étape 5 : Compiler les assets front-end

```bash
npm install
npm run build
```

Ou pour le développement avec hot reload :

```bash
npm run dev
```

## Étape 6 : Vérifier les permissions

Assurez-vous que les rôles ont les bonnes permissions. Si nécessaire, réexécutez le seeder des rôles :

```bash
php artisan db:seed --class=RolePermissionSeeder
```

## Étape 7 : Créer des données de test (optionnel)

Vous pouvez créer un seeder pour générer des examens et questions de test :

```bash
php artisan make:seeder ExamTestDataSeeder
```

Exemple de contenu pour le seeder :

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Exam;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\QuestionAnswer;
use App\Models\Subject;
use App\Models\ClassModel;
use App\Models\User;

class ExamTestDataSeeder extends Seeder
{
    public function run(): void
    {
        $teacher = User::role('teacher')->first();
        $subject = Subject::first();
        $class = ClassModel::first();

        if (!$teacher || !$subject || !$class) {
            $this->command->warn('Veuillez créer des utilisateurs, matières et classes d\'abord.');
            return;
        }

        // Créer un examen de test
        $exam = Exam::create([
            'title' => 'Examen de Test - Mathématiques',
            'description' => 'Examen de test pour démonstration du système',
            'subject_id' => $subject->id,
            'class_id' => $class->id,
            'created_by' => $teacher->id,
            'duration_minutes' => 60,
            'total_points' => 10,
            'passing_score' => 50,
            'start_date' => now(),
            'end_date' => now()->addDays(7),
            'status' => 'published',
            'shuffle_questions' => true,
            'show_results_immediately' => true,
            'max_attempts' => 3,
        ]);

        // Créer une question QCM
        $question = Question::create([
            'exam_id' => $exam->id,
            'type' => 'multiple_choice',
            'question_text' => 'Quelle est la valeur de π (pi) arrondie à deux décimales ?',
            'points' => 1,
            'order' => 0,
            'explanation' => 'π (pi) est une constante mathématique représentant le rapport entre la circonférence d\'un cercle et son diamètre.',
            'difficulty_level' => 'easy',
            'category' => 'Constantes mathématiques',
        ]);

        // Créer les options
        $options = [
            ['option_text' => '3.14', 'option_key' => 'A', 'is_correct' => true],
            ['option_text' => '3.41', 'option_key' => 'B', 'is_correct' => false],
            ['option_text' => '2.14', 'option_key' => 'C', 'is_correct' => false],
            ['option_text' => '4.13', 'option_key' => 'D', 'is_correct' => false],
        ];

        foreach ($options as $index => $optionData) {
            $option = QuestionOption::create([
                'question_id' => $question->id,
                'option_text' => $optionData['option_text'],
                'option_key' => $optionData['option_key'],
                'order' => $index,
            ]);

            if ($optionData['is_correct']) {
                QuestionAnswer::create([
                    'question_id' => $question->id,
                    'option_id' => $option->id,
                    'is_correct' => true,
                ]);
            }
        }

        $this->command->info('Examen de test créé avec succès !');
    }
}
```

Puis exécutez :

```bash
php artisan db:seed --class=ExamTestDataSeeder
```

## Commandes utiles

### Réinitialiser la base de données et tout recharger
```bash
php artisan migrate:fresh --seed
```

**⚠️ ATTENTION :** Cette commande supprime toutes les données !

### Exécuter uniquement les nouvelles migrations
```bash
php artisan migrate
```

### Vérifier l'état des migrations
```bash
php artisan migrate:status
```

### Rollback de la dernière migration
```bash
php artisan migrate:rollback
```

### Vider le cache
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### Optimiser pour la production
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

## Structure des URLs

### Admin/Enseignant
- `/admin/exams` - Liste des examens
- `/admin/exams/create` - Créer un examen
- `/admin/exams/{id}` - Voir un examen
- `/admin/exams/{id}/edit` - Modifier un examen
- `/admin/questions/bank` - Banque de questions
- `/admin/ai/generate` - Générer des questions avec IA

### Étudiant
- `/student/exams` - Examens disponibles
- `/student/exams/{id}` - Détails d'un examen
- `/student/exams/{id}/start` - Commencer un examen
- `/student/history` - Historique des tentatives
- `/student/statistics` - Statistiques personnelles

### Enseignant
- `/teacher/dashboard` - Tableau de bord
- `/teacher/exams/{id}/attempts` - Tentatives d'un examen
- `/teacher/students/{id}/statistics` - Statistiques d'un étudiant

## Vérification de l'installation

### 1. Vérifier les tables
```bash
php artisan tinker
```

Puis dans tinker :
```php
use Illuminate\Support\Facades\Schema;
$tables = ['exams', 'questions', 'question_options', 'question_answers', 'question_images', 'exam_attempts', 'exam_attempt_answers', 'ai_prompts', 'ai_generation_history'];
foreach($tables as $table) {
    echo Schema::hasTable($table) ? "✓ $table\n" : "✗ $table\n";
}
exit
```

### 2. Vérifier les prompts IA
```bash
php artisan tinker
```

Puis :
```php
\App\Models\AIPrompt::count(); // Devrait retourner 5 ou plus
\App\Models\AIPrompt::where('is_active', true)->get();
exit
```

### 3. Tester la création d'un examen
Visitez `/admin/exams/create` dans votre navigateur.

### 4. Tester la génération IA (si configurée)
Visitez `/admin/exams` et cliquez sur "Générer avec IA".

## Dépannage

### Problème : Erreur 404 sur les routes
**Solution :** Vider le cache des routes
```bash
php artisan route:clear
php artisan route:cache
```

### Problème : Images non affichées
**Solution :** Recréer le lien symbolique
```bash
php artisan storage:link
```

Vérifier les permissions :
```bash
chmod -R 775 storage
chmod -R 775 bootstrap/cache
```

### Problème : Erreur de migration
**Solution :** Vérifier que toutes les migrations précédentes sont exécutées
```bash
php artisan migrate:status
```

Si nécessaire, rollback et réexécuter :
```bash
php artisan migrate:rollback --step=1
php artisan migrate
```

### Problème : Erreur d'API IA
**Solution :** Vérifier les clés API dans le fichier `.env`
```bash
php artisan config:clear
php artisan config:cache
```

Tester la connexion :
```bash
php artisan tinker
```

Puis :
```php
config('services.openai.api_key'); // Devrait afficher votre clé
exit
```

### Problème : Composants Vue non trouvés
**Solution :** Recompiler les assets
```bash
npm run build
```

Ou en mode développement :
```bash
npm run dev
```

## Support

Pour plus d'informations, consultez :
- `EXAM_MODULE_README.md` - Documentation complète
- `FEATURES.md` - Liste des fonctionnalités
- `ARCHITECTURE.md` - Architecture du projet

## Notes Importantes

1. **Sauvegarde** : Toujours sauvegarder votre base de données avant de lancer des migrations en production.

2. **Environnement** : Testez d'abord dans un environnement de développement avant de déployer en production.

3. **Sécurité** : Ne committez jamais vos clés API dans Git. Utilisez toujours le fichier `.env`.

4. **Performances** : En production, activez toujours les caches :
   ```bash
   php artisan optimize
   ```

5. **Logs** : Surveillez les logs dans `storage/logs/laravel.log` pour détecter les problèmes.

## Checklist de déploiement

- [ ] Migrations exécutées
- [ ] Lien symbolique storage créé
- [ ] Clés API configurées (si nécessaire)
- [ ] Prompts IA créés
- [ ] Assets compilés
- [ ] Permissions vérifiées
- [ ] Cache optimisé
- [ ] Tests effectués
- [ ] Logs vérifiés
- [ ] Backup effectué

---

**Bon développement ! 🚀**

