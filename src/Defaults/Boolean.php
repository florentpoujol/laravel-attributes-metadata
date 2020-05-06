<?php

declare(strict_types=1);

namespace FlorentPoujol\LaravelAttributePresets\Defaults;

use FlorentPoujol\LaravelAttributePresets\BasePreset;
use FlorentPoujol\LaravelAttributePresets\NovaFieldDefinition;

class Boolean extends BasePreset
{
    public function __construct(bool $defaultValue = null)
    {
        $this->getColumnDefinitions()->setType('boolean');
        $this->setNovaField(NovaFieldDefinition::boolean()->nullable());
        $this->setValidationRule('boolean');
        $this->setCast('boolean');

        if ($defaultValue !== null) {
            $this->setDefaultValue($defaultValue, true);
        }
    }
}
