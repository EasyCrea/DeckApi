<?php

declare(strict_types=1);

namespace App\Model;

class Createur extends Model
{
    use TraitInstance;

    protected $tableName = APP_TABLE_PREFIX . 'createur';

    /**
     * Bannit un créateur.
     *
     * @param  int  $id  Identifiant du créateur à bannir.
     * @return bool
     */
    public function banCreateurModel(int $id): bool
    {
        $sql = 'UPDATE `' . $this->tableName . '` SET `banned` = 1 WHERE `id_createur` = :id';
        $sth = $this->query($sql, [':id' => $id]);
        return $sth->rowCount() > 0;
    }

    /**
     * Débannit un créateur.
     *
     * @param  int  $id  Identifiant du créateur à débannir.
     * @return bool
     */
    public function debanCreateurModel(int $id): bool
    {
        $sql = 'UPDATE `' . $this->tableName . '` SET `banned` = 0 WHERE `id_createur` = :id';
        $sth = $this->query($sql, [':id' => $id]);
        return $sth->rowCount() > 0;
    }
}
