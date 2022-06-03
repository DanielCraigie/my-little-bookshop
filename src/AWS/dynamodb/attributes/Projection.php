<?php

namespace Danielcraigie\Bookshop\AWS\dynamodb\attributes;

use Exception;

class Projection implements Attribute
{
    const TYPE_ALL = 'ALL';
    const TYPE_KEYS_ONLY = 'KEYS_ONLY';
    const TYPE_INCLUDE = 'INCLUDE';

    /**
     * @var array $nonKeyAttributes
     */
    private array $nonKeyAttributes;

    /**
     * @var string $projectionType
     */
    private string $projectionType;

    public function __construct(string $projectionType = self::TYPE_ALL, array $nonKeyAttributes = [])
    {
        if (!in_array($projectionType, [self::TYPE_ALL, self::TYPE_KEYS_ONLY, self::TYPE_INCLUDE])) {
            throw new Exception('Incorrect projection type passed to ' . __CLASS__);
        }

        $this->nonKeyAttributes = $nonKeyAttributes;
        $this->projectionType = $projectionType;
    }

    /**
     * @return string|array
     */
    public function getAttributeDefinition(): string|array
    {
        $return = [
            'ProjectionType' => $this->projectionType,
        ];

        if (!empty($this->nonKeyAttributes)) {
            $return['NonKeyAttributes'] = $this->nonKeyAttributes;
        }

        return $return;
    }
}
