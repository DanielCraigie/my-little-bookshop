<?php

namespace Danielcraigie\Bookshop\AWS;

use Aws\DynamoDb\DynamoDbClient;
use Aws\Sdk;

final class AWS
{
    /**
     * @var DynamoDbClient|null $dynamoDbClient
     */
    private static ?DynamoDbClient $dynamoDbClient = null;

    /**
     * Creates DynamoDB connection and stores in static var
     * @return DynamoDbClient
     */
    public static function DynamoDB():DynamoDbClient
    {
        if (!self::$dynamoDbClient instanceof DynamoDbClient) {
            self::$dynamoDbClient = self::getAwsSdk()->createDynamoDb([
                'version' => $_ENV['DYNAMODB_VERSION'],
                'endpoint' => $_ENV['DYNAMODB_ENDPOINT'],
            ]);
        }

        return self::$dynamoDbClient;
    }

    /**
     * Creates AWS SDK factory
     * @return Sdk
     */
    private static function getAwsSdk(): Sdk
    {
        return new Sdk([
            'credentials' => [
                'key' => $_ENV['AWS_ACCESS_KEY_ID'],
                'secret' => $_ENV['AWS_SECRET_ACCESS_KEY'],
            ],
            'region' => $_ENV['REGION'],
            'version' => 'latest',
        ]);
    }
}
