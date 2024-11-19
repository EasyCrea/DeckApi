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
    }

    /**
     * Vérifie le token JWT
     */
    public function checkToken(): void
    {
        $this->options();

        // Spécifiez que le retour sera en JSON
        header('Content-Type: application/json');

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
}
