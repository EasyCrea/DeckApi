<?php

declare(strict_types=1);

namespace App\Model;

class Carte extends Model
{
    use TraitInstance;

    protected $tableName = APP_TABLE_PREFIX . 'carte';

    public function getRandomCard(int $deckId)
    {
        $sql = "SELECT * FROM carte WHERE id_deck = :deckId ORDER BY RAND() LIMIT 1"; // Récupère une carte aléatoire
        $stmt = $this->query($sql, [':deckId' => $deckId]);
        if ($stmt) {
            return $stmt->fetch();
        }
        return null;
    }

    public function getCardById(int $id)
    {
        $sql = "SELECT * FROM carte WHERE id_carte = :id";
        $stmt = $this->query($sql, [':id' => $id]);
        if ($stmt) {
            return $stmt->fetch();
        }
        return null;
    }

    public function getNumberOfCardsInDeck(int $deckId)
    {
        $sql = "SELECT COUNT(*) FROM carte WHERE id_deck = :deckId";
        $stmt = $this->query($sql, [':deckId' => $deckId]);
        if ($stmt) {
            return $stmt->fetchColumn();
        }
        return 0;
    }

    public function getIfCreatorHasCreatedCard(int $creatorId)
    {
        $sql = "SELECT * FROM carte WHERE id_createur = :creatorId";
        $stmt = $this->query($sql, [':creatorId' => $creatorId]);
        if ($stmt) {
            return $stmt->fetch();
        }
        return null;
    }

    public function  getIfCreatorHasCreatedCardInDeck(int $creatorId, int $deckId)
    {
        $sql = "SELECT * FROM carte WHERE id_createur = :creatorId AND id_deck = :deckId";
        $stmt = $this->query($sql, [':creatorId' => $creatorId, ':deckId' => $deckId]);
        if ($stmt) {
            return $stmt->fetch();
        }
        return null;
    }

    public function updateCard(
        int $id,
        array $datas
    ): bool {
        if (empty($datas)) {
            // Si aucune donnée à mettre à jour, renvoyer false
            return false;
        }

        // Construction de la requête SQL
        $sql = 'UPDATE `' . $this->tableName . '` SET ';
        foreach (array_keys($datas) as $key) {
            // Vérifier que la clé n'est pas vide ou ne contient pas de caractères spéciaux
            if (!preg_match('/^[a-zA-Z0-9_]+$/', $key)) {
                continue;
            }
            $sql .= "`{$key}` = :{$key},";
        }
        $sql = rtrim($sql, ','); // Enlever la dernière virgule
        $sql .= ' WHERE id_carte = :id';

        // Préparation des attributs
        $attributes = [];
        foreach ($datas as $k => $v) {
            $attributes[':' . $k] = $v;
        }
        $attributes[':id'] = $id;

        // Exécuter la requête
        $sth = $this->query($sql, $attributes);
        return $sth ? $sth->rowCount() > 0 : false;
    }
}
