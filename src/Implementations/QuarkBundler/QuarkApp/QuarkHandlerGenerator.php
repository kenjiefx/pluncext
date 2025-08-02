<?php 

namespace Kenjiefx\Pluncext\Implementations\QuarkBundler\QuarkApp;

use Kenjiefx\Pluncext\Bindings\BindingRegistry;
use Kenjiefx\Pluncext\Implementations\QuarkBundler\QuarkApp\AppComponentHandlerGenerator;
use Kenjiefx\Pluncext\Implementations\QuarkBundler\QuarkApp\Generators\PluncObjectsGenerator;
use Kenjiefx\Pluncext\Implementations\QuarkBundler\QuarkApp\RegularHandlerGenerator;
use Kenjiefx\Pluncext\Modules\ModuleModel;
use Kenjiefx\Pluncext\Modules\ModuleRegistry;
use Kenjiefx\ScratchPHP\App\Pages\PageModel;

class QuarkHandlerGenerator {

    public function __construct(
        private RegularHandlerGenerator $regularHandlerGenerator,
        private AppComponentHandlerGenerator $appComponentHandlerGenerator,
        private PluncObjectsGenerator $pluncObjectsGenerator
    ) {}

    public function generateStarterScript() {
        return "const app = plunc.create('app'); \n";
    }

    public function generatePluncApiScripts() {
        return $this->pluncObjectsGenerator->blockService() . "\n" .
            $this->pluncObjectsGenerator->pluncAppService() . "\n" .
            $this->pluncObjectsGenerator->patchService() . "\n" . 
            $this->pluncObjectsGenerator->componentReflection() . "\n";
    }

    public function generateRegularHandler(
        ModuleRegistry $moduleRegistry,
        ModuleModel $moduleModel,
        PageModel $pageModel,
        string | null $pathOfInterfaceIfModuleImplementsInterface = null
    ) {
        return $this->regularHandlerGenerator->generateHandler(
            $moduleRegistry, 
            $moduleModel, 
            $pageModel,
            $pathOfInterfaceIfModuleImplementsInterface
        );
    }

    public function generateAppComponentHandler(
        ModuleRegistry $moduleRegistry,
        PageModel $pageModel
    ) {
        return $this->appComponentHandlerGenerator->generateHandler(
            $moduleRegistry, 
            $pageModel
        );
    }

    public function generateAppComponentModule(
        PageModel $pageModel
    ) {
        return $this->appComponentHandlerGenerator->createModule($pageModel);
    }


}