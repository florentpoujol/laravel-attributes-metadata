<?php

declare(strict_types=1);

namespace FlorentPoujol\LaravelAttributePresets\Defaults;

use FlorentPoujol\LaravelAttributePresets\BasePreset;

class DateTime extends BasePreset
{
    protected static $baseDefinitions = [
        'dbColumn' => ['timestamp', 'useCurrent'],
        'validation' => ['datetime'],
        'novaField' => ['DateTime', 'sortable'],
        'cast' => 'datetime',
        'date',
    ];

    /**
     * @param string $type 'timestamp', 'datetime' or 'date'
     */
    public function __construct(string $type = 'timestamp', int $precision = null)
    {
        parent::__construct();

        $defs = ['type' => $type];
        if ($precision !== null) {
            $defs['precision'] = $precision;
        }
        $this->dbColumn($defs);

        if ($type === 'date') {
            $this->novaField(['type' => 'date']);
        }
    }
}
