<?php

namespace Danielcraigie\Bookshop\models;

use Aws\Result;
use Danielcraigie\Bookshop\AWS\AWS;
use Exception;
use Ramsey\Uuid\Uuid;

abstract class AbstractModel
{
    /**
     * @var string
     */
    private string $partitionKey;

    /**
     * @var bool
     */
    private bool $newModel = true;

    public function __construct()
    {
        // auto generate PartitionKey for new Objects
        $classPath = explode('\\', get_class($this));
        $this->partitionKey = sprintf('%s#%s', mb_strtolower(end($classPath)), Uuid::uuid4()->toString());
    }

    /**
     * @return string
     */
    public function getPartitionKey():string
    {
        return $this->partitionKey;
    }

    /**
     * @param string $pk
     * @return void
     */
    protected function setPartitionKey(string $pk):void
    {
        $this->partitionKey = $pk;
        $this->newModel = false;
    }

    /**
     * @return bool
     */
    public function isNewModel():bool
    {
        return $this->newModel;
    }

    /**
     * @param string $partitionKey
     * @return void
     */
    abstract public function loadFromPartitionKey(string $partitionKey):void;

    /**
     * @return void
     */
    abstract public function create():void;

    /**
     * @param string $sortKey
     * @return array
     * @throws Exception
     */
    public function getData(string $sortKey = ''):array
    {
        $query = [
            'KeyConditionExpression' => 'PK=:pk',
            'ExpressionAttributeValues' => [
                ':pk' => ['S' => $this->getPartitionKey()],
            ],
            'TableName' => $_ENV['TABLE_NAME'],
        ];

        if (!empty($sortKey)) {
            $query['KeyConditionExpression'] .= ' AND begins_with(SK, :sk)';
            $query['ExpressionAttributeValues'][':sk'] = ['S' => $sortKey];
        }

        $result = AWS::DynamoDB()->query($query);

        if (!$result instanceof Result) {
            throw new Exception('Could not scan table.');
        }

        return $result['Items'];
    }

    /**
     * @param string $sortKey
     * @return void
     * @throws Exception
     */
    public function printDataRecords(string $sortKey = ''):void
    {
        foreach ($this->getData($sortKey) as $item) {
            if (isset($item['SK'])) {
                printf("\t[SK]=>\"%s\"", reset($item['SK']));
            }

            foreach ($item as $key => $attribute) {
                if (!in_array($key, ['PK', 'SK', 'GSI1PK', 'GSI1SK'])) {
                    printf(" [%s]=>\"%s\"", $key, reset($attribute));
                }
            }

            if (isset($item['GSI1PK'])) {
                printf(" [GSI1PK]=>\"%s\"", reset($item['GSI1PK']));

                if (isset($item['GSI1SK'])) {
                    printf(" [GSI1SK]=>\"%s\"", reset($item['GSI1SK']));
                }
            }

            echo "\n";
        }
    }
}
