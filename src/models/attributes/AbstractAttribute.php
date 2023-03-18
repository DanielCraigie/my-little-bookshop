<?php

namespace Danielcraigie\Bookshop\models\attributes;

use Danielcraigie\Bookshop\AWS\dynamodb\DynamoDB;
use Danielcraigie\Bookshop\traits\PartitionKey;
use Exception;

abstract class AbstractAttribute
{
    use PartitionKey;

    /**
     * @var string|null $parentPartitionKey
     */
    private ?string $parentPartitionKey;

    /**
     * @var string|array $value
     */
    private string|array $value;

    /**
     * @param string $parentPartitionKey
     */
    public function __construct(string $parentPartitionKey)
    {
        $this->parentPartitionKey = $parentPartitionKey;
    }

    /**
     * @return string
     */
    protected function getParentPartitionKey():string
    {
        return $this->parentPartitionKey;
    }

    /**
     * @param string|array $value
     * @return void
     */
    public function setValue(string|array $value):void
    {
        $this->value = $value;
    }

    /**
     * @return string|array
     */
    public function getValue():string|array
    {
        return $this->value;
    }

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
                'Value' => ['S' => $this->value],
            ]);
        } catch (Exception $e) {
            throw new Exception(sprintf('Could not add %s for \"%s\" to Table.', __CLASS__, $this->getParentPartitionKey()));
        }
    }
}
