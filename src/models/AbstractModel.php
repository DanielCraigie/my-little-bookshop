<?php

namespace Danielcraigie\Bookshop\models;

use Aws\Result;
use Danielcraigie\Bookshop\AWS\AWS;
use Danielcraigie\Bookshop\AWS\dynamodb\DynamoDB;
use Danielcraigie\Bookshop\AWS\dynamodb\DynamoDBTableException;
use Danielcraigie\Bookshop\traits\PartitionKey;
use Exception;

abstract class AbstractModel
{
    use PartitionKey;

    /**
     * @var string $name
     */
    private string $name = '';

    /**
     * @param string $name
     * @return void
     */
    public function setName(string $name):void
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName():string
    {
        return $this->name;
    }

    /**
     * @param string $partitionKey
     * @param string $sortKey
     * @return void
     * @throws DynamoDBTableException
     * @throws Exception
     */
    public function loadFromPartitionKey(string $partitionKey, string $sortKey = 'name'):void
    {
        $results = DynamoDB::query([
            'ExpressionAttributeNames' => ['#value' => 'Value'],
            'ExpressionAttributeValues' => [
                ':pk' => ['S' => $partitionKey],
                ':sk' => ['S' => $sortKey],
            ],
            'KeyConditionExpression' => 'PK=:pk AND SK=:sk',
            'ProjectionExpression' => 'PK,#value',
        ]);

        if (empty($results)) {
            throw new Exception(sprintf("%s with partition key: \"%s\" could not be found.", ucfirst(get_class($this)), $partitionKey));
        }

        $result = reset($results);
        $this->setPartitionKey(reset($result['PK']));
        $this->setName(reset($result['Value']));
    }

    /**
     * @return void
     */
    abstract public function create():void;

    /**
     * @param array $item
     * @return void
     * @throws Exception
     */
    protected function putItem(array $item):void
    {
        $item['PK'] = ['S' => $this->getPartitionKey()];

        try {
            DynamoDB::putItem($item);
        } catch (Exception $e) {
            throw new Exception(sprintf("Could not add %s %s to table.", ucfirst(get_class($this)), $this->getPartitionKey()));
        }
    }

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
