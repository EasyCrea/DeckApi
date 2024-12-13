<?php

declare(strict_types=1);

namespace App\Model;

use \PDO;

class CarteAleatoire extends Model
{
    use TraitInstance;

    protected $tableName = APP_TABLE_PREFIX . 'carte_aleatoire';

    public function getCardForDeckAndCreator(int $deckId, int $creatorId): ?array
    {
        // Préparer la requête SQL pour récupérer la carte aléatoire
        $sql = "SELECT * FROM carte_aleatoire WHERE id_deck = :deckId AND id_createur = :creatorId";

        // Exécuter la requête
        $sth = $this->query($sql, [
            ':deckId' => $deckId,
            ':creatorId' => $creatorId
        ]);

        // Vérifier si la requête a été exécutée avec succès
        if ($sth !== false) {
            // Récupérer le résultat
            return $sth->fetch(PDO::FETCH_ASSOC) ?: null; // Retourner le résultat ou null si aucun résultat
        }

        // Retourner null si la requête échoue
        return null;
    }


    public function addCardForDeckAndCreator(int $deckId, int $creatorId, int $cardId): bool
    {
        // Préparer la requête SQL pour insérer la nouvelle association
        $sql = "INSERT INTO carte_aleatoire (id_deck, id_createur, id_carte) VALUES (:deckId, :creatorId, :cardId)";

        // Exécuter la requête
        $sth = $this->query($sql, [
            ':deckId' => $deckId,
            ':creatorId' => $creatorId,
            ':cardId' => $cardId
        ]);

        // Vérifier si l'insertion a réussi
        return $sth->rowCount() > 0; // Retourne true si une ligne a été insérée
    }
}
