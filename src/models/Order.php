<?php

namespace Danielcraigie\Bookshop\models;

use Aws\Result;
use Danielcraigie\Bookshop\AWS\AWS;
use Danielcraigie\Bookshop\AWS\dynamodb\DynamoDB;
use Danielcraigie\Bookshop\AWS\dynamodb\DynamoDBTableException;
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
     * @var Supplier $supplier
     */
    private Supplier $supplier;

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
     * @param float $total
     * @return void
     */
    public function setTotal(float $total):void
    {
        $this->total = $total;
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
     * @param string $sortKey
     * @return void
     * @throws DynamoDBTableException
     * @throws Exception
     */
    public function loadFromPartitionKey(string $partitionKey, string $sortKey = 'name'):void
    {
        $results = DynamoDB::query([
            'ExpressionAttributeValues' => [
                ':pk' => ['S' => $partitionKey],
                ':sk' => ['S' => 'details'],
            ],
            'KeyConditionExpression' => 'PK=:pk AND SK=:sk',
            'ProjectionExpression' => 'PK',
        ]);

        if (empty($results)) {
            throw new Exception(sprintf("%s with partition key: \"%s\" could not be found.", ucfirst(get_class($this)), $partitionKey));
        }

        $result = reset($results);
        $this->setPartitionKey(reset($result['PK']));

        if (isset($result['Total'])) {
            $this->setTotal((float)reset($result['Total']));
        }

        if (isset($result['StartDate'])) {
            $this->setStartDate(new DateTime(reset($result['StartDate'])));
        }

        if (isset($result['EndDate'])) {
            $this->setEndDate(new DateTime(reset($result['EndDate'])));
        }
    }

    /**
     * @return void
     * @throws Exception
     */
    public function create():void
    {
        if (!$this->supplier instanceof Supplier) {
            throw new Exception('Order must have a Supplier set before it can be created.');
        }

        // create "details" record
        $this->putItem([
            'SK' => ['S' => 'details'],
            'StartDate' => ['S' => $this->startDate->format('c')],
            'Total' => ['N' => $this->total],
            'GSI1PK' => ['S' => 'orders'],
            'GSI1SK' => ['S' => $this->getPartitionKey()],
        ]);

        try {
            // create Supplier relation record
            $this->putItem([
                'SK' => ['S' => $this->supplier->getPartitionKey()],
                'Value' => ['S' => $this->supplier->getName()],
                'GSI1PK' => ['S' => $this->supplier->getPartitionKey()],
                'GSI1SK' => ['S' => $this->getPartitionKey()],
            ]);
        } catch (Exception) {
            throw new Exception(sprintf("Could not set Supplier: %s for Order: %s.", $this->supplier->getPartitionKey(), $this->getPartitionKey()));
        }
    }

    /**
     * @param Supplier $supplier
     * @return void
     */
    public function setSupplier(Supplier $supplier):void
    {
        $this->supplier = $supplier;
    }

    public function getData(string $sortKey = ''):array
    {
        $query = [
            'ExpressionAttributeValues' => [
                ':pk' => ['S' => $this->getPartitionKey()],
            ],
            'KeyConditionExpression' => 'PK=:pk',
            'TableName' => $_ENV['TABLE_NAME'],
        ];

        if (!empty($sortKey)) {
            $query['KeyConditionExpression'] .= ' AND begins_with(SK, :sk)';
            $query['ExpressionAttributeValues'][':sk'] = ['S' => $sortKey];
        }

        $result = AWS::DynamoDB()->query($query);

        if (!$result instanceof Result
            || empty($result['Items'])
        ) {
            throw new Exception('Could not find Supplier.');
        }

        return $result['Items'];
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
                ':total' => ['N' => round($price * $result['Attributes']['Quantity']['N'], 2)],
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
