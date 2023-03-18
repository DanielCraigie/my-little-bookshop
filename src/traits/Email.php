<?php

namespace Danielcraigie\Bookshop\traits;

use Danielcraigie\Bookshop\models\attributes\Email as EmailModel;
use Exception;

trait Email
{
    /**
     * @param string $newEmail
     * @return void
     * @throws Exception
     */
    public function addEmail(string $newEmail):void
    {
        $emailModel = new EmailModel($this->getPartitionKey());
        $emailModel->setValue($newEmail);
        $emailModel->create();
        printf("Email \"%s\" added to \"%s\".\n", $emailModel->getPartitionKey(), $this->getPartitionKey());
    }
}
