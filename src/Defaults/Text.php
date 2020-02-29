<?php

declare(strict_types=1);

namespace FlorentPoujol\LaravelModelMetadata;

class Text extends AttributeMetadata
{
    public function __construct(string $size = 'text')
    {
        parent::__construct();

        $this
            ->setColumnType($size === 'text' ? 'text' : $size . 'Text')
            ->setNovaFieldType('textarea');
    }
}