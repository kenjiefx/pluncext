<?php 

namespace Kenjiefx\Pluncext\Modules;

use Kenjiefx\Pluncext\Dependency\DependencyFactory;
use Kenjiefx\Pluncext\Dependency\DependencyIterator;
use Kenjiefx\Pluncext\Services\ModuleDependencyService;
use Kenjiefx\ScratchPHP\App\Files\FileFactory;
use Kenjiefx\ScratchPHP\App\Files\FileService;

class ModuleFactory {

    public function __construct(
        public readonly FileFactory $fileFactory,
        public readonly FileService $fileService,
        public readonly DependencyFactory $dependencyFactory,
        public readonly ModuleDependencyService $moduleDependencyService
    ) {}

    /**
     * Creates a new ModuleModel instance with the given absolute path.
     *
     * @param string $absolutePath The absolute path of the module.
     * @return ModuleModel A new instance of ModuleModel.
     */
    public function create(
        string $absolutePath,
        ModuleRole $moduleRole,
    ): ModuleModel {
        $file = $this->fileFactory->create($absolutePath);
        $moduleContent = $this->fileService->readFile($file);
        $importStatements = $this->moduleDependencyService->extractImportStmts($moduleContent);
        $dependencies = [];
        foreach ($importStatements as $importStatement) {
            $importData = $this->moduleDependencyService->parseImportStatement($importStatement);
            $importAbsPath = $this->moduleDependencyService->resolveImportPath($importData['location'], $absolutePath);
            if ($this->isPluncApi($importAbsPath)) {
                continue; // Skip Plunc API imports
            }
            $dependencies[] = $this->dependencyFactory->create(
                $importAbsPath, $importData['imports']
            );
        }
        $importFile = new ModuleModel (
            absolutePath: $absolutePath,
            name: $this->getFilenameWithoutExt($absolutePath),
            moduleRole: $moduleRole,
            dependencies: new DependencyIterator($dependencies)
        );
        return $importFile;
    }

    private function getFilenameWithoutExt(
        string $absolutePath
    ): string {
        $pathInfo = pathinfo($absolutePath);
        return $pathInfo['filename'] ?? '';
    }

    private function isPluncApi(
        string $absolutePath
    ): bool {
        return 
            str_contains($absolutePath, "/interfaces/pluncx.js") || 
            str_contains($absolutePath, "interfaces\pluncx.js");
    }

}