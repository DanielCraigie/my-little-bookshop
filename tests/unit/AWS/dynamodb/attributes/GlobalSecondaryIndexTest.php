<?php

namespace unit\AWS\dynamodb\attributes;

use Danielcraigie\Bookshop\AWS\dynamodb\attributes\GlobalSecondaryIndex;
use Danielcraigie\Bookshop\AWS\dynamodb\attributes\Key;
use Danielcraigie\Bookshop\AWS\dynamodb\attributes\Projection;
use Danielcraigie\Bookshop\AWS\dynamodb\attributes\ProvisionedThroughput;

class GlobalSecondaryIndexTest extends \Codeception\Test\Unit
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
    public function testGetAttributeDefinitionWithoutThroughput()
    {
        $index = new GlobalSecondaryIndex('name', [new Key('key')], new Projection());
        $this->assertArrayNotHasKey('ProvisionedThroughput', $index->getAttributeDefinition());
    }

    /**
     * @return void
     */
    public function testGetAttributeDefinitionWithThroughput()
    {
        $index = new GlobalSecondaryIndex(
            'name',
            [new Key('key')],
            new Projection(),
            new ProvisionedThroughput(1, 1)
        );
        $this->assertArrayHasKey('ProvisionedThroughput', $index->getAttributeDefinition());
    }
}