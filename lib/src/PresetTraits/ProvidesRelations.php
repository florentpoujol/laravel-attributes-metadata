<?php

declare(strict_types=1);

namespace FlorentPoujol\LaravelAttributePresets\PresetTraits;

use Illuminate\Database\Eloquent\Relations\Relation as BaseLaravelRelation;

/**
 * @method $this relation(string $method, ...$params)
 * @method bool isRelation()
 *
 * @method $this related(string $modelFqcn)
 */
trait ProvidesRelations
{
    // unlike other definitions (db column or validation) which have their own instance
    // relations definitions are stored in the main instance under the 'relation' key

    public function relationName(string $modelFqcn)
    {

    }

    public function related(string $modelFqcn)
    {

    }


    public function getRelationMethod(): ?string
    {
        return $this->get('relation')[0] ?? null;
    }

    public function getRelationInstance(): BaseLaravelRelation
    {
        $relation = $this->get('relation');
        $method = array_shift($relation);

        return $method(...$relation);
    }



    // have a field in the model's table
    // belongsTo($related, $foreignKey = null, $ownerKey = null, $relation = null)
    // hasManyThrough($related, $through, $firstKey = null, $secondKey = null, $localKey = null, $secondLocalKey = null)
    // morphTo($name = null, $type = null, $id = null, $ownerKey = null)

    // doesn't have field in the model's table
    // hasMany($related, $foreignKey = null, $localKey = null)
    // hasOne($related, $foreignKey = null, $localKey = null)
    // morphOne($related, $name, $type = null, $id = null, $localKey = null)
    // morphMany($related, $name, $type = null, $id = null, $localKey = null)
}
