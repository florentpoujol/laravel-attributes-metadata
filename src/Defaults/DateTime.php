<?php

declare(strict_types=1);

namespace FlorentPoujol\LaravelModelMetadata;

class DateTime extends AttributeMetadata
{
    /**
     * @param string $type 'timestamp', 'datetime' or 'date'
     */
    public function __construct(string $type = 'timestamp', string $castFormat = null)
    {
        parent::__construct();

        $this
            ->setColumnType($type)
            ->addColumnDefinition('useCurrent')
            ->markDate(true)
            ->setCast('datetime', $castFormat)
            ->setNovaFieldType($type);
    }

    /**
     * @return $this
     */
    public function setPrecision(int $precision): self
    {
        return $this->setColumnType($this->getColumnType(), $precision);
    }

    /**
     * @param string $format Moment.js format (not PHP format)
     *
     * @return $this
     */
    public function setNovaDisplayFormat(string $format): self
    {
        $this->setNovaFieldDefinition('format', $format);

        return $this;
    }
}
