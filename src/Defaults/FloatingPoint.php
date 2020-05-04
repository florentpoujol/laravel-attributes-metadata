<?php

declare(strict_types=1);

namespace FlorentPoujol\LaravelAttributePresets\Defaults;

use FlorentPoujol\LaravelAttributePresets\BasePreset;

class FloatingPoint extends BasePreset
{
    /**
     * @param array<int> $precision An array of two integers, the first is the total number of digits and the second one the number of decimal places after the coma.
     */
    public function __construct(string $type = 'float', array $precision = [8, 2], bool $isUnsigned = false)
    {
        $columnDef = $this->getColumnDefinitions()->setType($type, $precision);

        switch ($type) {
            case 'float':
            case 'double':
                $this->setCast('float');
                $this->setValidationRule('float');
                break;
            case 'decimal':
                $this->setCast('decimal', $precision[1]);
                break;
        }

        if ($isUnsigned) {
            $columnDef->unsigned();
        }

        $boundaries = $this->getValueBoundariesFromPrecision($precision);
        $this
            ->setValidationRule('min', $boundaries['min'])
            ->setValidationRule('max', $boundaries['max']);

        $this
            ->setNovaFieldType('number')
            ->setMinValue($boundaries['min'])
            ->setMaxValue($boundaries['max'])
            ->setStep(1 / max(1, $precision[1] * 10)); // 2 => 0.01
        // the use of max() is a protection against division by zero and gives a step of 1 when precision is 0
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
