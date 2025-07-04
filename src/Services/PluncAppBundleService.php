<?php 

namespace Kenjiefx\Pluncext\Services;

use Kenjiefx\Pluncext\Minifiers\TerserMinifier\TerserMinifier;
use Kenjiefx\Pluncext\Services\PluncHandlerGenerator;
use Kenjiefx\Pluncext\Modules\ModuleModel;
use Kenjiefx\Pluncext\Modules\ModuleRegistry;
use Kenjiefx\Pluncext\Modules\ModuleRole;
use Kenjiefx\ScratchPHP\App\Components\ComponentModel;
use Kenjiefx\ScratchPHP\App\Components\ComponentService;
use Kenjiefx\ScratchPHP\App\Configurations\ConfigurationInterface;
use Kenjiefx\ScratchPHP\App\Files\FileFactory;
use Kenjiefx\ScratchPHP\App\Files\FileService;
use Kenjiefx\ScratchPHP\App\Themes\ThemeFactory;
use Kenjiefx\ScratchPHP\App\Themes\ThemeService;

class PluncAppBundleService {

    /**
     * Stores registered components.
     * @var array
     */
    private static array $components = [];

    private static array $handlers = [];

    public function __construct(
        public readonly ComponentService $componentService,
        public readonly ConfigurationInterface $configuration,
        public readonly ThemeFactory $themeFactory,
        public readonly ThemeService $themeService,
        public readonly ModuleRegistry $moduleRegistry,
        public readonly PluncHandlerGenerator $pluncHandlerGenerator,
        public readonly FileFactory $fileFactory,
        public readonly FileService $fileService,
        public readonly TerserMinifier $minifier
    ) {}
    
    /**
     * Registers a component model to the service.
     * @param \Kenjiefx\ScratchPHP\App\Components\ComponentModel $componentModel
     * @return void
     */
    public function registerComponent(
        ComponentModel $componentModel
    ) {
        array_push(self::$components, $componentModel);
    }

    public function registerHandler(
        ModuleModel $moduleModel,
        string $handlerScript
    ) {
        $absolutePath = $moduleModel->absolutePath;
        if (!isset(self::$handlers[$absolutePath])) {
            self::$handlers[$absolutePath] = $handlerScript;
        }
    }

    /**
     * Clears the session data for components and handlers.
     * This is useful for resetting the state when a new session starts.
     */
    public function newSession() {
        self::$components = [];
        self::$handlers = [];
    }

    public function getBundle() {
        foreach (self::$components as $componentModel) {
            # echo $componentModel->namespace . "<br>";
            $themeModel = $this->getThemeModel();
            $jsAbsolutePath = $this->componentService->getJsPath($componentModel, $themeModel);
            $moduleModel = $this->moduleRegistry->findByPath($jsAbsolutePath->path);
            $this->handleModule($moduleModel);
        }
        $jsContent = "const app = plunc.create('app');\n";
        foreach (self::$handlers as $absolutePath => $handlerScript) {
            $jsContent .= $handlerScript . "\n";
        }
        return $this->minifier->minify($jsContent);
    }

    public function handleModule(
        ModuleModel $moduleModel
    ) {
        $handlerScript = $this->pluncHandlerGenerator->generateScript($moduleModel);
        $this->registerHandler($moduleModel, $handlerScript);
        foreach ($moduleModel->dependencies as $dependencyModel) {
            $dependencyModule = $this->moduleRegistry->findByPath($dependencyModel->absolutePath);
            $this->handleModule($dependencyModule);
        }
    }

    private function getThemeDir() {
        $themeModel = $this->getThemeModel();
        return $this->themeService->getThemeDir($themeModel);
    }

    private function getThemeModel() {
        $themeName = $this->configuration->getThemeName();
        return $this->themeFactory->create($themeName);
    }


}