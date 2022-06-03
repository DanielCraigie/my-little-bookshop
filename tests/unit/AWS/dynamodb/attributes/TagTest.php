<?php
namespace unit\AWS\dynamodb\attributes;

use Danielcraigie\Bookshop\AWS\dynamodb\attributes\Tag;

class TagTest extends \Codeception\Test\Unit
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
    public function testExceptionThrownWhenKeyIsEmptyString()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches('/ requires a Key name$/');
        new Tag('', '');
    }

    /**
     * @return void
     */
    public function testExceptionThrownWhenValueIsEmptyString()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches('/ requires a Value$/');
        new Tag('test', '');
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function testGetAttributeDefinition()
    {
        $key = 'test';
        $value = 'value';

        $tag = new Tag($key, $value);
        $this->assertEquals([
            'Key' => $key,
            'Value' => $value,
        ], $tag->getAttributeDefinition());
    }
}
