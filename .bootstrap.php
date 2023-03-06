<?php

use Dotenv\Dotenv;

require implode(DIRECTORY_SEPARATOR, ['vendor', 'autoload.php']);

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();
