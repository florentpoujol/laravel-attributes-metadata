<?php

declare(strict_types=1);

namespace FlorentPoujol\LaravelModelMetadata\Defaults;

use FlorentPoujol\LaravelModelMetadata\AttributeMetadata;

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
