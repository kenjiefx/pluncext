<?php 

namespace Kenjiefx\Pluncext\Implementations\DorkEngine;

use Kenjiefx\Pluncext\Implementations\DorkEngine\Generators\DependencyListGenerator;
use Kenjiefx\Pluncext\Implementations\DorkEngine\Generators\ReturnStatementGenerator;
use Kenjiefx\Pluncext\Implementations\DorkEngine\Services\ConstructorService;
use Kenjiefx\Pluncext\Implementations\DorkEngine\Services\JSContentProcessor;
use Kenjiefx\Pluncext\Implementations\DorkEngine\Services\JSOutputService;
use Kenjiefx\Pluncext\Modules\ModuleIterator;
use Kenjiefx\Pluncext\Modules\ModuleModel;
use Kenjiefx\Pluncext\Modules\ModuleRegistry;
use Kenjiefx\Pluncext\Modules\ModuleRole;
use Kenjiefx\Pluncext\Services\NameAliasPoolService;
use Kenjiefx\ScratchPHP\App\Pages\PageModel;
use Symfony\Component\Filesystem\Filesystem;

class DorkHandlerService {

    public function __construct(
        private ConstructorService $constructorService,
        private JSOutputService $jsOutputService,
        private NameAliasPoolService $nameAliasPoolService,
        private JSContentProcessor $jsContentProcessor,
        private Filesystem $filesystem,
        private ReturnStatementGenerator $returnStatementGenerator,
        private DependencyListGenerator $dependencyListGenerator
    ) {}

    public function generateHandler(
        ModuleRegistry $moduleRegistry,
        ModuleModel $moduleModel,
        PageModel $pageModel
    ): string {
        if ($moduleModel->moduleRole === ModuleRole::INTERFACE) {
            return "";
        }
        $jsPath = $this->getJsPath($moduleModel, $pageModel);
        $jsContent = $this->getJsContent($jsPath);
        $classNameDeclared = $this->jsContentProcessor->getClassDeclaration($jsContent);
        $dependencyModules = $this->getDependencies($moduleModel, $moduleRegistry);
        $placeholderScript = $this->createPlaceholderScript($moduleModel);
        $placeholderScript = $this->setHandlerContent(
            $placeholderScript, $jsContent
        );
        $placeholderScript = $this->setReturnStatement(
            $moduleModel, $dependencyModules, $classNameDeclared, $placeholderScript
        );
        $placeholderScript = $this->setHandlerDependencies(
            $dependencyModules, $placeholderScript
        );
        return $placeholderScript;
    }

    public function setHandlerContent(
        string $placeholderScript, 
        string $jsModuleContent
    ) {
        $lines = explode("\n", $jsModuleContent);
        $paddedContents = "";
        foreach ($lines as $line) {
            $paddedContents .= "    " . $line;
        }
        return str_replace(
            "===HANDLER_CONTENT===",
            $paddedContents,
            $placeholderScript
        );
    }

    public function setReturnStatement(
        ModuleModel $moduleModel, 
        ModuleIterator $dependencyModules,
        string $classNameDeclared,
        string $placeholderScript
    ) {
        if ($moduleModel->moduleRole === ModuleRole::FACTORY) {
            $returnStatement = $this->returnStatementGenerator->generateAsFactoryInstance(
                $classNameDeclared, $dependencyModules
            );
        } else {
            $returnStatement = $this->returnStatementGenerator->generateAsNewInstance(
                $classNameDeclared, $dependencyModules
            );
        }
        $returnStatement = "    " . $returnStatement;
        return str_replace(
            "===HANDLER_RETURN_STATEMENT===",
            $returnStatement,
            $placeholderScript
        );
    }

    public function setHandlerDependencies(
        ModuleIterator $dependencyModules,
        string $placeholderScript
    ) {
        $dependencyStatement = 
            $this->dependencyListGenerator->generate($dependencyModules);
        return str_replace(
            "===HANDLER_DEPENDENCIES===",
            $dependencyStatement,
            $placeholderScript
        );
    }

    public function getJsPath(
        ModuleModel $moduleModel,
        PageModel $pageModel
    ) {
        return $this->jsOutputService->locateModuleJsOutput(
            $moduleModel, $pageModel->theme
        );
    }

    public function getJsContent(
        string $jsPath
    ) {
        return $this->jsContentProcessor->cleanUpForWeb(
            $this->filesystem->readFile($jsPath)
        );
    }

    public function getDependencies(
        ModuleModel $moduleModel,
        ModuleRegistry $moduleRegistry
    ) {
        return $this->constructorService->getDependencies(
            $moduleModel, $moduleRegistry
        );
    }

    public function createPlaceholderScript(
        ModuleModel $moduleModel,
    ): string {
        $role = $moduleModel->moduleRole->value;
        $name = $moduleModel->name;
        $nameAlias = $this->nameAliasPoolService->getAliasOfPath(
            $moduleModel->absolutePath
        );
        return <<<EOT
        app.{$role}("{$nameAlias}", (===HANDLER_DEPENDENCIES===) => {
        ===HANDLER_CONTENT===
        ===HANDLER_RETURN_STATEMENT===
        });
        EOT;
    }

}