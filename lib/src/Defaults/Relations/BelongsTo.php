<?php

declare(strict_types=1);

namespace FlorentPoujol\LaravelAttributePresets\Defaults\Relations;

class BelongsTo extends Relation
{
    protected static $baseDefinitions = [
        'dbColumn' => ['integer', 'unisgned'],
        'validation' => ['integer'],
        'novaField' => ['BelongsTo', 'searchable', 'sortable'],
        'relation' => ['belongsTo'],
    ];

    /**
     * @param string|array $relationParams The FQCN of the related model, or an array with all the arguments that you would pass to the model's belongsTo() method
     */
    public function __construct($relationParams, bool $withIndex = false)
    {
        parent::__construct('belongsTo', $relationParams, $withIndex);
    }
}
