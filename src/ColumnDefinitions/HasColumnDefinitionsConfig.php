<?php

declare(strict_types=1);

namespace FlorentPoujol\LaravelModelMetadata\ColumnDefinitions;

use FlorentPoujol\LaravelModelMetadata\AttributeMetadata;
use Illuminate\Database\Schema\Blueprint;

/**
 * @mixin \FlorentPoujol\LaravelModelMetadata\HasAttributesMetadata
 */
trait HasColumnDefinitionsConfig
{
    /**
     * @param string|array<string> $attributes One or several attribute names to restrict the results to
     *
     * @return array<string, array<string|object>> Validation rules (as array) per attribute name
     */
    public static function addColumnsToTable(Blueprint $table, array $attributes = []): void
    {
        static::getAttributeConfigCollection()
            ->filterByNames($attributes)
            ->each(function (AttributeMetadata $attr) use ($table) {
                    $attr
                        ->getColumnDefinitions()
                        ->addToTable($table);
            });
    }
}
