<?php

declare(strict_types=1); // strict mode

namespace App\Controller;

use App\Helper\HTTP;
use App\Model\Createur;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Model\Deck;
use App\Model\Carte;

use App\Model\Admin;

class AdminController extends Controller
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
        if ($this->isGetMethod()) {
        } else {
            // 1. Vérifier les données soumises
            // 2. Exécuter la requête d'insertion
            Admin::getInstance()->create([
                'ad_email_admin' => trim($_POST['email']),
                'mdp_admin' => trim(password_hash($_POST['password'], PASSWORD_BCRYPT)),
            ]);

            // 3. Rediriger vers la page de connexion
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
        $admin = Admin::getInstance()->findOneBy([
            'ad_email_admin' => $email
        ]);

        if ($admin && password_verify($password, $admin['mdp_admin'])) {
            // Générer le token JWT
            $payload = [
                'id' => $admin['id_administrateur'],
                'email' => $admin['ad_email_admin'],
                'role' => 'admin',
                'exp' => time() + 3600 // Expiration dans 1 heure
            ];

            $token = JWT::encode($payload, JWT_SECRET, 'HS256');

            // Retourner la réponse avec le token
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
            // Identifiants incorrects
            http_response_code(401); // Code 401 Unauthorized pour identifiants incorrects
            echo json_encode([
                'status' => 'error',
                'message' => 'Identifiants incorrects'
            ]);
        }
    }


    public function createDeck()
    {
        // Démarrer la session si elle n'est pas déjà démarrée
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Vérifiez que l'administrateur est connecté
        if (!isset($_SESSION['id_administrateur'])) {
            HTTP::redirect('/admin/login');
        }

        $nb_deck = Deck::getInstance()->findAll();
        if (count($nb_deck) >= 1) {
            HTTP::redirect('/admin/dashboard?error=Un seul deck ne peut être créé à la fois');
        }

        if ($this->isGetMethod()) {
            $this->display('admin/createDeck.html.twig');
        } else {

            // Récupérer les données du formulaire
            $titreDeck = trim($_POST['titre_deck']);
            $dateDebutDeck = trim($_POST['date_debut_deck']);
            $dateFinDeck = trim($_POST['date_fin_deck']);
            $nbCarte = (int)trim($_POST['nb_carte']); // Convertir en entie


            // Vérifier si les choix sont bien définis
            if (json_last_error() !== JSON_ERROR_NONE) {
                // Gérer l'erreur de JSON ici
                $this->display('admin/createDeck.html.twig', ['error' => 'Valeurs de choix invalides.']);
                return;
            }

            // 1. Créer un nouveau deck
            $deckId = Deck::getInstance()->create([
                'titre_deck' => $titreDeck,
                'date_debut_deck' => $dateDebutDeck,
                'date_fin_deck' => $dateFinDeck,
                'nb_cartes' => $nbCarte,
            ]);

            $this->display('admin/createFirstCard.html.twig', compact('deckId'));
        }
    }


    public function createFirstCard()
    {
        // Démarrer la session si elle n'est pas déjà démarrée
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Vérifiez que l'administrateur est connecté
        if (!isset($_SESSION['id_administrateur'])) {
            HTTP::redirect('/admin/login');
        }

        if ($this->isGetMethod()) {
            $this->display('admin/createFirstCard.html.twig');
        } else {
            // Récupérer les données du formulaire
            $texteCarte = trim($_POST['texte_carte']);
            $valeursChoix1 = trim($_POST['valeurs_choix1']);
            $valeursChoix2 = trim($_POST['valeurs_choix2']);
            $valeurs_choix1bis = trim($_POST['valeurs_choix1bis']);
            $valeurs_choix2bis = trim($_POST['valeurs_choix2bis']);
            $deckId = (int)trim($_POST['deckId']);

            $valeur_choixFinal = $valeursChoix1 . ',' . $valeurs_choix1bis;
            $valeur_choixFinal2 = $valeursChoix2 . ',' . $valeurs_choix2bis;


            // Créer la carte
            $carteCreated = Carte::getInstance()->create([
                'date_soumission' => (new \DateTime())->format('Y-m-d'), // Format de date adapté
                'ordre_soumission' => 1,
                'valeurs_choix1' => $valeur_choixFinal,
                'texte_carte' => $texteCarte,
                'valeurs_choix2' => $valeur_choixFinal2,
                'id_deck' => $deckId,
                'id_administrateur' => $_SESSION['id_administrateur'],
            ]);

            // Vérifier si l'insertion a réussi
            if ($carteCreated) {
                // Rediriger vers une page de succès ou le tableau de bord
                HTTP::redirect('/admin/dashboard');
            } else {
                // Afficher un message d'erreur si l'insertion a échoué
                $this->display('admin/createFirstCard.html.twig', [
                    'error' => 'Une erreur est survenue lors de la création de la carte.'
                ]);
            }
        }
    }

    public function dashboard()
    {
        $this->options();
        $decks = Deck::getInstance()->findAll();
        echo json_encode($decks);
    }


    public function delete(int|string $id)
    {

        $id = (int)$id;
        $type = $_GET['type'] ?? null; // Récupérer le paramètre 'type' depuis la requête

        // Vérifier que le type est valide
        if ($type === 'deck') {
            Deck::getInstance()->delete($id);
        } elseif ($type === 'carte') {
            Carte::getInstance()->delete($id);
        } else {
            // Gérer le cas où le type est invalide
        }

        // Rediriger vers le tableau de bord après la suppression
    }


    public function deactivate(int|string $id)
    {
        $this->options();
        $id = (int)$id;
        $success = Deck::getInstance()->update($id, ['live' => 0]);
        if ($success) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false]);
        }
    }

    public function activate(int|string $id)
    {
        $this->options();
        $id = (int)$id;
        $success = Deck::getInstance()->update($id, ['live' => 1]);
        if ($success) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false]);
        }
    }


    public function showDeck(int|string $id)
    {

        $success = $_GET['success'] ?? null;
        $id = (int)$id;

        // Récupérer les cartes du deck
        $cartes = Carte::getInstance()->findAllBy(['id_deck' => $id]);


        // Préparer les données des cartes avec les valeurs séparées et le nom du créateur
        $cartesAvecValeurs = [];

        foreach ($cartes as $carte) {
            // Récupérer le nom du créateur en fonction de l'id_createur ou de l'ad_email_admin
            if (!empty($carte['id_createur'])) {
                $nomCreateur = Createur::getInstance()->findCreatorName($carte['id_createur']) ?? 'Inconnu';
            } else {
                // Si l'id_createur n'est pas défini, utiliser l'email de l'administrateur
                $administrateur = Admin::getInstance()->getAdminEmail($carte['id_administrateur']);

                $nomCreateur = $administrateur ?? 'Administrateur inconnu';
            }

            $valeursChoix1 = explode(',', $carte['valeurs_choix1']);
            $valeursChoix2 = explode(',', $carte['valeurs_choix2']);

            $cartesAvecValeurs[] = [
                'id_carte' => $carte['id_carte'],
                'texte_carte' => $carte['texte_carte'],
                'valeurs_choix1' => [
                    'Population' => $valeursChoix1[0] ?? null,
                    'Finances' => $valeursChoix1[1] ?? null
                ],
                'valeurs_choix2' => [
                    'Population' => $valeursChoix2[0] ?? null,
                    'Finances' => $valeursChoix2[1] ?? null
                ],
                'ordre_soumission' => $carte['ordre_soumission'],
                'nom_createur' => $nomCreateur
            ];
        }
    }





    public function edit(int|string $id)
    {

        $id = (int)$id;

        // Récupérer les données de la carte à modifier
        $carte = Carte::getInstance()->findOneBy(['id_carte' => $id]);

        // Vérifier si la carte existe
        if (!$carte) {
        }


        // Vérifier si la méthode de la requête est GET
        if ($this->isGetMethod()) {
        } else {
            // Récupérer les données du formulaire

            $texteCarte = trim($_POST['texte_carte']);
            $valeursChoix1 = trim($_POST['valeurs_choix1']);
            $valeursChoix2 = trim($_POST['valeurs_choix2']);
            $valeurs_choix1bis = trim($_POST['valeurs_choix1bis']);
            $valeurs_choix2bis = trim($_POST['valeurs_choix2bis']);

            $valeur_choixFinal = $valeursChoix1 . ',' . $valeurs_choix1bis;
            $valeur_choixFinal2 = $valeursChoix2 . ',' . $valeurs_choix2bis;

            // Mettre à jour la carte
            $newCard = Carte::getInstance()->updateCard($id, [
                'texte_carte' => $texteCarte,
                'valeurs_choix1' => $valeur_choixFinal,
                'valeurs_choix2' => $valeur_choixFinal2
            ]);

            // Rediriger vers le tableau de bord après la mise à jour
            HTTP::redirect('/admin/deck/' . $carte['id_deck'] . '?success=carte_modifiee');
        }
    }

    public function logout()
    {
        // Démarrer la session si elle n'est pas déjà démarrée
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Détruire la session
        session_destroy();

        // Rediriger vers la page de connexion
        HTTP::redirect('/admin/login');
    }
}
