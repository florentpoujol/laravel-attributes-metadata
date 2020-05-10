<?php

declare(strict_types=1);

namespace FlorentPoujol\LaravelAttributePresets\PresetTraits;

use Illuminate\Database\Eloquent\Relations\Relation as BaseLaravelRelation;

/**
 * @method $this relation(string $method, ...$params)
 * @method bool isRelation()
 */
trait ProvidesRelations
{
    // /**
    //  * @param null|string $method
    //  * @param array<string> $params
    //  */
    // public function setRelation(string $method = null, array $params = []): self
    // {
    //     $this->relationMethod = $method;
    //     $this->relationParams = $params;
    //
    //     $this
    //         ->setNovaFieldType($method)
    //         ->setNovaFieldDefinition('searchable')
    //         ->removeNovaFieldDefinition('sortable');
    //
    //     return $this;
    // }

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
}
