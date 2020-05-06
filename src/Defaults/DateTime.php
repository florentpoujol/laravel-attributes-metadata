<?php

declare(strict_types=1);

namespace FlorentPoujol\LaravelAttributePresets\Defaults;

use FlorentPoujol\LaravelAttributePresets\BasePreset;
use FlorentPoujol\LaravelAttributePresets\NovaFieldDefinition;

class DateTime extends BasePreset
{
    /**
     * @param string $type 'timestamp', 'datetime' or 'date'
     */
    public function __construct(string $type = 'timestamp', int $precision = null)
    {
        $this->markDate(true);

        $params = $precision ? [$precision] : [];
        $this->getColumnDefinitions()
            ->setType($type, ...$params)
            ->useCurrent();

        $novaField = NovaFieldDefinition::datetime();
        if ($type === 'date') {
            $novaField = NovaFieldDefinition::date();
        }

        $this->setNovaField($novaField);
    }

    /**
     * @param string $format Moment.js format (not PHP format)
     */
    public function setNovaDisplayFormat(string $format): self
    {
        $this->setNovaFieldDefinition('format', $format);

        return $this;
    }
}
