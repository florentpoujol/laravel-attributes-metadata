<?php

declare(strict_types=1);

namespace FlorentPoujol\LaravelAttributePresets\Defaults;

class Json extends Text
{
    public function __construct(bool $asArray = true)
    {
        parent::__construct();

        $this->getColumnDefinitions()->setType('json');
        $this->setValidationRule($asArray ? 'array' : 'object');

        $this
            ->setNovaFieldType('code')
            ->setNovaFieldDefinition('json');
    }
}
