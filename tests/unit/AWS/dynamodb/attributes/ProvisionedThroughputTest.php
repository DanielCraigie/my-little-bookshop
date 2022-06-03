<?php

namespace unit\AWS\dynamodb\attributes;

use Danielcraigie\Bookshop\AWS\dynamodb\attributes\ProvisionedThroughput;
use Exception;

class ProvisionedThroughputTest extends \Codeception\Test\Unit
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
    public function testReadCapacityUnitsCantBeZero()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Read capacity units must be a positive integer');
        new ProvisionedThroughput(0, 0);
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testReadCapacityUnitsCantBeANegativeNumber()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Read capacity units must be a positive integer');
        new ProvisionedThroughput(-1, 0);
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testWriteCapacityUnitsCantBeZero()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Write capacity units must be a positive integer');
        new ProvisionedThroughput(1, 0);
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testWriteCapacityUnitsCantBeANegativeNumber()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Write capacity units must be a positive integer');
        new ProvisionedThroughput(1, -1);
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testGetAttributeDefinition()
    {
        $readUnits = 1;
        $writeUnits = 2;

        $throughput = new ProvisionedThroughput($readUnits, $writeUnits);
        $this->assertEquals([
            'ReadCapacityUnits' => $readUnits,
            'WriteCapacityUnits' => $writeUnits,
        ], $throughput->getAttributeDefinition());
    }
}