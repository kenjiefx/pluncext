<?php 

namespace Kenjiefx\Pluncext\Implementations\DorkEngine;

use Kenjiefx\Pluncext\Dependencies\DependencyModel;
use Kenjiefx\Pluncext\Implementations\DorkEngine\Generators\DependencyListGenerator;
use Kenjiefx\Pluncext\Implementations\DorkEngine\Generators\ReturnStatementGenerator;
use Kenjiefx\Pluncext\Implementations\DorkEngine\Services\ConstructorService;
use Kenjiefx\Pluncext\Implementations\DorkEngine\Services\JSContentProcessor;
use Kenjiefx\Pluncext\Implementations\DorkEngine\Services\JSOutputService;
use Kenjiefx\Pluncext\Modules\ModuleFactory;
use Kenjiefx\Pluncext\Modules\ModuleIterator;
use Kenjiefx\Pluncext\Modules\ModuleModel;
use Kenjiefx\Pluncext\Modules\ModuleRegistry;
use Kenjiefx\Pluncext\Modules\ModuleRole;
use Kenjiefx\Pluncext\Services\ModuleRoleService;
use Kenjiefx\Pluncext\Services\NameAliasPoolService;
use Kenjiefx\ScratchPHP\App\Interfaces\ThemeServiceInterface;
use Kenjiefx\ScratchPHP\App\Pages\PageModel;
use Symfony\Component\Filesystem\Filesystem;

class DorkAppMainService {

    public function __construct(
        private ThemeServiceInterface $themeService,
        private ModuleFactory $moduleFactory,
        private NameAliasPoolService $nameAliasPoolService,
        private ModuleRoleService $moduleRoleServices,
        private DependencyListGenerator $dependencyListGenerator,
        private ConstructorService $constructorService,
        private ReturnStatementGenerator $returnStatementGenerator,
        private JSContentProcessor $jsContentProcessor,
        private JSOutputService $jsOutputService,
        private Filesystem $filesystem
    ) {}

    public function generateAppMain(
        ModuleRegistry $moduleRegistry,
        PageModel $pageModel
    ) {
        $templateTsPath = $this->getTemplateTsPath($pageModel);
        $moduleModel = $this->moduleFactory->create(
            $templateTsPath, 
            ModuleRole::ROOTAPP,
            'App'
        );
        $jsPath = $this->getJsPath($moduleModel, $pageModel);
        $jsContent = $this->getJsContent($jsPath);
        $dependencyModules = $this->getDependencies($moduleModel, $moduleRegistry);
        $placeHolderScript = $this->createPlaceholderScript($moduleModel);
        $placeHolderScript = $this->setHandlerContent(
            $placeHolderScript, $jsContent
        );
        $placeHolderScript = $this->setHandlerDependencies(
            $pageModel, $moduleRegistry, $dependencyModules, $placeHolderScript
        );
        $placeHolderScript = $this->setConstructorStatement(
            $dependencyModules, $moduleModel->name, $placeHolderScript
        );
        echo "<pre>";
        echo $placeHolderScript;
        echo "</pre>";
    }

    public function getDependencies(
        ModuleModel $moduleModel,
        ModuleRegistry $moduleRegistry
    ) {
        return $this->constructorService->getDependencies(
            $moduleModel, $moduleRegistry
        );
    }

    public function setHandlerDependencies(
        PageModel $pageModel,
        ModuleRegistry $moduleRegistry,
        ModuleIterator $dependencyModules,
        string $placeholderScript
    ) {
        $updatedDependencies = clone $dependencyModules;
        $this->addPluncAppServiceAsDependency(
            $pageModel, $updatedDependencies, $moduleRegistry
        );
        $dependencyStatement = 
            $this->dependencyListGenerator->generate($updatedDependencies);
        return str_replace(
            "===HANDLER_DEPENDENCIES===",
            $dependencyStatement,
            $placeholderScript
        );
    }

    public function setConstructorStatement(
        ModuleIterator $dependencyModules,
        string $classNameDeclared,
        string $placeholderScript
    ) {
        $returnStatement = $this->returnStatementGenerator->generateAsNewInstance(
            $classNameDeclared, $dependencyModules
        );
        $constructorStatement = str_replace(
            "return ", "", $returnStatement
        );
        return str_replace(
            "===HANDLER_CONSTRUCTION_STATEMENT===",
            $constructorStatement,
            $placeholderScript
        );
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

    public function getTemplateTsPath(
        PageModel $pageModel
    ): string {
        $themeModel = $pageModel->theme;
        $templateModel = $pageModel->template;
        $templatePhpPath = $this->themeService->getTemplatePath(
            $themeModel, $templateModel
        );
        return str_replace(
            ".php", ".ts", $templatePhpPath
        );
    }

    public function createPlaceholderScript(
        ModuleModel $moduleModel,
    ): string {
        return <<<JS
        app.component("App", (===HANDLER_DEPENDENCIES===) => {
        ===HANDLER_CONTENT===
            const appInstance = ===HANDLER_CONSTRUCTION_STATEMENT===
            \$app.ready(async () => {
                await appInstance.bootstrap();
            });
        });
        JS;
    }

    public function addPluncAppServiceAsDependency(
        PageModel $pageModel,
        ModuleIterator $dependencyModules,
        ModuleRegistry $moduleRegistry
    ) {
        $pluncAppServiceTsPath = $this->getPluncAppPath($pageModel);
        $pluncAppServiceTsPath = $this->normalizePath($pluncAppServiceTsPath);
        $isPluncAppServiceOneOfDependencies = false;
        foreach ($dependencyModules as $dependencyModule) {
            $dependencyPath = $dependencyModule->absolutePath;
            $dependencyPath = $this->normalizePath($dependencyPath);
            if ($dependencyPath === $pluncAppServiceTsPath) {
                $isPluncAppServiceOneOfDependencies = true;
                break;
            }
        }
        // No need to add if it's already a dependency
        if ($isPluncAppServiceOneOfDependencies) return;
        
        $pluncAppServiceModule = $moduleRegistry->findByPath(
            $pluncAppServiceTsPath
        );
        if ($pluncAppServiceModule === null) {
            throw new \Exception("PluncAppService module not found "
                . "at expected path: {$pluncAppServiceTsPath}"
            );
        }
        $dependencyModules->add($pluncAppServiceModule);
    }

    public function getJsPath(
        ModuleModel $moduleModel,
        PageModel $pageModel
    ) {
        return $this->jsOutputService->locateModuleJsOutput(
            $moduleModel, $pageModel->theme
        );
    }

    public function normalizePath(
        string $absolutePath
    ): string {
        // Normalize the absolute path to ensure consistent retrieval
        return str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $absolutePath);
    }

    public function getPluncAppPath(
        PageModel $pageModel
    ) {
        $themeDir = $this->themeService->getThemeDir(
            $pageModel->theme
        );
        $interfacesDir = $this->moduleRoleServices->getBaseDirByRole(
            $themeDir, ModuleRole::INTERFACE
        );
        return "{$interfacesDir}/PluncAPI/PluncAppService.ts";
    }

    public function getJsContent(
        string $jsPath
    ) {
        return $this->jsContentProcessor->cleanUpForWeb(
            $this->filesystem->readFile($jsPath)
        );
    }


}