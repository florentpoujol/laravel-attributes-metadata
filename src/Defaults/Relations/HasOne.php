<?php

declare(strict_types=1);

namespace FlorentPoujol\LaravelAttributeMetadata\Defaults\Relations;

class HasOne extends Relation
{
    /**
     * @param string|array $relationParams The FQCN of the related model, or a array with all the arguments that you would pass to the model's hasOne() method
     */
    public function __construct($relationParams, bool $withIndex = false)
    {
        parent::__construct('hasOne', $relationParams, $withIndex);

        // TODO set the Nova field as belongsto on index and details
    }
}
