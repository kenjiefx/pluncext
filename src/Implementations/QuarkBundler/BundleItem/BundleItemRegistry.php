<?php 

namespace Kenjiefx\Pluncext\Implementations\QuarkBundler\BundleItem;

class BundleItemRegistry {

    /**
     * The registry of bundle items.
     * This registry holds all the bundle items that can be used in the bundling process.
     * @var BundleItemModel[] $items
     */
    private array $items = [];

    public function __construct(

    ) {}

    /**
     * Adds a bundle item to the registry.
     *
     * @param BundleItemModel $item The bundle item to add.
     */
    public function add(BundleItemModel $item): void {
        $this->items[$item->id] = $item;
    }

    /**
     * Retrieves a bundle item by its ID.
     *
     * @param string $id The ID of the bundle item to retrieve.
     * @return BundleItemModel|null The bundle item if found, null otherwise.
     */
    public function get(string $id): ?BundleItemModel {
        return $this->items[$id] ?? null;
    }

    /**
     * Checks if a bundle item exists in the registry.
     *
     * @param string $id The ID of the bundle item to check.
     * @return bool True if the item exists, false otherwise.
     */
    public function has(string $id): bool {
        return isset($this->items[$id]);
    }

    /**
     * Retrieves all bundle items in the registry.
     *
     * @return BundleItemIterator An iterator over all bundle items.
     */
    public function getAll(): BundleItemIterator {
        $items = array_values($this->items);
        return new BundleItemIterator($items);
    }

}