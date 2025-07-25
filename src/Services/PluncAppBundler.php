<?php 

namespace Kenjiefx\Pluncext\Services;

use Kenjiefx\Pluncext\Implementations\DorkEngine\DorkEngine;
use Kenjiefx\Pluncext\Modules\ModuleRegistry;
use Kenjiefx\ScratchPHP\App\Interfaces\ThemeServiceInterface;
use Kenjiefx\ScratchPHP\App\Pages\PageModel;

class PluncAppBundler {

    public function __construct(
        private ThemeServiceInterface $themeService,
        private DorkEngine $appGeneratorInterface
    ) {}

    public function bundle(
        ModuleRegistry $moduleRegistry,
        PageModel $pageModel,
    ) {
        $components = $pageModel->componentRegistry->getAll();
        $themeModel = $pageModel->theme;
        $bundledScripts = [];
        foreach ($components as $component) {
            $componentJsPath = $this->themeService->getComponentJsPath($themeModel, $component);
            $componentTsPath = $this->convertJsToTs($componentJsPath);
            if (isset($bundledScripts[$componentTsPath])) return;
            $moduleModel = $moduleRegistry->findByPath($componentTsPath);
            if ($moduleModel === null) {
                throw new \Exception(
                    "Module not found for component: {$component->name} at path: {$componentTsPath}"
                );
            }
            $handlerScript = $this->appGeneratorInterface->generateAppHandler(
                $moduleRegistry, $moduleModel, $pageModel
            );
            $bundledScripts[$componentTsPath] = $handlerScript;
            foreach ($moduleModel->dependencies as $dependencyModel) {
                $dependencyTsPath = $dependencyModel->absolutePath;
                if (isset($bundledScripts[$dependencyTsPath])) return;
                $dependencyModule = $moduleRegistry->findByPath($dependencyTsPath);
                $handlerScript = $this->appGeneratorInterface->generateAppHandler(
                    $moduleRegistry, $dependencyModule, $pageModel
                );
                $bundledScripts[$dependencyTsPath] = $handlerScript;
            }
        }

        foreach ($bundledScripts as $path => $content) {
            echo "<pre>";
            echo $content;
            echo "</pre>";
        }

        $this->appGeneratorInterface->generateAppMain(
            $moduleRegistry, $pageModel
        );
    }

    public function convertJsToTs(string $jsPath) {
        $tsPath = str_replace('.js', '.ts', $jsPath);
        return $tsPath;
    }


}