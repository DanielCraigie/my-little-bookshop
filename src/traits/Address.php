<?php

namespace Danielcraigie\Bookshop\traits;

use Danielcraigie\Bookshop\models\attributes\Address as AddressModel;
use Exception;

trait Address
{
    /**
     * @param array $newAddress
     * @return void
     * @throws Exception
     */
    public function addAddress(array $newAddress):void
    {
        $addressModel = new AddressModel($this->getPartitionKey());
        $addressModel->setValue($newAddress);
        $addressModel->create();
        printf("Address \"%s\" added to \"%s\".\n", $addressModel->getPartitionKey(),  $this->getPartitionKey());
    }
}
