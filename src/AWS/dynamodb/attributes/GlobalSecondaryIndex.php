<?php

namespace Danielcraigie\Bookshop\AWS\dynamodb\attributes;

class GlobalSecondaryIndex extends SecondaryIndex
{
    /**
     * @var ?ProvisionedThroughput $provisionedThroughput
     */
    private ?ProvisionedThroughput $provisionedThroughput = null;

    public function __construct(string $indexName, array $keySchema, Projection $projection = null, ProvisionedThroughput $provisionedThroughput = null)
    {
        parent::__construct($indexName, $keySchema, $projection);

        if ($provisionedThroughput instanceof ProvisionedThroughput) {
            $this->provisionedThroughput = $provisionedThroughput;
        }
    }

    /**
     * @return string|array
     */
    public function getAttributeDefinition(): string|array
    {
        $return = parent::getAttributeDefinition();

        if ($this->provisionedThroughput instanceof ProvisionedThroughput) {
            $return['ProvisionedThroughput'] = $this->provisionedThroughput->getAttributeDefinition();
        }

        return $return;
    }
}
