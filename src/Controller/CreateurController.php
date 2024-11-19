<?php

declare(strict_types=1); // strict mode

namespace App\Controller;

use App\Helper\HTTP;
use App\Model\Createur;
use App\Model\Deck;
use App\Model\Carte;
use App\Model\CarteAleatoire;

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

    public function createCard(){
        if ($this->isPostMethod()){
            // 1. vérifier les données soumises
            $id_deck = trim($_POST['id_deck']);
            $text_carte = trim($_POST['text_carte']);
            $valeurs_choix1 = trim($_POST['valeurs_choix1']);
            $valeurs_choix2 = trim($_POST['valeurs_choix2']);
            $date_soumission = date('Y-m-d');
            $ordre_soumission = trim($_POST['ordre_soumission']);
           

            if ($_POST['id_createur'] != null){
                $id_createur = trim($_POST['id_createur']);
            }
            if ($_POST['id_administrateur'] != null){
                $id_administration = trim($_POST['id_administrateur']);
            }

            if ($id_createur){
                $creation =  Carte::getInstance()->create([    
                    'id_deck' => $id_deck,
                    'text_carte' => $text_carte,
                    'valeurs_choix1' => $valeurs_choix1,
                    'valeurs_choix2' => $valeurs_choix2,
                    'date_soumission' => $date_soumission,
                    'ordre_soumission' => $ordre_soumission,
                    'id_createur' => $id_createur,
                ]);
                $this->createRandomCard($id_deck, $id_createur);
            }
            if ($id_administration){
                $creation =  Carte::getInstance()->create([
                    'id_deck' => $id_deck,
                    'text_carte' => $text_carte,
                    'valeurs_choix1' => $valeurs_choix1,
                    'valeurs_choix2' => $valeurs_choix2,
                    'date_soumission' => $date_soumission,
                    'ordre_soumission' => $ordre_soumission,
                    'id_createur' => $id_createur,
                ]);
            }

            if ($creation){
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Carte créée avec succès'
                ]);
            }else{
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Erreur lors de la création de la carte'
                ]);
            }
           
           
            

        }
    }

    public function createRandomCard(
        int|string $id_deck,
        int|string $id_createur
        ){
            $id_deck = (int) $id_deck;
            $id_createur = (int) $id_createur;

            $all_card = Carte::getInstance()->findAll();
            if ($all_card){
                $id_random = mt_rand(0, count($all_card) - 1);
            }
          

            $carteAleatoire = CarteAleatoire::getInstance()->create([
                'id_deck' => $id_deck,
                'id_createur' => $id_createur,
                'id_carte' => $id_random
            ]);

            if ($carteAleatoire){
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Carte aléatoire créée avec succès'
                ]);
            }else{
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Erreur lors de la création de la carte aléatoire'
                ]);
            }

    }
    
    public function getAllDecks(){
        if ($this->isGetMethod()){
            // 1. vérifier les données soumises
            $decks = Deck::getInstance()->findAll();
            if ($decks){
                echo json_encode([
                    'status' => 'success',
                    'decks' => $decks
                ]);
            }else{
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Erreur lors de la récupération des decks'
                ]);
            }
        }
    }
    public function getRandomCard(
        int|string $id
    ){
        $id = (int) $id;
        if ($this->isGetMethod()){
            // 1. vérifier les données soumises
            $card = CarteAleatoire::getInstance()->find($id);
            if ($card){
                echo json_encode([
                    'status' => 'success',
                    'card' => $card
                ]);
            }else{
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Erreur lors de la récupération de la carte aléatoire'
                ]);
            }
        }
    }
    public function getDeck(){
        if ($this->isGetMethod()){
            // 1. vérifier les données soumises
        }
    }
    public function getCard(){
        if ($this->isGetMethod()){
            // 1. vérifier les données soumises
        }
    }
    public function getCardByDeck(){
        if ($this->isGetMethod()){
            // 1. vérifier les données soumises
        }
    }
}
