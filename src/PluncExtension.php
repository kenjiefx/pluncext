<?php

namespace Kenjiefx\Pluncext;

use Kenjiefx\Pluncext\ComponentProxy\ComponentProxyModel;
use Kenjiefx\Pluncext\ComponentProxy\ComponentProxyRegistry;
use Kenjiefx\Pluncext\Implementations\QuarkBundler\QuarkBundleService;
use Kenjiefx\Pluncext\Modules\ModuleRegistry;
use Kenjiefx\Pluncext\Services\ComponentService;
use Kenjiefx\Pluncext\Services\ModuleCollector;
use Kenjiefx\ScratchPHP\App\Events\Instances\ComponentHTMLCollectedEvent;
use Kenjiefx\ScratchPHP\App\Events\Instances\ExtensionSettingsRegisterEvent;
use Kenjiefx\ScratchPHP\App\Events\Instances\PageAfterBuildEvent;
use Kenjiefx\ScratchPHP\App\Events\Instances\PageBeforeBuildEvent;
use Kenjiefx\ScratchPHP\App\Events\Instances\PageHTMLBuildCompleteEvent;
use Kenjiefx\ScratchPHP\App\Events\Instances\PageJSBuildCompleteEvent;
use Kenjiefx\ScratchPHP\App\Events\ListensTo;
use Kenjiefx\ScratchPHP\App\Extensions\ExtensionInterface;
use Kenjiefx\ScratchPHP\App\Extensions\ExtensionSettings;
use Kenjiefx\ScratchPHP\App\Interfaces\ConfigurationInterface;
use Kenjiefx\ScratchPHP\App\Interfaces\ThemeServiceInterface;
use Kenjiefx\ScratchPHP\App\Themes\ThemeModel;

class PluncExtension implements ExtensionInterface {

    private ModuleRegistry $moduleRegistry;
    private ComponentProxyRegistry $componentProxyRegistry;

    public function __construct(
        private ModuleCollector $moduleCollector,
        private ConfigurationInterface $configuration,
        private ThemeServiceInterface $themeService,
        private QuarkBundleService $scriptBundlerInterface,
        private ComponentService $componentService
    ) {}
    
    #[ListensTo(ExtensionSettingsRegisterEvent::class)]
    public function onSettingsRegistry(ExtensionSettings $settings) {
        
    }

    #[ListensTo(PageBeforeBuildEvent::class)]
    public function beforePageBuild(PageBeforeBuildEvent $event): void {
        $this->componentProxyRegistry = new ComponentProxyRegistry();
        $this->moduleRegistry = $this->moduleCollector->collect(
            $this->getThemeDir()
        );
    }

    #[ListensTo(PageJSBuildCompleteEvent::class)]
    public function pageJsBuild(PageJSBuildCompleteEvent $event) {
        $event->content = $this->scriptBundlerInterface->bundle(
            $this->moduleRegistry,
            $event->pageModel
        );
    }

    #[ListensTo(ComponentHTMLCollectedEvent::class)]
    public function onComponentCollected(ComponentHTMLCollectedEvent $event){
        $pageModel = $event->page;
        $component = $event->component;
        $proxy = new ComponentProxyModel(
            $component, $event->content
        );
        $this->componentProxyRegistry->register($proxy);
        $content = $this->componentService->createReferenceElement(
            $pageModel, 
            $component->name, 
            $component->data["classlist"] ?? "",
            $component->adata["as"] ?? null,
            $component->data["tag"] ?? "section"
        );
        $event->content = $content;
    }

    #[ListensTo(PageHTMLBuildCompleteEvent::class)]
    public function onPageHtmlBuildComponent(PageHTMLBuildCompleteEvent $event) {
        $componentTemplates = $this->componentService->createTemplateElements(
            $event->pageModel, $this->componentProxyRegistry
        );
        $event->content = str_replace(
            "</body>",
            "{$componentTemplates}\n</body>",
            $event->content
        );
    }

    public function getThemeDir(){
        $themeName = $this->configuration->getThemeName();
        $themeModel = new ThemeModel($themeName);
        return $this->themeService->getThemeDir($themeModel);
    }

}