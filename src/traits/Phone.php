<?php

namespace Danielcraigie\Bookshop\traits;

use Danielcraigie\Bookshop\models\attributes\Phone as PhoneModel;
use Exception;

trait Phone
{
    /**
     * @param string $newPhone
     * @return void
     * @throws Exception
     */
    public function addPhone(string $newPhone):void
    {
        $phoneModel = new PhoneModel($this->getPartitionKey());
        $phoneModel->setValue($newPhone);
        $phoneModel->create();
        printf("Phone Number \"%s\" added to \"%s\".\n", $phoneModel->getPartitionKey(), $this->getPartitionKey());
    }
}
