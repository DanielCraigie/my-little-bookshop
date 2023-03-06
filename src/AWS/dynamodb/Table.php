<?php

namespace Danielcraigie\Bookshop\AWS\dynamodb;

use Aws\DynamoDb\DynamoDbClient;
use Aws\Result;
use Danielcraigie\Bookshop\AWS\AWS;
use Exception;

class Table
{
    /**
     * @var Result|null $tableDescription
     */
    private Result|null $tableDescription = null;

    /**
     * @param DynamoDbClient $dynamoDbClient
     * @param string $tableName
     * @throws Exception
     */
    public function __construct()
    {
        if (empty($_ENV['TABLE_NAME'])) {
            throw new Exception('Table name has not been defined.');
        }
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function exists():bool
    {
        $result = AWS::DynamoDB()->listTables();

        if (!$result instanceof Result) {
            throw new Exception('An error occurred when performing table lookup.');
        }

        return in_array($_ENV['TABLE_NAME'], $result['TableNames']);
    }

    /**
     * @return void
     * @throws Exception
     */
    public function confirmExists():void
    {
        if (!$this->exists()) {
            throw new Exception(sprintf("Table[%s] does not exist.\n", $_ENV['TABLE_NAME']));
        }
    }

    /**
     * @return void
     * @throws Exception
     */
    public function confirmNotExists():void
    {
        if ($this->exists()) {
            throw new Exception(sprintf("Table[%s] not found.", $_ENV['TABLE_NAME']));
        }
    }

    /**
     * @return Result
     * @throws Exception
     */
    private function getTableDescription():Result
    {
        if (!$this->tableDescription instanceof Result) {
            $description = AWS::DynamoDB()->describeTable(['TableName' => $_ENV['TABLE_NAME']]);

            if (!$description instanceof Result) {
                throw new Exception('An error occurred when trying to get Table Description.');
            }

            $this->tableDescription = $description;
        }

        return $this->tableDescription;
    }

    /**
     * @return string
     * @throws Exception
     */
    public function getPartitionKey():string
    {
        $description = $this->getTableDescription();

        foreach ($description['Table']['KeySchema'] as $key) {
            if ($key['KeyType'] == 'HASH') {
                return $key['AttributeName'];
            }
        }

        throw new Exception('Table Partition Key could not be found.');
    }

    /**
     * @return string
     * @throws Exception
     */
    public function getSortKey():string
    {
        $description = $this->getTableDescription();

        foreach ($description['Table']['KeySchema'] as $key) {
            if ($key['KeyType'] == 'RANGE') {
                return $key['AttributeName'];
            }
        }

        throw new Exception('Table Sort Key could not be found.');
    }

    /**
     * @return string
     * @throws Exception
     */
    public function getGlobalSecondaryIndexes():array
    {
        $description = $this->getTableDescription();

        $indexes = [];

        foreach ($description['Table']['GlobalSecondaryIndexes'] as $index) {
            $indexes[$index['IndexName']] = [];

            foreach ($index['KeySchema'] as $schema) {
                if ($schema['KeyType'] == 'HASH') {
                    $indexes[$index['IndexName']]['PK'] = $schema['AttributeName'];
                } else {
                    $indexes[$index['IndexName']]['SK'] = $schema['AttributeName'];
                }
            }
        }

        return $indexes;
    }
}
