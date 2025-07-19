<?php 

namespace Kenjiefx\Pluncext\Modules;

class ModuleIterator implements \Iterator {

    private int $position = 0;
    private array $modules = [];

    public function __construct(array $modules) {
        $this->modules = $modules;
    }

    public function current(): ModuleModel {
        return $this->modules[$this->position];
    }

    public function key(): int {
        return $this->position;
    }

    public function next(): void {
        ++$this->position;
    }

    public function rewind(): void {
        $this->position = 0;
    }

    public function valid(): bool {
        return isset($this->modules[$this->position]);
    }

}