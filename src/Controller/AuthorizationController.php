<?php

declare(strict_types=1); // strict mode

namespace App\Controller;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;


class AuthorizationController extends Controller
{


    /**
     * Options
     */
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


    /**
     * CheckToken
     */
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
}
