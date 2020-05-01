<?php

declare(strict_types=1);

namespace FlorentPoujol\LaravelAttributePresets\ModelTraits;

use FlorentPoujol\LaravelAttributePresets\BasePreset;
use Illuminate\Database\Schema\Blueprint;

/**
 * @mixin \FlorentPoujol\LaravelAttributePresets\HasAttributePresets
 */
trait HasColumnDefinitionsFromAttributePresets
{
    /**
     * @param string|array<string> $attributes One or several attribute names to restrict the results to
     *
     * @return array<string, array<string|object>> Validation rules (as array) per attribute name
     */
    public static function addColumnsToTable(Blueprint $table, array $attributes = []): void
    {
        static::getAttributePresetCollection()
            ->filterByNames($attributes)
            ->each(function (BasePreset $preset) use ($table) {
                $preset
                    ->getColumnDefinitions()
                    ->addToTable($table);
            });
    }
}
