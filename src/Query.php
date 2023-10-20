<?php
declare(strict_types=1);

namespace Compass;

class Query extends AbstractPath
{
    protected QueryStrategy $strategy;

    /**
     * @param string $source
     * @param QueryStrategy|null $strategy
     */
    public function __construct(string $source, ?QueryStrategy $strategy = null)
    {
        $this->initUrlQuery($strategy);
        parent::__construct($source);
    }

    /**
     * @param QueryStrategy|null $strategy
     * @return $this
     */
    protected function initUrlQuery(?QueryStrategy $strategy = null): static
    {
        $this->strategy = $strategy ?? new DefaultQueryStrategy();
        return $this;
    }

    /**
     * @param QueryStrategy|null $strategy
     * @return static
     */
    public static function createBlank(?QueryStrategy $strategy = null): static
    {
        static $prototypeQuery;
        if (!$prototypeQuery instanceof Query) {
            $class = Query::class;
            /** @var Query $prototypeQuery */
            $prototypeQuery = unserialize(sprintf('O:%d:"%s":0:{}', \strlen($class), $class));
            $prototypeQuery->source = '';
            $prototypeQuery->items = [];
        }
        return (clone $prototypeQuery)->initUrlQuery($strategy);
    }

    /**
     * @param QueryStrategy $strategy
     */
    public function setStrategy(QueryStrategy $strategy): void
    {
        $this->strategy = $strategy;
    }

    /**
     * @return QueryStrategy
     */
    public function getStrategy(): QueryStrategy
    {
        return $this->strategy;
    }

    /**
     * @param string $source
     */
    protected function parse(string $source)
    {
        parse_str($source, $items);
        $this->items = $items;
        $this->updateSource();
    }

    /**
     * @return string
     */
    public function getSeparator(): string
    {
        return '&';
    }

    /**
     * @inheritDoc
     */
    public function updateSource(): void
    {
        $this->source = $this->strategy->updateQuery($this->items);
    }

    /**
     * @param QueryStrategy $strategy
     * @return bool
     */
    public function equalsStrategy(QueryStrategy $strategy): bool
    {
        return $this->strategy === $strategy;
    }

    /**
     * ["query"]=> "name=param&name2=para2m"
     * @param string $source
     */
    public function setSource(string $source): void
    {
        $this->parse($source);
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function getValueByName(string $name): mixed
    {
        if (array_key_exists($name, $this->items)) {
            return $this->items[$name];
        }
        return null;
    }

    /**
     * @param string $name
     * @param mixed $value
     * @return mixed
     */
    public function setParam(string $name, mixed $value): static
    {
        $this->items[$name] = $value;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function reset(): static
    {
        $this->source = '';
        $this->items = [];
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setAll(array $items): void
    {
        $this->items = $items;
    }
}