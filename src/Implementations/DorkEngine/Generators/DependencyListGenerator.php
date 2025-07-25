<?php

namespace Kenjiefx\Pluncext\Implementations\DorkEngine\Generators;

use Kenjiefx\Pluncext\Modules\ModuleIterator;
use Kenjiefx\Pluncext\Modules\ModuleRole;
use Kenjiefx\Pluncext\Services\NameAliasPoolService;

class DependencyListGenerator {

    public function __construct(
        private NameAliasPoolService $nameAliasPoolService
    ) {}

    public function generate(
        ModuleIterator $dependencyModules
    ) {
        $dependencies = [];
        foreach ($dependencyModules as $dependencyModule) {
            if ($dependencyModule->moduleRole === ModuleRole::INTERFACE) {
                $nameAlias = "";
                switch ($dependencyModule->name) {
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