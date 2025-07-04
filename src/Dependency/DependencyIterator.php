<?php 

namespace Kenjiefx\Pluncext\Dependency;

class DependencyIterator implements \Iterator
{
    private int $position = 0;
    private array $dependencies = [];

    public function __construct(array $dependencies)
    {
        $this->dependencies = $dependencies;
    }

    public function current(): DependencyModel
    {
        return $this->dependencies[$this->position];
    }

    public function key(): int
    {
        return $this->position;
    }

    public function next(): void
    {
        ++$this->position;
    }

    public function rewind(): void
    {
        $this->position = 0;
    }

    public function valid(): bool
    {
        return isset($this->dependencies[$this->position]);
    }
}