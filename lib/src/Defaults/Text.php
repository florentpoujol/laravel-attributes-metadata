<?php

declare(strict_types=1);

namespace FlorentPoujol\LaravelAttributePresets\Defaults;

use FlorentPoujol\LaravelAttributePresets\BasePreset;

class Text extends BasePreset
{
    protected static $baseDefinitions = [
        'dbColumn' => ['type' => 'text'],
        'novaField' => ['Textarea'],
    ];

    /**
     * @param string $type 'text', 'medium' or 'long'
     */
    public function __construct(string $type = 'text')
    {
        $defs = static::getBaseDefinitions();

        // reference for max storage
        // https://dev.mysql.com/doc/refman/5.7/en/storage-requirements.html#data-types-storage-reqs-strings
        switch (strtolower($type)) {
            case 'tiny':
            case 'tinytext':
                $type = 'tinyText';
                $max = 2**8;
                break;
            case 'text':
            default:
                $type = 'text';
                $max = 2**16;
                break;
            case 'medium':
            case 'mediumtext':
                $type = 'mediumText';
                $max = 2**24;
                break;
            case 'long':
            case 'longtext':
                $type = 'longText';
                $max = 2**32;
                break;
        }

        $defs['dbColumn']['type'] = $type;
        $defs['validation'] = ['string', 'max' => $max];

        $this->fill($defs);
    }
}
