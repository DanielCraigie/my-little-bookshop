<?php

namespace unit\AWS\dynamodb\attributes;

use Danielcraigie\Bookshop\AWS\dynamodb\attributes\Stream;

class StreamTest extends \Codeception\Test\Unit
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
    public function testExceptionThrownForIncorrectType()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches('/ requires a valid Stream View Type$/');
        new Stream('xyz');
    }

    /**
     * @return void
     */
    public function testStreamEnabledInDefinitionByDefault()
    {
        $stream = new Stream(Stream::TYPE_KEYS_ONLY);
        $definition = $stream->getAttributeDefinition();
        $this->assertArrayHasKey('StreamEnabled', $definition);
        $this->assertEquals($definition['StreamEnabled'], true);
    }

    /**
     * @param $type
     * @param $enabled
     * @return void
     * @throws \Exception
     * @dataProvider definitionData
     */
    public function testGetAttributeDefinition($type, $enabled)
    {
        $stream = new Stream($type, $enabled);
        $this->assertEquals([
            'StreamEnabled' => $enabled,
            'StreamViewType' => $type,
        ], $stream->getAttributeDefinition());
    }

    /**
     * @return array[]
     */
    protected function definitionData()
    {
        return [
            [Stream::TYPE_KEYS_ONLY, true],
            [Stream::TYPE_NEW_AND_OLD_IMAGES, true],
            [Stream::TYPE_OLD_IMAGE, true],
            [Stream::TYPE_NEW_IMAGE, true],
            [Stream::TYPE_KEYS_ONLY, false],
            [Stream::TYPE_NEW_AND_OLD_IMAGES, false],
            [Stream::TYPE_OLD_IMAGE, false],
            [Stream::TYPE_NEW_IMAGE, false],
        ];
    }
}