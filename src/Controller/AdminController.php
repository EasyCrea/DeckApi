<?php

declare(strict_types=1);

namespace App\Controller;

use Firebase\JWT\JWT;
use App\Model\Admin;
use App\Model\Deck;
use App\Model\Carte;
use App\Model\CarteAleatoire;


class AdminController extends Controller
{

    // Connexion API
    public function login()
    {
        // Création d'une instance de l'autre contrôleur
        $authorizationController = new AuthorizationController();

        $authorizationController->options();

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

            // Utiliser une clé secrète pour encoder le token (assurez-vous d'avoir défini JWT_SECRET dans votre projet)
            $token = JWT::encode($payload, JWT_SECRET, 'HS256');

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
        $success = Deck::getInstance()->update($id, ['live' => 0]);
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
        $success = Deck::getInstance()->update($id, ['live' => 1]);
        echo json_encode(['success' => $success ? 'Deck activé avec succès' : 'Échec de l\'activation']);
    }

    //Créer premiere carte
    public function createFirstCard()
    {
        // Création d'une instance de l'autre contrôleur
        $authorizationController = new AuthorizationController();

        $authorizationController->options();
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Vérifier que l'administrateur est connecté
        // if (!isset($_SESSION['id_administrateur'])) {
        //     echo json_encode(['error' => 'Non autorisé']);
        //     http_response_code(403);
        //     return;
        // }

        if ($this->isGetMethod()) {
            echo json_encode(['message' => 'Ready to create first card']);
        } else {
            $texteCarte = trim($_POST['texte_carte']);
            $valeursChoix1 = trim($_POST['valeurs_choix1']);
            $valeursChoix2 = trim($_POST['valeurs_choix2']);
            $valeurs_choix1bis = trim($_POST['valeurs_choix1bis']);
            $valeurs_choix2bis = trim($_POST['valeurs_choix2bis']);
            $deckId = (int) trim($_POST['deckId']);

            $valeur_choixFinal = $valeursChoix1 . ',' . $valeurs_choix1bis;
            $valeur_choixFinal2 = $valeursChoix2 . ',' . $valeurs_choix2bis;

            $carteCreated = Carte::getInstance()->create([
                'date_soumission' => (new \DateTime())->format('Y-m-d'),
                'ordre_soumission' => 1,
                'valeurs_choix1' => $valeur_choixFinal,
                'texte_carte' => $texteCarte,
                'valeurs_choix2' => $valeur_choixFinal2,
                'id_deck' => $deckId,
                'id_administrateur' => $_SESSION['id_administrateur'],
            ]);

            if ($carteCreated) {
                echo json_encode(['success' => 'Carte créée avec succès']);
            } else {
                echo json_encode(['error' => 'Une erreur est survenue lors de la création de la carte']);
            }
        }
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

        // Vérifier si des cartes sont trouvées
        if (!empty($cards)) {
            // Retourner les cartes trouvées avec un statut 200
            echo json_encode(['cards' => $cards, 'status' => 200]);
        } else {
            // Si aucune carte n'est trouvée, retourner un message d'erreur avec statut 404
            http_response_code(404);
            echo json_encode(['message' => 'Aucune carte trouvée pour ce deck.', 'status' => 404]);
        }
    }
};
