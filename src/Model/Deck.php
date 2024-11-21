<?php

declare(strict_types=1);

namespace App\Model;



class Deck extends Model
{
    use TraitInstance;

    protected $tableName = APP_TABLE_PREFIX . 'deck';

    public function getLiveDeck()
    {
        $sql = "SELECT * FROM deck WHERE live = 1 LIMIT 1";
        $sth = $this->query($sql);
        if ($sth) {
            return $sth->fetch();
        }
        return null;
    }

    public function getTotalCardsInDeck(int $deckId)
    {
        $sql = "SELECT nb_cartes FROM deck WHERE id_deck = :deckId";
        $sth = $this->query($sql, [':deckId' => $deckId]);
        if ($sth) {
            return $sth->fetchColumn();
        }
        return 0;
    }

    public function disableDeck(int $deckId)
    {
        $sql = "UPDATE deck SET live = 0 WHERE id_deck = :deckId";
        $sth = $this->query($sql, [':deckId' => $deckId]);
        return $sth->rowCount() > 0;
    }


    public function getCardsByDeckId(int $deckId): array
    {
        $sql = "SELECT * FROM carte WHERE id_deck = :deckId";
        $stmt = $this->query($sql, [':deckId' => $deckId]);
        if ($stmt) {
            return $stmt->fetchAll(); // Renvoie toutes les cartes trouvées
        }
        return []; // Si aucune carte n'est trouvée, renvoie un tableau vide
    }
}
