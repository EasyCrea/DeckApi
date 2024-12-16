<?php

declare(strict_types=1);

namespace App\Controller;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Dotenv\Dotenv;

class AuthorizationController extends Controller
{
    /**
     * Définit les en-têtes CORS
     */
    public function options(): void
    {
        $allowed_origins = [
            'http://localhost:5173',
            'https://easydeck.alwaysdata.net'
        ];

        if (isset($_SERVER['HTTP_ORIGIN']) && in_array($_SERVER['HTTP_ORIGIN'], $allowed_origins)) {
            header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN']);
        }
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
        // Charger les variables d'environnement
        $dotenv = Dotenv::createImmutable(dirname(__DIR__, 2)); // Remonte à la racine du projet
        $dotenv->load();

        // Récupérer la clé secrète JWT depuis le .env
        $jwtSecret = $_ENV['JWT_SECRET'];
        try {
            $key = new Key($jwtSecret, 'HS256');
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
        $headers = getallheaders();
        if (!isset($headers['Authorization'])) {
            return null;
        }

        $token = str_replace('Bearer ', '', $headers['Authorization']);
        // Charger les variables d'environnement
        $dotenv = Dotenv::createImmutable(dirname(__DIR__, 2)); // Remonte à la racine du projet
        $dotenv->load();

        // Récupérer la clé secrète JWT depuis le .env
        $jwtSecret = $_ENV['JWT_SECRET'];
        try {
            $key = new Key($jwtSecret, 'HS256');
            $decoded = JWT::decode($token, $key);

            // Vérifier si le rôle est "admin"
            if ($decoded->role !== 'admin') {
                return null;
            }

            return $decoded;
        } catch (\Exception $e) {
            return null; // Retourner null en cas d'erreur ou d'invalidité
        }
    }


    public function validateCreateurToken(): ?object
    {
        $headers = getallheaders();
        if (!isset($headers['Authorization'])) {
            return null;
        }

        $token = str_replace('Bearer ', '', $headers['Authorization']);
        $dotenv = Dotenv::createImmutable(dirname(__DIR__, 2)); // Remonte à la racine du projet
        $dotenv->load();

        // Récupérer la clé secrète JWT depuis le .env
        $jwtSecret = $_ENV['JWT_SECRET'];
        try {
            $key = new Key($jwtSecret, 'HS256');
            $decoded = JWT::decode($token, $key);

            // Vérifier si le rôle est "createur"
            if ($decoded->role !== 'createur') {
                return null;
            }

            return $decoded;
        } catch (\Exception $e) {
            return null; // Retourner null en cas d'erreur ou d'invalidité
        }
    }
}
