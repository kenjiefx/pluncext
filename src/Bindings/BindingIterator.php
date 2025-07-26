<?php 

namespace Kenjiefx\Pluncext\Bindings;

class BindingIterator implements \Iterator {
    
    /**
     * @var BindingModel[]
     */
    private $bindings;
    private $position = 0;

    public function __construct(array $bindings) {
        $this->bindings = $bindings;
    }

    public function current(): BindingModel {
        return $this->bindings[$this->position];
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
        return isset($this->bindings[$this->position]);
    }

}