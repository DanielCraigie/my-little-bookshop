<?php

namespace Danielcraigie\Bookshop\AWS\dynamodb\attributes;

interface Attribute
{
    /**
     * Returns attribute definition
     * @return string|array
     */
    public function getAttributeDefinition(): string|array;
}
