<?php

declare(strict_types=1);

namespace FlorentPoujol\LaravelModelMetadata;

use Illuminate\Database\Schema\Builder;

class Varchar extends AttributeMetadata
{
    /**
     * @param string $type 'string' or 'char'
     */
    public function __construct(string $type = 'string', int $maxLength = null)
    {
        $maxLength = $maxLength ?: Builder::$defaultStringLength;

        $this
            ->setColumnType($type, $maxLength)
            ->setValidationRule('string')
            ->setValidationRule('max', $maxLength);

        parent::__construct();
    }
}