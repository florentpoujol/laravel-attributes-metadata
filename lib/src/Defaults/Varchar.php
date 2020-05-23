<?php

declare(strict_types=1);

namespace FlorentPoujol\LaravelAttributePresets\Defaults;

use FlorentPoujol\LaravelAttributePresets\BasePreset;
use Illuminate\Database\Schema\Builder;

class Varchar extends BasePreset
{
    /**
     * @param string $type 'string' or 'char'
     */
    public function __construct(string $type = 'string', int $maxLength = null)
    {
        $maxLength = $maxLength ?: Builder::$defaultStringLength;

        $this->dbColumn([$type => $maxLength]);
        $this->validation(['max' => $maxLength]);
        $this->novaField(['Text']);
    }

    public function primary(): self
    {
        $this->primaryKey(true, 'string', false);

        return $this;
    }
}
