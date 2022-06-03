<?php

namespace unit\AWS\dynamodb\attributes;

use Danielcraigie\Bookshop\AWS\dynamodb\attributes\SSE;
use Exception;

class SSETest extends \Codeception\Test\Unit
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
    public function testExceptionThrownForInvalidEncryptionType()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessageMatches('/^Invalid SSE type passed to /');
        new SSE('xyz');
    }

    /**
     * @return void
     */
    public function testExceptionThrownWhenKmsEncryptionSelectedWithoutMasterKey()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessageMatches('/ requires a KMS Master Key ID$/');
        new SSE(SSE::TYPE_KMS);
    }

    /**
     * @return void
     */
    public function testGetAttributeDefinitionWithAes()
    {
        $sse = new SSE();
        $this->assertEquals([
            'Enabled' => true,
            'SSEType' => SSE::TYPE_AES,
        ], $sse->getAttributeDefinition());
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testGetAttributeDefinitionWithKms()
    {
        $masterKey = 'masterKey';
        $sse = new SSE(SSE::TYPE_KMS, $masterKey);
        $this->assertEquals([
            'Enabled' => true,
            'SSEType' => SSE::TYPE_KMS,
            'KMSMasterKeyId' => $masterKey,
        ], $sse->getAttributeDefinition());
    }

    /**
     * @return void
     */
    public function testGetAttributeDefinitionDoesntContainMasterKeyWithAes()
    {
        $sse = new SSE(SSE::TYPE_AES, 'masterKey');
        $this->assertArrayNotHasKey('KMSMasterKeyId', $sse->getAttributeDefinition());
    }

    /**
     * @return void
     */
    public function testEncryptionEnabledInDefinitionByDefault()
    {
        $sse = new SSE();
        $definition = $sse->getAttributeDefinition();
        $this->assertArrayHasKey('Enabled', $definition);
        $this->assertTrue($definition['Enabled']);
    }

    /**
     * @return void
     */
    public function testEncryptionCanBeDisabledInDefinition()
    {
        $sse = new SSE(SSE::TYPE_AES, '', false);
        $definition = $sse->getAttributeDefinition();
        $this->assertArrayHasKey('Enabled', $definition);
        $this->assertFalse($definition['Enabled']);
    }
}