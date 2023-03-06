<?php

namespace Danielcraigie\Bookshop\models\attributes;

use Aws\Result;
use Danielcraigie\Bookshop\AWS\AWS;
use Exception;

class Address extends AbstractAttribute
{
    /**
     * @param string $partitionKey
     * @param mixed $value
     * @return void
     * @throws Exception
     */
    public function create(string $partitionKey, mixed $value):void
    {
        $result = AWS::DynamoDB()->putItem([
            'Item' => [
                'PK' => ['S' => $partitionKey],
                'SK' => ['S' => $this->getPartitionKey()],
                'Details' => ['S' => json_encode($value)],
            ],
            'TableName' => $_ENV['TABLE_NAME'],
        ]);

        if (!$result instanceof Result) {
            throw new Exception(sprintf('Could not add Address for \"%s\" to [%s].',$partitionKey, $_ENV['TABLE_NAME']));
        }
    }
}
