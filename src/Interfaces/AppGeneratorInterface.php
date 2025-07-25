<?php 

namespace Kenjiefx\Pluncext\Interfaces;

use Kenjiefx\Pluncext\Modules\ModuleModel;
use Kenjiefx\Pluncext\Modules\ModuleRegistry;
use Kenjiefx\ScratchPHP\App\Pages\PageModel;

interface AppGeneratorInterface {

    public function generateAppHandler(
        ModuleRegistry $moduleRegistry,
        ModuleModel $moduleModel,
        PageModel $pageModel
    ): string;

    public function generateAppMain(
        ModuleRegistry $moduleRegistry,
        PageModel $pageModel
    ): void;
}