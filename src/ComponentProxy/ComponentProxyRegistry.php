<?php 

namespace Kenjiefx\Pluncext\ComponentProxy;

class ComponentProxyRegistry {

    /**
     * @var array<string, ComponentProxyModel>
     */
    private array $registry = [];

    public function __construct() {}

    public function register(ComponentProxyModel $componentProxyModel) {
        $name = $componentProxyModel->component->name;
        if (!isset($this->registry[$name])) {
            $this->registry[$name] = $componentProxyModel;
        }
    }

    public function getAll(): ComponentProxyIterator {
        return new ComponentProxyIterator(
            array_values($this->registry)
        );
    }

}