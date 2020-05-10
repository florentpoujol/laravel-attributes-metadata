<?php

declare(strict_types=1);

namespace FlorentPoujol\LaravelAttributePresets\Definitions;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Builder;
use function array_unshift;
use function method_exists;

/**
 * The type of the DB column can be set either via the `type()` method
 * or via the method that you would normally call on a Blueprint object
 * but without the name argument
 *
 * Eg:
 * ```
 * $dBColumnDefs->string(50)
 * // same as
 * $dBColumnDefs->type('string')->length(50)
 *
 * $dBColumnDefs->unsignedInteger(true)
 * // same as
 * $dBColumnDefs->integer()->unsigned()->autoIncrement()
 * // same as
 * $dBColumnDefs->type('integer')->unsigned()->autoIncrement()
 * ```
 *
 * @mixin \Illuminate\Database\Schema\ColumnDefinition
 * @mixin \Illuminate\Database\Schema\Blueprint
 *
 * @method $this type(string $type) Sets the column type
 * @method $this name(string $name) Sets the column name
 */
class DbColumn extends Fluent
{
    public function __call($method, $parameters = [])
    {
        if (method_exists(Blueprint::class, $method)) {
            // allow to set the type via the same method that you actually call on the blueprint class
            // eg: $dbColumnDefs->type(string)->length(50)
            // is the same as $dbColumnDefs->string(50)
            $this->type($method);

            if (
                empty($parameters) &&
                ($method === 'string' || $method === 'char')
            ) {
                $parameters[] = Builder::$defaultStringLength;
            }

            // now use reflection on that method to get the name of the parameters
            $reflMethod = new \ReflectionMethod(Blueprint::class, $method);
            $reflParams = $reflMethod->getParameters();
            array_unshift($reflParams); // the $column param is always the first one

            foreach ($reflParams as $i => $reflParam) {
                $name = $reflParam->getName();
                $value = $parameters[$i] ?? $reflParam->getDefaultValue();

                $this->$name($value);
            }

            return $this;
        }

        return parent::__call($method, $parameters);
    }

    /**
     * @param \Illuminate\Database\Schema\Blueprint $table
     */
    public function addToTable(Blueprint $table): void
    {
        if (! $this->has('type')) {
            return;
        }

        $table->addColumn('', '', $this->toArray());
    }
}
