<?php

declare(strict_types=1);

namespace FlorentPoujol\LaravelModelMetadata;

use Illuminate\Support\Facades\Request;

/**
 * To be added on form request classes.
 *
 * Expect two properties to be set on the class
 * - `metadataModelFqcn` with the FQCN of the validated model
 * - `validatedAttributes` (optional) with the list of attributes to validates
 *
 * @property string $metadataModelFqcn
 * @property string[] $validatedAttributes
 */
trait HandlesResourceControllerFromMetadata
{
    /**
    POST 	/photos 	store 	photos.store
    PUT/PATCH 	/photos/{photo} 	update 	photos.update
     */
    /**
     * Handles the POST creation route, that hopefully stores a new model in the DB
     *
     * @param \Illuminate\Http\Request $request
     */
    public function store(Request $request): array
    {
        if (! property_exists($this, 'metadataModelFqcn')) {
            $fqcn = static::class;
            throw new \Exception("Missing property 'metadataModelFqcn' on form request '$fqcn'.");
        }

        /** @var \FlorentPoujol\LaravelModelMetadata\ModelMetadata $modelMeta */
        $modelMeta = $this->metadataModelFqcn::getMetadata();

        $validatedAttrs = [];
        if (property_exists($this, 'validatedAttributes')) {
            $validatedAttrs = $this->validatedAttributes;
        }

        // TODO only return the creation rules when the http method is POST
        $rules = $modelMeta->getCreateValidationRules($validatedAttrs);
        $this->request->validate($modelMeta->getCreateValidationRules($validatedAttrs));

        $success = $this->metadataModelFqcn::query()->create($this->request->all(array_keys($rules)));

        // if this is an api controller what to do if this is an api controller ?
        return view('??', []);
    }
}

