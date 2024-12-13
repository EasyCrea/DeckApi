<?php
declare(strict_types=1);

namespace App\Model;

class Gamehistory extends Model
{
    use TraitInstance;

    protected $tableName = APP_TABLE_PREFIX . 'game_history';
    public function getGameHistoryByCreateurAndDeck(int $userId, int $deckId)
    {
        $sql = "SELECT * FROM game_history WHERE user_id = :user_id AND deck_id = :deck_id ORDER BY game_date DESC";
        $sth = $this->query($sql, ['user_id' => $userId, 'deck_id' => $deckId]);
        
        if ($sth) {
            return $sth->fetchAll(); // Retourner tous les enregistrements d'historique
        }
        
        return null; // Aucun enregistrement trouvé
    }
    
    
 

    // Fonction pour ajouter un enregistrement dans la table game_history
    public function addGameHistory(int $userId, int $deckId, int $turnCount, int $finalPeople, int $finalTreasury, bool $isWinner): bool
    {
        $sql = "INSERT INTO game_history (user_id, deck_id, turn_count, final_people, final_treasury, is_winner)
                VALUES (:user_id, :deck_id, :turn_count, :final_people, :final_treasury, :is_winner)";
        $sth = $this->query($sql);
        $sth->bindParam(':user_id', $userId, \PDO::PARAM_INT);
        $sth->bindParam(':deck_id', $deckId, \PDO::PARAM_INT);
        $sth->bindParam(':turn_count', $turnCount, \PDO::PARAM_INT);
        $sth->bindParam(':final_people', $finalPeople, \PDO::PARAM_INT);
        $sth->bindParam(':final_treasury', $finalTreasury, \PDO::PARAM_INT);
        $sth->bindParam(':is_winner', $isWinner, \PDO::PARAM_BOOL);

        return $sth->execute();
    }
    public function deleteGameHistory(int $id): bool
{
    $sql = "DELETE FROM game_history WHERE id = :id";
    $sth = $this->query($sql);
    $sth->bindParam(':id', $id, \PDO::PARAM_INT);
    return $sth->execute();
}
public function updateGameHistory(int $id, int $turnCount, int $finalPeople, int $finalTreasury, bool $isWinner): bool
{
    $sql = "UPDATE game_history 
            SET turn_count = :turn_count,
                final_people = :final_people,
                final_treasury = :final_treasury,
                is_winner = :is_winner
            WHERE id = :id";
    $sth = $this->query($sql);
    $sth->bindParam(':id', $id, \PDO::PARAM_INT);
    $sth->bindParam(':turn_count', $turnCount, \PDO::PARAM_INT);
    $sth->bindParam(':final_people', $finalPeople, \PDO::PARAM_INT);
    $sth->bindParam(':final_treasury', $finalTreasury, \PDO::PARAM_INT);
    $sth->bindParam(':is_winner', $isWinner, \PDO::PARAM_BOOL);
    return $sth->execute();
}


}

