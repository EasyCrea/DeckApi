<?php
/*
  Fichier : src/config/config.prod.php
*/

/**
 * le DSN de la base
 */
define('APP_DB_DSN', 'mysql:host=mysql-easydeck.alwaysdata.net;dbname=easydeck_db;charset=UTF8');

/**
 * le nom de l'utilisateur MYSQL
 */
define('APP_DB_USER', 'easydeck');

/**
 * le mot de passe de l'utilisateur MYSQL
 */
define('APP_DB_PASSWORD', 'lemotdepassecestmmi');

/**
 * le préfixe des tables dans la base (utile pour les bases partagées)
 */
define('APP_TABLE_PREFIX', '');

