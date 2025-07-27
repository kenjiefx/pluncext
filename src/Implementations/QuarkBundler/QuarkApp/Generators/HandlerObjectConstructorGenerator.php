<?php 

namespace Kenjiefx\Pluncext\Implementations\QuarkBundler\QuarkApp\Generators;

use Kenjiefx\Pluncext\Implementations\QuarkBundler\QuarkApp\Services\PluncObjectService;
use Kenjiefx\Pluncext\Modules\ModuleIterator;
use Kenjiefx\Pluncext\Modules\ModuleRole;
use Kenjiefx\Pluncext\Services\NameAliasPoolService;
use Kenjiefx\ScratchPHP\App\Pages\PageModel;

class HandlerObjectConstructorGenerator {

    public function __construct(
        private NameAliasPoolService $nameAliasPoolService,
        private PluncObjectService $pluncObjectService
    ) {}
    
    /**
     * Creates a constructor statement, which is used to instantiate a new object
     * @example
     * ```js
     * new MyClass($scope, new BlockService($block), $this);
     * ``
     *
     * @param string $className The class name to instantiate.
     * @param ModuleIterator $dependencyModules The modules that are dependencies.
     * @return string The constructor statement
     */
    public function generateAsNewInstance(
        string $className,
        ModuleIterator $dependencyModules,
        PageModel $pageModel
    ) {
        $constructorArgs = [];
        foreach ($dependencyModules as $dependencyModule) {
            if ($dependencyModule->moduleRole === ModuleRole::INTERFACE) {
                $statement = "";
                switch ($this->pluncObjectService->getPluncObjectName($pageModel->theme, $dependencyModule)) {
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
        return "new {$className}({$argsString});";
    }

}