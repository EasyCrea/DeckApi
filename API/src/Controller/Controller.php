<?php

namespace App\Controller;

class Controller
{
    protected $log;

    // Constructeur : en-têtes CORS + gestion des requêtes OPTIONS
    public function __construct()
    {
        // Gérer les en-têtes CORS
        $this->cors();

        // Vérifier si la requête est une requête OPTIONS (preflight)
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(204); // No Content
            exit(); // Terminer ici pour éviter de passer à d'autres contrôleurs
        }

        // Référence aux variables globales pour la gestion des logs, etc.
        global $logger;
        $this->log = $logger;
    }

    /**
     * Cette méthode gère les en-têtes CORS.
     */
    private function cors()
    {
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, PATCH, OPTIONS");
        header("Access-Control-Allow-Headers: Content-Type");
        header("Content-Type: application/json");
    }

    /**
     * Vérifie si la requête est de type AJAX.
     * Le header de la requête doit contenir le paramètre X-Requested-With=XMLHttpRequest
     *
     * @return bool
     */
    public function isAjaxRequest(): bool
    {
        $headers = getallheaders();
        return isset($headers['X-Requested-With']) && $headers['X-Requested-With'] === 'XMLHttpRequest';
    }

    /**
     * Vérifie si la méthode de la requête est GET.
     *
     * @return bool
     */
    public function isGetMethod(): bool
    {
        return 'GET' === strtoupper($_SERVER['REQUEST_METHOD']);
    }

    /**
     * Vérifie si la méthode de la requête est POST.
     *
     * @return bool
     */
    public function isPostMethod(): bool
    {
        return 'POST' === strtoupper($_SERVER['REQUEST_METHOD']);
    }

    /**
     * Retourne une structure JSON.
     *
     * @param array $response
     * @return string
     */
    public function json(array $response): string
    {
        // Assure que les en-têtes CORS sont présents
        $this->cors();

        // Envoie les données en format JSON
        return json_encode($response);
    }
}
