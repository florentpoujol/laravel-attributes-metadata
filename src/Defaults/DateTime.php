<?php

declare(strict_types=1);

namespace FlorentPoujol\LaravelModelMetadata;

class DateTime extends AttributeMetadata
{
    /**
     * @param string $type 'timestamp', 'datetime', 'date'
     */
    public function __construct(string $type = 'timestamp', string $castFormat = null)
    {
        $this
            ->setColumnType($type)
            ->addColumnDefinition('useCurrent')
            ->setCast('datetime')
            ->markDate(true)
            ->setNovaFieldType('datetime');

        if ($castFormat !== null) {
            $this->setCast('datetime', $castFormat);
        }

        parent::__construct();
    }

    /**
     * @return $this
     */
    public function setPrecision(int $precision): self
    {
        return $this->setColumnType($this->getColumnType(), $precision);
    }
}
