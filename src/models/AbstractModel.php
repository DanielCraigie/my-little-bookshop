<?php

namespace Danielcraigie\Bookshop\models;

use Aws\DynamoDb\DynamoDbClient;
use Ramsey\Uuid\Uuid;

abstract class AbstractModel
{
    private DynamoDbClient $dynamoDbClient;

    private string $tableName;

    private string $partitionKey;

    public function __construct(DynamoDbClient $dynamoDbClient, string $tableName)
    {
        $this->dynamoDbClient = $dynamoDbClient;
        $this->tableName = $tableName;
        $classPath = explode('\\', get_class($this));
        $this->partitionKey = sprintf('%s#%s', mb_strtolower(end($classPath)), Uuid::uuid4()->toString());
    }

    protected function getDynamoDbClient():DynamoDbClient
    {
        return $this->dynamoDbClient;
    }

    protected function getTableName():string
    {
        return $this->tableName;
    }

    public function getPartitionKey():string
    {
        return $this->partitionKey;
    }

    protected function setPartitionKey(string $pk):void
    {
        $this->partitionKey = $pk;
    }
}
