<?php

declare(strict_types=1);

namespace FlorentPoujol\LaravelModelMetadata\Defaults;

use FlorentPoujol\LaravelModelMetadata\AttributeMetadata;

class Text extends AttributeMetadata
{
    /**
     * @param string $size 'text', 'medium' or 'long'
     */
    public function __construct(string $size = 'text')
    {
        $this->getColumnDefinitions()
            ->setType($size === 'text' ? 'text' : $size . 'Text');
    }
}
