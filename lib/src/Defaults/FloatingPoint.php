<?php

declare(strict_types=1);

namespace FlorentPoujol\LaravelAttributePresets\Defaults;

use FlorentPoujol\LaravelAttributePresets\BasePreset;

class FloatingPoint extends BasePreset
{
    protected static $baseDefinitions = [
        'dbColumn' => ['float'],
        'validation' => ['float'],
        'novaField' => ['Number', 'step' => 0.1],
    ];

    /**
     * @param array<int> $precision An array of two integers, the first is the total number of digits and the second one the number of decimal places after the coma.
     */
    public function __construct(string $type = 'float', array $precision = [8, 2], bool $isUnsigned = false)
    {
        parent::__construct();

        $this->dbColumn([$type => $precision]);

        switch ($type) {
            case 'float':
            case 'double':
                $this->cast('float');
                $this->validation(['float']);
                break;

            case 'decimal':
                $this->cast('decimal', $precision[1]);
                break;
        }

        if ($isUnsigned) {
            $this->dbColumn(['unsigned']);
        }

        $boundaries = $this->getValueBoundariesFromPrecision($precision);
        $this->validation([
            'min' => $boundaries['min'],
            'max' => $boundaries['max'],
        ]);

        $this->novaField([
            'number', 'sortable',
            'min' => $boundaries['min'],
            'max' => $boundaries['max'],
            'step' => 1 / max(1, $precision[1] * 10), // 2 => 0.01
            // the use of max() is a protection against division by zero and gives a step of 1 when precision is 0
        ]);
    }

    protected function getValueBoundariesFromPrecision(array $precision): array
    {
        $max = array_fill(0, $precision[0], 9);
        if ($precision[1] > 0) {
            array_splice($max, - $precision[1], 0, '.');
        }
        $max = implode('', $max);

        return [
            'min' => $this->isUnsigned() ? 0 : - $max,
            'max' => $max, // float/double max value do not change when unsigned
        ];
    }
}
