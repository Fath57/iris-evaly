# Module de Gestion des Examens - Documentation

## Vue d'ensemble

Ce module complet de gestion d'examens comprend la création d'examens, la gestion de questions, les tentatives d'examen des étudiants, et la génération automatique de questions par IA (ChatGPT, Gemini, Perplexity).

## Architecture

Le module suit une architecture propre en couches :

- **Models** : Eloquent models avec relations
- **Repositories** : Couche d'accès aux données
- **Services** : Logique métier
- **Controllers** : Gestion des requêtes HTTP
- **Components** : Interface utilisateur Vue.js

## Structure de la Base de Données

### Tables Créées

1. **exams** - Examens
   - Titre, description, instructions
   - Matière, classe, créateur
   - Durée, dates de début/fin
   - Paramètres d'affichage (mélange, navigation, etc.)
   - Statut (draft, published, ongoing, completed, archived)

2. **questions** - Questions
   - Type (multiple_choice, true_false, short_answer, essay)
   - Texte de la question
   - Points, ordre
   - Difficulté, catégorie
   - Support de la banque de questions

3. **question_options** - Options de réponse
   - Texte de l'option
   - Clé (A, B, C, D)
   - Ordre

4. **question_answers** - Réponses correctes
   - Référence à l'option ou texte de réponse
   - Explication

5. **question_images** - Images des questions/options
   - Chemin de l'image
   - Type (question, option, diagram, schema)
   - Alt text pour l'accessibilité
   - Dimensions

6. **exam_attempts** - Tentatives d'examen
   - Étudiant, examen
   - Dates de début/fin
   - Score, pourcentage
   - Statut (in_progress, completed, abandoned)
   - Temps passé

7. **exam_attempt_answers** - Réponses individuelles
   - Tentative, question, option/texte
   - Correcte ou non
   - Points attribués
   - Temps passé

8. **ai_prompts** - Templates de prompts IA
   - Nom, description
   - Template de prompt
   - Fournisseur IA (chatgpt, gemini, perplexity)
   - Statistiques d'utilisation et notation

9. **ai_generation_history** - Historique des générations IA
   - Utilisateur, prompt
   - Thème, difficulté, nombre de questions
   - Statistiques (acceptées, tokens, coût)
   - Notation de qualité

## Services Développés

### ExamService
- Création, modification, suppression d'examens
- Publication et archivage
- Duplication d'examens
- Calcul des statistiques
- Gestion des permissions

### QuestionService
- Création, modification, suppression de questions
- Gestion des options et réponses
- Upload et gestion d'images
- Banque de questions
- Import/export de questions
- Réorganisation des questions

### AIGenerationService
- Génération de questions via ChatGPT, Gemini, Perplexity
- Gestion des prompts personnalisés
- Historique des générations
- Notation de qualité
- Calcul des coûts

### ExamAttemptService
- Démarrage d'une tentative
- Sauvegarde des réponses
- Notation automatique (QCM, Vrai/Faux)
- Notation manuelle (questions ouvertes)
- Calcul des scores
- Statistiques étudiants et examens

## Contrôleurs API

### ExamController
```
GET    /admin/exams                     - Liste des examens
POST   /admin/exams                     - Créer un examen
GET    /admin/exams/{id}                - Détails d'un examen
PUT    /admin/exams/{id}                - Modifier un examen
DELETE /admin/exams/{id}                - Supprimer un examen
POST   /admin/exams/{id}/publish        - Publier un examen
POST   /admin/exams/{id}/archive        - Archiver un examen
POST   /admin/exams/{id}/duplicate      - Dupliquer un examen
GET    /admin/exams/{id}/statistics     - Statistiques d'un examen
```

### QuestionController
```
GET    /admin/questions/bank            - Banque de questions
POST   /admin/exams/{id}/questions      - Créer une question
GET    /admin/questions/{id}            - Détails d'une question
PUT    /admin/questions/{id}            - Modifier une question
DELETE /admin/questions/{id}            - Supprimer une question
POST   /admin/questions/{id}/duplicate  - Dupliquer une question
POST   /admin/questions/{id}/import     - Importer vers un examen
POST   /admin/questions/{id}/images     - Upload d'image
```

