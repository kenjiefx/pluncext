<?php 

namespace Kenjiefx\Pluncext\Modules;

use Kenjiefx\Pluncext\Dependencies\DependencyFactory;
use Kenjiefx\Pluncext\Dependencies\DependencyIterator;
use Kenjiefx\Pluncext\Services\TypeScriptParser;
use Symfony\Component\Filesystem\Filesystem;

class ModuleFactory {

    public function __construct(
        private Filesystem $filesystem,
        private TypeScriptParser $typeScriptParser,
        private DependencyFactory $dependencyFactory,
    ) {}

    public function create(
        string $absolutePath,
        ModuleRole $moduleRole,
    ) {
        $content = $this->filesystem->readFile($absolutePath);
        $dependencies = $this->parseDependencies($content, $absolutePath);
        $moduleName = $this->getFilenameWithoutExt($absolutePath);
        return new ModuleModel(
            absolutePath: $absolutePath,
            name: $moduleName,
            moduleRole: $moduleRole,
            dependencies: $dependencies
        );
    }

    public function parseDependencies(
        string $moduleContent,
        string $moduleAbsolutePath
    ): DependencyIterator {
        $importStatements = $this->typeScriptParser->extractImportStatements($moduleContent);
        $dependencyModels = [];
        foreach ($importStatements as $importStatement) {
            $dependencyModel = $this->dependencyFactory->createFromImportStatement(
                $importStatement, $moduleAbsolutePath
            );
            $dependencyModels[] = $dependencyModel;
        }
        return new DependencyIterator($dependencyModels);
    }

    private function getFilenameWithoutExt(
        string $absolutePath
    ): string {
        $pathInfo = pathinfo($absolutePath);
        return $pathInfo['filename'] ?? '';
    }

}