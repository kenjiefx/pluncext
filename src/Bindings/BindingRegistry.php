<?php 

namespace Kenjiefx\Pluncext\Bindings;

use Exception;
use Kenjiefx\Pluncext\Modules\ModuleModel;
use Kenjiefx\Pluncext\Modules\ModuleRole;

class BindingRegistry {

    /**
     * @var array<BindingModel>
     */
    private array $bindings = [];

    public function __construct(

    ) {}

    public function register(BindingModel $binding): void {
        $this->bindings[] = $binding;
    }

    /**
     * Retrieves the implementation module for a given interface module.
     *
     * @param ModuleModel $interface The interface module for which to find the implementation.
     * @return ModuleModel The implementation module that corresponds to the given interface.
     * @throws Exception If the provided module is not an interface or if no implementation is found.
     */
    public function getImplementation(ModuleModel $interface): ModuleModel {
        if ($interface->moduleRole !== ModuleRole::INTERFACE) {
            throw new Exception(
                "Module {$interface->name} is not an interface module, cannot get implementation."
            );
        }
        $interfaceModulePath = $this->normalizePath($interface->absolutePath);
        foreach ($this->bindings as $binding) {
            $implementationModulePath = $this->normalizePath($binding->interface->absolutePath);
            if ($implementationModulePath === $interfaceModulePath) {
                return $binding->implementation;
            }
        }
        throw new Exception(
            "No implementation found for interface module {$interface->name} at path {$interface->absolutePath}."
        );
    }

    /**
     * Retrieves the interface module for a given implementation module.
     *
     * @param ModuleModel $implementation The implementation module for which to find the interface.
     * @return ModuleModel The interface module that corresponds to the given implementation.
     * @throws Exception If the provided module is not an implementation or if no interface is found.
     */
    public function getInterface(ModuleModel $implementation): ModuleModel {
        if ($implementation->moduleRole === ModuleRole::INTERFACE) {
            throw new Exception(
                "Module {$implementation->name} is not an implementation module, cannot get interface."
            );
        }
        $implModulePath = $this->normalizePath($implementation->absolutePath);
        foreach ($this->bindings as $binding) {
            $boundImplPath = $this->normalizePath($binding->implementation->absolutePath);
            if ($boundImplPath === $implModulePath) {
                return $binding->interface;
            }
        }
        throw new Exception(
            "No interface found for implementation module {$implementation->name} at path {$implementation->absolutePath}."
        );
    }

    /**
     * Normalizes a file path by replacing backslashes with forward slashes,
     * removing duplicate slashes, and trimming trailing slashes.
     *
     * @param string $path The file path to normalize.
     * @return string The normalized file path.
     */
    public function normalizePath($path) {
        $path = str_replace('\\', '/', $path);
        $path = preg_replace('#/+#', '/', $path);
        return rtrim($path, '/');
    }

    /**
     * Returns all registered bindings.
     *
     * @return array<BindingModel> An array of all registered bindings.
     */
    public function getBindings(): BindingIterator {
        return new BindingIterator($this->bindings);
    }

}