<?php 

namespace Kenjiefx\Pluncext\Implementations\QuarkBundler;

use Kenjiefx\Pluncext\Bindings\BindingRegistry;
use Kenjiefx\Pluncext\Dependencies\DependencyIterator;
use Kenjiefx\Pluncext\Implementations\QuarkBundler\BundleItem\BundleItemModel;
use Kenjiefx\Pluncext\Implementations\QuarkBundler\BundleItem\BundleItemRegistry;
use Kenjiefx\Pluncext\Implementations\QuarkBundler\QuarkApp\QuarkHandlerGenerator;
use Kenjiefx\Pluncext\Implementations\QuarkBundler\QuarkApp\Services\PluncObjectService;
use Kenjiefx\Pluncext\Interfaces\ScriptBundlerInterface;
use Kenjiefx\Pluncext\Modules\ModuleFactory;
use Kenjiefx\Pluncext\Modules\ModuleModel;
use Kenjiefx\Pluncext\Modules\ModuleRegistry;
use Kenjiefx\Pluncext\Modules\ModuleRole;
use Kenjiefx\ScratchPHP\App\Interfaces\ThemeServiceInterface;
use Kenjiefx\ScratchPHP\App\Pages\PageModel;

class QuarkBundleService implements ScriptBundlerInterface {

    static $i = 0;

    public function __construct(
        private QuarkHandlerGenerator $handlerGenerator,
        private ThemeServiceInterface $themeService,
        private PluncObjectService $pluncObjectService,
        private ModuleFactory $moduleFactory
    ) {}

    public function bundle(
        BindingRegistry $bindingRegistry,
        ModuleRegistry $moduleRegistry,
        PageModel $pageModel
    ): string {
        $bundledItems = new BundleItemRegistry();
        $resultScript = $this->handlerGenerator->generateStarterScript();
        $resultScript .= $this->handlerGenerator->generatePluncApiScripts();
        $this->bundleComponents(
            $bindingRegistry, $moduleRegistry, $pageModel, $bundledItems
        );
        $this->bundleAppComponent(
            $bindingRegistry, $moduleRegistry, $pageModel, $bundledItems
        );
        foreach ($bundledItems->getAll() as $bundleItem) {
            $resultScript .= $bundleItem->content . "\n";
        }
        $resultScript .= $this->handlerGenerator->generateAppComponentHandler(
            $moduleRegistry, $pageModel
        );
        return $resultScript;
    }

    public function iterate() {
        static::$i++;
        if (static::$i > 1000) {
            throw new \Exception(
                "Infinite loop detected in QuarkBundleService. " .
                "Please check your dependencies and module definitions."
            );
        }
    }

    public function bundleAppComponent(
        BindingRegistry $bindingRegistry,
        ModuleRegistry $moduleRegistry,
        PageModel $pageModel,
        BundleItemRegistry $bundledItems
    ) {
        $appComponentModule 
            = $this->handlerGenerator->generateAppComponentModule(
                $pageModel
            );
        $this->bundleDependencies(
            $bindingRegistry, $appComponentModule->dependencies, $moduleRegistry, $pageModel, $bundledItems
        );
    }

    public function bundleComponents(
        BindingRegistry $bindingRegistry,
        ModuleRegistry $moduleRegistry,
        PageModel $pageModel,
        BundleItemRegistry $bundledItems
    ) {
        foreach ($pageModel->componentRegistry->getAll() as $component) {
            $componentJsPath = $this->themeService->getComponentJsPath(
                $pageModel->theme, $component
            );
            $this->iterate();
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
            $maybeInterfacePath = $moduleModel->absolutePath;
            $moduleModel = $this->overrideModuleModelIfInterface(
                $pageModel, $moduleModel, $bindingRegistry
            );
            if ($maybeInterfacePath === $moduleModel->absolutePath) {
                // This means the module was not an interface, since 
                // it was not overriden by an implementation.
                $maybeInterfacePath = null;
            }
            $this->iterate();
            $dependencies = $moduleModel->dependencies;
            $this->bundleDependencies(
                $bindingRegistry, $dependencies, $moduleRegistry, $pageModel, $bundledItems
            );
            $handlerScript = $this->handlerGenerator->generateRegularHandler(
                $moduleRegistry, $moduleModel, $pageModel, $maybeInterfacePath
            );
            $bundledItem = new BundleItemModel(
                $componentTsPath, $handlerScript
            );
            $bundledItems->add($bundledItem);
        }
    }

