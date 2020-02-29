<?php

declare(strict_types=1);

namespace FlorentPoujol\LaravelModelMetadata;

class Relation extends Integer
{
    /**
     * @param string|array $params The FQCN of the related model, or a array with all the arguments that you would pass to the model's belongsTo() method
     */
    public function __construct(string $type, $params, bool $withIndex = false)
    {
        if (!is_array($params)) {
            $params = [$params];
        }
        $this->setRelation($type, $params);

        $this->markUnsigned(true);

        if ($withIndex) {
            $this->addColumnDefinition('index');
        }

        parent::__construct();
    }
}