<?php

namespace Danielcraigie\Bookshop\models;

use Ramsey\Uuid\Uuid;

abstract class AbstractModel
{
    private string $partitionKey;

    private bool $newModel = true;

    public function __construct()
    {
        // auto generate PartitionKey for new Objects
        $classPath = explode('\\', get_class($this));
        $this->partitionKey = sprintf('%s#%s', mb_strtolower(end($classPath)), Uuid::uuid4()->toString());
    }

    public function getPartitionKey():string
    {
        return $this->partitionKey;
    }

    protected function setPartitionKey(string $pk):void
    {
        $this->partitionKey = $pk;
        $this->newModel = false;
    }

    public function isNewModel():bool
    {
        return $this->newModel;
    }
}
