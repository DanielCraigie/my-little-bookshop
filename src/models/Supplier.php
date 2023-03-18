<?php

namespace Danielcraigie\Bookshop\models;

use Danielcraigie\Bookshop\AWS\dynamodb\DynamoDB;
use Danielcraigie\Bookshop\traits\Address;
use Danielcraigie\Bookshop\traits\Email;
use Danielcraigie\Bookshop\traits\Phone;
use Exception;

class Supplier extends AbstractModel
{
    use Address;
    use Phone;
    use Email;

    /**
     * @param string $name
     * @return void
     * @throws Exception
     */
    public function loadFromName(string $name):void
    {
        $results = DynamoDB::query([
            'IndexName' => 'GSI1',
            'KeyConditionExpression' => 'GSI1PK=:pk AND begins_with(GSI1SK, :sk)',
            'ExpressionAttributeValues' => [
                ':pk' => ['S' => 'suppliers'],
                ':sk' => ['S' => $name],
            ],
        ]);

        if (empty($results)) {
            throw new Exception(sprintf("Could not find Supplier with name \"%s\"", $name));
        }

        $supplier = reset($results);
        $this->setPartitionKey(reset($supplier['PK']));
        $this->setName(reset($supplier['Value']));
    }

    /**
     * @return void
     * @throws Exception
     */
    public function create():void
    {
        $this->putItem([
            'SK' => ['S' => 'name'],
            'GSI1PK' => ['S' => 'suppliers'],
            'GSI1SK' => ['S' => $this->getName()],
            'Value' => ['S' => $this->getName()],
        ]);

        printf("Supplier \"%s\" Name added to Table.\n", $this->getName());
    }
}
