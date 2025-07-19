<?php

namespace Kenjiefx\Pluncext;

use Kenjiefx\Pluncext\Services\ModuleCollector;
use Kenjiefx\ScratchPHP\App\Configurations\ConfigurationInterface;
use Kenjiefx\ScratchPHP\App\Events\ListensTo;
use Kenjiefx\ScratchPHP\App\Events\PageBuildStartedEvent;
use Kenjiefx\ScratchPHP\App\Extensions\ExtensionsInterface;
use Kenjiefx\ScratchPHP\App\Themes\ThemeFactory;
use Kenjiefx\ScratchPHP\App\Themes\ThemeService;

class PluncExtension implements ExtensionsInterface {

    public function __construct(
        private ModuleCollector $moduleCollector,
        private ConfigurationInterface $configuration,
        private ThemeFactory $themeFactory,
        private ThemeService $themeService
    ) {}

    #[ListensTo(PageBuildStartedEvent::class)]
    public function onPageBuildStart(PageBuildStartedEvent $event): void {
        $moduleRegistry = $this->moduleCollector->collect(
            $this->getThemeDir()
        );
        $modules = $moduleRegistry->getAll();
        foreach ($modules as $module) {
            
        }
    }

    public function getThemeDir(){
        $themeName = $this->configuration->getThemeName();
        $themeModel = $this->themeFactory->create($themeName);
        return $this->themeService->getThemeDir($themeModel);
    }

}