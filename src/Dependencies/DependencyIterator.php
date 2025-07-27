<?php

namespace Kenjiefx\Pluncext\Dependencies;

class DependencyIterator implements \Iterator {

    private $dependencies;
    private $position = 0;

    public function __construct(array $dependencies) {
        $this->dependencies = $dependencies;
    }

    public function current(): DependencyModel {
        return $this->dependencies[$this->position];
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
        return isset($this->dependencies[$this->position]);
    }

    public function count(): int {
        return count($this->dependencies);
    }

    public function add(DependencyModel $dependency): void {
        $this->dependencies[] = $dependency;
    }

}