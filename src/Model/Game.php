<?php

declare(strict_types=1);

namespace App\Model;

class Game extends Model
{
    use TraitInstance;

    protected $tableName = APP_TABLE_PREFIX . 'carte';
}
