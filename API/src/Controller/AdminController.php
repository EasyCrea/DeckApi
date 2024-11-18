<?php

namespace App\Controller;

use App\Model\Admin;
use App\Model\Carte;
use App\Model\Deck;

class AdminController extends Controller
{
    // Cette fonction gère les en-têtes CORS
    private function cors()
    {
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, PATCH, OPTIONS");
        header("Access-Control-Allow-Headers: Content-Type, Authorization");
        header("Access-Control-Allow-Credentials: true");
        header("Content-Type: application/json");

        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(204);
            exit();
        }
    }

    // Fonction de connexion
    public function login()
    {
        $this->cors();

        if ($this->isGetMethod()) {
            echo json_encode(['message' => 'Login page (GET)', 'status' => 200]);
            return;
        }

        $email = trim($_POST['email'] ?? '');
        $password = trim($_POST['password'] ?? '');

        if (!$email || !$password) {
            echo json_encode(['error' => 'Email and password are required', 'status' => 400]);
            return;
        }

        $admin = Admin::getInstance()->findOneBy(['ad_email_admin' => $email]);

        if ($admin && password_verify($password, $admin['mdp_admin'])) {
            echo json_encode(['message' => 'Login successful', 'status' => 200]);
            return;
        }

        echo json_encode(['error' => 'Invalid email or password', 'status' => 401]);
    }

    // Fonction de déconnexion
    public function logout()
    {
        $this->cors();
        // Suppression de la gestion de session
        echo json_encode(['message' => 'Logout successful', 'status' => 200]);
    }

    // Créer un deck
    public function createDeck()
    {
        $this->cors();

        // Suppression de la vérification de session
        // Limiter à un seul deck
        $nb_deck = Deck::getInstance()->findAll();
        if (count($nb_deck) >= 1) {
            echo json_encode(['error' => 'Only one deck can be created at a time', 'status' => 403]);
            return;
        }

        if ($this->isGetMethod()) {
            echo json_encode(['message' => 'Deck creation page (GET)', 'status' => 200]);
            return;
        }

        $titreDeck = trim($_POST['titre_deck'] ?? '');
        $dateDebutDeck = trim($_POST['date_debut_deck'] ?? '');
        $dateFinDeck = trim($_POST['date_fin_deck'] ?? '');
        $nbCarte = isset($_POST['nb_carte']) ? (int) trim($_POST['nb_carte']) : 0;

        if (!$titreDeck || !$dateDebutDeck || !$dateFinDeck || !$nbCarte) {
            echo json_encode(['error' => 'All deck fields are required', 'status' => 400]);
            return;
        }

        $deckId = Deck::getInstance()->create([
            'titre_deck' => $titreDeck,
            'date_debut_deck' => $dateDebutDeck,
            'date_fin_deck' => $dateFinDeck,
            'nb_cartes' => $nbCarte,
        ]);

        echo json_encode(['message' => 'Deck created successfully', 'deckId' => $deckId, 'status' => 201]);
    }

    // Créer une première carte dans un deck
    public function createFirstCard()
    {
        $this->cors();

        // Suppression de la vérification de session
        $title = trim($_POST['title'] ?? '');
        $content = trim($_POST['content'] ?? '');
        $deckId = isset($_POST['deck_id']) ? (int) $_POST['deck_id'] : 0;

        if (!$title || !$content || !$deckId) {
            echo json_encode(['error' => 'All card fields are required', 'status' => 400]);
            return;
        }

        $cardId = Carte::getInstance()->create([
            'title' => $title,
            'content' => $content,
            'deck_id' => $deckId,
        ]);

        echo json_encode(['message' => 'First card created successfully', 'cardId' => $cardId, 'status' => 201]);
    }

    // Obtenir tous les decks
    public function getAllDeck()
    {
        $this->cors();
        $decks = Deck::getInstance()->findAll();

        echo json_encode(['decks' => $decks, 'status' => 200]);
    }

    // Modifier un deck par ID
    public function editById($id)
    {
        $this->cors();

        // Suppression de la vérification de session
        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data) {
            echo json_encode(['error' => 'Invalid input', 'status' => 400]);
            return;
        }

        $deck = Deck::getInstance()->update($id, $data);
        echo json_encode(['message' => 'Deck updated successfully', 'deck' => $deck, 'status' => 200]);
    }

    // Supprimer un deck par ID
    public function deleteById($id)
    {
        $this->cors();

        // Suppression de la vérification de session
        $deck = Deck::getInstance()->find($id);

        if (!$deck) {
            echo json_encode(['error' => 'Deck not found', 'status' => 404]);
            return;
        }

        Deck::getInstance()->delete($id);
        echo json_encode(['message' => 'Deck deleted successfully', 'status' => 200]);
    }

    // Obtenir toutes les cartes d'un deck
    public function getAllCardInDeck($id)
    {
        $this->cors();

        // Suppression de la vérification de session
        $cards = Carte::getInstance()->getCardsByDeckId((int) $id);  // Utilisation de la méthode correcte
        if ($cards) {
            echo json_encode(['cards' => $cards, 'status' => 200]);
        } else {
            echo json_encode(['error' => 'No cards found for this deck', 'status' => 404]);
        }
    }


    // Obtenir une carte par ID
// Obtenir une carte par ID
public function getCardById($id)
{
    $this->cors();  // Gérer CORS

    $id = (int) $id;

    // Vérifier si l'ID est valide
    if (!$id) {
        http_response_code(400);
        echo json_encode(['error' => 'L\'ID de la carte est requis.']);
        return;
    }

    // Appeler la méthode dans le modèle Carte pour récupérer la carte par son ID
    $card = Carte::getInstance()->getCardById($id);

    if (!$card) {
        http_response_code(404);
        echo json_encode(['error' => 'Carte non trouvée.']);
        return;
    }

    // Retourner la carte au format JSON
    echo json_encode($card);
}


    // Modifier une carte par ID
    public function editCardById($id)
    {
        $this->cors();

        // Suppression de la vérification de session
        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data) {
            echo json_encode(['error' => 'Invalid input', 'status' => 400]);
            return;
        }

        $card = Carte::getInstance()->update($id, $data);
        echo json_encode(['message' => 'Card updated successfully', 'card' => $card, 'status' => 200]);
    }

    // Supprimer une carte par ID
    public function deleteCardById($id)
    {
        $this->cors();

        // Suppression de la vérification de session
        $card = Carte::getInstance()->find($id);

        if (!$card) {
            echo json_encode(['error' => 'Card not found', 'status' => 404]);
            return;
        }

        Carte::getInstance()->delete($id);
        echo json_encode(['message' => 'Card deleted successfully', 'status' => 200]);
    }
}
