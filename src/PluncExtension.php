<?php 

namespace Kenjiefx\Pluncext;

use Kenjiefx\Pluncext\Services\ModuleCollectionService;
use Kenjiefx\Pluncext\Services\PluncAppBundleService;
use Kenjiefx\Pluncext\Services\TypeScriptService;
use Kenjiefx\ScratchPHP\App\Events\ComponentHTMLCollectedEvent;
use Kenjiefx\ScratchPHP\App\Events\ComponentHTMLCreatedEvent;
use Kenjiefx\ScratchPHP\App\Events\ComponentJSCollectedEvent;
use Kenjiefx\ScratchPHP\App\Events\ComponentJSCreatedEvent;
use Kenjiefx\ScratchPHP\App\Events\JSBuildCompletedEvent;
use Kenjiefx\ScratchPHP\App\Events\ListensTo;
use Kenjiefx\ScratchPHP\App\Events\PageBuildStartedEvent;
use Kenjiefx\ScratchPHP\App\Events\SettingsRegisteredEvent;
use Kenjiefx\ScratchPHP\App\Extensions\ExtensionsInterface;
use Kenjiefx\Pluncext\API\Component;

class PluncExtension implements ExtensionsInterface {

    public function __construct(
        public readonly ModuleCollectionService $moduleCollectionService,
        public readonly PluncAppBundleService $pluncAppBundleService,
        public readonly PluncExtensionSettings $settings,
        public readonly TypeScriptService $typeScriptService,
        public readonly Component $component
    ) {
        
    }

    #[ListensTo(SettingsRegisteredEvent::class)]
    public function onExtSettingsRegistered(array $settings): void {
        $this->settings->load($settings);
    }

    #[ListensTo(PageBuildStartedEvent::class)]
    public function beforePageBuild(PageBuildStartedEvent $event){
        $this->typeScriptService->compile();
        $this->moduleCollectionService->collect();
        $this->pluncAppBundleService->newSession();
        $this->component::clear();
    }
    
    #[ListensTo(ComponentJSCollectedEvent::class)]
    public function onCollectJs(ComponentJSCollectedEvent $event){
        $componentModel = $event->getComponent();
        $this->pluncAppBundleService->registerComponent($componentModel);
        $event->updateContent("");
    }

    #[ListensTo(JSBuildCompletedEvent::class)]
    public function onBuildJS(JSBuildCompletedEvent $event){
        $event->updateContent(
            $this->pluncAppBundleService->getBundle()
        );
    }

    #[ListensTo(ComponentHTMLCollectedEvent::class)]
    public function onComponentCollected(ComponentHTMLCollectedEvent $event){
        $originalContent = $event->getContent();
        $proxyElement = $this->component::register(
            name: $event->getComponent()->namespace,
            content: $originalContent,
            classlist: $event->getData()['classlist'] ?? '', 
            as: $event->getData()['as'] ?? null,
            tag: $event->getData()['tag'] ?? 'section'
        );
        $event->updateContent($proxyElement);
    }

}