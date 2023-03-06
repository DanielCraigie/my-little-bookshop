<?php

namespace Danielcraigie\Bookshop\models\attributes;

use Aws\Result;
use Danielcraigie\Bookshop\AWS\AWS;
use Danielcraigie\Bookshop\models\AbstractModel;
use Exception;

abstract class AbstractAttribute extends AbstractModel
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
                'Details' => ['S' => $value],
            ],
            'TableName' => $_ENV['TABLE_NAME'],
        ]);

        if (!$result instanceof Result) {
            throw new Exception(sprintf('Could not add %s for \"%s\" to [%s].', __CLASS__, $partitionKey, $_ENV['TABLE_NAME']));
        }
    }
}
