<?php

namespace Danielcraigie\Bookshop\AWS\dynamodb\attributes;

use Exception;

class ProvisionedThroughput implements Attribute
{
    /**
     * @var int $readCapacityUnits
     */
    private int $readCapacityUnits;

    /**
     * @var int $writeCapacityUnits
     */
    private int $writeCapacityUnits;

    public function __construct(int $readCapacityUnits, int $writeCapacityUnits)
    {
        if ($readCapacityUnits <= 0) {
            throw new Exception('Read capacity units must be a positive integer');
        }

        if ($writeCapacityUnits <= 0) {
            throw new Exception('Write capacity units must be a positive integer');
        }

        $this->readCapacityUnits = $readCapacityUnits;
        $this->writeCapacityUnits = $writeCapacityUnits;
    }

    /**
     * @return string|array
     */
    public function getAttributeDefinition(): string|array
    {
        return [
            'ReadCapacityUnits' => $this->readCapacityUnits,
            'WriteCapacityUnits' => $this->writeCapacityUnits,
        ];
    }
}
