<?php

declare(strict_types=1);

namespace FlorentPoujol\LaravelModelMetadata\Defaults;

use FlorentPoujol\LaravelModelMetadata\AttributeMetadata;
use Illuminate\Database\Schema\Builder;

class Varchar extends AttributeMetadata
{
    /**
     * @param string $type 'string' or 'char'
     */
    public function __construct(string $type = 'string', int $maxLength = null)
    {
        parent::__construct();

        $maxLength = $maxLength ?: Builder::$defaultStringLength;

        $this
            ->setColumnType($type, $maxLength)
            ->setValidationRule('max', $maxLength)
            ->setNovaFieldType('text')
            ->setNovaFieldDefinition('sortable');
    }

    public function primary(): self
    {
        $this->markPrimaryKey(true, 'string', false); // string, not incrementing

        return $this;
    }
}
