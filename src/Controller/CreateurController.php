<?php

declare(strict_types=1); // strict mode

namespace App\Controller;

use App\Helper\HTTP;
use App\Model\Createur;

class CreateurController extends Controller

{

    public function register()
    {
        if ($this->isGetMethod()) {
            $this->display('createurs/register.html.twig');
        } else {
            // 1. vérifier les données soumises
            // 2. exécuter la requête d'insertion
            Createur::getInstance()->create([
                'nom_createur' => trim($_POST['name']),
                'ad_email_createur' => trim($_POST['email']),
                'mdp_createur' => trim(password_hash($_POST['password'], PASSWORD_BCRYPT)),
                'ddn' => trim($_POST['ddn']),
                'genre' => trim($_POST['genre']),
            ]);
            // 3. rediriger vers la page de connexion
            HTTP::redirect('/createurs/login');
        }
    }

    public function login()
    {
        if ($this->isGetMethod()) {
            $this->display('createurs/login.html.twig');
        } else {
            // 1. Vérifier les données soumises
            $email = trim($_POST['email']);
            $password = trim($_POST['password']);

            // 2. Exécuter la requête de recherche
            $createur = Createur::getInstance()->findOneBy([
                'ad_email_createur' => $email
            ]);

            session_start([
                'cookie_path' => '/',
                'cookie_lifetime' => 0,
                'cookie_secure' => true,
                'cookie_httponly' => true,
                'cookie_samesite' => 'strict',
            ]);

            // 3. Si le créateur est trouvé, vérifier le mot de passe
            if ($createur && password_verify($password, $createur['mdp_createur'])) {
                // 4. Stocker l'identifiant du créateur dans la session
                $_SESSION['id_createur'] = $createur['id_createur'];

                // 5. Rediriger vers la page d'accueil
                HTTP::redirect('/game');
            } else {
                // 6. Sinon, afficher un message d'erreur
                $this->display('createurs/login.html.twig', ['error' => 'Identifiant ou mot de passe incorrect']);
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
        HTTP::redirect('/');
    }
}
