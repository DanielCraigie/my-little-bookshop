<?php

namespace Danielcraigie\Bookshop\models;

use Aws\Result;
use Danielcraigie\Bookshop\models\attributes\Address;
use Danielcraigie\Bookshop\models\attributes\Email;
use Danielcraigie\Bookshop\models\attributes\Phone;
use Exception;

class Supplier extends AbstractModel
{
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
     * @param string $name
     * @return void
     * @throws Exception
     */
    public function loadFromName(string $name):void
    {
        $result = $this->getDynamoDbClient()->query([
            'IndexName' => 'GSI1',
            'KeyConditionExpression' => 'GSI1PK=:pk AND begins_with(GSI1SK, :sk)',
            'ExpressionAttributeValues' => [
                ':pk' => ['S' => 'suppliers'],
                ':sk' => ['S' => $name],
            ],
            'TableName' => $this->getTableName(),
        ]);

        if (!$result instanceof Result) {
            throw new Exception('Could not query table.');
        }

        if (empty($result['Items'])) {
            throw new Exception(sprintf("Could not find Supplier with name \"%s\"", $name));
        }

        $supplier = reset($result['Items']);
        $this->setPartitionKey($supplier['PK']['S']);
        $this->setName($supplier['Value']['S']);
    }

    /**
     * @param string $partitionKey
     * @return void
     * @throws Exception
     */
    public function loadFromPartitionKey(string $partitionKey):void
    {
        $result = $this->getDynamoDbClient()->query([
            'KeyConditionExpression' => 'PK=:pk AND SK=:sk',
            'ExpressionAttributeNames' => ['#value' => 'Value'],
            'ExpressionAttributeValues' => [
                ':pk' => ['S' => $partitionKey],
                ':sk' => ['S' => 'name'],
            ],
            'ProjectionExpression' => 'PK,#value',
            'TableName' => $this->getTableName(),
        ]);

        if (!$result instanceof Result) {
            throw new Exception('Could not scan table.');
        }

        $supplier = reset($result['Items']);
        $this->setPartitionKey($supplier['PK']['S']);
        $this->setName($supplier['Value']['S']);
    }

    public function getData(string $sortKey = ''):array
    {
        $query = [
            'KeyConditionExpression' => 'PK=:pk',
            'ExpressionAttributeValues' => [
                ':pk' => ['S' => $this->getPartitionKey()],
            ],
            'TableName' => $this->getTableName(),
        ];

        if (!empty($sortKey)) {
            $query['KeyConditionExpression'] .= ' AND begins_with(SK, :sk)';
            $query['ExpressionAttributeValues'][':sk'] = ['S' => $sortKey];
        }

        $result = $this->getDynamoDbClient()->query($query);

        if (!$result instanceof Result) {
            throw new Exception('Could not scan table.');
        }

        return $result['Items'];
    }

    /**
     * @return void
     * @throws Exception
     */
    public function create():void
    {
        $result =  $this->getDynamoDbClient()->putItem([
            'Item' => [
                'PK' => ['S' => $this->getPartitionKey()],
                'SK' => ['S' => 'name'],
                'GSI1PK' => ['S' => 'suppliers'],
                'GSI1SK' => ['S' => $this->getName()],
                'Value' => ['S' => $this->getName()],
            ],
            'TableName' => $this->getTableName(),
        ]);

        if ($result instanceof Result) {
            printf("Supplier \"%s\" Name added to [%s].\n", $this->getName(), $this->getTableName());
        } else {
            throw new Exception(sprintf("Could not add \"%s\" to [%s].", $this->getName(), $this->getTableName()));
        }
    }

    /**
     * @param array $newAddress
     * @return void
     * @throws Exception
     */
    public function addAddress(array $newAddress):void
    {
        $addressModel = new Address($this->getDynamoDbClient(), $this->getTableName());
        $addressModel->create($this->getPartitionKey(), $newAddress);
        printf("Supplier \"%s\" Address added to [%s].\n", $this->getName(), $this->getTableName());
    }

    /**
     * @param string $newPhone
     * @return void
     * @throws Exception
     */
    public function addPhone(string $newPhone):void
    {
        $phoneModel = new Phone($this->getDynamoDbClient(), $this->getTableName());
        $phoneModel->create($this->getPartitionKey(), $newPhone);
        printf("Supplier \"%s\" Phone Number added to [%s].\n", $this->getName(), $this->getTableName());
    }

    /**
     * @param string $newEmail
     * @return void
     * @throws Exception
     */
    public function addEmail(string $newEmail):void
    {
        $emailModel = new Email($this->getDynamoDbClient(), $this->getTableName());
        $emailModel->create($this->getPartitionKey(), $newEmail);
        printf("Supplier \"%s\" Email added to [%s].\n", $this->getName(), $this->getTableName());
    }
}
