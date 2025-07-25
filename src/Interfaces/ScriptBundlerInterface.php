<?php 

namespace Kenjiefx\Pluncext\Interfaces;

use Kenjiefx\Pluncext\Modules\ModuleRegistry;
use Kenjiefx\ScratchPHP\App\Pages\PageModel;

interface ScriptBundlerInterface {

    /**
     * Bundles scripts for the given page model using the provided module registry.
     *
     * @param ModuleRegistry $moduleRegistry The registry of modules to use for bundling.
     * @param PageModel $pageModel The page model containing components to bundle.
     * @return string The bundled script as a string.
     */
    public function bundle(
        ModuleRegistry $moduleRegistry,
        PageModel $pageModel
    ): string;

}