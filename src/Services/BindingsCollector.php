<?php 

namespace Kenjiefx\Pluncext\Services;

use Kenjiefx\Pluncext\Bindings\BindingModel;
use Kenjiefx\Pluncext\Bindings\BindingRegistry;
use Kenjiefx\Pluncext\Modules\ModuleRegistry;
use Kenjiefx\Pluncext\Modules\ModuleRole;
use Kenjiefx\ScratchPHP\App\Interfaces\ThemeServiceInterface;
use Kenjiefx\ScratchPHP\App\Pages\PageData;
use Kenjiefx\ScratchPHP\App\Pages\PageModel;
use Kenjiefx\ScratchPHP\App\Themes\ThemeModel;

class BindingsCollector {

    public function __construct(
        private ThemeServiceInterface $themeService,
        private ModuleRoleService $moduleRoleService
    ) {}

    public function collect(
        ModuleRegistry $moduleRegistry,
        PageModel $pageModel,
    ) {
        $bindings = $this->getGlobalBindings($pageModel->theme);
        $bindings = $this->overrideBindings($pageModel->data, $bindings);
        $bindingRegistry = new BindingRegistry();
        foreach ($bindings as $interfaceModulePath => $implementationModulePath) {
            $interfaceAbsolutePath = $this->convertToAbsolutePath(
                $pageModel->theme, $interfaceModulePath
            );
            $interfaceModule = $moduleRegistry->findByPath($interfaceAbsolutePath);
            if ($interfaceModule === null) {
                throw new \Exception(
                    "Interface module not found for path: {$interfaceModulePath}"
                );
            }
            $implementationAbsolutePath = $this->convertToAbsolutePath(
                $pageModel->theme, $implementationModulePath
            );
            $implementationModule = $moduleRegistry->findByPath($implementationAbsolutePath);
            if ($implementationModule === null) {
                throw new \Exception(
                    "Implementation module not found for path: {$implementationModulePath}"
                );
            }
            $bindingModel = new BindingModel(
                interface: $interfaceModule,
                implementation: $implementationModule
            );
            $bindingRegistry->register($bindingModel);
        }
        return $bindingRegistry;
    }

    public function convertToAbsolutePath(
        ThemeModel $themeModel,
        string $path
    ) {
        if (str_starts_with($path, "/")) {
            $path = ltrim($path, "/");
        }
        if (!$this->isValidRelativePath($path)) {
            throw new \Exception("Invalid bindings path: {$path}" );
        }
        $themeDir = $this->themeService->getThemeDir($themeModel);
        if (!str_ends_with($themeDir, "/")) {
            $themeDir .= "/";
        }
        return "{$themeDir}{$path}";
    }

    public function isValidRelativePath(string $path) {
        $path = trim($path);
        if (empty($path)) {
            return false;
        }
        // Check if the path starts with a valid role base dir name
        $roleBaseDirNames = $this->moduleRoleService->getAllBaseDirNames();
        foreach ($roleBaseDirNames as $baseDirName) {
            if (str_starts_with($path, $baseDirName)) {
                return true;
            }
        }
        return false;
    }

    public function overrideBindings(
        PageData $pageData,
        array $globalBindings
    ) {
        $pageBindings = $pageData["bindings"] ?? [];
        foreach ($pageBindings as $interfaceModulePath => $implementationModulePath) {
            $globalBindings[$interfaceModulePath] = $implementationModulePath;
        }
        return $globalBindings;
    }

    public function getGlobalBindings(
        ThemeModel $themeModel,
    ) {
        $bindingJsonPath = $this->getBindingsJsonPath($themeModel);
        return $this->parseBindingJson($bindingJsonPath);
    }

    public function parseBindingJson(
        string $bindingsJsonPath
    ): array {
        if (!file_exists($bindingsJsonPath)) {
            return [];
        }
        $bindingsJson = file_get_contents($bindingsJsonPath);
        if ($bindingsJson === false) {
            throw new \Exception(
                "Failed to read bindings.json from {$bindingsJsonPath}"
            );
        }
        $bindings = json_decode($bindingsJson, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception(
                "Failed to parse bindings.json: " . json_last_error_msg()
            );
        }
        return $bindings;
    }
    
    /**
     * Get the path to the bindings.json file
     * @param ThemeModel $themeModel
     * @return string
     */
    public function getBindingsJsonPath(
        ThemeModel $themeModel
    ) {
        $themeDir = $this->themeService->getThemeDir($themeModel);
        $interfacesDir = $this->moduleRoleService->getBaseDirByRole(
            $themeDir, ModuleRole::INTERFACE
        );
        return "{$interfacesDir}/bindings.json";
    }

}