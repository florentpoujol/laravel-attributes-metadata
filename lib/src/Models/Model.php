<?php

namespace FlorentPoujol\LaravelAttributePresets\Models;

use FlorentPoujol\LaravelAttributePresets\Definitions\DbColumn;
use FlorentPoujol\LaravelAttributePresets\Definitions\NovaField;
use FlorentPoujol\LaravelAttributePresets\Examples\Integer;
use FlorentPoujol\LaravelAttributePresets\Examples\PrimaryId;
use FlorentPoujol\LaravelAttributePresets\ModelTraits\HandlesRelationsFromAttributePresets;
use FlorentPoujol\LaravelAttributePresets\ModelTraits\HasAttributePresets;
use FlorentPoujol\LaravelAttributePresets\ModelTraits\HasMutatorsInAttributePresets;
use FlorentPoujol\LaravelAttributePresets\ModelTraits\SetupModelFromAttributePresets;

class Model extends \Illuminate\Database\Eloquent\Model
{
    use HasAttributePresets;
    use HandlesRelationsFromAttributePresets;
    use SetupModelFromAttributePresets;
    use HasMutatorsInAttributePresets;

    public static function getRawAttributePresets(): array
    {
        return [
            'id' => new PrimaryId(),
            (new Integer('column_name'))
                ->tapValidationrules(function (ValidationRulesDefinitions $defs) {
                    $defs->nullable();
                })
                ->tapColumnDefinition(function (DbColumn $def) {
                    $def->nullable();
                })
                ->tapNovaField(function (NovaField $def) {
                    $def->nullable();
                })
                ->nullable()
        ];
    }
}
