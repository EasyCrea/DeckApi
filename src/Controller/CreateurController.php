<?php

declare(strict_types=1); // strict mode

namespace App\Controller;

use App\Helper\HTTP;
use App\Model\Createur;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Model\Deck;
use App\Model\Carte;
use App\Model\CarteAleatoire;

class CreateurController extends Controller

{
    public function options()
    {
        // Permet uniquement localhost:5173 (ou votre URL frontend)
        header('Access-Control-Allow-Origin: http://localhost:5173');

        // Ajoutez les méthodes autorisées
        header('Access-Control-Allow-Methods: GET, POST, PATCH, DELETE, OPTIONS');

        // Ajoutez les en-têtes autorisés
        header('Access-Control-Allow-Headers: Authorization, Content-Type, X-Requested-With');

        // Si vous utilisez des cookies ou des informations d'identification
        header('Access-Control-Allow-Credentials: true');
    }


    public function register()
    {

        $request = Createur::getInstance()->create([
            'nom_createur' => trim($_POST['name']),
            'ad_email_createur' => trim($_POST['email']),
            'mdp_createur' => trim(password_hash($_POST['password'], PASSWORD_BCRYPT)),
            'ddn' => trim($_POST['ddn']),
            'genre' => trim($_POST['genre']),
        ]);
        if ($request) {
            echo json_encode([
                'status' => 'success',
                'nom_createur' => trim($_POST['name']),
                'ad_email_createur' => trim($_POST['email']),

            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Erreur lors de l\'enregistrement'
            ]);
        }
        if ($this->isPostMethod()) {
            // 1. vérifier les données soumises
            // 2. exécuter la requête d'insertion
            $request = Createur::getInstance()->create([
                'nom_createur' => trim($_POST['name']),
                'ad_email_createur' => trim($_POST['email']),
                'mdp_createur' => trim(password_hash($_POST['password'], PASSWORD_BCRYPT)),
                'ddn' => trim($_POST['ddn']),
                'genre' => trim($_POST['genre']),
            ]);

            if ($request) {
                echo json_encode([
                    'status' => 'success',
                    'nom_createur' => trim($_POST['name']),
                    'ad_email_createur' => trim($_POST['email']),

                ]);
            } else {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Erreur lors de l\'enregistrement'
                ]);
            }
        }
    }

    public function login()
    {
        $this->options();
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


    public function checkToken()
    {
        $this->options();

        // Vérification de la présence du token dans les en-têtes HTTP Authorization
        $headers = getallheaders();
        if (!isset($headers['Authorization'])) {
            http_response_code(400); // Code HTTP 400 pour Token manquant
            echo json_encode([
                'status' => 'error',
                'message' => 'Token manquant'
            ]);
            return;
        }

        // Extraction du token de l'en-tête Authorization
        $token = str_replace('Bearer ', '', $headers['Authorization']);

        try {
            // Tentative de décodage du token JWT
            $key = new Key(JWT_SECRET, 'HS256');
            $decoded = JWT::decode($token, $key);

            // Vérification du rôle encodé dans le token
            if (isset($decoded->role) && $decoded->role === 'admin') {
                // Si l'utilisateur est un admin
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Token valide',
                    'admin' => [
                        'id' => $decoded->id,
                        'email' => $decoded->email,
                        'role' => $decoded->role
                    ]
                ]);
            } elseif (isset($decoded->role) && $decoded->role === 'createur') {
                // Si l'utilisateur est un créateur
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Token valide',
                    'createur' => [
                        'id' => $decoded->id,
                        'email' => $decoded->email,
                        'role' => $decoded->role
                    ]
                ]);
            } else {
                // Si le rôle n'est pas reconnu
                http_response_code(403); // Code HTTP 403 pour rôle non autorisé
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Rôle non autorisé ou non spécifié'
                ]);
            }
        } catch (\Exception $e) {
            // Si une erreur se produit (token invalide, expiré, etc.)
            http_response_code(401); // Code HTTP 401 pour Token invalide ou expiré
            echo json_encode([
                'status' => 'error',
                'message' => 'Token invalide ou expiré',
                'error' => $e->getMessage() // Ajout du message d'erreur pour aider au débogage
            ]);
        }
    }




    public function createCard()
    {
        if ($this->isPostMethod()) {
            // 1. vérifier les données soumises
            $id_deck = trim($_POST['id_deck']);
            $text_carte = trim($_POST['text_carte']);
            $valeurs_choix1 = trim($_POST['valeurs_choix1']);
            $valeurs_choix2 = trim($_POST['valeurs_choix2']);
            $date_soumission = date('Y-m-d');
            $ordre_soumission = trim($_POST['ordre_soumission']);


            if ($_POST['id_createur'] != null) {
                $id_createur = trim($_POST['id_createur']);
            }
            if ($_POST['id_administrateur'] != null) {
                $id_administration = trim($_POST['id_administrateur']);
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
    public function getDeck()
    {
        if ($this->isGetMethod()) {
            // 1. vérifier les données soumises
        }
    }
    public function getCard()
    {
        if ($this->isGetMethod()) {
            // 1. vérifier les données soumises
        }
    }
    public function getCardByDeck()
    {
        if ($this->isGetMethod()) {
            // 1. vérifier les données soumises
        }
    }
}
