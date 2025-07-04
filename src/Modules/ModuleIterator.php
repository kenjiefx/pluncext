<?php 

namespace Kenjiefx\Pluncext\Modules;

class ModuleIterator implements \Iterator
{
    private int $position = 0;
    private array $files = [];

    public function __construct(array $files)
    {
        $this->files = $files;
    }

    public function current(): ModuleModel
    {
        return $this->files[$this->position];
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
        return isset($this->files[$this->position]);
    }
}