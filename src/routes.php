<?php

declare(strict_types=1);

/*
-------------------------------------------------------------------------------
Les routes
-------------------------------------------------------------------------------
*/

return [

    // Gérer les créateurs (Créateurs, Inscription, Connexion, etc.)
    // Inscription
    ['POST', '/createurs/register', 'createur@register'],

    // Connexion
    ['POST', '/createurs/login', 'createur@login'],

    // Vérification du token
    ['GET', '/createurs/checkToken', 'authorization@checkToken'],
    ['OPTIONS', '/createurs/checkToken', 'authorization@options'],

    // Création carte
    ['POST', '/createCard', 'createur@createCard'],
    ['OPTIONS', '/createCard', 'authorization@options'],

    // Récupérer informations deck pour le créateur
    ['GET', '/createur', 'createur@getAllDecks'],
    ['GET', '/createur/deck/{id:\d+}', 'createur@getDeck'],

    // Récupérer informations carte pour le créateur
    ['GET', '/createur/random/{id:\d+}', 'createur@getRandomCard'],
    ['GET', '/createur/selfCard/{id:\d+}', 'createur@getCard'],
    ['GET', '/createur/deckCard/{id:\d+}', 'createur@getCardByDeck'],

    // OPTIONS pour /createurs/login
    ['OPTIONS', '/createurs/login', 'authorization@options'],


    // Gérer la connexion des administrateurs
    ['POST', '/admin/login', 'admin@login'],
    ['OPTIONS', '/admin/login', 'authorization@options'],


    // Gérer les actions quand l'admin est connecté
    // Gestion des decks
    ['GET', '/admin/createDeck', 'admin@createDeck'],
    ['POST', '/admin/createDeck', 'admin@createDeck'],
    ['OPTIONS', '/admin/createDeck', 'authorization@options'],

    ['GET', '/createFirstCard', 'admin@createFirstCard'],
    ['POST', '/createFirstCard', 'admin@createFirstCard'],
    ['OPTIONS', '/createFirstCard', 'authorization@options'],

    // Tableau de bord de l'administrateur
    ['GET', '/admin/dashboard', 'admin@dashboard'],
    ['OPTIONS', '/admin/dashboard', 'authorization@options'],

    // Actions sur le deck
    ['GET', '/admin/delete/{id:\d+}', 'admin@delete'],
    ['GET', '/admin/deactivate/{id:\d+}', 'admin@deactivate'],
    ['GET', '/admin/activate/{id:\d+}', 'admin@activate'],
    ['GET', '/admin/edit/{id:\d+}', 'admin@edit'],
    ['POST', '/admin/edit/{id:\d+}', 'admin@edit'],

    // Suppression des decks et cartes
    ['DELETE', '/admin/delete/deck/{id:\d+}', 'admin@deleteDeck'],
    ['DELETE', '/admin/delete/card/{id:\d+}', 'admin@deleteCard'],
    ['OPTIONS', '/admin/delete/deck/{id:\d+}', 'authorization@options'],
    ['OPTIONS', '/admin/delete/card/{id:\d+}', 'authorization@options'],

    // Gestion des actions PATCH des decks
    ['PATCH', '/admin/deactivate/{id:\d+}', 'admin@deactivate'],
    ['PATCH', '/admin/activate/{id:\d+}', 'admin@activate'],
    ['OPTIONS', '/admin/deactivate/{id:\d+}', 'authorization@options'],
    ['OPTIONS', '/admin/activate/{id:\d+}', 'authorization@options'],

    // Récupérer les informations d'un deck spécifique
    ['GET', '/admin/deck/{id:\d+}', 'admin@showDeck'],
    ['OPTIONS', '/admin/deck/{id:\d+}', 'authorization@options'],

    // Gestion des decks sans deck
    ['GET', '/noDecks', 'game@noDecks']
];
