<?php

declare(strict_types=1); // strict mode

namespace App\Controller;

use App\Model\Createur;
use Firebase\JWT\JWT;
use Dotenv\Dotenv;
use App\Model\Deck;
use App\Model\Gamehistory;
use App\Model\Like;
use App\Model\Carte;
use App\Model\CarteAleatoire;
use App\Controller\AuthorizationController;
use Exception;



class CreateurController extends Controller

{



    public function register()
    {
        // Création d'une instance de l'autre contrôleur (AuthorizationController)
        $authorizationController = new AuthorizationController();
        $authorizationController->options(); // Appel des options CORS si nécessaire

        // Récupération et décodage des données JSON envoyées dans le body de la requête
        $data = json_decode(file_get_contents('php://input'), true);

        // Vérification des données soumises
        if (empty($data['nom_createur']) || empty($data['ad_email_createur']) || empty($data['mdp_createur']) || empty($data['ddn']) || empty($data['genre'])) {
            http_response_code(400); // Code 400 Bad Request
            echo json_encode([
                'status' => 'error',
                'message' => 'Tous les champs sont obligatoires.'
            ]);
            return;
        }

        // Validation de la date de naissance
        $date = \DateTime::createFromFormat('Y-m-d', $data['ddn']);
        if (!$date || $date->format('Y-m-d') !== $data['ddn']) {
            http_response_code(400); // Code 400 Bad Request
            echo json_encode([
                'status' => 'error',
                'message' => 'Format de date invalide. Utilisez le format YYYY-MM-DD.'
            ]);
            return;
        }

        // Hashage du mot de passe
        $hashedPassword = password_hash($data['mdp_createur'], PASSWORD_BCRYPT);
        if (!$hashedPassword) {
            http_response_code(500); // Code 500 Internal Server Error
            echo json_encode([
                'status' => 'error',
                'message' => 'Erreur lors du hashage du mot de passe.'
            ]);
            return;
        }

        // Préparation des données pour l'insertion
        $createurData = [
            'nom_createur' => trim($data['nom_createur']),
            'ad_email_createur' => trim($data['ad_email_createur']),
            'mdp_createur' => $hashedPassword,
            'ddn' => $date->format('Y-m-d'),
            'genre' => trim($data['genre']),
        ];

        // Exécution de la requête d'insertion
        try {
            $request = Createur::getInstance()->create($createurData);

            if ($request) {
                http_response_code(201); // Code 201 Created
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Créateur enregistré avec succès.',
                    'data' => [
                        'nom_createur' => $createurData['nom_createur'],
                        'ad_email_createur' => $createurData['ad_email_createur']
                    ]
                ]);
            } else {
                throw new Exception("Erreur lors de l'enregistrement.");
            }
        } catch (Exception $e) {
            http_response_code(500); // Code 500 Internal Server Error
            echo json_encode([
                'status' => 'error',
                'message' => 'Adresse e-mail déjà utilisée.'
            ]);
        }
    }


    public function login()
    {
        // Création d'une instance de l'autre contrôleur (par exemple, AuthorizationController)
        $authorizationController = new AuthorizationController();

        // Appel de la méthode options() depuis l'autre contrôleur
        $authorizationController->options();

        // Charger les variables d'environnement
        $dotenv = Dotenv::createImmutable(dirname(__DIR__, 2)); // Remonte à la racine du projet
        $dotenv->load();


        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['email'], $data['password'])) {
            http_response_code(400); // Code 400 Bad Request pour les données manquantes
            echo json_encode([
                'status' => 'error',
                'message' => 'Données manquantes'
            ]);
            return;
        }

        $email = $data['email'];
        $password = $data['password'];

        // Rechercher le créateur dans la base de données
        $createur = Createur::getInstance()->findOneBy([
            'ad_email_createur' => $email
        ]);

        if ($createur && password_verify($password, $createur['mdp_createur'])) {
            // Générer le token JWT
            $payload = [
                'id' => $createur['id_createur'],
                'email' => $createur['ad_email_createur'],
                'role' => 'createur',
                'exp' => time() + 3600 // Expiration dans 1 heure
            ];

            // Récupérer la clé secrète JWT depuis le .env
            $jwtSecret = $_ENV['JWT_SECRET'];

            // Encoder le token avec la clé secrète du .env
            $token = JWT::encode($payload, $jwtSecret, 'HS256');
            // Retourner la réponse avec le token
            echo json_encode([
                'status' => 'success',
                'message' => 'Connexion réussie',
                'token' => $token,
                'createur' => [
                    'id' => $createur['id_createur'],
                    'email' => $createur['ad_email_createur'],
                    'role' => 'createur'
                ]
            ]);
        } else {
            // Identifiants incorrects
            http_response_code(401); // Code 401 Unauthorized pour identifiants incorrects
            echo json_encode([
                'status' => 'error',
                'message' => 'Identifiants incorrects'
            ]);
        }
    }


    public function getGameHistory($userId, $deckId)
    {
        $authorizationController = new AuthorizationController();
        $authorizationController->options();
        // Vérifier si l'ID du créateur et de deck ont bien été passés en paramètres
        if ($userId && $deckId) {
            // Convertir l'ID du créateur et de deck en entiers (si nécessaire)
            $userId = (int) $userId;
            $deckId = (int) $deckId;

            // Vérifier que l'ID du créateur et du deck sont valides
            if ($userId > 0 && $deckId > 0) {
                // Récupérer l'historique de jeu pour cet utilisateur et ce deck
                $gameHistory = GameHistory::getInstance()->getGameHistoryByCreateurAndDeck($userId, $deckId);

                if ($gameHistory) {
                    // Si des historiques de jeu sont trouvés, les retourner sous forme JSON
                    echo json_encode([
                        'status' => 'success',
                        'game_history' => $gameHistory
                    ]);
                } else {
                    // Aucun historique trouvé pour cet utilisateur et ce deck
                    echo json_encode([
                        'status' => 'error',
                        'message' => 'Aucun historique de jeu trouvé pour ce créateur et ce deck.'
                    ]);
                }
            } else {
                // ID invalide ou non numérique
                echo json_encode([
                    'status' => 'error',
                    'message' => 'ID du créateur ou du deck invalide.'
                ]);
            }
        } else {
            // ID utilisateur ou deck manquant
            echo json_encode([
                'status' => 'error',
                'message' => 'ID du créateur ou du deck manquant.'
            ]);
        }
    }
    public function createGameHistory()
    {
        $authorizationController = new AuthorizationController();
        $authorizationController->options();

        // Récupérer les données de l'entrée (par exemple via POST)
        $data = json_decode(file_get_contents('php://input'), true);

        // Vérifier les champs requis
        if (
            !isset($data['user_id']) || !isset($data['deck_id']) || !isset($data['turn_count']) ||
            !isset($data['final_people']) || !isset($data['final_treasury']) || !isset($data['is_winner'])
        ) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Tous les champs requis doivent être fournis.'
            ]);
            return;
        }

        // Ajouter l'historique du jeu à la base de données
        $gameHistory = new Gamehistory();
        $result = $gameHistory->addGameHistory(
            $data['user_id'],
            $data['deck_id'],
            $data['turn_count'],
            $data['final_people'],
            $data['final_treasury'],
            $data['is_winner']
        );

        if ($result) {
            echo json_encode([
                'status' => 'success',
                'message' => 'Historique du jeu ajouté avec succès.'
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Une erreur est survenue lors de l\'ajout de l\'historique.'
            ]);
        }
    }
    public function deleteGameHistory(int $id)
    {
        $authorizationController = new AuthorizationController();
        $authorizationController->options();

        // Vérifier que l'ID est valide
        if (!$id) {
            echo json_encode([
                'status' => 'error',
                'message' => 'ID de l\'historique du jeu invalide.'
            ]);
            return;
        }

        // Supprimer l'historique du jeu
        $gameHistory = new Gamehistory();
        $result = $gameHistory->deleteGameHistory($id);

        if ($result) {
            echo json_encode([
                'status' => 'success',
                'message' => 'Historique du jeu supprimé avec succès.'
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Une erreur est survenue lors de la suppression.'
            ]);
        }
    }
    public function updateGameHistory()
    {
        $authorizationController = new AuthorizationController();
        $authorizationController->options();

        // Récupérer les données de l'entrée (par exemple via POST)
        $data = json_decode(file_get_contents('php://input'), true);

        // Vérifier les champs requis
        if (
            !isset($data['id']) || !isset($data['turn_count']) || !isset($data['final_people']) ||
            !isset($data['final_treasury']) || !isset($data['is_winner'])
        ) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Tous les champs requis doivent être fournis.'
            ]);
            return;
        }

        // Mettre à jour l'historique du jeu
        $gameHistory = new GameHistory();
        $result = $gameHistory->updateGameHistory(
            $data['id'],
            $data['turn_count'],
            $data['final_people'],
            $data['final_treasury'],
            $data['is_winner']
        );

        if ($result) {
            echo json_encode([
                'status' => 'success',
                'message' => 'Historique du jeu mis à jour avec succès.'
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Une erreur est survenue lors de la mise à jour de l\'historique.'
            ]);
        }
    }


    public function createCard(int|string $id)
    {
        $id = (int) $id;
        $authorizationController = new AuthorizationController();
        $authorizationController->options();

        // Vérification du token
        $decoded = $authorizationController->validateAdminToken() ?: $authorizationController->validateCreateurToken();

        if (!$decoded) {
            http_response_code(403);
            echo json_encode([
                'status' => 'error',
                'message' => 'Accès refusé : aucun token valide ou rôle incorrect',
            ]);
            return;
        }

        $role = $decoded->role ?? null; // Récupérer le rôle
        $userId = $decoded->id ?? null; // Récupérer l'ID utilisateur

        if (!$role || !$userId) {
            http_response_code(403);
            echo json_encode([
                'status' => 'error',
                'message' => 'Accès refusé : rôle ou ID utilisateur manquant',
            ]);
            return;
        }

        // Traitement des données
        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['event_description'], $data['choice_1'], $data['population_impact_1'], $data['finance_impact_1'], $data['choice_2'], $data['population_impact_2'], $data['finance_impact_2'])) {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => 'Données invalides ou incomplètes',
            ]);
            return;
        }

        $carteDansLeDeck = Carte::getInstance()->getNumberOfCardsInDeck($id);
        if ($carteDansLeDeck >= 10) {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => 'Le deck est complet',
            ]);
            return;
        }

        $userHasCreatedCard = Carte::getInstance()->getIfCreatorHasCreatedCardInDeck($userId, $id);
        if ($userHasCreatedCard) {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => 'Vous avez déjà créé une carte dans ce deck',
            ]);
            return;
        }
        $date_soumission = (new \DateTime())->format('Y-m-d');

        $cardData = [
            'event_description' => $data['event_description'],
            'choice_1' => $data['choice_1'],
            'population_impact_1' => $data['population_impact_1'],
            'finance_impact_1' => $data['finance_impact_1'],
            'choice_2' => $data['choice_2'],
            'population_impact_2' => $data['population_impact_2'],
            'finance_impact_2' => $data['finance_impact_2'],
            'created_at' => $date_soumission,
            'ordre_soumission' => $carteDansLeDeck + 1,
            'id_deck' => $id,
        ];

        // Associer l'utilisateur selon le rôle
        if ($role === 'admin') {
            $cardData['id_administrateur'] = $userId;
        } elseif ($role === 'createur') {
            $cardData['id_createur'] = $userId;
        }

        try {
            Carte::getInstance()->create($cardData);
            echo json_encode([
                'status' => 'success',
                'message' => 'Carte créée avec succès',
            ]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => $e->getMessage(),
            ]);
        }
    }



    public function assignRandomCard(
        int|string $id_deck,
        int|string $id_createur
    ) {
        $id_deck = (int) $id_deck;
        $id_createur = (int) $id_createur;

        $authorizationController = new AuthorizationController();
        $authorizationController->options();

        // Vérification du token
        $decodedToken = $authorizationController->validateCreateurToken();
        if (!$decodedToken) {
            // La méthode `validateAdminToken` gère déjà la réponse HTTP en cas d'erreur.
            return;
        }
        // Verifier si le createur a déjà une carte aléatoire dans ce deck
        $verif = CarteAleatoire::getInstance()->findOneBy([
            'id_deck' => $id_deck,
            'id_createur' => $id_createur
        ]);
        if ($verif) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Vous avez déjà une carte aléatoire dans ce deck'
            ]);
        } else {

            $all_card = Carte::getInstance()->findAll();

            $id_possible = [];
            foreach ($all_card as $card) {
                if ($card['id_deck'] === $id_deck) {
                    $id_possible[] = $card['id_carte'];
                }
            }

            $id_random_key = array_rand($id_possible); // Obtenir une clé aléatoire
            $id_random = $id_possible[$id_random_key]; // Obtenir l'ID correspondant



            $carteAleatoire = CarteAleatoire::getInstance()->create([
                'id_deck' => $id_deck,
                'id_createur' => $id_createur,
                'id_carte' => $id_random,
            ]);

            if ($carteAleatoire) {
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Carte aléatoire créée avec succès',
                    'carteAleatoire' => Carte::getInstance()->findOneBy(['id_carte' => $id_random])
                ]);
            } else {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Erreur lors de la création de la carte aléatoire'
                ]);
            }
        }
    }

    public function checkIfCreatorHasCard(
        int|string $id_deck,
        int|string $id_createur
    ) {
        $id_deck = (int) $id_deck;
        $id_createur = (int) $id_createur;
        $authorizationController = new AuthorizationController();
        $authorizationController->options();
        // Vérification du token
        $decodedToken = $authorizationController->validateCreateurToken();
        if (!$decodedToken) {
            // La méthode `validateAdminToken` gère déjà la réponse HTTP en cas d'erreur.
            return;
        }
        $verif = CarteAleatoire::getInstance()->findOneBy([
            'id_deck' => $id_deck,
            'id_createur' => $id_createur
        ]);
        if ($verif) {
            echo json_encode([
                'status' => 'success',
                'message' => 'Vous avez déjà une carte aléatoire dans ce deck',
                'card' => Carte::getInstance()->findOneBy(['id_carte' => $verif['id_carte']])
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Vous n\'avez pas de carte aléatoire dans ce deck'
            ]);
        }
    }

    public function getAllDecks()
    {
        $authorizationController = new AuthorizationController();
        $authorizationController->options();
        $decodedToken = $authorizationController->validateCreateurToken();
        if (!$decodedToken) {
            // La méthode `validateAdminToken` gère déjà la réponse HTTP en cas d'erreur.
            return;
        }
        // 1. vérifier les données soumises
        $decks = Deck::getInstance()->findAll();
        if ($decks) {
            echo json_encode([
                'status' => 'success',
                'decks' => $decks
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Erreur lors de la récupération des decks'
            ]);
        }
    }
    public function getCreateurByDeck($id)
    {
        $authorizationController = new AuthorizationController();
        $authorizationController->options();
        $decodedToken = $authorizationController->validateCreateurToken();
        if (!$decodedToken) {
            // La méthode `validateAdminToken` gère déjà la réponse HTTP en cas d'erreur.
            return;
        }
        $id = (int) $id;
        $carte = Carte::getInstance()->findAllBy([
            'id_deck' => $id
        ]);
        $createurs = [];
        foreach ($carte as $card) {
            $createur = Createur::getInstance()->findOneBy([
                'id_createur' => $card['id_createur']
            ]);
            if ($createur) {
                $createurs[] = $createur;
            }
        }
        if ($createurs) {
            echo json_encode([
                'status' => 'success',
                'createurs' => $createurs
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Erreur lors de la récupération des createurs'
            ]);
        }
    }
    public function getRandomCard(
        int|string $id
    ) {
        $id = (int) $id;
        // 1. vérifier les données soumises
        $card = Carte::getInstance()->findOneBy([
            'id_createur' => $id,
        ]);;


        $cardalea = CarteAleatoire::getInstance()->findOneBy([
            'id_createur' => $card['id_createur'],
        ]);
        if ($cardalea) {
            echo json_encode([
                'status' => 'success',
                'card' => $cardalea
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Erreur lors de la récupération de la carte aléatoire'
            ]);
        }
    }
    public function getDeck(
        int|string $id
    ) {
        $authorizationController = new AuthorizationController();
        $authorizationController->options();

        // 1. vérifier les données soumises
        $id = (int) $id;

        $deck = Deck::getInstance()->findOneBy([
            'id_deck' => $id
        ]);

        if ($deck) {
            echo json_encode([
                'status' => 'success',
                'deck' => $deck
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Deck not found'
            ]);
        }
    }
    public function getCard(
        int|string $id
    ) {
        // 1. vérifier les données soumises
        $id = (int) $id;
        $card = Carte::getInstance()->findOneBy([
            'id_carte' => $id
        ]);
        if ($card) {
            echo json_encode([
                'status' => 'success',
                'card' => $card
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Carte not found'
            ]);
        }
    }
    public function getCardByDeck(
        int|string $id
    ) {
        $authorizationController = new AuthorizationController();
        $authorizationController->options();
        // 1. vérifier les données soumises
        $id = (int) $id;
        $cards = Carte::getInstance()->findAllBy([
            'id_deck' => $id
        ]);
        if ($cards) {
            echo json_encode([
                'status' => 'success',
                'cards' => $cards
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Cartes not found'
            ]);
        }
    }

    public function getLiveDeck()
    {
        $authorizationController = new AuthorizationController();
        $authorizationController->options();
        $decodedToken = $authorizationController->validateCreateurToken();
        if (!$decodedToken) {
            // La méthode `validateAdminToken` gère déjà la réponse HTTP en cas d'erreur.
            return;
        }
        $deck = Deck::getInstance()->getLiveDeck();
        if ($deck) {
            echo json_encode([
                'status' => 'success',
                'deck' => $deck
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Deck not found'
            ]);
        }
    }

    public function getLiveDeckCards($id_deck)
    {

        $authorizationController = new AuthorizationController();
        $authorizationController->options();
        $decodedToken = $authorizationController->validateCreateurToken();
        if (!$decodedToken) {
            // La méthode `validateAdminToken` gère déjà la réponse HTTP en cas d'erreur.
            return;
        }
        // 1. vérifier les données soumises
        $id_deck = (int) $id_deck;
        $cards = Deck::getInstance()->getCardsByDeckId($id_deck);
        $deck = Deck::getInstance()->findOneBy([
            'id_deck' => $id_deck
        ]);
        $titleDeck = $deck['titre_deck'] ?? null;
        $descriptionDeck = $deck['description'] ?? null;
        if ($cards && $titleDeck) {
            echo json_encode([
                'status' => 'success',
                'cards' => $cards,
                'titleDeck' => $titleDeck,
                'descriptionDeck' => $descriptionDeck
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Cartes not found'
            ]);
        }
    }

    public function getCreatedCard($id_deck, $id_createur)
    {
        $authorizationController = new AuthorizationController();
        $authorizationController->options();
        $decodedToken = $authorizationController->validateCreateurToken();
        if (!$decodedToken) {
            // La méthode `validateAdminToken` gère déjà la réponse HTTP en cas d'erreur.
            return;
        }
        $id_deck = (int) $id_deck;
        $id_createur = (int) $id_createur;
        $card = Carte::getInstance()->getIfCreatorHasCreatedCardInDeck($id_createur, $id_deck);
        if ($card) {
            echo json_encode([
                'status' => 'success',
                'card' => $card
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Carte not found'
            ]);
        }
    }

    public function likeDeck($id_deck)
    {
        $authorizationController = new AuthorizationController();
        $authorizationController->options();
        $decodedToken = $authorizationController->validateCreateurToken();
        if (!$decodedToken) {
            // La méthode `validateAdminToken` gère déjà la réponse HTTP en cas d'erreur.
            return;
        }
        $id_deck = (int) $id_deck;
        $deck = Deck::getInstance()->findOneBy([
            'id_deck' => $id_deck
        ]);

        if ($deck) {
            $nb_jaime = $deck['nb_jaime'] + 1;
            $update = Deck::getInstance()->updateDeck($id_deck, ['nb_jaime' => $nb_jaime]);
            if ($update) {
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Like ajouté avec succès',
                    'likes' => $nb_jaime
                ]);
            } else {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Erreur lors de l\'ajout du like'
                ]);
            }
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Deck not found'
            ]);
        }
    }
    public function ajoutLike($id_deck, $id_createur)
    {
        
        $authorizationController = new AuthorizationController();
        $authorizationController->options();
        $decodedToken = $authorizationController->validateCreateurToken();

        if (!$decodedToken) {
            // La méthode `validateAdminToken` gère déjà la réponse HTTP en cas d'erreur.
            return;
        }
        $id_createur = (int) $id_createur;
        $id_deck = (int) $id_deck;

        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $existing = Like::getInstance()->findOneBy([
                'id_deck' => $id_deck,
                'id_createur' => $id_createur
            ]);
            
            if ($existing) {
                echo json_encode([
                    'status' => 'success',
                    'message' => $existing
                ]);
            } else {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Like non trouvé'
                ]);
            }
        }

       
        $existing = Like::getInstance()->findOneBy([
            'id_deck' => $id_deck,
            'id_createur' => $id_createur
        ]);
        if ($existing) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Like déjà ajouté'
            ]);
            return;
        } else {
            $createur = Like::getInstance()->create([
                'id_deck' => $id_deck,
                'id_createur' => $id_createur
            ]);
            if ($createur) {
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Like ajouté avec succès'
                ]);
            } else {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Erreur lors de l\'ajout du like'
                ]);
            }
        }
    }
}
