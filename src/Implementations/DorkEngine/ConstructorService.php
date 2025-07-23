<?php 

namespace Kenjiefx\Pluncext\Implementations\DorkEngine;

use Kenjiefx\Pluncext\Dependencies\DependencyRegistry;
use Kenjiefx\Pluncext\Modules\ModuleModel;
use Kenjiefx\Pluncext\Modules\ModuleRegistry;
use Kenjiefx\Pluncext\Services\TypeScriptParser;
use Symfony\Component\Filesystem\Filesystem;
use Kenjiefx\Pluncext\Modules\ModuleIterator;

class ConstructorService {

    public function __construct(
        private TypeScriptParser $typeScriptParser,
        private Filesystem $filesystem
    ) {}
    
    /**
     * Summary of getDependencies
     * @param ModuleModel $moduleModel
     * @param ModuleRegistry $moduleRegistry
     * @return ModuleIterator
     */
    public function getDependencies(
        ModuleModel $moduleModel,
        ModuleRegistry $moduleRegistry
    ) {
        $constructorTypes = $this->getConstructorTypes($moduleModel);
        /** @var ModuleModel[] */
        $dependencies = [];
        foreach ($constructorTypes as $constructorType) {
            $absolutePathOfType = $this->getAbsolutePathsOfType($constructorType, $moduleModel);
            $dependency = $moduleRegistry->findByPath($absolutePathOfType);
            $dependencies[] = $dependency;
        }
        return new ModuleIterator($dependencies);
    }

    /**
     * Return the types declared for each constructor argument in the 
     * class within the module.
     * @param ModuleModel $moduleModel
     * @throws \Exception
     * @return string[]
     */
    public function getConstructorTypes(
        ModuleModel $moduleModel
    ) {
        $moduleTsContent = $this->filesystem->readFile($moduleModel->absolutePath);
        $constructorArgs = $this->typeScriptParser->getArgumentOfFirstClassConstructor($moduleTsContent);
        /** @var array<string> - the type declared for each constructor argument */
        $constructorTypes = [];
        foreach ($constructorArgs as $constructorArg) {
            $parts = explode(':', $constructorArg);
            if (!isset($parts[1])) {
                $modulePath = $moduleModel->absolutePath;
                $message = "Constructor argument '{$constructorArg}' type declaration is strictly required in {$modulePath}.";
                throw new \Exception($message);
            }
            $type = trim($parts[1]);
            $constructorTypes[] = $this->removeTypeGenerics($type);
        }
        return $constructorTypes;
    }

    public function removeTypeGenerics(
        string $type
    ): string {
        // Remove generic types like `Array<string>` or `Promise<T>`
        $type = preg_replace('/<.*>/', '', $type);
        // Remove any trailing whitespace
        return trim($type);
    }

    public function getAbsolutePathsOfType(
        string $type, 
        ModuleModel $moduleModel
    ) {
        $dependencies = $moduleModel->dependencies;
        $registry = new DependencyRegistry($dependencies);
        $absolutePath = $registry->getAbsolutePathByImportAlias($type);
        return $absolutePath;
    }

}