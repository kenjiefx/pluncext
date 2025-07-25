<?php 

namespace Kenjiefx\Pluncext\Implementations\QuarkBundler;

use Kenjiefx\Pluncext\Dependencies\DependencyIterator;
use Kenjiefx\Pluncext\Implementations\QuarkBundler\BundleItem\BundleItemModel;
use Kenjiefx\Pluncext\Implementations\QuarkBundler\BundleItem\BundleItemRegistry;
use Kenjiefx\Pluncext\Interfaces\ScriptBundlerInterface;
use Kenjiefx\Pluncext\Modules\ModuleRegistry;
use Kenjiefx\Pluncext\Modules\ModuleRole;
use Kenjiefx\ScratchPHP\App\Interfaces\ThemeServiceInterface;
use Kenjiefx\ScratchPHP\App\Pages\PageModel;

class QuarkBundleService implements ScriptBundlerInterface {

    public function __construct(
        private QuarkHandlerGenerator $handlerGenerator,
        private ThemeServiceInterface $themeService
    ) {}

    public function bundle(
        ModuleRegistry $moduleRegistry,
        PageModel $pageModel
    ): string {
        $bundledItems = new BundleItemRegistry();
        foreach ($pageModel->componentRegistry->getAll() as $component) {
            $componentJsPath = $this->themeService->getComponentJsPath(
                $pageModel->theme, $component
            );
            $componentTsPath = $this->convertJsToTs($componentJsPath);
            if ($bundledItems->has($componentTsPath)) {
                // Component already bundled, skip
                continue;
            }
            $moduleModel = $moduleRegistry->findByPath($componentTsPath);
            if ($moduleModel === null) {
                throw new \Exception(
                    "Module not found at {$componentTsPath}"
                );
            }
            $handlerScript = $this->handlerGenerator->generateHandler(
                
            );
            $bundledItem = new BundleItemModel(
                $componentTsPath, $handlerScript
            );
            $bundledItems->add($bundledItem);
        }
        return "";
    }

    public function bundleDependencies(
        DependencyIterator $dependencyIterator,
        ModuleRegistry $moduleRegistry,
        PageModel $pageModel,
        BundleItemRegistry $bundledItems
    ): void {
        foreach ($dependencyIterator as $dependencyModel) {
            $dependencyTsPath = $dependencyModel->absolutePath;
            if ($bundledItems->has($dependencyTsPath)) {
                // Dependency already bundled, skip
                continue;
            }
            $dependencyModule = $moduleRegistry->findByPath($dependencyTsPath);
            if ($dependencyModule === null) {
                throw new \Exception(
                    "Module not found at {$dependencyTsPath}"
                );
            }
            if ($dependencyModule->moduleRole === ModuleRole::COMPONENT) {
                // Component handler will be handled separately
                continue;
            }
            $handlerScript = $this->handlerGenerator->generateHandler(
                
            );
            $bundledItem = new BundleItemModel(
                $dependencyTsPath, $handlerScript
            );
            $bundledItems->add($bundledItem);
        }
    }

    /**
     * Convert a JavaScript file path to TypeScript file path.
     * @param string $jsPath
     * @return string
     */
    public function convertJsToTs(string $jsPath) {
        $tsPath = str_replace('.js', '.ts', $jsPath);
        return $tsPath;
    }

}