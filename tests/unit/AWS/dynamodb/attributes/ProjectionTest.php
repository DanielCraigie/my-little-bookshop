<?php

namespace unit\AWS\dynamodb\attributes;

use Danielcraigie\Bookshop\AWS\dynamodb\attributes\Projection;
use Exception;

class ProjectionTest extends \Codeception\Test\Unit
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
    public function testExceptionThrownWithBadType()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessageMatches('/^Incorrect projection type passed to /');
        new Projection('xyz');
    }

    /**
     * @param $type
     * @return void
     * @throws Exception
     * @dataProvider projectionTypes
     */
    public function testGetAttributeDefinitionWithoutNonKeyAttributes($type)
    {
        $projection = new Projection($type);
        $this->assertEquals([
            'ProjectionType' => $type,
        ], $projection->getAttributeDefinition());
    }

    /**
     * @return array[]
     */
    protected function projectionTypes()
    {
        return [
            [Projection::TYPE_ALL],
            [Projection::TYPE_INCLUDE],
            [Projection::TYPE_KEYS_ONLY],
        ];
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testGetAttributeDefinitionWithNonKeyAttributes()
    {
        $nonKeyAttributes = ['attribute_1', 'attribute_2'];
        $projection = new Projection(Projection::TYPE_ALL, $nonKeyAttributes);
        $attributeDefinition = $projection->getAttributeDefinition();
        $this->assertArrayHasKey('NonKeyAttributes', $attributeDefinition);
        $this->assertEquals($nonKeyAttributes, $attributeDefinition['NonKeyAttributes']);
    }
}