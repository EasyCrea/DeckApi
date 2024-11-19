<?php

declare(strict_types=1); // strict mode

namespace App\Controller;

use App\Helper\HTTP;
use App\Model\Createur;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class CreateurController extends Controller

{
    public function options()
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PATCH, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: *');
        header('Content-Type: application/json');
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
    }

    public function login()
    {
        $this->options();
        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['email'], $data['password'])) {
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
                    'email' => $createur['ad_email_createur']
                ]
            ]);
        } else {
            // Identifiants incorrects
            echo json_encode([
                'status' => 'error',
                'message' => 'Identifiants incorrects'
            ]);
        }
    }

    public function me()
    {
        $this->options();

        // Vérification de la présence du token dans les en-têtes HTTP Authorization
        $headers = getallheaders();
        if (!isset($headers['Authorization'])) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Token manquant'
            ]);
            return;
        }

        // Extraction du token de l'en-tête Authorization
        $token = str_replace('Bearer ', '', $headers['Authorization']);
        dd($token);
        try {
            // Tentative de décodage du token JWT
            $key = new Key(JWT_SECRET, 'HS256');
            $decoded = JWT::decode($token, $key);

            // Si le token est valide, renvoyer les informations de l'utilisateur
            echo json_encode([
                'status' => 'success',
                'message' => 'Token valide',
                'createur' => [
                    'id' => $decoded->id,
                    'email' => $decoded->email
                ]
            ]);
        } catch (\Exception $e) {
            // Si une erreur se produit (token invalide, expiré, etc.), renvoyer un message d'erreur
            echo json_encode([
                'status' => 'error',
                'message' => 'Token invalide ou expiré',
                'error' => $e->getMessage() // Ajout du message d'erreur pour aider au débogage
            ]);
        }
    }






    public function logout()
    {
        session_start([
            'cookie_path' => '/',
            'cookie_lifetime' => 0,
            'cookie_secure' => true,
            'cookie_httponly' => true,
            'cookie_samesite' => 'strict',
        ]);
        session_destroy();
    }
}
