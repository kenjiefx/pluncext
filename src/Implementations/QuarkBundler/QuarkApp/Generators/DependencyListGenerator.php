<?php 

namespace Kenjiefx\Pluncext\Implementations\QuarkBundler\QuarkApp\Generators;

use Kenjiefx\Pluncext\Implementations\QuarkBundler\QuarkApp\Services\PluncObjectService;
use Kenjiefx\Pluncext\Modules\ModuleIterator;
use Kenjiefx\Pluncext\Modules\ModuleRole;
use Kenjiefx\Pluncext\Services\NameAliasPoolService;
use Kenjiefx\ScratchPHP\App\Pages\PageModel;

class DependencyListGenerator {

    public function __construct(
        private NameAliasPoolService $nameAliasPoolService,
        private PluncObjectService $pluncObjectService
    ) {}
    
    /**
     * Returns a comma-separated list of dependencies for the given modules.
     * @param ModuleIterator $dependencyModules
     * @return string
     */
    public function generate(
        PageModel $pageModel,
        ModuleIterator $dependencyModules
    ) {
        $dependencies = [];
        foreach ($dependencyModules as $dependencyModule) {
            if ($dependencyModule->moduleRole === ModuleRole::INTERFACE) {
                $nameAlias = "";
                switch ($this->pluncObjectService->getPluncObjectName($pageModel->theme, $dependencyModule)) {
                    case "ComponentScope": 
                        $nameAlias = "\$scope";
                        break;
                    case "BlockService": 
                        $nameAlias = "\$block";
                        break;
                    case "PatchService": 
                        $nameAlias = "\$patch";
                        break;
                    case "PluncAppService": 
                        $nameAlias = "\$app";
                        break;
                    case "ComponentReflection": 
                        $nameAlias = "\$this";
                        break;
                    default: 
                        $nameAlias = $this->nameAliasPoolService->getAliasOfPath(
                            $dependencyModule->absolutePath
                        );
                        break;
                }
                $dependencies[] = $nameAlias;
            } else {
                $nameAlias = $this->nameAliasPoolService->getAliasOfPath(
                    $dependencyModule->absolutePath
                );
                $dependencies[] = $nameAlias;
            }
        }
        return implode(',', $dependencies);
    }

}