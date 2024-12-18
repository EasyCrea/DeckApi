<?php

namespace App\Controller;

use Firebase\JWT\JWT;
use App\Model\Admin;
use Dotenv\Dotenv;
use App\Model\Deck;
use App\Model\Carte;

class AdminController extends Controller
{
    public function index()
    {
        echo json_encode("Ceci est un test");
    }

    // Connexion API
    public function login()
    {
        // Création d'une instance de l'autre contrôleur
        $authorizationController = new AuthorizationController();

        $authorizationController->options();

        // Charger les variables d'environnement
        $dotenv = Dotenv::createImmutable(dirname(__DIR__, 2)); // Remonte à la racine du projet

        $dotenv->load();

        // Lire les données JSON envoyées dans la requête
        $data = json_decode(file_get_contents('php://input'), true);

        // Vérifier si les données requises (email et mot de passe) sont présentes
        if (!isset($data['email'], $data['password'])) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Données manquantes'
            ]);
            return;
        }

        // Récupérer les données envoyées
        $email = $data['email'];
        $password = $data['password'];

        // Rechercher l'administrateur dans la base de données
        $admin = Admin::getInstance()->findOneBy([
            'ad_email_admin' => $email
        ]);

        // Vérifier si l'administrateur existe et si le mot de passe est correct
        if ($admin && password_verify($password, $admin['mdp_admin'])) {
            // Générer un token JWT
            $payload = [
                'id' => $admin['id_administrateur'],
                'email' => $admin['ad_email_admin'],
                'role' => 'admin',
                'exp' => time() + 3600 // Le token expire après 1 heure
            ];

            // Récupérer la clé secrète JWT depuis le .env
            $jwtSecret = $_ENV['JWT_SECRET'];

            // Encoder le token avec la clé secrète du .env
            $token = JWT::encode($payload, $jwtSecret, 'HS256');

            // Retourner la réponse avec le token généré
            echo json_encode([
                'status' => 'success',
                'message' => 'Connexion réussie',
                'token' => $token,
                'admin' => [
                    'id' => $admin['id_administrateur'],
                    'email' => $admin['ad_email_admin'],
                    'role' => 'admin'
                ]
            ]);
        } else {
            // Si les identifiants sont incorrects
            http_response_code(401); // Code 401 Unauthorized pour identifiants incorrects
            echo json_encode([
                'status' => 'error',
                'message' => 'Identifiants incorrects'
            ]);
        }
    }


    // Créer un deck API
    public function createDeck()
    {
        $authorizationController = new AuthorizationController();
        $authorizationController->options();

        $decodedToken = $authorizationController->validateAdminToken();
        if (!$decodedToken) {
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['titre_deck'], $data['date_debut_deck'], $data['date_fin_deck'], $data['nb_cartes'])) {
            http_response_code(400); // Mauvaise requête si des champs sont manquants
            echo json_encode(['error' => 'Données manquantes.']);
            return;
        }

        $nb_deck = Deck::getInstance()->findAll();
        if (count($nb_deck) >= 10) {
            http_response_code(400); // Mauvaise requête si un deck existe déjà
            echo json_encode(['error' => 'Vous avez déjà un deck en cours.']);
            return;
        }

        $titreDeck = $data['titre_deck'];
        $dateDebutDeck = $data['date_debut_deck'];
        $dateFinDeck = $data['date_fin_deck'];
        $nbCarte = $data['nb_cartes'];

        // Créer un nouveau deck et récupérer l'ID du deck
        $idDeck = Deck::getInstance()->create([
            'titre_deck' => $titreDeck,
            'date_debut_deck' => $dateDebutDeck,
            'date_fin_deck' => $dateFinDeck,
            'nb_cartes' => $nbCarte,
        ]);

        if ($idDeck) {
            http_response_code(201); // 201 Created
            echo json_encode([
                'success' => 'Deck créé avec succès',
                'id_deck' => $idDeck, // Retourne l'ID du deck
            ]);
        } else {
            http_response_code(500); // Erreur serveur
            echo json_encode(['error' => 'Une erreur est survenue lors de la création du deck']);
        }
    }


    // Dashboard API
    public function dashboard()
    {
        // Création d'une instance de l'autre contrôleur
        $authorizationController = new AuthorizationController();

        $authorizationController->options();
        $decodedToken = $authorizationController->validateAdminToken();
        if (!$decodedToken) {
            // La méthode `validateAdminToken` gère déjà la réponse HTTP en cas d'erreur.
            return;
        }
        $decks = Deck::getInstance()->findAll();
        echo json_encode($decks);
    }

    // Supprimer un deck
    public function deleteDeck(int|string $id): void
    {

        $authorizationController = new AuthorizationController();
        $authorizationController->options();
        $decodedToken = $authorizationController->validateAdminToken();
        if (!$decodedToken) {
            // La méthode `validateAdminToken` gère déjà la réponse HTTP en cas d'erreur.
            return;
        }

        // Validation de l'ID
        $id = (int) $id;
        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(['error' => 'ID invalide'], JSON_PRETTY_PRINT);
            return;
        }

        // Suppression du deck
        $success = Deck::getInstance()->delete($id);
        if ($success) {
            http_response_code(200);
            echo json_encode(['success' => 'Deck supprimé avec succès'], JSON_PRETTY_PRINT);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Une erreur est survenue lors de la suppression du deck'], JSON_PRETTY_PRINT);
        }
    }


    // Supprimer une carte
    public function deleteCard(int|string $id)
    {
        // Création d'une instance de l'autre contrôleur
        $authorizationController = new AuthorizationController();

        $authorizationController->options();
        $decodedToken = $authorizationController->validateAdminToken();
        if (!$decodedToken) {
            // La méthode `validateAdminToken` gère déjà la réponse HTTP en cas d'erreur.
            return;
        }
        $id = (int) $id;
        // echo json_encode($id);
        $success = Carte::getInstance()->delete($id);
        if ($success) {
            echo json_encode(['success' => 'Carte supprimée avec succès']);
        } else {
            echo json_encode(['error' => 'Une erreur est survenue lors de la suppression de la carte']);
        }
    }



    // Désactiver un deck
    public function deactivate(int|string $id)
    {
        // Création d'une instance de l'autre contrôleur
        $authorizationController = new AuthorizationController();

        $authorizationController->options();
        $decodedToken = $authorizationController->validateAdminToken();
        if (!$decodedToken) {
            // La méthode `validateAdminToken` gère déjà la réponse HTTP en cas d'erreur.
            return;
        }
        $id = (int) $id;
        $success = Deck::getInstance()->updateDeck($id, ['live' => 0]);
        echo json_encode(['success' => $success ? 'Deck désactivé avec succès' : 'Échec de la désactivation']);
    }

    // Activer un deck
    public function activate(int|string $id)
    {
        // Création d'une instance de l'autre contrôleur
        $authorizationController = new AuthorizationController();

        $authorizationController->options();
        $decodedToken = $authorizationController->validateAdminToken();
        if (!$decodedToken) {
            // La méthode `validateAdminToken` gère déjà la réponse HTTP en cas d'erreur.
            return;
        }

        $id = (int) $id;
        $success = Deck::getInstance()->updateDeck($id, ['live' => 1]);
        echo json_encode(['success' => $success ? 'Deck activé avec succès' : 'Échec de l\'activation']);
    }



    // Afficher les cartes d'un deck
    public function showDeck($deckId)
    {
        // Création d'une instance de l'autre contrôleur
        $authorizationController = new AuthorizationController();
        $authorizationController->options();
        $decodedToken = $authorizationController->validateAdminToken();
        if (!$decodedToken) {
            // La méthode `validateAdminToken` gère déjà la réponse HTTP en cas d'erreur.
            return;
        }
        // Convertir l'ID du deck en entier et vérifier sa validité
        $deckId = (int) $deckId;
        if ($deckId <= 0) {
            http_response_code(400);
            echo json_encode(['error' => 'L\'ID du deck est invalide.', 'status' => 400]);
            return;
        }

        // Récupérer toutes les cartes associées à ce deck
        $cards = Deck::getInstance()->getCardsByDeckId($deckId);

        // récuperer le nom du deck
        $deck = Deck::getInstance()->findOneBy(['id_deck' => $deckId]);

        $nomDeck = $deck['titre_deck'];

        // Vérifier si des cartes sont trouvées
        if (!empty($cards)) {
            // Retourner les cartes trouvées avec un statut 200 et le nom du deck
            http_response_code(200);
            echo json_encode(['cards' => $cards, 'nom_deck' => $nomDeck, 'status' => 200]);
        } else {
            // Si aucune carte n'est trouvée, retourner un message d'erreur avec statut 404
            http_response_code(404);
            echo json_encode(['message' => 'Aucune carte trouvée pour ce deck.', 'status' => 404]);
        }
    }

    // Récuper les infos d'une carte
    public function getCard(int|string $id)
    {
        // Création d'une instance de l'autre contrôleur
        $authorizationController = new AuthorizationController();
        $authorizationController->options();
        $decodedToken = $authorizationController->validateAdminToken();
        if ($decodedToken === null) {
            // La méthode `validateAdminToken` gère déjà la réponse HTTP en cas d'erreur.
            return;
        }

        $id = (int) $id;
        $card = Carte::getInstance()->findOneBy(['id_carte' => $id]);

        if ($card) {
            echo json_encode(['card' => $card]);
        } else {
            echo json_encode(['error' => 'Carte non trouvée']);
        }
    }


    // Editer une carte
    public function editCard(int|string $id)
    {
        // Création d'une instance de l'autre contrôleur
        $authorizationController = new AuthorizationController();
        $authorizationController->options();
        $decodedToken = $authorizationController->validateAdminToken();
        if ($decodedToken === null) {
            // La méthode `validateAdminToken` gère déjà la réponse HTTP en cas d'erreur.
            return;
        }

        $id = (int) $id;
        $data = json_decode(file_get_contents('php://input'), true);

        // Validate input data
        if (!isset(
            $data['event_description'],
            $data['choice_1'],
            $data['choice_2'],
            $data['population_impact_1'],
            $data['finance_impact_1'],
            $data['population_impact_2'],
            $data['finance_impact_2'],
            $data['id_deck']
        )) {
            http_response_code(400);
            echo json_encode(['error' => 'Données manquantes']);
            return;
        }

        // Prepare data for update
        $updateData = [
            'event_description' => $data['event_description'],
            'choice_1' => $data['choice_1'],
            'population_impact_1' => (int)$data['population_impact_1'],
            'finance_impact_1' => (int)$data['finance_impact_1'],
            'choice_2' => $data['choice_2'],
            'population_impact_2' => (int)$data['population_impact_2'],
            'finance_impact_2' => (int)$data['finance_impact_2'],
            'id_deck' => (int)$data['id_deck']
        ];

        // Attempt to update the card
        $success = Carte::getInstance()->updateCarte($id, $updateData);

        if ($success) {
            http_response_code(200);
            echo json_encode(['success' => 'Carte modifiée avec succès']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Une erreur est survenue lors de la modification de la carte']);
        }
    }
}
