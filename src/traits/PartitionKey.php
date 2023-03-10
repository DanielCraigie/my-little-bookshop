<?php

namespace Danielcraigie\Bookshop\traits;

use Ramsey\Uuid\Uuid;

trait PartitionKey
{
    /**
     * @var string $partitionKey
     */
    private string $partitionKey;

    /**
     * @var bool
     */
    private bool $newModel = true;

    /**
     * @return string
     */
    public function getPartitionKey():string
    {
        if (empty($this->partitionKey)) {
            // auto generate PartitionKey for new Objects
            $classPath = explode('\\', get_class($this));
            $this->partitionKey = sprintf('%s#%s', mb_strtolower(end($classPath)), Uuid::uuid4()->toString());
        }

        return $this->partitionKey;
    }

    /**
     * @param string $pk
     * @return void
     */
    protected function setPartitionKey(string $pk):void
    {
        $this->partitionKey = $pk;
        $this->newModel = false;
    }

    /**
     * @return bool
     */
    public function isNewModel():bool
    {
        return $this->newModel;
    }
}
