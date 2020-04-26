<?php

declare(strict_types=1);

namespace FlorentPoujol\LaravelModelMetadata\Defaults;

use FlorentPoujol\LaravelModelMetadata\AttributeMetadata;

class DateTime extends AttributeMetadata
{
    /** @var string */
    protected $type;

    /**
     * @param string $type 'timestamp', 'datetime' or 'date'
     */
    public function __construct(string $type = 'timestamp', string $castFormat = null)
    {
        // parent::__construct();

        $this
            ->markDate(true)
            ->setCast('datetime', $castFormat)
            ->setNovaFieldType($type);

        $this->type = $type;
        $this->getColumnDefinitions()
            ->setType($type)
            ->useCurrent();
    }

    public function setPrecision(int $precision): self
    {
        $this->getColumnDefinitions()->setType($this->type, $precision);

        return $this;
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
