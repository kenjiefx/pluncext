<?php 

namespace Kenjiefx\Pluncext\Services;

use Kenjiefx\Pluncext\Modules\ModuleFactory;
use Kenjiefx\Pluncext\Modules\ModuleIterator;
use Kenjiefx\Pluncext\Modules\ModuleRegistry;
use Kenjiefx\Pluncext\Modules\ModuleRole;
use Kenjiefx\ScratchPHP\App\Utils\DirectoryService;
use Symfony\Component\Filesystem\Filesystem;

class ModuleCollector {

    public function __construct(
        private GlobService $globService,
        private Filesystem $fileSystem,
        private DirectoryService $directoryService,
        private ModuleRoleService $moduleRoleService,
        private ModuleFactory $moduleFactory,
    ) {}

    public function collect(string $themeDir) {
        /**
         * @var \Kenjiefx\Pluncext\Modules\ModuleModel[] $moduleModels
         */
        $moduleModels = [];
        $roleBaseDirs = $this->moduleRoleService->getAllRoleBaseDirs($themeDir);
        foreach ($roleBaseDirs as $moduleRole => $baseDir) {
            // convert the string to ModuleRole enum
            if (!ModuleRole::tryFrom($moduleRole)) {
                throw new \InvalidArgumentException("Invalid module role: {$moduleRole}");
            }
            $moduleRoleEnum = ModuleRole::from($moduleRole);
            $moduleTsFiles = [];
            $this->getTsFiles($baseDir, $moduleTsFiles);
            foreach ($moduleTsFiles as $moduleTsFile) {
                $moduleModel = $this->moduleFactory->create(
                    $moduleTsFile, $moduleRoleEnum
                );
                $moduleModels[] = $moduleModel;
            }
        }
        $moduleIterator = new ModuleIterator($moduleModels);
        return new ModuleRegistry($moduleIterator);
    }

    /**
     * Recursively collects all TypeScript files from a directory and its subdirectories.
     *
     * @param string $directory The directory to start searching for TypeScript files.
     * @param array $tsFilePaths Reference to an array that will hold the paths of found TypeScript files.
     */
    public function getTsFiles(
        string $directory,
        array &$tsFilePaths
    ) {
        $files = $this->directoryService->listFiles($directory);
        foreach ($files as $file) {
            $filePath = "{$directory}/{$file}";
            if ($this->directoryService->isDirectory($filePath)) {
                $this->getTsFiles($filePath, $tsFilePaths);
                continue;
            }
        }
        $allTypescriptFilesinDir = $this->globService->glob(
            "{$directory}/*.ts",
            GLOB_BRACE | GLOB_NOSORT
        );
        foreach ($allTypescriptFilesinDir as $tsFile) {
            if (str_ends_with($tsFile, '.ts')) {
                $tsFilePaths[] = $tsFile;
            }
        }
    }

}