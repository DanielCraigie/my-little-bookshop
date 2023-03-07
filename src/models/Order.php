<?php

namespace Danielcraigie\Bookshop\models;

use Aws\Result;
use Danielcraigie\Bookshop\AWS\AWS;
use DateTime;
use Exception;

class Order extends AbstractModel
{
    /**
     * @var float $total
     */
    private float $total = 0.0;

    /**
     * @var DateTime $startDate
     */
    private DateTime $startDate;

    /**
     * @var DateTime $endDate
     */
    private DateTime $endDate;

    /**
     * @return float
     */
    public function getTotal():float
    {
        return $this->total;
    }

    /**
     * @return DateTime
     */
    public function getStartDate():DateTime
    {
        return $this->startDate;
    }

    /**
     * @return DateTime
     */
    public function getEndDate():DateTime
    {
        return $this->endDate;
    }

    /**
     * @param DateTime $date
     * @return void
     */
    public function setStartDate(DateTime $date):void
    {
        $this->startDate = $date;
    }

    /**
     * @param DateTime $date
     * @return void
     */
    public function setEndDate(DateTime $date):void
    {
        $this->endDate = $date;
    }

    /**
     * @param string $partitionKey
     * @return void
     * @throws Exception
     */
    public function loadFromPartitionKey(string $partitionKey):void
    {
        $result = AWS::DynamoDB()->query([
            'KeyConditionExpression' => 'PK=:pk AND SK=:sk',
            'ExpressionAttributeValues' => [
                ':pk' => ['S' => $partitionKey],
                ':sk' => ['S' => 'details'],
            ],
            'TableName' => $_ENV['TABLE_NAME'],
        ]);

        if (!$result instanceof Result) {
            throw new Exception('Could not scan table.');
        }

        $supplier = reset($result['Items']);
        $this->setPartitionKey($supplier['PK']['S']);
    }

    /**
     * @param Supplier $supplier
     * @return void
     * @throws Exception
     */
    public function create(Supplier $supplier):void
    {
        // create "details" record
        $result = AWS::DynamoDB()->putItem([
            'Item' => [
                'PK' => ['S' => $this->getPartitionKey()],
                'SK' => ['S' => 'details'],
                'StartDate' => ['S' => $this->startDate->format('c')],
                'Total' => ['N' => $this->total],
                'GSI1PK' => ['S' => 'orders'],
                'GSI1SK' => ['S' => $this->getPartitionKey()],
            ],
            'TableName' => $_ENV['TABLE_NAME'],
        ]);

        if (!$result instanceof Result) {
            throw new Exception('Could not create new Order record.');
        }

        // create Supplier relation record
        $result = AWS::DynamoDB()->putItem([
            'Item' => [
                'PK' => ['S' => $this->getPartitionKey()],
                'SK' => ['S' => $supplier->getPartitionKey()],
                'Value' => ['S' => $supplier->getName()],
                'GSI1PK' => ['S' => $supplier->getPartitionKey()],
                'GSI1SK' => ['S' => $this->getPartitionKey()],
            ],
            'TableName' => $_ENV['TABLE_NAME'],
        ]);

        if (!$result instanceof Result) {
            throw new Exception('Could not set Supplier for new Order.');
        }
    }

    /**
     * @param string $isbn
     * @param string $title
     * @param float $price
     * @param int $quantity
     * @return void
     * @throws Exception
     */
    public function addBook(string $isbn, string $title, float $price, int $quantity):void
    {
        $bookPartitionKey = sprintf("book#%s", $isbn);

        $result = AWS::DynamoDB()->updateItem([
            'ExpressionAttributeNames' => [
                '#value' => 'Value',
            ],
            'ExpressionAttributeValues' => [
                ':value' => ['S' => $title],
                ':price' => ['S' => $price],
                ':quantity' => ['N' => $quantity],
                ':defaultQuantity' => ['N' => 0.0],
                ':gsi1pk' => ['S' => $bookPartitionKey],
                ':gsi1sk' => ['S' => $this->getPartitionKey()],
            ],
            'Key' => [
                'PK' => ['S' => $this->getPartitionKey()],
                'SK' => ['S' => $bookPartitionKey],
            ],
            'ReturnConsumedCapacity' => 'TOTAL',
            'ReturnValues' => 'UPDATED_NEW',
            'TableName' => $_ENV['TABLE_NAME'],
            'UpdateExpression' => 'SET #value=if_not_exists(#value,:value), Price=if_not_exists(Price,:price), Quantity=if_not_exists(Quantity,:defaultQuantity)+:quantity, GSI1PK=if_not_exists(GSI1PK,:gsi1pk), GSI1SK=if_not_exists(GSI1SK,:gsi1sk)',
        ]);

        if (!$result instanceof Result) {
            throw new Exception(sprintf("Could not update Total for Order[%s]", $this->getPartitionKey()));
        }

        /*
         * update Total in existing Order details record
         * @todo: This needs to be replaced by Stream processing to automatically update the Order Total
         */
        $result = AWS::DynamoDB()->updateItem([
            'ExpressionAttributeNames' => [
                '#total' => 'Total',
            ],
            'ExpressionAttributeValues' => [
                ':total' => ['N' => $price * $result['Attributes']['Quantity']['N']],
            ],
            'Key' => [
                'PK' => ['S' => $this->getPartitionKey()],
                'SK' => ['S' => 'details'],
            ],
            'ReturnValues' => 'UPDATED_NEW',
            'TableName' => $_ENV['TABLE_NAME'],
            'UpdateExpression' => 'SET #total = :total',
        ]);

        if (!$result instanceof Result) {
            throw new Exception(sprintf("Could not update Total for Order[%s]", $this->getPartitionKey()));
        }

        // update Model with new Order Total
        $this->total = $result['Attributes']['Total']['N'];
    }
}
