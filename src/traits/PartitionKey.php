<?php

namespace Danielcraigie\Bookshop\traits;

use Ramsey\Uuid\Uuid;

trait PartitionKey
{
    /**
     * @var string $partitionKey
     */
    private string $partitionKey = '';

    /**
     * @var bool
     */
    private bool $newModel = true;

    /**
     * @param string $prefix
     * @return string
     */
    public function createPartitionKey(string $prefix):string
    {
        return sprintf('%s#%s', mb_strtolower($prefix), Uuid::uuid4()->toString());
    }
}
