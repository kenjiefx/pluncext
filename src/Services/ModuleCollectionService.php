<?php 

namespace Kenjiefx\Pluncext\Services;

use Kenjiefx\Pluncext\Modules\ModuleFactory;
use Kenjiefx\Pluncext\Modules\ModuleNamespaceRegistry;
use Kenjiefx\Pluncext\Modules\ModuleRegistry;
use Kenjiefx\Pluncext\Modules\ModuleRole;
use Kenjiefx\ScratchPHP\App\Configurations\ConfigurationInterface;
use Kenjiefx\ScratchPHP\App\Directories\DirectoryService;
use Kenjiefx\ScratchPHP\App\Themes\ThemeFactory;
use Kenjiefx\ScratchPHP\App\Themes\ThemeService;

class ModuleCollectionService {

    private static bool $collected = false;

    public function __construct(
        public readonly ModuleNamespaceRegistry $moduleNamespaceRegistry,
        public readonly DirectoryService $directoryService,
        public readonly ModuleFactory $moduleFactory,
        public readonly ModuleRegistry $moduleRegistry,
    ) {}

    public function collect() {
        if (static::$collected) return;
        $namespaceDirs = $this->moduleNamespaceRegistry->getAllNamespaces();
        foreach ($namespaceDirs as $moduleRole => $namespaceDir) {
            // convert the string to ModuleRole enum
            if (!ModuleRole::tryFrom($moduleRole)) {
                throw new \InvalidArgumentException("Invalid module role: {$moduleRole}");
            }
            $moduleRoleEnum = ModuleRole::from($moduleRole);
            $this->collectFromDirAndRegister($moduleRoleEnum, $namespaceDir);
        }
        static::$collected = true;
    }

    /**
     * Collects modules from a directory and registers them in the module registry.
     *
     * @param ModuleRole $moduleRole The role of the module being collected.
     * @param string $dirPath The path to the directory containing the modules.
     */
    private function collectFromDirAndRegister(
        ModuleRole $moduleRole,
        string $dirPath
    ) {
        if (!$this->directoryService->isDirectory($dirPath)) {
            return;
        }
        $files = $this->directoryService->listFiles($dirPath);
        foreach ($files as $file) {
            $filePath = "{$dirPath}/{$file}";
            if ($this->directoryService->isDirectory($filePath)) {
                $this->collectFromDirAndRegister($moduleRole, $filePath);
                continue;
            }
            if (!str_ends_with($filePath, '.js')) {
                continue;
            }
            $moduleModel = $this->moduleFactory->create(
                absolutePath: $filePath,
                moduleRole: $moduleRole
            );
            $this->moduleRegistry->register($moduleModel);
        }
    }

}