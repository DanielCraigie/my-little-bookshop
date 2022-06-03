<?php

namespace Danielcraigie\Bookshop\AWS\dynamodb\attributes;

use Exception;

class Key implements Attribute
{
    const TYPE_HASH = 'HASH';
    const TYPE_RANGE = 'RANGE';

    /**
     * @var string $attributeName
     */
    private string $attributeName;

    /**
     * @var string $keyType
     */
    private string $keyType;

    public function __construct(string $attributeName, string $keyType = self::TYPE_HASH)
    {
        if (empty($attributeName)) {
            throw new Exception(__CLASS__ . ' requires an attribute name');
        }

        if (!in_array($keyType, [self::TYPE_HASH, self::TYPE_RANGE])) {
            throw new Exception('Incorrect key type passed to ' . __CLASS__);
        }

        $this->attributeName = $attributeName;
        $this->keyType = $keyType;
    }

    /**
     * @return string
     */
    public function getAttributeName(): string
    {
        return $this->attributeName;
    }

    /**
     * @return string|array
     */
    public function getAttributeDefinition(): string|array
    {
        return [
            'AttributeName' => $this->attributeName,
            'KeyType' => $this->keyType,
        ];
    }
}
