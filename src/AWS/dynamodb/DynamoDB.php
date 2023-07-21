<?php

namespace Danielcraigie\Bookshop\AWS\dynamodb;

use Aws\Result;
use Danielcraigie\Bookshop\AWS\AWS;
use Exception;

class DynamoDB
{
    /**
     * @param array $query
     * @return array
     * @throws DynamoDBTableException
     */
    public static function query(array $query):array
    {
        $query['TableName'] = $_ENV['TABLE_NAME'];

        try {
            $result = AWS::DynamoDB()->query($query);

            if (!$result instanceof Result) {
                throw new Exception('Query result is not an instance of Aws\Result');
            }

            return $result['Items'];
        } catch (Exception $e) {
            throw new DynamoDBTableException('Could not query DynamoDB table: ' . $e->getMessage());
        }
    }

    /**
     * @param array $item
     * @return void
     * @throws DynamoDBTableException
     */
    public static function putItem(array $item):void
    {
        try {
            $result = AWS::DynamoDB()->putItem([
                'Item' => $item,
                'TableName' => $_ENV['TABLE_NAME'],
            ]);

            if (!$result instanceof Result) {
                throw new Exception('putItem result is not an instance of Aws\Result');
            }
        } catch (Exception $e) {
            throw new DynamoDBTableException('Could not write item to table: ' . $e->getMessage());
        }
    }

    /**
     * @param array $item
     * @return void
     * @throws DynamoDBTableException
     */
    public static function updateItem(array $item): void
    {
        try {
            $result = AWS::DynamoDB()->updateItem([
                'Item' => $item,
                'TableName' => $_ENV['TABLE_NAME'],
            ]);

            if (!$result instanceof Result) {
                throw new Exception('putItem result is not an instance of Aws\Result');
            }
        } catch (Exception $e) {
            throw new DynamoDBTableException('Could not write item to table: ' . $e->getMessage());
        }
    }
}
