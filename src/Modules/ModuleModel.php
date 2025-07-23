<?php 

namespace Kenjiefx\Pluncext\Modules;

use Kenjiefx\Pluncext\Dependencies\DependencyIterator;

class ModuleModel {

    public function __construct(
        public readonly string $absolutePath,
        public readonly string $name,
        public readonly ModuleRole $moduleRole,
        public readonly DependencyIterator $dependencies
    ) {}

    public function debugPrint(): void {
        $printArr = [
            'name' => $this->name,
            'absolutePath' => $this->absolutePath,
            'moduleRole' => $this->moduleRole->name,
            'dependencies' => []
        ];
        foreach ($this->dependencies as $dependency) {
            $imports = $dependency->imports;
            $absolutePath = $dependency->absolutePath;
            $printArr['dependencies'][] = [
                'absolutePath' => $absolutePath,
                'imports' => $imports
            ];
        }
        echo "<pre>";
        print_r($printArr);
        echo "</pre>";
    }

}