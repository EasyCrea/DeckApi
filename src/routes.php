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
    ['OPTIONS', '/createurs/register', 'authorization@options'],

    // Connexion
    ['POST', '/createurs/login', 'createur@login'],

    // Vérification du token
    ['GET', '/createurs/checkToken', 'authorization@checkToken'],
    ['OPTIONS', '/createurs/checkToken', 'authorization@options'],

    // Création carte
    ['POST', '/createCard{id:\d+}', 'createur@createCard'],
    ['OPTIONS', '/createCard{id:\d+}', 'authorization@options'],

    // Edition d'une carte
    ['PATCH', '/admin/edit/card/{id:\d+}', 'admin@editCard'],
    ['OPTIONS', '/admin/edit/card/{id:\d+}', 'authorization@options'],

    // Récupérer informations sur une carte la modification
    ['GET', '/admin/card/{id:\d+}', 'admin@getCard'],
    ['OPTIONS', '/admin/card/{id:\d+}', 'authorization@options'],

    // Récuperer le deck en live avec ses informations
    ['GET', '/createur/liveDeck', 'createur@getLiveDeck'],
    ['OPTIONS', '/createur/liveDeck', 'authorization@options'],

    // Récupérer les cartes du deck en live
    ['GET', '/createur/liveDeckCards/{id_deck:\d+}', 'createur@getLiveDeckCards'],
    ['OPTIONS', '/createur/liveDeckCards/{id_deck:\d+}', 'authorization@options'],

    // Récupérer la carte créée par le créateur connecté
    ['GET', '/createur/{id_deck:\d+}/{id_createur:\d+}', 'createur@getCreatedCard'],
    ['OPTIONS', '/createur/{id_deck:\d+}/{id_createur:\d+}', 'authorization@options'],

    // Récupérer informations deck pour le créateur
    ['GET', '/createur', 'createur@getAllDecks'],
    ['OPTIONS', '/createur', 'authorization@options'],
    
    // Récupérer les données d'un seul deck
    ['GET', '/createur/deck/{id:\d+}', 'createur@getDeck'],
    ['OPTIONS', '/createur/deck/{id:\d+}', 'authorization@options'],

    // Ajout d'un like sur le deck
    ['PATCH', '/likeDeck/{id_deck:\d+}', 'createur@likeDeck'],
    ['OPTIONS', '/likeDeck/{id:\d+}', 'authorization@options'],

    // Route POST
    ['POST', '/like/{id_deck:\d+}/{id_createur:\d+}', 'createur@ajoutLike'],
    ['OPTIONS', '/like/{id_deck:\d+}/{id_createur:\d+}', 'authorization@options'],
    
    // Récupérer informations carte pour le créateur
    ['GET', '/createur/random/{id:\d+}', 'createur@getRandomCard'],
    ['GET', '/createur/selfCard/{id:\d+}', 'createur@getCard'],
    ['GET', '/createur/deckCard/{id:\d+}', 'createur@getCardByDeck'],

    //Récupérer les participants des différents decks
    ['GET', '/createur/participants/{id:\d+}', 'createur@getCreateurByDeck'],
    ['OPTIONS', '/createur/participants/{id:\d+}', 'authorization@options'],

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
