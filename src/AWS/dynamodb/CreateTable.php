<?php

namespace Danielcraigie\Bookshop\AWS\dynamodb;

use Danielcraigie\Bookshop\AWS\dynamodb\attributes\AttributeDefinition;
use Danielcraigie\Bookshop\AWS\dynamodb\attributes\GlobalSecondaryIndex;
use Danielcraigie\Bookshop\AWS\dynamodb\attributes\Key;
use Danielcraigie\Bookshop\AWS\dynamodb\attributes\ProvisionedThroughput;
use Danielcraigie\Bookshop\AWS\dynamodb\attributes\SecondaryIndex;
use Danielcraigie\Bookshop\AWS\dynamodb\attributes\SSE;
use Danielcraigie\Bookshop\AWS\dynamodb\attributes\Stream;
use Danielcraigie\Bookshop\AWS\dynamodb\attributes\Tag;
use Exception;

class CreateTable
{
    const MODE_PROVISIONED = 'PROVISIONED';
    const MODE_PAY_PER_REQUEST = 'PAY_PER_REQUEST';

    const CLASS_STANDARD = 'STANDARD';
    const CLASS_STANDARD_INFREQUENT_ACCESS = 'STANDARD_INFREQUENT_ACCESS';

    /**
     * @var AttributeDefinition[] $attributeDefinitions
     */
    private array $attributeDefinitions = [];

    /**
     * @var string $billingMode
     */
    private string $billingMode;

    /**
     * @var GlobalSecondaryIndex[] $globalSecondaryIndexes
     */
    private array $globalSecondaryIndexes = [];

    /**
     * @var Key[] $keySchema
     */
    private array $keySchema = [];

    /**
     * @var SecondaryIndex[] $localSecondaryIndexes
     */
    private array $localSecondaryIndexes = [];

    /**
     * @var ?ProvisionedThroughput $provisionedThroughput
     */
    private ?ProvisionedThroughput $provisionedThroughput = null;

    /**
     * @var ?SSE $sseSpecification
     */
    private ?SSE $sseSpecification = null;

    /**
     * @var ?Stream $streamSpecification
     */
    private ?Stream $streamSpecification = null;

    /**
     * @var string $tableClass
     */
    private string $tableClass;

    /**
     * @var string $tableName
     */
    private string $tableName;

    /**
     * @var Tag[] $tags
     */
    private array $tags = [];

    public function __construct(string $tableName, string $billingMode = self::MODE_PROVISIONED, string $tableClass = self::CLASS_STANDARD)
    {
        if (empty($tableName)) {
            throw new Exception(__CLASS__ . ' ' . __METHOD__ . ' requires a Table Name');
        }

        if (!in_array($billingMode, [self::MODE_PROVISIONED, self::MODE_PAY_PER_REQUEST])) {
            throw new Exception('Incorrect Billing Mode passed to ' . __CLASS__);
        }

        if (!in_array($tableClass, [self::CLASS_STANDARD, self::CLASS_STANDARD_INFREQUENT_ACCESS])) {
            throw new Exception(__CLASS__ . ' ' . __METHOD__ . ' requires a valid Table Class');
        }

        $this->tableName = $tableName;
        $this->billingMode = $billingMode;
        $this->tableClass = $tableClass;
    }

    /**
     * @param AttributeDefinition $attributeDefinition
     * @return void
     */
    public function addAttributeDefinition(AttributeDefinition $attributeDefinition)
    {
        $this->attributeDefinitions[] = $attributeDefinition;
    }

    /**
     * @param GlobalSecondaryIndex $globalSecondaryIndex
     * @return void
     */
    public function addGlobalSecondaryIndex(GlobalSecondaryIndex $globalSecondaryIndex)
    {
        $this->globalSecondaryIndexes[] = $globalSecondaryIndex;
    }

    /**
     * @param Key $key
     * @return void
     * @throws Exception
     */
    public function addKeyToSchema(Key $key)
    {
        $tableAttributes = $this->getAttributeNames();

        if (!in_array($key->getAttributeName(), $tableAttributes)) {
            throw new Exception(__METHOD__ . ' key is not a valid table attribute');
        }

        $this->keySchema[] = $key;
    }

    /**
     * @param SecondaryIndex $secondaryIndex
     * @return void
     */
    public function addLocalSecondaryIndex(SecondaryIndex $secondaryIndex)
    {
        $this->localSecondaryIndexes[] = $secondaryIndex;
    }

    /**
     * @param ProvisionedThroughput $provisionedThroughput
     * @return void
     */
    public function setProvisionedThroughput(ProvisionedThroughput $provisionedThroughput)
    {
        $this->provisionedThroughput = $provisionedThroughput;
    }

    /**
     * @param SSE $sseSpecification
     * @return void
     */
    public function setSseSpecification(SSE $sseSpecification)
    {
        $this->sseSpecification = $sseSpecification;
    }

    /**
     * @param Stream $streamSpecification
     * @return void
     */
    public function setStreamSpecification(Stream $streamSpecification)
    {
        $this->streamSpecification = $streamSpecification;
    }

    /**
     * @param Tag $tag
     * @return void
     */
    public function addTag(Tag $tag)
    {
        $this->tags[] = $tag;
    }

    /**
     * @return array
     */
    private function getAttributeNames(): array
    {
        return array_map(function ($attribute) {
            /** @var AttributeDefinition $attribute */
            return $attribute->getAttributeName();
        }, $this->attributeDefinitions);
    }

    /**
     * @return array
     * @throws Exception
     */
    public function getTableDefinition(): array
    {
        foreach (['attributeDefinitions', 'keySchema', 'tableName'] as $attribute) {
            if (empty($this->$attribute)) {
                throw new Exception(__CLASS__ . ' requires ' . $attribute . ' to be defined');
            }
        }

        $return = [];

        $return['AttributeDefinitions'] = [];
        foreach ($this->attributeDefinitions as $attribute) {
            $return['AttributeDefinitions'][] = $attribute->getAttributeDefinition();
        }

        $return['BillingMode'] = $this->billingMode;

        if (!empty($this->globalSecondaryIndexes)) {
            $return['GlobalSecondaryIndexes'] = [];
            foreach ($this->globalSecondaryIndexes as $index) {
                $return['GlobalSecondaryIndexes'][] = $index->getAttributeDefinition();
            }
        }

        $return['KeySchema'] = [];
        foreach ($this->keySchema as $key) {
            $return['KeySchema'][] = $key->getAttributeDefinition();
        }

        if (!empty($this->localSecondaryIndexes)) {
            $return['LocalSecondaryIndexes'] = [];
            foreach ($this->localSecondaryIndexes as $index) {
                $return['LocalSecondaryIndexes'][] = $index->getAttributeDefinition();
            }
        }

        if ($this->provisionedThroughput instanceof ProvisionedThroughput) {
            $return['ProvisionedThroughput'] = $this->provisionedThroughput->getAttributeDefinition();
        }

        if ($this->sseSpecification instanceof SSE) {
            $return['SSESpecification'] = $this->sseSpecification->getAttributeDefinition();
        }

        if ($this->streamSpecification instanceof Stream) {
            $return['StreamSpecification'] = $this->streamSpecification->getAttributeDefinition();
        }

        $return['TableClass'] = $this->tableClass;
        $return['TableName'] = $this->tableName;

        if (!empty($this->tags)) {
            $return['Tags'] = [];
            foreach ($this->tags as $tag) {
                $return['Tags'][] = $tag->getAttributeDefinition();
            }
        }

        return $return;
    }
}