### AIGenerationController
```
POST   /admin/ai/generate               - Générer des questions
POST   /admin/ai/history/{id}/accept    - Accepter les questions générées
POST   /admin/ai/history/{id}/rate      - Noter la génération
GET    /admin/ai/statistics             - Statistiques de génération
GET    /admin/ai/prompts                - Liste des prompts
POST   /admin/ai/prompts                - Créer un prompt
PUT    /admin/ai/prompts/{id}           - Modifier un prompt
DELETE /admin/ai/prompts/{id}           - Supprimer un prompt
```

### ExamAttemptController (Étudiant)
```
POST   /student/exams/{id}/start        - Démarrer un examen
POST   /student/attempts/{id}/answer    - Soumettre une réponse
POST   /student/attempts/{id}/complete  - Terminer l'examen
GET    /student/attempts/{id}/results   - Voir les résultats
GET    /student/history                 - Historique des tentatives
GET    /student/statistics              - Statistiques de l'étudiant
```

### ExamAttemptController (Enseignant)
```
GET    /teacher/exams/{id}/attempts     - Tentatives d'un examen
POST   /teacher/answers/{id}/grade      - Noter une réponse manuellement
GET    /teacher/students/{id}/statistics - Statistiques d'un étudiant
```

## Composants Vue.js

### QuestionEditor
Composant pour créer/éditer des questions avec :
- Sélection du type de question
- Gestion des options de réponse
- Upload d'images
- Paramètres de difficulté et catégorie
- Ajout à la banque de questions

### ExamCard
Carte d'affichage d'un examen avec :
- Informations principales
- Badge de statut
- Actions selon le rôle (voir, modifier, commencer)

### AIQuestionGenerator
Interface de génération de questions par IA :
- Sélection du fournisseur IA
- Configuration du sujet et de la difficulté
- Prompts personnalisables
- Prévisualisation des questions générées
- Sélection et acceptation des questions

### ExamTaker
Interface de passage d'examen pour les étudiants :
- Affichage des questions
- Gestion des réponses
- Chronomètre
- Barre de progression
- Navigation entre questions
- Liste des questions avec statut

## Configuration

### Variables d'environnement

Ajoutez ces variables dans votre fichier `.env` :

```env
# OpenAI (ChatGPT)
OPENAI_API_KEY=your_openai_api_key
OPENAI_ORGANIZATION=your_org_id

# Google Gemini
GEMINI_API_KEY=your_gemini_api_key

# Perplexity AI
PERPLEXITY_API_KEY=your_perplexity_api_key
```

## Installation et Configuration

### 1. Exécuter les migrations

```bash
php artisan migrate
```

### 2. Créer le lien de stockage pour les images

```bash
php artisan storage:link
```

### 3. Installer les dépendances NPM (si nécessaire)

```bash
npm install
npm run build
```

### 4. Créer les permissions et rôles

```bash
php artisan db:seed --class=RolePermissionSeeder
```

## Fonctionnalités Principales

### 1. Gestion des Examens

- ✅ Création d'examens avec paramètres avancés
- ✅ Support de multiples types de questions
- ✅ Paramètres d'affichage personnalisables
- ✅ Instructions et messages personnalisés
- ✅ Statuts de workflow (draft → published → completed → archived)

### 2. Gestion des Questions

- ✅ Types de questions : QCM, Vrai/Faux, Questions courtes, Questions ouvertes
- ✅ Support d'images pour questions et options
- ✅ Formatage basique (gras, italique)
- ✅ Catégorisation et niveaux de difficulté
- ✅ Banque de questions réutilisables
- ✅ Import/Export de questions

### 3. Support Visuel

- ✅ Upload d'images (JPG, PNG, GIF, SVG)
- ✅ Images dans l'énoncé
- ✅ Images dans les options de réponse
- ✅ Alt-text pour l'accessibilité
- ✅ Redimensionnement automatique

