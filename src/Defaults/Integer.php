<?php

declare(strict_types=1);

namespace FlorentPoujol\LaravelAttributePresets\Defaults;

use FlorentPoujol\LaravelAttributePresets\BasePreset;
use FlorentPoujol\LaravelAttributePresets\Validation\ValidationHandler;

class Integer extends BasePreset
{
    /**
     * @param int $size The size in byte the field should take (1, 2, 3, 4 or 8)
     * @param bool $isUnsigned
     */
    public function __construct(int $size = 4, bool $isUnsigned = true)
    {
        // reference for boundaries: https://dev.mysql.com/doc/refman/8.0/en/integer-types.html
        switch ($size) {
            case 1:
                $field = 'tinyInteger';
                $min = $isUnsigned ? 0 : -128;
                $max = $isUnsigned ? 255 : 127;
                break;
            case 2:
                $field = 'smallInteger';
                $min = $isUnsigned ? 0 : -32768;
                $max = $isUnsigned ? 65535 : 32767;
                break;
            case 3:
                $field = 'mediumInteger';
                $min = $isUnsigned ? 0 : -8388608;
                $max = $isUnsigned ? 16777215 : 8388607;
                break;
            case 4:
            default:
                $field = 'integer';
                $min = $isUnsigned ? 0 : -2147483648;
                $max = $isUnsigned ? 4294967295 : 2147483647;
                break;
            case 8:
                $field = 'bigInteger';
                $min = $isUnsigned ? 0 : null;
                // $max = a LOT...
                break;
        }

        $this
            ->setCast('int')
            ->setNovaFieldType('number')
            ->setNovaFieldDefinition('step', 1);

        $definitions = $this->getColumnDefinitions()->setType($field);
        if ($isUnsigned) {
            $definitions->unsigned();
        }

        $this->getValidationHandler()->setRule('int');

        if (isset($min)) {
            $this->setMinValue($min);
            $this->getValidationHandler()->setRule('min', $min);
        }

        if (isset($max)) {
            $this->setMaxValue($max);
            $this->getValidationHandler()->setRule('max', $max);
        }
    }

    public function primary(): self
    {
        $this->markPrimaryKey(); // int, incrementing, primary

        return $this;
    }
}
