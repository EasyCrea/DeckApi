<?php

declare(strict_types=1);
/*
-------------------------------------------------------------------------------
les routes
-------------------------------------------------------------------------------
 */

return [
    // Gérer l'accueil
    ['GET', '/', 'createur@login'],

    // Gérer la création de créateurs
    ['GET', '/createurs/register', 'createur@register'],
    ['POST', '/createurs/register', 'createur@register'],

    // Gérer les connexions de créateurs
    ['GET', '/createurs/login', 'createur@login'],
    ['POST', '/createurs/login', 'createur@login'],

    // Gérer les déconnexions de créateurs
    ['GET', '/createurs/logout', 'createur@logout'],

    // Gérer les actions quand utilisateur est connecté
    ['GET', '/game', 'game@index'],
    ['POST', '/game', 'game@index'],


    // Gérer la déconnexion de l'utilisateur
    ['GET', '/createur/logout', 'createur@logout'],

    // Gérer la création de l'administrateur
    // ['GET', '/admin/register', 'admin@register'],
    // ['POST', '/admin/register', 'admin@register'],

    // Gérer la connexion des administrateurs
    ['GET', '/admin/login', 'admin@login'],
    ['POST', '/admin/login', 'admin@login'],

    // Gérer les déconnexions des administrateurs
    ['GET', '/admin/logout', 'admin@logout'],

    // Gérer les actions quand admin est connecté
    ['GET', '/createDeck', 'admin@createDeck'],
    ['POST', '/createDeck', 'admin@createDeck'],
    ['GET', '/createFirstCard', 'admin@createFirstCard'],
    ['POST', '/createFirstCard', 'admin@createFirstCard'],
    // Gérer le tableau de bord de l'administrateur
    ['GET', '/admin/dashboard', 'admin@dashboard'],
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
