<?php 

namespace Kenjiefx\Pluncext\Implementations\DorkEngine\Generators;

use Kenjiefx\Pluncext\Modules\ModuleIterator;
use Kenjiefx\Pluncext\Modules\ModuleRole;
use Kenjiefx\Pluncext\Services\NameAliasPoolService;

class ReturnStatementGenerator {

    public function __construct(
        private NameAliasPoolService $nameAliasPoolService
    ) {}
    
    /**
     * Creates a return statement as new instance
     *
     * @param string $className The class name to instantiate.
     * @param ModuleIterator $dependencyModules The modules that are dependencies.
     * @return string The return statement for creating a new instance.
     */
    public function generateAsNewInstance(
        string $className,
        ModuleIterator $dependencyModules
    ) {
        $constructorArgs = [];
        foreach ($dependencyModules as $dependencyModule) {
            if ($dependencyModule->moduleRole === ModuleRole::INTERFACE) {
                $statement = "";
                switch ($dependencyModule->name) {
                    case "ComponentScope": 
                        $statement = "\$scope";
                        break;
                    case "BlockService": 
                        $statement = "new BlockService(\$block)";
                        break;
                    case "PatchService": 
                        $statement = "new PatchService(\$patch)";
                        break;
                    case "PluncAppService": 
                        $statement = "new PluncService(\$app)";
                        break;
                    case "ComponentReflection": 
                        $statement = "\$this";
                        break;
                    default: 
                        $statement = $this->nameAliasPoolService->getAliasOfPath(
                            $dependencyModule->absolutePath
                        );
                        break;
                }
                $constructorArgs[] = $statement;
            } else if ($dependencyModule->moduleRole === ModuleRole::FACTORY) {
                $nameAlias = $this->nameAliasPoolService->getAliasOfPath(
                    $dependencyModule->absolutePath
                );
                $statement = "(new {$nameAlias}()).__()";
                $constructorArgs[] = $statement;
            } else {
                $nameAlias = $this->nameAliasPoolService->getAliasOfPath(
                    $dependencyModule->absolutePath
                );
                $constructorArgs[] = $nameAlias;
            }
        }
        $argsString = implode(', ', $constructorArgs);
        // Generate a return statement for creating a new instance
        return "return new {$className}({$argsString});";
    }

    public function generateAsFactoryInstance(
        string $className,
        ModuleIterator $dependencyModules
    ) {
        $asNewInstance = $this->generateAsNewInstance(
            $className, $dependencyModules
        );
        return "return class ___ { __(){ $asNewInstance } } ";
    }

}