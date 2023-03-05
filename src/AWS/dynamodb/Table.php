<?php

namespace Danielcraigie\Bookshop\AWS\dynamodb;

use Aws\DynamoDb\DynamoDbClient;
use Aws\Result;
use Exception;

class Table
{
    /**
     * @var DynamoDbClient $dynamoDbClient
     */
    private DynamoDbClient $dynamoDbClient;

    /**
     * @var string $tableName
     */
    private string $tableName;

    /**
     * @var Result $tableDescription
     */
    private Result|null $tableDescription = null;

    /**
     * @param DynamoDbClient $dynamoDbClient
     * @param string $tableName
     * @throws Exception
     */
    public function __construct(DynamoDbClient $dynamoDbClient, string $tableName)
    {
        if (empty($tableName)) {
            throw new Exception('You must provide a table name.');
        }

        $this->dynamoDbClient = $dynamoDbClient;
        $this->tableName = $tableName;
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function exists():bool
    {
        $result = $this->dynamoDbClient->listTables();

        if (!$result instanceof Result) {
            throw new Exception('An error occurred when performing table lookup.');
        }

        return in_array($this->tableName, $result['TableNames']);
    }

    /**
     * @return Result
     * @throws Exception
     */
    private function getTableDescription():Result
    {
        if (!$this->tableDescription instanceof Result) {
            $description = $this->dynamoDbClient->describeTable(['TableName' => $this->tableName]);

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
