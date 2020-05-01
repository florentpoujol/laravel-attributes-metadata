<?php

declare(strict_types=1);

namespace FlorentPoujol\LaravelAttributePresets\Defaults;

use FlorentPoujol\LaravelAttributePresets\AttributeMetadata;
use Illuminate\Database\Schema\Builder;

class Varchar extends AttributeMetadata
{
    /**
     * @param string $type 'string' or 'char'
     */
    public function __construct(string $type = 'string', int $maxLength = null)
    {
        $maxLength = $maxLength ?: Builder::$defaultStringLength;

        $this->getColumnDefinitions()->setType($type, $maxLength);
        $this->getValidationHandler()->setRule('max', $maxLength);

        $this
            ->setNovaFieldType('text')
            ->setNovaFieldDefinition('sortable');
    }

    public function primary(): self
    {
        $this->getColumnDefinitions()->primary();

        $this->markPrimaryKey(true, 'string', false); // string, not incrementing

        return $this;
    }
}
