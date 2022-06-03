<?php

namespace Danielcraigie\Bookshop\AWS\dynamodb\attributes;

use Exception;

class Tag implements Attribute
{
    /**
     * @var string $key
     */
    private string $key;

    /**
     * @var string $value
     */
    private string $value;

    public function __construct(string $key, string $value)
    {
        if (empty($key)) {
            throw new Exception(__CLASS__ . ' requires a Key name');
        }

        if (empty($value)) {
            throw new Exception(__CLASS__ . ' requires a Value');
        }

        $this->key = $key;
        $this->value = $value;
    }

    /**
     * @return string|array
     */
    public function getAttributeDefinition(): string|array
    {
        return [
            'Key' => $this->key,
            'Value' => $this->value,
        ];
    }
}
