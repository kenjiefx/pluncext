<?php 

namespace Kenjiefx\Pluncext\Interfaces;

use Kenjiefx\Pluncext\Modules\ModuleModel;
use Kenjiefx\Pluncext\Modules\ModuleRegistry;
use Kenjiefx\ScratchPHP\App\Pages\PageModel;

interface HandlerGeneratorInterface {

    public function generateHandler(
        ModuleRegistry $moduleRegistry,
        ModuleModel $moduleModel,
        PageModel $pageModel
    ): string;

}