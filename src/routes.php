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
    ['GET', '/createurs/checkToken', 'createur@checkToken'],

    //Création carte
    ['POST', '/createur', 'createur@createCard'],

    //Récupérer informations deck pour le créateur
    ['GET', '/createur','createur@getAllDecks'],
    ['GET', '/createu/deck/{id:\d+}', 'createur@getDeck'],

    //Récupérer informations carte pour le créateur 
    ['GET', '/createur/random/{id:\d+}', 'createur@getRandomCard'],
    ['GET', '/createur/selfCard/{id:\d+}', 'createur@getCard'],
    ['GET', '/createur/deckCard/{id:\d+}', 'createur@getCardByDeck'],


    //OPTIONS
    ['OPTIONS', '/createurs/login', 'createur@options'],
    ['OPTIONS', '/createurs/checkToken', 'createur@options'],


    // Gérer la connexion des administrateurs
    ['POST', '/admin/login', 'admin@login'],
    ['OPTIONS', '/admin/login', 'admin@options'],


    // Gérer les actions quand admin est connecté
    ['GET', '/createDeck', 'admin@createDeck'],
    ['POST', '/createDeck', 'admin@createDeck'],
    ['GET', '/createFirstCard', 'admin@createFirstCard'],
    ['POST', '/createFirstCard', 'admin@createFirstCard'],
    // Gérer le tableau de bord de l'administrateur
    ['GET', '/admin/dashboard', 'admin@dashboard'],
    ['OPTIONS', '/admin/dashboard', 'admin@options'],
    ['GET', '/admin/delete/{id:\d+}', 'admin@delete'],
    ['GET', '/admin/deactivate/{id:\d+}', 'admin@deactivate'],
    ['GET', '/admin/activate/{id:\d+}', 'admin@activate'],
    ['GET', '/admin/edit/{id:\d+}', 'admin@edit'],
    ['POST', '/admin/edit/{id:\d+}', 'admin@edit'],

    // Getion des patch des decks
    ['PATCH', '/admin/deactivate/{id:\d+}', 'admin@deactivate'],
    ['PATCH', '/admin/activate/{id:\d+}', 'admin@activate'],
    ['OPTIONS', '/admin/deactivate/{id:\d+}', 'admin@options'],
    ['OPTIONS', '/admin/activate/{id:\d+}', 'admin@options'],



    ['GET', '/admin/deck/{id:\d+}', 'admin@showDeck'],

    ['GET', '/noDecks', 'game@noDecks']

];
