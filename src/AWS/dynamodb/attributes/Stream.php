<?php

namespace Danielcraigie\Bookshop\AWS\dynamodb\attributes;

use Exception;

class Stream implements Attribute
{
    const TYPE_NEW_IMAGE = 'NEW_IMAGE';
    const TYPE_OLD_IMAGE = 'OLD_IMAGE';
    const TYPE_NEW_AND_OLD_IMAGES = 'NEW_AND_OLD_IMAGES';
    const TYPE_KEYS_ONLY = 'KEYS_ONLY';

    /**
     * @var bool $streamEnabled
     */
    private bool $streamEnabled;

    /**
     * @var string $streamViewType
     */
    private string $streamViewType;

    public function __construct(string $streamViewType, bool $streamEnabled = true)
    {
        if (!in_array($streamViewType, [
            self::TYPE_NEW_IMAGE,
            self::TYPE_OLD_IMAGE,
            self::TYPE_NEW_AND_OLD_IMAGES,
            self::TYPE_KEYS_ONLY
        ])) {
            throw new Exception(__CLASS__ . ' requires a valid Stream View Type');
        }

        $this->streamEnabled = $streamEnabled;
        $this->streamViewType = $streamViewType;
    }

    /**
     * @return string|array
     */
    public function getAttributeDefinition(): string|array
    {
        return [
            'StreamEnabled' => $this->streamEnabled,
            'StreamViewType' => $this->streamViewType,
        ];
    }
}
