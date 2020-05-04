<?php

namespace FlorentPoujol\LaravelAttributePresets;

use Illuminate\Support\Fluent;

/**
 * @mixin \Laravel\Nova\Fields\Field
 */
class NovaFieldDefinition extends Fluent
{
    // An important bit to remember is that Nova may not be installed on the user's
    // project, so we must *not* use any of Nova classes here, at least outside PHPDocs.
    // That's why we use here a class extending the Fluent one, pretty uch like for
    // the DB column definitions

    public function __call($method, $parameters)
    {
        $this->attributes[$method] = empty($parameters) ? null : $parameters; // allow to store multiple parameters

        return $this;
    }
}
