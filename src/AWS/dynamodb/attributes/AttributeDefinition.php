<?php

namespace Danielcraigie\Bookshop\AWS\dynamodb\attributes;

use Exception;

class AttributeDefinition implements Attribute
{
    const TYPE_STRING = 'S';
    const TYPE_NUMBER = 'N';
    const TYPE_BINARY = 'B';

    /**
     * @var string $attributeName
     */
    private string $attributeName;

    /**
     * @var string $attributeType
     */
    private string $attributeType;

    public function __construct(string $attributeName, string $attributeType = self::TYPE_STRING)
    {
        if (empty($attributeName)) {
            throw new Exception(__CLASS__ . ' requires an attribute name');
        }

        if (!in_array($attributeType, [self::TYPE_STRING, self::TYPE_NUMBER, self::TYPE_BINARY])) {
            throw new Exception('Incorrect Attribute Type passed to ' . __CLASS__);
        }

        $this->attributeName = $attributeName;
        $this->attributeType = $attributeType;
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
            'AttributeType' => $this->attributeType,
        ];
    }
}
