<?php
declare(strict_types=1);

namespace Compass;

abstract class AbstractPath implements \Stringable
{
    /** @var string */
    protected string $source;

    /** @var array */
    protected array $items;

    /**
     * Path constructor.
     * @param string $source
     */
    public function __construct(string $source)
    {
        $this->setSource($source);
    }

    /**
     * @param string $source
     */
    abstract protected function parse(string $source);

    /**
     * @return string
     */
    abstract public function getSeparator(): string;

    /**
     *
     */
    abstract public function updateSource(): void;

    /**
     * @return $this
     */
    abstract public function reset(): static;

    /**
     * @return string
     */
    public function getSource(): string
    {
        return $this->source;
    }

    /**
     * @param string $source
     */
    abstract public function setSource(string $source): void;

    /**
     * @return array
     */
    public function getAll(): array
    {
        return $this->items;
    }

    /**
     * @param array $items
     */
    abstract public function setAll(array $items): void;

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->source;
    }
}