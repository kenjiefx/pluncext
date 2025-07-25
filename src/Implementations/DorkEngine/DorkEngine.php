<?php 

namespace Kenjiefx\Pluncext\Implementations\DorkEngine;

use Kenjiefx\Pluncext\Interfaces\AppGeneratorInterface;
use Kenjiefx\Pluncext\Modules\ModuleModel;
use Kenjiefx\Pluncext\Modules\ModuleRegistry;
use Kenjiefx\ScratchPHP\App\Pages\PageModel;

class DorkEngine implements AppGeneratorInterface {

    public function __construct(
        private DorkHandlerService $dorkHandlerService,
        private DorkAppMainService $dorkAppMainService
    ) {}

    public function generateAppHandler(
        ModuleRegistry $moduleRegistry,
        ModuleModel $moduleModel,
        PageModel $pageModel
    ): string {
        return $this->dorkHandlerService->generateHandler(
            $moduleRegistry, $moduleModel, $pageModel
        );
    }

    public function generateAppMain(
        ModuleRegistry $moduleRegistry,
        PageModel $pageModel
    ): void {
        $this->dorkAppMainService->generateAppMain(
            $moduleRegistry, $pageModel
        );
    }

}