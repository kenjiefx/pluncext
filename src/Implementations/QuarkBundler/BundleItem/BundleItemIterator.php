<?php 

namespace Kenjiefx\Pluncext\Implementations\QuarkBundler\BundleItem;

class BundleItemIterator implements \Iterator {

    /**
     * The items in the iterator.
     * This array holds the BundleItemModel objects that are being iterated over.
     * @var BundleItemModel[] $items
     */
    private array $items;
    private int $position = 0;

    public function __construct(array $items) {
        $this->items = $items;
    }

    public function current(): BundleItemModel {
        return $this->items[$this->position];
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
        return isset($this->items[$this->position]);
    }

}