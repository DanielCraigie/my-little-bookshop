<?php

namespace unit\AWS\dynamodb\attributes;

use Danielcraigie\Bookshop\AWS\dynamodb\attributes\AttributeDefinition;
use Exception;

class AttributeDefinitionTest extends \Codeception\Test\Unit
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
    public function testExceptionThrownWithEmptyAttributeName()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessageMatches('/requires an attribute name$/');
        new AttributeDefinition('');
    }

    /**
     * @return void
     */
    public function testExceptionThrownForInvaildType()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessageMatches('/^Incorrect Attribute Type passed to/');
        new AttributeDefinition('test', 'xyz');
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testGetAttributeName()
    {
        $attributeName = 'testAttribute';
        $attribute = new AttributeDefinition($attributeName);
        $this->assertEquals($attributeName, $attribute->getAttributeName());
    }

    /**
     * @param $attributeName
     * @param $attributeType
     * @throws Exception
     * @dataProvider typeProvider
     */
    public function testGetAttributeDefinition($attributeName, $attributeType)
    {
        $attribute = new AttributeDefinition($attributeName, $attributeType);
        $this->assertEquals([
            'AttributeName' => $attributeName,
            'AttributeType' => $attributeType,
        ], $attribute->getAttributeDefinition());
    }

    /**
     * @return array[]
     */
    protected function typeProvider(): array
    {
        return [
            ['testAttribute', AttributeDefinition::TYPE_BINARY],
            ['testAttribute', AttributeDefinition::TYPE_NUMBER],
            ['testAttribute', AttributeDefinition::TYPE_STRING],
        ];
    }
}