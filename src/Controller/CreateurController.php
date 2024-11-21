<?php

declare(strict_types=1); // strict mode

namespace App\Controller;

use App\Model\Createur;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Model\Deck;
use App\Model\Carte;
use App\Model\CarteAleatoire;
use App\Controller\AuthorizationController;



class CreateurController extends Controller

{



    public function register()
    {

        $data = json_decode(file_get_contents('php://input'), true);
        // 1. vérifier les données soumises
        // 2. exécuter la requête d'insertion
        $date = \DateTime::createFromFormat('Y-m-d', $data['ddn']);
        if (!$date || $date->format('Y-m-d') !== $data['ddn']) {
            http_response_code(400); // Code 400 Bad Request
            echo json_encode([
                'status' => 'error',
                'message' => 'Format de date invalide. Utilisez le format YYYY-MM-DD.'
            ]);
            return;
        }

        $request = Createur::getInstance()->create([
            'nom_createur' => trim($data['name']),
            'ad_email_createur' => trim($data['email']),
            'mdp_createur' => trim(password_hash($data['password'], PASSWORD_BCRYPT)),
            'ddn' => $date->format('Y-m-d'),
            'genre' => trim($data['genre']),
        ]);

        if ($request) {
            echo json_encode([
                'status' => 'success',
                'nom_createur' => trim($data['name']),
                'ad_email_createur' => trim($data['email']),

            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Erreur lors de l\'enregistrement'
            ]);
        }
    }

    public function login()
    {
        // Création d'une instance de l'autre contrôleur (par exemple, AuthorizationController)
        $authorizationController = new AuthorizationController();

        // Appel de la méthode options() depuis l'autre contrôleur
        $authorizationController->options();
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

            $token = JWT::encode($payload, JWT_SECRET, 'HS256');

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

        if (!isset($data['texte_carte'], $data['valeurs_choix1'], $data['valeurs_choix2'])) {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => 'Données invalides ou incomplètes',
            ]);
            return;
        }

        $carteDansLeDeck = Carte::getInstance()->getNumberOfCardsInDeck($id);
        $date_soumission = (new \DateTime())->format('Y-m-d');

        $cardData = [
            'texte_carte' => $data['texte_carte'],
            'valeurs_choix1' => $data['valeurs_choix1'],
            'valeurs_choix2' => $data['valeurs_choix2'],
            'date_soumission' => $date_soumission,
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



    public function createRandomCard(
        int|string $id_deck,
        int|string $id_createur
    ) {
        $id_deck = (int) $id_deck;
        $id_createur = (int) $id_createur;

        $verif = CarteAleatoire::getInstance()->findOneBy([
            'id_createur' => $id_createur,
        ]);
        if ($verif) {
            return;
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
                    'message' => 'Carte aléatoire créée avec succès'
                ]);
            } else {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Erreur lors de la création de la carte aléatoire'
                ]);
            }
        }
    }

    public function getAllDecks()
    {
        if ($this->isGetMethod()) {
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
}
