<?php

declare(strict_types=1);

namespace FlorentPoujol\LaravelModelMetadata;

class Text extends AttributeMetadata
{
    /**
     * @param string $size 'text', 'medium' or 'long'
     */
    public function __construct(string $size = 'text')
    {
        parent::__construct();

        $this
            ->setColumnType($size === 'text' ? 'text' : $size . 'Text')
            ->setNovaFieldType('textarea');
    }
}