<?php

declare(strict_types=1);

namespace FlorentPoujol\LaravelAttributePresets\Defaults;

use FlorentPoujol\LaravelAttributePresets\BasePreset;

class Boolean extends BasePreset
{
    protected static $baseDefinitions = [
        'dbColumn' => ['boolean', 'default' => false],
        'validation' => ['boolean'],
        'novaField' => ['Boolean', 'sortable', 'nullable'],
        'cast' => 'boolean',
    ];

    public function __construct(bool $defaultValue = null)
    {
        if ($defaultValue !== null) {
            static::$baseDefinitions['dbColumn']['default'] = $defaultValue;
        }

        parent::__construct();
    }
}
