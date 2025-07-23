<?php 

namespace Kenjiefx\Pluncext\Modules;

class ModuleRegistry {

    public function __construct(
        private ModuleIterator $moduleIterator,
    ) {
        
    }

    public function findByPath(
        string $absolutePath
    ): ?ModuleModel {
        foreach ($this->moduleIterator as $module) {
            // normalize the absolute path to ensure consistent retrieval
            $moduleAbsolutePath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $module->absolutePath);
            $givenAbsolutePath  = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $absolutePath);
            if ($moduleAbsolutePath === $givenAbsolutePath) {
                return $module;
            }
        }
        return null;
    }

    public function getAll(): ModuleIterator {
        return $this->moduleIterator;
    }

    public function debugPrint(): void {
        $printArr = [];
        foreach ($this->moduleIterator as $module) {
            $role = $module->moduleRole;
            $printArr[] = [
                'name' => $module->name,
                'path' => $module->absolutePath,
                'role' => $role->name
            ];
        }
        echo "<pre>";
        print_r($printArr);
        echo "</pre>";
    }

}