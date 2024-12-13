<?php

declare(strict_types=1);

return [

    // Gestion des administrateurs
    ['POST', '/admin/login', 'admin@login'],
    ['GET', '/admin/logout', 'admin@logout'],

    // Gestion des decks
    ['POST', '/admin', 'admin@createDeck'], 
    ['POST', '/admin/firstcard', 'admin@createFirstCard'], 
    ['GET', '/admin', 'admin@getAllDeck'],
    ['PATCH', '/admin/deck/{id:\d+}', 'admin@editById'],
    ['DELETE', '/admin/{id:\d+}', 'admin@deleteById'],

    // Gestion des cartes dans les decks
    ['GET', '/admin/deck/{id:\d+}', 'admin@getAllCardInDeck'],
    ['GET', '/admin/card/{id:\d+}', 'admin@getCardById'],
    ['PATCH', '/admin/edit/{id:\d+}', 'admin@editCardById'],
    ['DELETE', '/admin/delete/{id:\d+}', 'admin@deleteCardById'],

    // OPTIONS pour les requêtes CORS
    ['OPTIONS', '/admin', 'admin@options'],
    ['OPTIONS', '/admin/{id:\d+}', 'admin@options'],
    ['OPTIONS', '/admin/deck/{id:\d+}', 'admin@options'],
    ['OPTIONS', '/admin/card/{id:\d+}', 'admin@options'],
];
