<?php

require '../vendor/autoload.php';

$dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR);
$dotenv->load();

use Aws\DynamoDb\DynamoDbClient;

$client = new DynamoDbClient([
    'region' => $_ENV['REGION'],
    'version' => $_ENV['DYNAMODB_VERSION'],
    'endpoint' => $_ENV['DYNAMODB_ENDPOINT'],
]);
