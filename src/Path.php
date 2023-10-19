<?php

namespace Compass;

use Compass\Exception\InvalidPathException;

class Path extends AbstractPath
{

    protected PathStrategy $strategy;
    protected ?string $suffix = null;
    protected string $separator;

    /**
     * @param string $source
     * @param string $separator
     * @param PathStrategy|null $strategy
     */
    public function __construct(string $source, string $separator = DIRECTORY_SEPARATOR, ?PathStrategy $strategy = null)
    {
        $this->assertNotEmpty($source);
        $this->initUrlPath($separator, $strategy);
        parent::__construct($source);
    }

    /**
     * @param $source
     * @return void
     */
    private function assertNotEmpty($source)
    {
        if (empty($source)) {
            throw new InvalidPathException('Source UrlPath cannot be empty.');
        }
    }

    /**
     * @param string $separator
     * @param PathStrategy|null $strategy
     * @return $this
     */
    protected function initUrlPath(string $separator, ?PathStrategy $strategy = null): static
    {
        $this->strategy = $strategy ?? new DefaultPathStrategy();
        $this->separator = $separator;
        return $this;
    }

    /**
     * @param string $separator
     * @param PathStrategy|null $strategy
     * @return static
     */
    public static function createBlank(string $separator = DIRECTORY_SEPARATOR, ?PathStrategy $strategy = null): static
    {
        static $prototypePath;
        if (!$prototypePath instanceof Path) {
            $class = Path::class;
            /** @var Path $prototypePath */
            $prototypePath = unserialize(sprintf('O:%d:"%s":0:{}', \strlen($class), $class));
            $prototypePath->items = [];
            $prototypePath->source = '';
        }
        return (clone $prototypePath)->initUrlPath($separator, $strategy);
    }

    /**
     * @return string|null
     */
    public function getLast(): ?string
    {
        return $this->items[count($this->items) - 1] ?? null;
    }

    /**
     * @param PathStrategy $strategy
     */
    public function setStrategy(PathStrategy $strategy): void
    {
        $this->strategy = $strategy;
    }

    /**
     * @return PathStrategy
     */
    public function getStrategy(): PathStrategy
    {
        return $this->strategy;
    }

    /**
     * @inheritDoc
     */
    protected function parse(string $source)
    {
        if (empty($source)) {
            $this->reset();
            return;
        }
        $this->items = explode($this->getSeparator(), $source);
        $this->updateSource();
    }

    /**
     * @inheritDoc
     */
    public function getSeparator(): string
    {
        return $this->separator;
    }

    /**
     * @param UrlStrategy $strategy
     * @return bool
     */
    public function equalsStrategy(UrlStrategy $strategy): bool
    {
        return $this->strategy === $strategy;
    }

    /**
     * @param string|null $suffix
     * @return bool
     */
    public function equalsSuffix(?string $suffix): bool
    {
        return $this->suffix === $suffix;
    }

    /**
     * @return string|null
     */
    public function getSuffix(): ?string
    {
        return $this->suffix;
    }

    /**
     * @param string|null $suffix
     */
    public function setSuffix(?string $suffix): void
    {
        $this->suffix = $suffix;
    }

    /**
     * @return void
     */
    public function updateSource(): void
    {
        $this->source = $this->strategy->updatePath($this->items, $this->separator, $this->suffix);
    }

    /**
     * @inheritDoc
     */
    public function reset(): static
    {
        $this->source = '';
        $this->items = [];
        $this->suffix = null;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setSource(string $source): void
    {
        $this->parse(rtrim($source, $this->getSeparator()));
    }

    /**
     * @inheritDoc
     */
    public function setAll(array $items): void
    {
        $this->items = $items;
    }

    /**
     * @return string
     */
    public function dirname(): string
    {
        return \dirname($this->source);
    }

    /**
     * @param array $searchReplace
     */
    public function replaceAll(array $searchReplace): void
    {
        foreach ($searchReplace as $search => $replace) {
            $this->replace($search, $replace);
        }
    }

    /**
     * @param string $search
     * @param string $replace
     * @return void
     */
    public function replace(string $search, string $replace): void
    {
        foreach ($this->items as $idx => $part) {
            $this->replaceIn($idx, $search, $replace, $part);
        }
    }

    /**
     * @param int $idx
     * @param string $search
     * @param string $replace
     * @param string $part
     * @return void
     */
    private function replaceIn(int $idx, string $search, string $replace, string $part): void
    {
        $this->items[$idx] = str_replace($search, $replace, $part);
    }

    /**
     * @param string $currentValue
     * @param string $newValue
     */
    public function setBy(string $currentValue, string $newValue): void
    {
        $key = $this->getKey($currentValue);
        if ($key === null) {
            throw new InvalidPathException(sprintf('You are trying to replace "%s" with "%s", but there is no such item in the path.', $currentValue, $newValue));
        }
        $this->items[$key] = $newValue;
    }

    /**
     * @param string $name
     * @return int|null
     */
    public function getKey(string $name): ?int
    {
        if (!$this->contains($name)) {
            return null;
        }
        return array_search($name, $this->items, true);
    }

    /**
     * @param string $needle
     * @return bool
     */
    public function contains(string $needle): bool
    {
        return in_array($needle, $this->items, true);
    }

    /**
     * @param int $key
     * @return string
     */
    public function get(int $key): string
    {
        $this->assertOfBounds($key, 'All elements in the path have an index less than the requested one.');
        return $this->items[$key];
    }

    /**
     * @param int $key
     * @param string $newValue
     */
    public function set(int $key, string $newValue): void
    {
        $this->assertOfBounds($key, 'The change could not be completed. All elements in the path have a lower index.');
        $this->items[$key] = $newValue;
    }

    /**
     * @param int $key
     * @param string $message
     * @return void
     */
    protected function assertOfBounds(int $key, string $message): void
    {
        if (count($this->items) <= $key) {
            throw new InvalidPathException($message);
        }
        if (0 > $key) {
            throw new InvalidPathException('Index cannot be less than 0.');
        }
    }

}