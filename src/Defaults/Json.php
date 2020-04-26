<?php

declare(strict_types=1);

namespace FlorentPoujol\LaravelModelMetadata\Defaults;

class Json extends Text
{
    public function __construct(bool $asArray = true)
    {
        parent::__construct();

        $this->getColumnDefinitions()->setType('json');
        $this->getValidationHandler()->setRule($asArray ? 'array' : 'object');

        $this
            ->setNovaFieldType('code')
            ->setNovaFieldDefinition('json');
    }
}