    public function bundleDependencies(
        BindingRegistry $bindingRegistry,
        DependencyIterator $dependencyIterator,
        ModuleRegistry $moduleRegistry,
        PageModel $pageModel,
        BundleItemRegistry $bundledItems
    ): void {
        foreach ($dependencyIterator as $dependencyModel) {
            $this->iterate();
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
            $maybeInterfacePath = $dependencyModule->absolutePath;
            $dependencyModule = $this->overrideModuleModelIfInterface(
                $pageModel, $dependencyModule, $bindingRegistry
            );
            if ($maybeInterfacePath === $dependencyModule->absolutePath) {
                // This means the module was not an interface, since 
                // it was not overriden by an implementation.
                $maybeInterfacePath = null;
            }
            $dependencies = $dependencyModule->dependencies;
            $this->bundleDependencies(
                $bindingRegistry, $dependencies, $moduleRegistry, $pageModel, $bundledItems
            );
            $handlerScript = $this->handlerGenerator->generateRegularHandler(
                $moduleRegistry, $dependencyModule, $pageModel, $maybeInterfacePath
            );
            $this->iterate();
            $bundledItem = new BundleItemModel(
                $dependencyTsPath, $handlerScript
            );
            $bundledItems->add($bundledItem);
        }
    }

    /**
     * Override the module model if it is an interface.
     * @param \Kenjiefx\ScratchPHP\App\Pages\PageModel $pageModel
     * @param \Kenjiefx\Pluncext\Modules\ModuleModel $moduleModel
     * @param \Kenjiefx\Pluncext\Bindings\BindingRegistry $bindingRegistry
     * @return ModuleModel
     */
    public function overrideModuleModelIfInterface(
        PageModel $pageModel,
        ModuleModel $moduleModel,
        BindingRegistry $bindingRegistry
    ): ModuleModel {
        if ($moduleModel->moduleRole !== ModuleRole::INTERFACE) {
            return $moduleModel;
        }
        if ($this->isNonImplementableInterface($pageModel, $moduleModel, $bindingRegistry)) {
            return $moduleModel;
        }
        $moduleInterfaceName = $moduleModel->name;
        $moduleInterfaceAbsolutePath = $this->normalizePath($moduleModel->absolutePath);
        $implementationModule = $bindingRegistry->getImplementation(
            interface: $moduleModel
        );
        $overrideModule = $this->moduleFactory->create(
            absolutePath: $implementationModule->absolutePath, 
            moduleRole: $implementationModule->moduleRole,
            moduleName: $moduleInterfaceName
        );
        $overrideDependencies = [];
        $actualDepdencies = $overrideModule->dependencies;
        // To avoid infinite loop, we need to filter out the interface itself
        // if we do not do this, $this->bundleDependencies will process the interface again
        // and again, leading to an infinite loop. 
        // The implementing class uses implements keyword, which includes the interface 
        // as dependency of the module, but it's not an actual dependency, but rather 
        // a type definition.
        foreach ($actualDepdencies as $dependencyModel) {
            $dependencyAbsolutePath = $this->normalizePath($dependencyModel->absolutePath);
            if ($dependencyAbsolutePath !== $moduleInterfaceAbsolutePath) {
                $overrideDependencies[] = $dependencyModel;
            }
        }
        return $this->moduleFactory->create(
            absolutePath: $implementationModule->absolutePath,  
            moduleRole: $implementationModule->moduleRole,
            moduleName: $moduleInterfaceName,
            dependencies: new DependencyIterator($overrideDependencies)
        );
    }

    /**
     * Check if the module is a non-implementable interface, 
     * basically, just a type definition.
     * @param \Kenjiefx\ScratchPHP\App\Pages\PageModel $pageModel
     * @param \Kenjiefx\Pluncext\Modules\ModuleModel $moduleModel
     * @return bool
     */
    public function isNonImplementableInterface(
        PageModel $pageModel,
        ModuleModel $moduleModel,
        BindingRegistry $bindingRegistry
    ) {
        if (!$bindingRegistry->hasImplementation($moduleModel)) {
            return true;
        }
        return $this->pluncObjectService->getPluncObjectName(
            $pageModel->theme, $moduleModel
        ) !== null;
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

    public function normalizePath(string $path): string {
        return str_replace('\\', '/', $path);
    }

}