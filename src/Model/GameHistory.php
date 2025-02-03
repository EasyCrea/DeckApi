<?php
declare(strict_types=1);

namespace App\Model;

class Gamehistory extends Model
{
    use TraitInstance;

    protected $tableName = APP_TABLE_PREFIX . 'game_history';
    
    public function getGameHistoryByCreateurAndDeck(int $userId, int $deckId)
    {
        $sql = "SELECT * FROM game_history WHERE user_id = :user_id AND deck_id = :deck_id ORDER BY id DESC";
        $sth = $this->query($sql, ['user_id' => $userId, 'deck_id' => $deckId]);
        
        if ($sth) {
            return $sth->fetchAll(); // Retourner tous les enregistrements d'historique
        }
        
        return null; // Aucun enregistrement trouvé
    }




}

