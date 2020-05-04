<?php

declare(strict_types=1);

namespace FlorentPoujol\LaravelAttributePresets\Defaults;

class Json extends Text
{
    public function __construct(bool $castAsArray = true)
    {
        parent::__construct();

        $this->getColumnDefinitions()->setType('json');
        $this->setCast($castAsArray ? 'array' : 'object');

        $this
            ->setNovaFieldType('code')
            ->setNovaFieldDefinition('json');
    }
}
