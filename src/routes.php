<?php

declare(strict_types=1);
/*
-------------------------------------------------------------------------------
les routes
-------------------------------------------------------------------------------
 */

return [

    // Gérer l'administrateur


    //Gérer les créateurs
    //Inscription
    ['POST', '/createurs/register', 'createur@register'],

    //Connexion
    ['POST', '/createurs/login', 'createur@login'],

    //Token
    ['GET', '/createurs/checkToken', 'authorization@checkToken'],
    ['OPTIONS', '/createurs/checkToken', 'authorization@options'],
    //Création carte
    ['POST', '/createur', 'createur@createCard'],

    //Récupérer informations deck pour le créateur
    ['GET', '/createur', 'createur@getAllDecks'],
    ['GET', '/createur/deck/{id:\d+}', 'createur@getDeck'],

    //Récupérer informations carte pour le créateur 
    ['GET', '/createur/random/{id:\d+}', 'createur@getRandomCard'],
    ['GET', '/createur/selfCard/{id:\d+}', 'createur@getCard'],
    ['GET', '/createur/deckCard/{id:\d+}', 'createur@getCardByDeck'],

    //OPTIONS
    ['OPTIONS', '/createurs/login', 'authorization@options'],



    // Gérer la connexion des administrateurs
    ['POST', '/admin/login', 'admin@login'],
    ['OPTIONS', '/admin/login', 'authorization@options'],


    // Gérer les actions quand admin est connecté
    ['GET', '/createDeck', 'admin@createDeck'],
    ['POST', '/createDeck', 'admin@createDeck'],
    ['OPTIONS', '/createDeck', 'authorization@options'],

    ['GET', '/createFirstCard', 'admin@createFirstCard'],
    ['POST', '/createFirstCard', 'admin@createFirstCard'],
    ['OPTIONS', '/createFirstCard', 'authorization@options'],


    // Gérer le tableau de bord de l'administrateur
    ['GET', '/admin/dashboard', 'admin@dashboard'],
    ['OPTIONS', '/admin/dashboard', 'authorization@options'],
    ['GET', '/admin/delete/{id:\d+}', 'admin@delete'],
    ['GET', '/admin/deactivate/{id:\d+}', 'admin@deactivate'],
    ['GET', '/admin/activate/{id:\d+}', 'admin@activate'],
    ['GET', '/admin/edit/{id:\d+}', 'admin@edit'],
    ['POST', '/admin/edit/{id:\d+}', 'admin@edit'],

    // Delete des decks et cartes
    ['DELETE', '/admin/delete/deck/{id:\d+}', 'admin@deleteDeck'],
    ['DELETE', '/admin/delete/card/{id:\d+}', 'admin@deleteCard'],
    ['OPTIONS', '/admin/delete/deck/{id:\d+}', 'authorization@options'],
    ['OPTIONS', '/admin/delete/card/{id:\d+}', 'authorization@options'],



    // Getion des patch des decks
    ['PATCH', '/admin/deactivate/{id:\d+}', 'admin@deactivate'],
    ['PATCH', '/admin/activate/{id:\d+}', 'admin@activate'],
    ['OPTIONS', '/admin/deactivate/{id:\d+}', 'authorization@options'],
    ['OPTIONS', '/admin/activate/{id:\d+}', 'authorization@options'],



    ['GET', '/admin/deck/{id:\d+}', 'admin@showDeck'],

    ['GET', '/noDecks', 'game@noDecks']

];
