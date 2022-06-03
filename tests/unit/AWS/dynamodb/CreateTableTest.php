<?php
namespace unit\AWS\dynamodb;

use Danielcraigie\Bookshop\AWS\dynamodb\attributes\AttributeDefinition;
use Danielcraigie\Bookshop\AWS\dynamodb\attributes\GlobalSecondaryIndex;
use Danielcraigie\Bookshop\AWS\dynamodb\attributes\Key;
use Danielcraigie\Bookshop\AWS\dynamodb\attributes\ProvisionedThroughput;
use Danielcraigie\Bookshop\AWS\dynamodb\attributes\SecondaryIndex;
use Danielcraigie\Bookshop\AWS\dynamodb\attributes\SSE;
use Danielcraigie\Bookshop\AWS\dynamodb\attributes\Stream;
use Danielcraigie\Bookshop\AWS\dynamodb\attributes\Tag;
use Danielcraigie\Bookshop\AWS\dynamodb\CreateTable;

class CreateTableTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;
    
    protected function _before()
    {
    }

    protected function _after()
    {
    }

    /**
     * @return void
     */
    public function testExcptionThrownOnEmptyTableName()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches('/ requires a Table Name$/');
        new CreateTable('');
    }

    /**
     * @return void
     */
    public function testExceptionThrownForInvalidBillingMode()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches('/^Incorrect Billing Mode passed to /');
        new CreateTable('test', 'xyz');
    }

    /**
     * @return void
     */
    public function testExceptionThrownForInvalidTableClass()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches('/ requires a valid Table Class$/');
        new CreateTable('test', CreateTable::MODE_PROVISIONED, 'xyz');
    }

    /**
     * @param $attribute
     * @param $key
     * @param $result
     * @return void
     * @throws \Exception
     * @dataProvider requiredAttributesDataProvider
     */
    public function testExceptionThrownWhenGetTableDefinitionIsCalledWithoutRequiredAttributesSet($attribute, $key, $result)
    {
        $table = new CreateTable('test');

        if ($attribute instanceof AttributeDefinition) {
            $table->addAttributeDefinition($attribute);
        }

        if ($key instanceof Key) {
            $table->addKeyToSchema($key);
        }

        if (!$result) {
            $this->expectException(\Exception::class);
        }

        $definition = $table->getTableDefinition();

        if ($result) {
            $this->assertIsArray($definition);
        }
    }

    /**
     * @return array[]
     */
    protected function requiredAttributesDataProvider()
    {
        return [
            [null, null, false],
            [new AttributeDefinition('test'), null, false],
            [new AttributeDefinition('test'), new Key('test'), true],
        ];
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function testExceptionIsThrownWhenKeyAddedForNonAttribute()
    {
        $table = new CreateTable('test');
        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches('/ key is not a valid table attribute$/');
        $table->addKeyToSchema(new Key('test'));
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function testGetTableDefinitionWithDefaultAttributes()
    {
        $table = $this->getCreateTableObject();
        $definition = $table->getTableDefinition();
        $this->assertArrayHasKey('TableName', $definition);
        $this->assertArrayHasKey('AttributeDefinitions', $definition);
        $this->assertNotEmpty($definition['AttributeDefinitions']);
        $this->assertArrayHasKey('KeySchema', $definition);
        $this->assertNotEmpty($definition['KeySchema']);
        $this->assertArrayHasKey('BillingMode', $definition);
        $this->assertArrayHasKey('TableClass', $definition);
    }

    /**
     * @return CreateTable
     * @throws \Exception
     */
    protected function getCreateTableObject(): CreateTable
    {
        $attribute = 'attribute_1';
        $table = new CreateTable('test');
        $table->addAttributeDefinition(new AttributeDefinition($attribute));
        $table->addKeyToSchema(new Key($attribute));
        return $table;
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function testGetTableDefinitionWithGlobalSecondaryIndex()
    {
        $table = $this->getCreateTableObject();
        $table->addGlobalSecondaryIndex(new GlobalSecondaryIndex('test', [new Key('T')]));
        $definition = $table->getTableDefinition();
        $this->assertArrayHasKey('GlobalSecondaryIndexes', $definition);
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function testGetTableDefinitionWithLocalSecondaryIndex()
    {
        $table = $this->getCreateTableObject();
        $table->addLocalSecondaryIndex(new SecondaryIndex('test', [new Key('T')]));
        $definition = $table->getTableDefinition();
        $this->assertArrayHasKey('LocalSecondaryIndexes', $definition);
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function testGetTableDefinitionWithProvisionedThroughput()
    {
        $table = $this->getCreateTableObject();
        $table->setProvisionedThroughput(new ProvisionedThroughput(1, 1));
        $definition = $table->getTableDefinition();
        $this->assertArrayHasKey('ProvisionedThroughput', $definition);
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function testGetTableDefinitionWithSSESpecification()
    {
        $table = $this->getCreateTableObject();
        $table->setSseSpecification(new SSE());
        $definition = $table->getTableDefinition();
        $this->assertArrayHasKey('SSESpecification', $definition);
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function testGetTableDefinitionWithStreamSpecification()
    {
        $table = $this->getCreateTableObject();
        $table->setStreamSpecification(new Stream(Stream::TYPE_NEW_IMAGE));
        $definition = $table->getTableDefinition();
        $this->assertArrayHasKey('StreamSpecification', $definition);
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function testGetTableDefinitionWithTags()
    {
        $table = $this->getCreateTableObject();
        $table->addTag(new Tag('name', 'value'));
        $definition = $table->getTableDefinition();
        $this->assertArrayHasKey('Tags', $definition);
    }
}