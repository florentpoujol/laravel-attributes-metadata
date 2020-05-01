<?php

declare(strict_types=1);

namespace FlorentPoujol\LaravelAttributePresets\Defaults\Relations;

use FlorentPoujol\LaravelAttributePresets\Defaults\Integer;

class Relation extends Integer
{
    /**
     * @param string|array $params The Fqcn of the related model, or a array with all the arguments that you would pass to the model's belongsTo() method
     */
    public function __construct(string $type, $params, bool $withIndex = false)
    {
        parent::__construct();

        if (! is_array($params)) {
            $params = [$params];
        }

        $this->setRelation($type, $params);

        $this->getColumnDefinitions()->unsigned();

        if ($withIndex) {
            $this->getColumnDefinitions()->index();
        }
    }
}
