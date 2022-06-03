<?php

namespace unit\AWS\dynamodb\attributes;

use Danielcraigie\Bookshop\AWS\dynamodb\attributes\Key;
use Exception;

class KeyTest extends \Codeception\Test\Unit
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
    public function testExceptionThrownOnEmptyAttributeName()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessageMatches('/ requires an attribute name$/');
        new Key('');
    }

    /**
     * @return void
     */
    public function testExceptionThrownOnInvalidKeyType()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessageMatches('/^Incorrect key type passed to /');
        new Key('test', 'xyz');
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testGetAttributeName()
    {
        $attributeName = 'testAttribute';
        $key = new Key($attributeName);
        $this->assertEquals($attributeName, $key->getAttributeName());
    }

    /**
     * @param $name
     * @param $type
     * @return void
     * @throws Exception
     * @dataProvider testData
     */
    public function testGetAttributeDefinition($name, $type)
    {
        $key = new Key($name, $type);
        $this->assertEquals([
            'AttributeName' => $name,
            'KeyType' => $type,
        ], $key->getAttributeDefinition());
    }

    /**
     * @return array[]
     */
    protected function testData()
    {
        return [
            ['test_1', Key::TYPE_HASH],
            ['test_2', Key::TYPE_RANGE],
        ];
    }
}