<?php

namespace Danielcraigie\Bookshop\models\attributes;

use Danielcraigie\Bookshop\AWS\dynamodb\DynamoDB;
use Exception;

class Address extends AbstractAttribute
{
    /**
     * @return void
     * @throws Exception
     */
    public function create():void
    {
        try {
            DynamoDB::putItem([
                'PK' => ['S' => $this->getParentPartitionKey()],
                'SK' => ['S' => $this->getPartitionKey()],
                'Details' => ['S' => json_encode($this->getValue())],
            ]);
        } catch (Exception $e) {
            throw new Exception(sprintf('Could not add Address for \"%s\" to Table.', $this->getParentPartitionKey()));
        }
    }
}
