<?php 

namespace Kenjiefx\Pluncext\Dependencies;

class DependencyRegistry {

    public function __construct(
        private DependencyIterator $dependencies
    ) {}

    public function getAbsolutePathByImportAlias(
        string $importAlias
    ): string | null {
        foreach ($this->dependencies as $dependency) {
            $imports = $dependency->imports;
            foreach ($imports as $name => $alias) {
                if ($importAlias === $alias) {
                    return $dependency->absolutePath;
                } 
            }
        }
        return null;
    }


}