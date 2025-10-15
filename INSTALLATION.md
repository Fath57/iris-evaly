# Guide d'installation - Evaly

## ✅ Installation terminée avec succès !

Votre backoffice Evaly avec Inertia.js + Vue 3 est maintenant opérationnel.

## 🎉 Ce qui a été configuré

### 1. Backend Laravel
✅ Laravel 12 avec Inertia.js  
✅ Spatie Laravel Permission pour la gestion des rôles  
✅ Système d'authentification complet  
✅ Middlewares de sécurité configurés  

### 2. Base de données
✅ Migrations créées pour :
- users (utilisateurs)
- roles & permissions (rôles et permissions)
- classes (classes/groupes)
- subjects (matières)
- exams (examens)
- questions (questions)
- exam_attempts (tentatives d'examen)

✅ Seeders exécutés avec :
- 5 rôles par défaut (super-admin, admin, teacher, student, assistant)
- 55 permissions granulaires
- 11 utilisateurs de test
- 8 matières
- 4 classes avec étudiants et matières assignées

### 3. Frontend Vue 3 + Inertia
✅ Vue 3 avec Composition API  
✅ Tailwind CSS (mobile-first)  
✅ Ziggy pour les routes Laravel dans Vue  
✅ Composants créés :
- Page de connexion
- Layout admin avec navigation
- Dashboard avec statistiques
- Gestion des classes

## 🚀 Démarrage rapide

### Option 1 : Serveur de développement Laravel
```bash
cd /opt/lampp/htdocs/personal/iris-m2/evaly
php artisan serve
```

Accédez à : `http://localhost:8000`

### Option 2 : Serveur avec hot reload (développement)
Terminal 1 :
```bash
npm run dev
```

Terminal 2 :
```bash
php artisan serve
```

## 🔑 Connexion

### URL de connexion
`http://localhost:8000/login`

### Comptes disponibles

#### Super Administrateur
- **Email** : admin@evaly.com
- **Mot de passe** : password
- **Accès** : Toutes les fonctionnalités

#### Administrateur
- **Email** : admin2@evaly.com
- **Mot de passe** : password
- **Accès** : Gestion complète sauf paramètres système

#### Professeur de Mathématiques
- **Email** : prof.math@evaly.com
- **Mot de passe** : password
- **Accès** : Gestion de classes, création d'examens, notation

#### Professeur de Sciences
- **Email** : prof.sciences@evaly.com
- **Mot de passe** : password
- **Accès** : Gestion de classes, création d'examens, notation

#### Assistant Pédagogique
- **Email** : assistant@evaly.com
- **Mot de passe** : password
- **Accès** : Création de questions, aide à la notation

#### Étudiants (1 à 5)
- **Email** : etudiant1@evaly.com à etudiant5@evaly.com
- **Mot de passe** : password
- **Accès** : Participation aux examens, consultation des résultats

## 📊 Données de test

### 4 Classes créées
1. **Terminale S - Groupe A** (TS-A)
   - Professeur principal : Professeur Math
   - 3 étudiants inscrits
   - 5 matières (Math, Physique, Chimie, Biologie, Informatique)

2. **Terminale S - Groupe B** (TS-B)
   - Professeur principal : Professeur Sciences
   - 3 étudiants inscrits
   - 5 matières

3. **Première S** (1S)
   - Professeur principal : Professeur Math
   - 3 étudiants inscrits
   - 5 matières

4. **Seconde Générale** (2ND)
   - Professeur principal : Professeur Sciences
   - 3 étudiants inscrits
   - 5 matières

### 8 Matières créées
1. Mathématiques (MATH) - Bleu
2. Physique (PHYS) - Vert
3. Chimie (CHEM) - Orange
4. Biologie (BIO) - Violet
5. Informatique (INFO) - Rouge
6. Français (FR) - Orange primaire
7. Anglais (EN) - Cyan
8. Histoire (HIST) - Lime

## 🎯 Prochaines étapes recommandées

### 1. Compléter les pages Vue manquantes

#### Gestion des utilisateurs
- [ ] `resources/js/Pages/Admin/Users/Index.vue` - Liste des utilisateurs
- [ ] `resources/js/Pages/Admin/Users/Create.vue` - Création d'utilisateur
- [ ] `resources/js/Pages/Admin/Users/Edit.vue` - Édition d'utilisateur

#### Gestion des classes
- [ ] `resources/js/Pages/Admin/Classes/Create.vue` - Création de classe
- [ ] `resources/js/Pages/Admin/Classes/Edit.vue` - Édition de classe

#### Gestion des matières
- [ ] Créer `SubjectController.php`
- [ ] `resources/js/Pages/Admin/Subjects/Index.vue`
- [ ] `resources/js/Pages/Admin/Subjects/Create.vue`
- [ ] `resources/js/Pages/Admin/Subjects/Edit.vue`

#### Gestion des examens
- [ ] Créer `ExamController.php`
- [ ] `resources/js/Pages/Admin/Exams/Index.vue`
- [ ] `resources/js/Pages/Admin/Exams/Create.vue`
- [ ] `resources/js/Pages/Admin/Exams/Edit.vue`
- [ ] `resources/js/Pages/Admin/Exams/Questions.vue`

#### Interface étudiant
- [ ] `resources/js/Pages/Student/Dashboard.vue`
- [ ] `resources/js/Pages/Student/Exams/Available.vue`
- [ ] `resources/js/Pages/Student/Exams/Take.vue`
- [ ] `resources/js/Pages/Student/Results/Index.vue`

### 2. Améliorer les fonctionnalités

#### Système d'examens
- [ ] Interface de passation d'examen en temps réel
- [ ] Minuteur avec sauvegarde automatique
- [ ] Correction automatique pour QCM
- [ ] Système de notation manuel pour questions ouvertes
- [ ] Export des résultats en PDF

#### Notifications
- [ ] Notifications en temps réel (Laravel Echo + Pusher)
- [ ] Notifications par email pour :
  - Nouveaux examens publiés
  - Résultats disponibles
  - Rappels d'examens à venir

#### Rapports et statistiques
- [ ] Graphiques de performance par étudiant
- [ ] Statistiques par classe et matière
- [ ] Export de rapports Excel/CSV
- [ ] Analyse des résultats d'examen

#### Sécurité
- [ ] Vérification d'email obligatoire
- [ ] Authentification à deux facteurs (2FA)
- [ ] Logs d'activité
- [ ] Politique de mot de passe renforcée

### 3. Composants Vue réutilisables

Créer dans `resources/js/Components/` :
- [ ] `Button.vue` - Bouton réutilisable avec variants
- [ ] `Card.vue` - Carte avec styles cohérents
- [ ] `Modal.vue` - Modal pour confirmations/formulaires
- [ ] `Table.vue` - Table avec tri et pagination
- [ ] `FormInput.vue` - Input avec validation
- [ ] `FormSelect.vue` - Select avec recherche
- [ ] `Badge.vue` - Badge de statut
- [ ] `Alert.vue` - Messages d'alerte
- [ ] `Loader.vue` - Indicateur de chargement

### 4. Tests

#### Tests backend (Pest/PHPUnit)
```bash
php artisan make:test Auth/LoginTest
php artisan make:test Admin/ClassControllerTest
php artisan make:test Admin/ExamControllerTest
```

#### Tests frontend (Vitest)
```bash
npm install -D vitest @vue/test-utils
```

### 5. Optimisations

- [ ] Mettre en cache les permissions avec Redis
- [ ] Optimiser les requêtes N+1 avec Eager Loading
- [ ] Ajouter l'indexation des recherches
- [ ] Configurer Laravel Horizon pour les queues
- [ ] Mettre en place un CDN pour les assets

## 🔧 Commandes utiles

### Réinitialiser la base de données
```bash
php artisan migrate:fresh --seed
```

### Vider le cache
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### Lister les routes
```bash
php artisan route:list
```

### Voir les permissions
```bash
php artisan permission:show
```

### Créer un nouveau contrôleur
```bash
php artisan make:controller Admin/ExamController
```

### Créer un nouveau modèle avec migration
```bash
php artisan make:model Exam -m
```

## 📝 Notes importantes

### Tailwind CSS uniquement
Le projet utilise **uniquement Tailwind CSS** pour le styling. Pas de CSS personnalisé [[memory:8830459]].

### Couleur primaire
La couleur primaire est **#FF5E0E** (orange) [[memory:8828545]].

### Mobile-first
Toujours commencer par les styles mobile, puis ajouter les breakpoints responsive [[memory:8828545]].

### Logging
Utiliser le système de logging Laravel (facade `Log`) pour tous les logs [[memory:3175861]].

## 🐛 Dépannage

### Erreur 500
```bash
php artisan config:cache
php artisan route:cache
```

### Assets non compilés
```bash
npm run build
```

### Permissions manquantes
```bash
php artisan db:seed --class=RolePermissionSeeder
```

### Session expirée
Vérifier `SESSION_DRIVER` dans `.env` (recommandé : `database` ou `redis`)

## 📚 Documentation complémentaire

- [Laravel 12](https://laravel.com/docs/12.x)
- [Inertia.js](https://inertiajs.com/)
- [Vue 3](https://vuejs.org/)
- [Tailwind CSS](https://tailwindcss.com/)
- [Spatie Laravel Permission](https://spatie.be/docs/laravel-permission/)

## 🎊 Félicitations !

Votre plateforme d'examens en ligne est prête à être utilisée et développée davantage !

