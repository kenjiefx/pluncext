<?php 

namespace Kenjiefx\Pluncext\Modules;

use Kenjiefx\Pluncext\Dependency\DependencyIterator;

/**
 * Represents a JavaScript module with its absolute path.
 */
class ModuleModel {

    public function __construct(
        public readonly string $absolutePath,
        public readonly string $name,
        public readonly ModuleRole $moduleRole,
        public readonly DependencyIterator $dependencies
    ) {}

}