<?php

namespace Danielcraigie\Bookshop\models;

use Danielcraigie\Bookshop\AWS\dynamodb\DynamoDB;
use Danielcraigie\Bookshop\traits\Address;
use Danielcraigie\Bookshop\traits\Email;
use Danielcraigie\Bookshop\traits\Phone;
use Exception;

class Customer extends AbstractModel
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
                ':pk' => ['S' => 'customers'],
                ':sk' => ['S' => $name],
            ],
        ]);

        if (empty($results)) {
            throw new Exception(sprintf("Could not find Customer with name \"%s\"", $name));
        }

        $customer = reset($results);
        $this->setPartitionKey(reset($customer['PK']));
        $this->setName(reset($customer['Value']));
    }

    /**
     * @return void
     * @throws Exception
     */
    public function create():void
    {
        $this->putItem([
            'SK' => ['S' => 'name'],
            'GSI1PK' => ['S' => 'customers'],
            'GSI1SK' => ['S' => $this->getName()],
            'Value' => ['S' => $this->getName()],
        ]);

        printf("Customer \"%s\" Name added to Table.\n", $this->getName());
    }
}
