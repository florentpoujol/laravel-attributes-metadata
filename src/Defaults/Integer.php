<?php

declare(strict_types=1);

namespace FlorentPoujol\LaravelAttributePresets\Defaults;

use FlorentPoujol\LaravelAttributePresets\BasePreset;

class Integer extends BasePreset
{
    protected static $baseDefinitions = [
        'dbColumn' => ['integer'],
        'validation' => ['integer'],
        'novaField' => ['Number', 'step' => 1],
        'cast' => 'integer',
    ];

    /**
     * @param string $type The size in byte the field should take (1, 2, 3, 4 or 8)
     * @param bool $isUnsigned
     */
    public function __construct(string $type = 'integer', bool $isUnsigned = true)
    {
        parent::__construct();

        // reference for boundaries: https://dev.mysql.com/doc/refman/8.0/en/integer-types.html
        switch (strtolower($type)) {
            case 'tiny':
            case 'tinyinteger':
                $type = 'tinyInteger';
                $min = $isUnsigned ? 0 : -128;
                $max = $isUnsigned ? 255 : 127;
                break;
            case 'small':
            case 'smallinteger':
                $type = 'smallInteger';
                $min = $isUnsigned ? 0 : -32768;
                $max = $isUnsigned ? 65535 : 32767;
                break;
            case 'medium':
            case 'mediuminteger':
                $type = 'mediumInteger';
                $min = $isUnsigned ? 0 : -8388608;
                $max = $isUnsigned ? 16777215 : 8388607;
                break;
            case 'int':
            case 'integer':
            default:
                $type = 'integer';
                $min = $isUnsigned ? 0 : -2147483648;
                $max = $isUnsigned ? 4294967295 : 2147483647;
                break;
            case 'big':
            case 'biginteger':
                $type = 'bigInteger';
                $min = $isUnsigned ? 0 : null;
                // $max = a LOT...
                break;
        }

        $dbColumn = ['type' => $type];
        if ($isUnsigned) {
            $dbColumn[] = 'unsigned';
        }
        $this->dbColumn($dbColumn);

        if (isset($min)) {
            $this->validation(['min' => $min]);
            $this->novaField(['min' => $min]);
        }

        if (isset($max)) {
            $this->validation(['max' => $max]);
            $this->novaField(['max' => $max]);
        }
    }
}
