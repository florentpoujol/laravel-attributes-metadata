<?php

declare(strict_types=1);

namespace FlorentPoujol\LaravelModelMetadata;

class Boolean_ extends AttributeMetadata
{
    public function __construct(bool $defaultValue = null)
    {
        parent::__construct();

        $this
            ->setColumnType('boolean')
            ->setValidationRule('boolean')
            ->setNovaFieldType('boolean');

        if ($defaultValue !== null) {
            $this->setDefaultValue($defaultValue);
        }
    }
}