<?php

namespace Danielcraigie\Bookshop\AWS\dynamodb\attributes;

use Exception;

class SSE implements Attribute
{
    const TYPE_AES = 'AES256';
    const TYPE_KMS = 'KMS';

    /**
     * @var bool $enabled
     */
    private bool $enabled;

    /**
     * @var string $kmsMasterKeyId
     */
    private string $kmsMasterKeyId;

    /**
     * @var string $sseType
     */
    private string $sseType;

    public function __construct(string $sseType = self::TYPE_AES, string $kmsMasterKeyId = '', bool $enabled = true)
    {
        if (!in_array($sseType, [self::TYPE_AES, self::TYPE_KMS])) {
            throw new Exception('Invalid SSE type passed to ' . __CLASS__);
        }

        if ($sseType == self::TYPE_KMS
            && empty($kmsMasterKeyId)
        ) {
            throw new Exception(__CLASS__ . ' requires a KMS Master Key ID');
        }

        $this->enabled = $enabled;
        $this->kmsMasterKeyId = $kmsMasterKeyId;
        $this->sseType = $sseType;
    }

    /**
     * @return string|array
     */
    public function getAttributeDefinition(): string|array
    {
        $return = [
            'Enabled' => $this->enabled,
            'SSEType' => $this->sseType
        ];

        if ($this->sseType == self::TYPE_KMS
            && !empty($this->kmsMasterKeyId)
        ) {
            $return['KMSMasterKeyId'] = $this->kmsMasterKeyId;
        }

        return $return;
    }
}