### 4. Génération Automatique par IA

- ✅ Intégration ChatGPT (OpenAI)
- ✅ Intégration Gemini (Google)
- ✅ Intégration Perplexity AI
- ✅ Prompts personnalisables
- ✅ Génération en lot
- ✅ Prévisualisation et validation
- ✅ Historique des générations
- ✅ Système de notation de qualité
- ✅ Suivi des coûts et tokens

### 5. Banque de Questions

- ✅ Stockage de questions réutilisables
- ✅ Filtrage par catégorie, difficulté, type
- ✅ Import dans différents examens
- ✅ Duplication de questions
- ✅ Gestion centralisée

### 6. Passage d'Examens (Étudiants)

- ✅ Interface intuitive
- ✅ Chronomètre avec limite de temps
- ✅ Sauvegarde automatique des réponses
- ✅ Navigation libre entre questions
- ✅ Indicateurs de progression
- ✅ Support des images

### 7. Notation et Résultats

- ✅ Notation automatique (QCM, Vrai/Faux)
- ✅ Notation manuelle (questions ouvertes)
- ✅ Affichage des résultats détaillés
- ✅ Statistiques par examen
- ✅ Statistiques par étudiant
- ✅ Historique des tentatives

## Prochaines Étapes

### Développement recommandé

1. **Seeders pour données de test**
   - Créer des examens d'exemple
   - Créer des prompts IA par défaut
   - Créer des questions dans la banque

2. **Pages Vue.js complètes**
   - Page de création/édition d'examen
   - Page de gestion de la banque de questions
   - Dashboard des statistiques
   - Page des résultats détaillés

3. **Fonctionnalités avancées**
   - Export des résultats en PDF/Excel
   - Graphiques de statistiques
   - Notifications par email
   - Calendrier des examens
   - Mode d'entraînement/révision

4. **Optimisations**
   - Cache des examens et questions
   - Optimisation des requêtes (eager loading)
   - Indexation de la base de données
   - File d'attente pour les générations IA

5. **Sécurité**
   - Validation approfondie des entrées
   - Rate limiting pour les API IA
   - Prévention de la triche
   - Vérification d'intégrité des réponses

6. **Tests**
   - Tests unitaires des services
   - Tests d'intégration des contrôleurs
   - Tests E2E du workflow complet
   - Tests des générations IA

## Support et Maintenance

### Logs

Les logs sont enregistrés via le système Laravel Logging :
- Création/modification d'examens
- Génération de questions par IA
- Tentatives d'examen
- Erreurs et exceptions

Consultez les logs dans `storage/logs/laravel.log`

### Debugging

Pour activer le mode debug :
```env
APP_DEBUG=true
LOG_LEVEL=debug
```

## Notes Importantes

1. **Séparation des données** : Les options et réponses sont stockées dans des tables séparées (pas de JSON) pour faciliter l'analyse et les requêtes.

2. **Images** : Les images sont stockées dans `storage/app/public/questions/images` et accessibles via le lien symbolique.

3. **IA** : Les générations IA peuvent prendre du temps. Pensez à implémenter un système de queue pour de meilleures performances.

4. **Coûts IA** : Surveillez vos coûts API. Les prix approximatifs sont :
   - ChatGPT : ~$0.002 / 1K tokens
   - Gemini : ~$0.001 / 1K tokens
   - Perplexity : ~$0.001 / 1K tokens

5. **Permissions** : Assurez-vous que les permissions sont correctement configurées pour chaque rôle (admin, teacher, student).

## Ressources

- [Documentation Laravel](https://laravel.com/docs)
- [Documentation Vue.js](https://vuejs.org/)
- [Documentation Inertia.js](https://inertiajs.com/)
- [Documentation Tailwind CSS](https://tailwindcss.com/)
- [OpenAI API](https://platform.openai.com/docs)
- [Google Gemini API](https://ai.google.dev/docs)
- [Perplexity API](https://docs.perplexity.ai/)

## Auteur

Développé pour le système Evaly - Plateforme de gestion éducative

Date : 13 Octobre 2025

