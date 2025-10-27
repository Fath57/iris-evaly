# Gestion des Étudiants - Documentation

## Introduction

Ce document décrit la gestion des étudiants dans l'application Evaly. La gestion des étudiants a été intégrée dans l'interface backoffice Inertia.js pour offrir une expérience utilisateur optimale avec moins de clics.

## Fonctionnalités

La gestion des étudiants comprend les fonctionnalités suivantes :

1. **Liste des étudiants** : Affichage paginé de tous les étudiants avec filtres
2. **Ajout d'étudiants** : Formulaire pour ajouter un étudiant individuellement
3. **Import d'étudiants** : Import en masse d'étudiants à partir d'un fichier Excel
4. **Modification d'étudiants** : Mise à jour des informations d'un étudiant
5. **Suppression d'étudiants** : Suppression d'un étudiant du système
6. **Gestion des classes** : Assignation d'étudiants à des classes
7. **Vue par classe** : Affichage des étudiants par classe

## Accès aux fonctionnalités

### Menu de navigation

La gestion des étudiants est accessible depuis le menu de navigation du backoffice, dans la section "Étudiants".

### Permissions requises

Les fonctionnalités sont protégées par les permissions suivantes :

- `view students` : Voir la liste des étudiants
- `create students` : Ajouter ou importer des étudiants
- `edit students` : Modifier les informations d'un étudiant ou l'assigner à une classe
- `delete students` : Supprimer un étudiant

## Utilisation

### Liste des étudiants

La page principale affiche la liste de tous les étudiants avec des options de filtrage :

- Recherche par nom, prénom, email ou numéro étudiant
- Filtrage par classe
- Filtrage par statut (actif/inactif)

### Ajout d'un étudiant

Pour ajouter un étudiant :

1. Cliquer sur le bouton "Ajouter un étudiant"
2. Remplir le formulaire avec les informations de l'étudiant
3. Optionnellement, sélectionner une classe
4. Cliquer sur "Créer l'étudiant"

### Import d'étudiants

Pour importer des étudiants en masse :

1. Cliquer sur le bouton "Importer"
2. Sélectionner une classe pour les étudiants importés
3. Télécharger le modèle si nécessaire
4. Sélectionner le fichier Excel contenant les données des étudiants
5. Cliquer sur "Importer"

Le fichier d'import doit contenir les colonnes suivantes :
- `prenom` (obligatoire)
- `nom` (obligatoire)
- `email` (obligatoire)
- `numero_etudiant` (optionnel)
- `telephone` (optionnel)
- `date_naissance` (optionnel)
- `adresse` (optionnel)
- `contact_parent` (optionnel)

### Modification d'un étudiant

Pour modifier un étudiant :

1. Cliquer sur l'icône de modification (crayon) à côté de l'étudiant
2. Mettre à jour les informations
3. Cliquer sur "Mettre à jour"

### Gestion des classes

Dans la page de modification d'un étudiant, vous pouvez :

1. Voir les classes auxquelles l'étudiant est assigné
2. Assigner l'étudiant à une nouvelle classe
3. Retirer l'étudiant d'une classe

### Vue par classe

Pour voir tous les étudiants d'une classe :

1. Accéder à la liste des classes
2. Cliquer sur "Voir les étudiants" pour la classe souhaitée

Cette vue permet également d'importer des étudiants directement dans la classe sélectionnée.

## Changements techniques

### Migration de l'API vers Inertia.js

La gestion des étudiants a été migrée de l'API REST vers l'interface Inertia.js pour offrir une meilleure expérience utilisateur. Les principales modifications sont :

1. Création de routes web pour la gestion des étudiants
2. Création d'un contrôleur Admin\StudentController pour gérer les requêtes Inertia.js
3. Développement de vues Inertia.js pour l'interface utilisateur
4. Réutilisation du service StudentService existant pour la logique métier
5. Suppression des routes API qui ne sont plus nécessaires

### Authentification des étudiants

L'authentification des étudiants reste gérée par l'API pour permettre l'accès mobile et externe.

## Dépannage

### Problèmes d'import

Si vous rencontrez des problèmes lors de l'import d'étudiants :

1. Vérifiez que le format du fichier est correct (XLSX, XLS ou CSV)
2. Assurez-vous que les colonnes obligatoires sont présentes et correctement nommées
3. Vérifiez que les emails sont uniques
4. Consultez les messages d'erreur affichés après la tentative d'import

### Problèmes d'assignation de classe

Si vous ne pouvez pas assigner un étudiant à une classe :

1. Vérifiez que l'étudiant n'est pas déjà assigné à cette classe
2. Assurez-vous que la classe existe et est active
3. Vérifiez que vous avez les permissions nécessaires
