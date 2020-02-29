<?php

declare(strict_types=1);

namespace FlorentPoujol\LaravelModelMetadata;

class Text extends AttributeMetadata
{
    public function __construct(string $size = 'text')
    {
        $this->setColumnType($size === 'text' ? 'text' : $size . 'Text');

        parent::__construct();
    }
}