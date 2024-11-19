<?php

declare(strict_types=1);

namespace App\Controller;

use App\Helper\HTTP;
use App\Model\Deck;
use App\Model\Carte;
use App\Model\CarteAleatoire;

class GameController extends Controller
{
    public function index()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['id_createur'])) {
            HTTP::redirect('/createurs/login');
        }

        $idCreateur = $_SESSION['id_createur'];
        $deck = Deck::getInstance()->getLiveDeck();

        if ($deck && strtotime($deck['date_fin_deck']) < time()) {
            Deck::getInstance()->disableDeck($deck['id_deck']);
            $deck = null;
        }

        if ($deck) {
            $totalCartes = Carte::getInstance()->getNumberOfCardsInDeck($deck['id_deck']);

            // Vérifier si le deck est complet
            if ($totalCartes >= $deck['nb_cartes']) {
                Deck::getInstance()->disableDeck($deck['id_deck']);
                HTTP::redirect('/noDecks');
                return;
            }
        }

        if ($deck) {
            $totalCartes = Deck::getInstance()->getTotalCardsInDeck($deck['id_deck']);
            $carteAleatoire = null;
            $carteCreeeDetails = null;
            $carteCréeValeurChoix1Final1 = $carteCréeValeurChoix1Final2 = $carteCréeValeurChoix2Final1 = $carteCréeValeurChoix2Final2 = null;
            $carteAleatoireValeursChoix1Final1 = $carteAleatoireValeursChoix1Final2 = $carteAleatoireValeursChoix2Final1 = $carteAleatoireValeursChoix2Final2 = null;
            $carteCreee = Carte::getInstance()->getIfCreatorHasCreatedCardInDeck($idCreateur, $deck['id_deck']);

            if ($carteCreee) {
                $carteCreeeDetails = Carte::getInstance()->getCardById($carteCreee['id_carte']);
                $carteCréeValeurChoix1 = explode(',', $carteCreeeDetails['valeurs_choix1']);
                $carteCréeValeurChoix1Final1 = $carteCréeValeurChoix1[0];
                $carteCréeValeurChoix1Final2 = $carteCréeValeurChoix1[1];
                $carteCréeValeurChoix2 = explode(',', $carteCreeeDetails['valeurs_choix2']);
                $carteCréeValeurChoix2Final1 = $carteCréeValeurChoix2[0];
                $carteCréeValeurChoix2Final2 = $carteCréeValeurChoix2[1];
            }

            $carteAleatoire = CarteAleatoire::getInstance()->getCardForDeckAndCreator($deck['id_deck'], $idCreateur);
            if ($carteAleatoire) {
                $carteAleatoire = Carte::getInstance()->getCardById($carteAleatoire['id_carte']);
            } else {
                $carteAleatoire = Carte::getInstance()->getRandomCard($deck['id_deck']);
                if ($carteAleatoire) {
                    CarteAleatoire::getInstance()->addCardForDeckAndCreator($deck['id_deck'], $idCreateur, $carteAleatoire['id_carte']);
                }
            }

            if ($carteAleatoire) {
                $carteAleatoireValeursChoix1 = explode(',', $carteAleatoire['valeurs_choix1']);
                $carteAleatoireValeursChoix1Final1 = $carteAleatoireValeursChoix1[0];
                $carteAleatoireValeursChoix1Final2 = $carteAleatoireValeursChoix1[1];
                $carteAleatoireValeursChoix2 = explode(',', $carteAleatoire['valeurs_choix2']);
                $carteAleatoireValeursChoix2Final1 = $carteAleatoireValeursChoix2[0];
                $carteAleatoireValeursChoix2Final2 = $carteAleatoireValeursChoix2[1];
            }
        } else {
            HTTP::redirect('/noDecks');
        }

        $error = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($carteCreee) {
                $error = "Vous avez déjà créé une carte.";
            } else {
                $texteCarte = htmlspecialchars(trim($_POST['texte_carte']));

                if (strlen($texteCarte) < 50 || strlen($texteCarte) > 280) {
                    $error = "Le texte de la carte doit contenir entre 50 et 280 caractères.";
                }

                $valeursChoix1 = htmlspecialchars(trim($_POST['valeurs_choix1'] . ',' . $_POST['valeurs_choix1bis']));
                $valeursChoix2 = htmlspecialchars(trim($_POST['valeurs_choix2bis'] . ',' . $_POST['valeurs_choix2']));
                $carteDansLeDeck = Carte::getInstance()->getNumberOfCardsInDeck($deck['id_deck']);

                if ($carteDansLeDeck < $totalCartes && empty($error)) {
                    $ordreSoumission = $carteDansLeDeck + 1;
                    Carte::getInstance()->create([
                        'date_soumission' => (new \DateTime())->format('Y-m-d'),
                        'ordre_soumission' => $ordreSoumission,
                        'valeurs_choix1' => $valeursChoix1,
                        'texte_carte' => $texteCarte,
                        'valeurs_choix2' => $valeursChoix2,
                        'id_deck' => $deck['id_deck'],
                        'id_createur' => $idCreateur,
                    ]);

                    HTTP::redirect('/game');
                } elseif ($carteDansLeDeck >= $totalCartes) {
                    $error = "Le deck est complet. Vous ne pouvez pas ajouter plus de cartes.";
                }
            }
        }

        $this->display('game/index.html.twig', compact('idCreateur', 'carteAleatoire', 'carteCreeeDetails', 'totalCartes', 'deck', 'error', 'carteCréeValeurChoix1Final1', 'carteCréeValeurChoix1Final2', 'carteCréeValeurChoix2Final1', 'carteCréeValeurChoix2Final2', 'carteAleatoireValeursChoix1Final1', 'carteAleatoireValeursChoix1Final2', 'carteAleatoireValeursChoix2Final1', 'carteAleatoireValeursChoix2Final2'));
    }

    public function noDecks()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['id_createur'])) {
            HTTP::redirect('/createurs/login');
        }

        $this->display('game/noDecks.html.twig');
    }
}
