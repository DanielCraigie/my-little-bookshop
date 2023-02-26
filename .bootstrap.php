<?php

use Dotenv\Dotenv;
use Aws\Sdk;

require implode(DIRECTORY_SEPARATOR, ['vendor', 'autoload.php']);

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

/*
 * Init AWS PHP SDK
 */
$sdk = new Sdk([
    'credentials' => [
        'key' => $_ENV['AWS_ACCESS_KEY_ID'],
        'secret' => $_ENV['AWS_SECRET_ACCESS_KEY'],
    ],
    'region' => $_ENV['REGION'],
    'version' => 'latest',
]);

$dynClient = $sdk->createDynamoDb([
    'version' => $_ENV['DYNAMODB_VERSION'],
    'endpoint' => $_ENV['DYNAMODB_ENDPOINT'],
]);

$tableName = $_ENV['TABLE_NAME'];
