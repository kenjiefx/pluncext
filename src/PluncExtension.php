<?php

namespace Kenjiefx\Pluncext;

use Kenjiefx\Pluncext\Implementations\DorkEngine\DorkEngine;
use Kenjiefx\Pluncext\Interfaces\HandlerGeneratorInterface;
use Kenjiefx\Pluncext\Modules\ModuleRegistry;
use Kenjiefx\Pluncext\Services\ModuleCollector;
use Kenjiefx\Pluncext\Services\PluncAppBundler;
use Kenjiefx\ScratchPHP\App\Events\Instances\ExtensionSettingsRegisterEvent;
use Kenjiefx\ScratchPHP\App\Events\Instances\PageBeforeBuildEvent;
use Kenjiefx\ScratchPHP\App\Events\Instances\PageJSBuildCompleteEvent;
use Kenjiefx\ScratchPHP\App\Events\ListensTo;
use Kenjiefx\ScratchPHP\App\Extensions\ExtensionInterface;
use Kenjiefx\ScratchPHP\App\Extensions\ExtensionSettings;
use Kenjiefx\ScratchPHP\App\Interfaces\ConfigurationInterface;
use Kenjiefx\ScratchPHP\App\Interfaces\ThemeServiceInterface;
use Kenjiefx\ScratchPHP\App\Themes\ThemeModel;
use Kenjiefx\ScratchPHP\Container;

class PluncExtension implements ExtensionInterface {

    private ModuleRegistry $moduleRegistry;

    public function __construct(
        private ModuleCollector $moduleCollector,
        private ConfigurationInterface $configuration,
        private ThemeServiceInterface $themeService,
        private PluncAppBundler $pluncAppBundler
    ) {}
    
    #[ListensTo(ExtensionSettingsRegisterEvent::class)]
    public function onSettingsRegistry(ExtensionSettings $settings) {
        
    }

    #[ListensTo(PageBeforeBuildEvent::class)]
    public function beforePageBuild(PageBeforeBuildEvent $event): void {
        $this->moduleRegistry = $this->moduleCollector->collect(
            $this->getThemeDir()
        );
    }

    #[ListensTo(PageJSBuildCompleteEvent::class)]
    public function pageJsBuild(PageJSBuildCompleteEvent $event) {
        $this->pluncAppBundler->bundle(
            $this->moduleRegistry,
            $event->pageModel
        );
    }

    public function getThemeDir(){
        $themeName = $this->configuration->getThemeName();
        $themeModel = new ThemeModel($themeName);
        return $this->themeService->getThemeDir($themeModel);
    }

}