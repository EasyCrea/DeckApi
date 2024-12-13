<?php

declare(strict_types=1);

namespace App\Model;

class Admin extends Model
{
    use TraitInstance;

    protected $tableName = APP_TABLE_PREFIX . 'administrateur';
}
