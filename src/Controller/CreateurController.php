<?php

declare(strict_types=1); // strict mode

namespace App\Controller;

use App\Helper\HTTP;
use App\Model\Createur;

class CreateurController extends Controller

{
    public function options()
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PATCH, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: X-Requested-With');
        header('Content-Type: application/json');
    }

    public function register()
    {
        if ($this->isPostMethod()){
            // 1. vérifier les données soumises
            // 2. exécuter la requête d'insertion
            $request = Createur::getInstance()->create([
                'nom_createur' => trim($_POST['name']),
                'ad_email_createur' => trim($_POST['email']),
                'mdp_createur' => trim(password_hash($_POST['password'], PASSWORD_BCRYPT)),
                'ddn' => trim($_POST['ddn']),
                'genre' => trim($_POST['genre']),
            ]);
            if ($request){
                echo json_encode([
                    'status' => 'success',
                    'nom_createur' => trim($_POST['name']),
                    'ad_email_createur' => trim($_POST['email']),
                    
                ]);
            }else{
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Erreur lors de l\'enregistrement'
                ]);
            }
           
        }

      
    }

    public function login()
    {
        if ($this->isPostMethod()) {
            // 1. Vérifier les données soumises
            $email = trim($_POST['email']);
            $password = trim($_POST['password']);

            // 2. Exécuter la requête de recherche
            $createur = Createur::getInstance()->findOneBy([
                'ad_email_createur' => $email
            ]);

            if ($createur && password_verify($password, $createur['mdp_createur'])) {
              echo json_encode([
                'status' => 'success',
                'message' => 'Connexion réussie',
                'createur' => $createur
              ]);
            }
            else {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Identifiants incorrects'
                ]);
            }
    
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
