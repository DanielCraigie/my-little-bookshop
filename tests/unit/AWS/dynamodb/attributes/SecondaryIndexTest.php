<?php

namespace unit\AWS\dynamodb\attributes;

use Danielcraigie\Bookshop\AWS\dynamodb\attributes\Key;
use Danielcraigie\Bookshop\AWS\dynamodb\attributes\Projection;
use Danielcraigie\Bookshop\AWS\dynamodb\attributes\SecondaryIndex;
use Exception;

class SecondaryIndexTest extends \Codeception\Test\Unit
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
     * @throws Exception
     */
    public function testExceptionThrownWithEmptyIndexName()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessageMatches('/ requires an Index Name$/');
        new SecondaryIndex('', []);
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testExceptionThrownWithEmptyKeyArray()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessageMatches('/ requires a key schema$/');
        new SecondaryIndex('test', []);
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testExceptionThrownWithNonKeyValues()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessageMatches('/ requires an array of Key objects$/');
        new SecondaryIndex('test', ['key']);
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testGetAttributeDefinitionWithoutProjection()
    {
        $name = 'testIndex';

        $index = new SecondaryIndex($name, [new Key('attr')]);

        $definition = $index->getAttributeDefinition();

        $this->assertArrayHasKey('IndexName', $definition);
        $this->assertEquals($definition['IndexName'], $name);
        $this->assertArrayHasKey('KeySchema', $definition);
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testGetAttributeDefinitionWithProjection()
    {
        $name = 'testIndex';

        $index = new SecondaryIndex($name, [new Key('attr')], new Projection());

        $definition = $index->getAttributeDefinition();

        $this->assertArrayHasKey('IndexName', $definition);
        $this->assertEquals($definition['IndexName'], $name);
        $this->assertArrayHasKey('KeySchema', $definition);
        $this->assertArrayHasKey('Projection', $definition);
    }
}
