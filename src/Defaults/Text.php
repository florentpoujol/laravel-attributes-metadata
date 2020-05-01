<?php

declare(strict_types=1);

namespace FlorentPoujol\LaravelAttributePresets\Defaults;

use FlorentPoujol\LaravelAttributePresets\BasePreset;

class Text extends BasePreset
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
