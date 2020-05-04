<?php

declare(strict_types=1);

namespace FlorentPoujol\LaravelAttributePresets\Defaults;

use FlorentPoujol\LaravelAttributePresets\BasePreset;

class Boolean extends BasePreset
{
    public function __construct(bool $defaultValue = null)
    {
        $this->getColumnDefinitions()->setType('boolean');
        $this->getNovaDefinitions()->setFieldType('boolean')->nullable();
        $this->setValidationRule('boolean');
        $this->setCast('boolean');

        if ($defaultValue !== null) {
            $this->setDefaultValue($defaultValue, true);
        }
    }
}
