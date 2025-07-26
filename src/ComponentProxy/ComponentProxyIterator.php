<?php 

namespace Kenjiefx\Pluncext\ComponentProxy;

class ComponentProxyIterator implements \Iterator {

    private int $position = 0;
    private array $components = [];

    public function __construct(array $components) {
        $this->components = $components;
    }

    public function current(): ComponentProxyModel {
        return $this->components[$this->position];
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
        return isset($this->components[$this->position]);
    }

}