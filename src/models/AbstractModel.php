<?php

namespace Danielcraigie\Bookshop\models;

use Aws\Result;
use Danielcraigie\Bookshop\AWS\AWS;
use Danielcraigie\Bookshop\traits\PartitionKey;
use Exception;

abstract class AbstractModel
{
    use PartitionKey;

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
