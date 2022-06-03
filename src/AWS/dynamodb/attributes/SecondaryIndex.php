<?php

namespace Danielcraigie\Bookshop\AWS\dynamodb\attributes;

use Exception;

class SecondaryIndex implements Attribute
{
    /**
     * @var string $indexName
     */
    private string $indexName;

    /**
     * @var Key[] $keySchema
     */
    private array $keySchema;

    /**
     * @var ?Projection $projection
     */
    private ?Projection $projection = null;

    public function __construct(string $indexName, array $keySchema, Projection $projection = null)
    {
        if (empty($indexName)) {
            throw new Exception(__CLASS__ . ' requires an Index Name');
        }

        if (empty($keySchema)) {
            throw new Exception(__CLASS__ . ' requires a key schema');
        }

        foreach ($keySchema as $key) {
            if (!$key instanceof Key) {
                throw new Exception(__CLASS__ . ' requires an array of Key objects');
            }
        }

        $this->indexName = $indexName;
        $this->keySchema = $keySchema;
        $this->projection = $projection;
    }

    /**
     * @return string|array
     */
    public function getAttributeDefinition(): string|array
    {
        $return = [
            'IndexName' => $this->indexName,
        ];

        /** @var Key $key */
        foreach ($this->keySchema as $key) {
            $return['KeySchema'][] = $key->getAttributeDefinition();
        }

        if ($this->projection instanceof Projection) {
            $return['Projection'] = $this->projection->getAttributeDefinition();
        }

        return $return;
    }
}
