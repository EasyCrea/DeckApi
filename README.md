# API EasyCrea

## Description
Cette API PHP utilise l'architecture MVC (Modèle-Vue-Contrôleur) pour gérer les fonctionnalités de EasyCrea et Deckouverte.

## Prérequis
- PHP 7.4+
- Serveur web (Apache/Nginx)
- Base de données MySQL
- Composer

## Installation

### Clonage du projet
```bash
git clone [https://github.com/EasyCrea/API.git](https://github.com/EasyCrea/DeckApi)
cd API
```

### Installation des dépendances
```bash
composer install
```

## Routes Principales

### Authentification
- `POST /createurs/register` : Inscription de créateur
- `POST /createurs/login` : Connexion de créateur
- `POST /admin/login` : Connexion admin
- `GET /authorization/checkToken` : Vérification de token

### Créateurs
- `GET /allcreateur` : Liste de tous les créateurs
- `GET /createur/{deck_id}/{createur_id}` : Informations du créateur
- `POST /createur/{deck_id}/{createur_id}` : Assignation de carte aléatoire

### Cartes
- `POST /createCard{id}` : Création de carte
- `PATCH /admin/edit/card/{id}` : Modification de carte
- `GET /createur/random/{id}` : Récupération de carte aléatoire

### Decks
- `GET /getAllDeck` : Liste de tous les decks
- `GET /createur/deck/{id}` : Détails d'un deck
- `POST /admin/createDeck` : Création de deck

### Administration
- `GET /admin/dashboard` : Tableau de bord admin
- `DELETE /admin/delete/deck/{id}` : Suppression de deck
- `PATCH /admin/deactivate/{id}` : Désactivation de deck

### Historique de Jeu
- `GET /gamehistory/{user_id}/{deck_id}` : Historique de jeu
- `POST /creategamehistory` : Création d'entrée d'historique
- `DELETE /deletegamehistory/{id}` : Suppression d'entrée d'historique

## Gestion des Likes
- `POST /like/{deck_id}/{createur_id}` : Ajout de like
- `DELETE /like/delete/{deck_id}/{createur_id}` : Suppression de like

## Authentification & Sécurité
- JWT (JSON Web Token) pour l'authentification
- Middleware de vérification de token
- Protection contre les CORS
- Validation et assainissement des entrées
