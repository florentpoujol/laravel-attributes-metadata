<?php

declare(strict_types=1);

namespace FlorentPoujol\LaravelModelMetadata;

class Json extends Text
{
    public function __construct(bool $asArray = true)
    {
        parent::__construct();

        $this
            ->setColumnType('json')
            ->setValidationRule($asArray ? 'array' : 'object')
            ->setNovaFieldType('code')
            ->setNovaFieldDefinition('json');
    }
}