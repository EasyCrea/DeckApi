<?php

declare(strict_types=1); // strict mode

namespace App\Controller;

use App\Model\Createur;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Model\Deck;
use App\Model\Carte;
use App\Model\CarteAleatoire;



class CreateurController extends Controller

{



    public function register()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        // 1. vérifier les données soumises
        // 2. exécuter la requête d'insertion

        $request = Createur::getInstance()->create([
            'nom_createur' => trim($data['name']),
            'ad_email_createur' => trim($data['email']),
            'mdp_createur' => trim(password_hash($data['password'], PASSWORD_BCRYPT)),
            'ddn' => trim($data['ddn']),
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







    public function createCard()
    {
        if ($this->isPostMethod()) {

            $data = json_decode(file_get_contents('php://input'), true);

            // 1. vérifier les données soumises
            $id_deck = trim($data['id_deck']);
            $text_carte = trim($data['text_carte']);
            $valeurs_choix1 = trim($data['valeurs_choix1']);
            $valeurs_choix2 = trim($data['valeurs_choix2']);
            $date_soumission = date('Y-m-d');
            $ordre_soumission = trim($data['ordre_soumission']);


            if ($_POST['id_createur'] != null) {
                $id_createur = trim($data['id_createur']);
            }
            if ($_POST['id_administrateur'] != null) {
                $id_administration = trim($data['id_administrateur']);
            }

            if ($id_createur) {
                $creation =  Carte::getInstance()->create([
                    'id_deck' => $id_deck,
                    'text_carte' => $text_carte,
                    'valeurs_choix1' => $valeurs_choix1,
                    'valeurs_choix2' => $valeurs_choix2,
                    'date_soumission' => $date_soumission,
                    'ordre_soumission' => $ordre_soumission,
                    'id_createur' => $id_createur,
                ]);
                $this->createRandomCard($id_deck, $id_createur);
            }
            if ($id_administration) {
                $creation =  Carte::getInstance()->create([
                    'id_deck' => $id_deck,
                    'text_carte' => $text_carte,
                    'valeurs_choix1' => $valeurs_choix1,
                    'valeurs_choix2' => $valeurs_choix2,
                    'date_soumission' => $date_soumission,
                    'ordre_soumission' => $ordre_soumission,
                    'id_createur' => $id_createur,
                ]);
            }

            if ($creation) {
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Carte créée avec succès'
                ]);
            } else {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Erreur lors de la création de la carte'
                ]);
            }
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

            if ($all_card) {
                $id_random = mt_rand(0, count($all_card) - 1);
            }

            $carteAleatoire = CarteAleatoire::getInstance()->create([
                'id_deck' => $id_deck,
                'id_createur' => $id_createur,
                'id_carte' => $id_random
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
        if ($this->isGetMethod()) {
            // 1. vérifier les données soumises
            $card = CarteAleatoire::getInstance()->find($id);
            if ($card) {
                echo json_encode([
                    'status' => 'success',
                    'card' => $card
                ]);
            } else {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Erreur lors de la récupération de la carte aléatoire'
                ]);
            }
        }
    }
    public function getDeck(
        int|string $id
    ) {
        if ($this->isGetMethod()) {
            // 1. vérifier les données soumises
            $id = (int) $id;
            $deck = Deck::getInstance()->find($id);
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
    }
    public function getCard(
        int|string $id
    ) {
        if ($this->isGetMethod()) {
            // 1. vérifier les données soumises
            $id = (int) $id;
            $card = Carte::getInstance()->find($id);
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
    public function getCardByDeck(
        int|string $id
    ) {
        if ($this->isGetMethod()) {
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
    }
}
