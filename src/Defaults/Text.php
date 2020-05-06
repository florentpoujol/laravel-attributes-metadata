<?php

declare(strict_types=1);

namespace FlorentPoujol\LaravelAttributePresets\Defaults;

use FlorentPoujol\LaravelAttributePresets\BasePreset;
use FlorentPoujol\LaravelAttributePresets\NovaFieldDefinition;

class Text extends BasePreset
{
    /**
     * @param string $size 'text', 'medium' or 'long'
     */
    public function __construct(string $size = 'text')
    {
        switch (strtolower($size)) {
            case 'long':
            case 'longtext':
                $size = 'longText';
                break;
            case 'medium':
            case 'mediumtext':
                $size = 'mediumText';
                break;
            default:
                $size = 'text';
                break;
        }

        $this->getColumnDefinitions()->setType($size);

        $this->setNovaField(NovaFieldDefinition::textarea());
    }
}
