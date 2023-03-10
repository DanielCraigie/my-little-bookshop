<?php

namespace Danielcraigie\Bookshop\models\attributes;

use Aws\Result;
use Danielcraigie\Bookshop\AWS\AWS;
use Danielcraigie\Bookshop\models\AbstractModel;
use Danielcraigie\Bookshop\traits\PartitionKey;
use Exception;

abstract class AbstractAttribute
{
    use PartitionKey;

    /**
     * @var AbstractModel|null $model
     */
    private ?AbstractModel $model;

    /**
     * @var string|array $value
     */
    private string|array $value;

    /**
     * @param AbstractModel $model
     */
    public function __construct(AbstractModel $model)
    {
        $this->model = $model;
    }

    /**
     * @return AbstractModel
     */
    protected function getModel():AbstractModel
    {
        return $this->model;
    }

    /**
     * @param string|array $value
     * @return void
     */
    public function setValue(string|array $value):void
    {
        $this->value = $value;
    }

    /**
     * @return string|array
     */
    public function getValue():string|array
    {
        return $this->value;
    }

    /**
     * @return void
     * @throws Exception
     */
    public function create():void
    {
        $result = AWS::DynamoDB()->putItem([
            'Item' => [
                'PK' => ['S' => $this->model->getPartitionKey()],
                'SK' => ['S' => $this->getPartitionKey()],
                'Value' => ['S' => $this->value],
            ],
            'TableName' => $_ENV['TABLE_NAME'],
        ]);

        if (!$result instanceof Result) {
            throw new Exception(sprintf('Could not add %s for \"%s\" to [%s].', __CLASS__, $this->model->getPartitionKey(), $_ENV['TABLE_NAME']));
        }
    }
}
