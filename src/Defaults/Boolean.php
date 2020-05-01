<?php

declare(strict_types=1);

namespace FlorentPoujol\LaravelAttributePresets\Defaults;

use FlorentPoujol\LaravelAttributePresets\AttributeMetadata;

class Boolean extends AttributeMetadata
{
    public function __construct(bool $defaultValue = null)
    {
        $this
            ->setNovaFieldType('boolean');

        $this->getColumnDefinitions()->setType('boolean');
        $this->getValidationHandler()->setRule('boolean');

        if ($defaultValue !== null) {
            $this->setDefaultValue($defaultValue);
        }
    }
}
