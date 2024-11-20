<?php

declare(strict_types=1);

namespace App\Controller;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AuthorizationController extends Controller
{
    /**
     * Définit les en-têtes CORS
     */
    public function options(): void
    {
        header('Access-Control-Allow-Origin: http://localhost:5173');
        header('Access-Control-Allow-Methods: GET, POST, PATCH, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Authorization, Content-Type, X-Requested-With');
        header('Access-Control-Allow-Credentials: true');
        header('Content-Type: application/json');
    }

    /**
     * Vérifie le token JWT
     */
    public function checkToken(): void
    {
        $this->options();

        $headers = getallheaders();
        if (!isset($headers['Authorization'])) {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => 'Token manquant'
            ]);
            return;
        }

        $token = str_replace('Bearer ', '', $headers['Authorization']);

        try {
            $key = new Key(JWT_SECRET, 'HS256');
            $decoded = JWT::decode($token, $key);

            echo json_encode([
                'status' => 'success',
                'decoded' => $decoded
            ]);
        } catch (\Exception $e) {
            http_response_code(401);
            echo json_encode([
                'status' => 'error',
                'message' => 'Token invalide ou expiré',
                'error' => $e->getMessage()
            ]);
        }
    }


    public function validateAdminToken(): ?object
    {
        header('Content-Type: application/json');

        // Récupérer les headers
        $headers = getallheaders();
        if (!isset($headers['Authorization'])) {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => 'Token manquant'
            ], JSON_PRETTY_PRINT);
            return null;
        }

        // Extraire le token
        $token = str_replace('Bearer ', '', $headers['Authorization']);

        try {
            $key = new Key(JWT_SECRET, 'HS256');
            $decoded = JWT::decode($token, $key);

            // Vérifier le rôle dans le token
            if ($decoded->role !== 'admin') {
                http_response_code(403);
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Accès refusé : vous n\'êtes pas administrateur'
                ], JSON_PRETTY_PRINT);
                return null;
            }

            return $decoded; // Retourner le token décodé si tout est valide
        } catch (\Exception $e) {
            http_response_code(401);
            echo json_encode([
                'status' => 'error',
                'message' => 'Token invalide ou expiré',
                'error' => $e->getMessage()
            ], JSON_PRETTY_PRINT);
            return null;
        }
    }

    public function validateCreateurToken()
    {
        header('Content-Type: application/json');

        // Récupérer les headers
        $headers = getallheaders();
        if (!isset($headers['Authorization'])) {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => 'Token manquant'
            ], JSON_PRETTY_PRINT);
            return null;
        }

        // Extraire le token
        $token = str_replace('Bearer ', '', $headers['Authorization']);

        try {
            $key = new Key(JWT_SECRET, 'HS256');
            $decoded = JWT::decode($token, $key);

            // Vérifier le rôle dans le token
            if ($decoded->role !== 'createur') {
                http_response_code(403);
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Accès refusé : vous n\'êtes pas créateur'
                ], JSON_PRETTY_PRINT);
                return null;
            }

            return $decoded; // Retourner le token décodé si tout est valide
        } catch (\Exception $e) {
            http_response_code(401);
            echo json_encode([
                'status' => 'error',
                'message' => 'Token invalide ou expiré',
                'error' => $e->getMessage()
            ], JSON_PRETTY_PRINT);
            return null;
        }
    }
}
