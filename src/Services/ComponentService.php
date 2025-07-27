<?php 

namespace Kenjiefx\Pluncext\Services;

use Kenjiefx\Pluncext\ComponentProxy\ComponentProxyRegistry;
use Kenjiefx\ScratchPHP\App\Components\ComponentFactory;
use Kenjiefx\ScratchPHP\App\Interfaces\ThemeServiceInterface;
use Kenjiefx\ScratchPHP\App\Pages\PageModel;

class ComponentService {

    public function __construct(
        private ThemeServiceInterface $themeService,
        private ComponentFactory $componentFactory,
        private NameAliasPoolService $nameAliasPoolService
    ) {}

    public function createReferenceElement(
        PageModel $pageModel,
        string $name,
        string $classlist = '', 
        string|null $as = null, 
        string $tag = 'section'
    ) {
        $componentModel 
            = $this->componentFactory->create(
                $name, []
            );
        $componentJsPath = $this->themeService->getComponentJsPath(
            $pageModel->theme, $componentModel
        );
        $componentTsPath = $this->convertJsToTsPath($componentJsPath);
        $uniqueName = $this->nameAliasPoolService->getAliasOfPath($componentTsPath);
        $alias = $as ? ' as '.$as : '';
        return "<{$tag} plunc-component=\"{$uniqueName}{$alias}\" class=\"{$classlist}\"></{$tag}>";
    }

    public function createTemplateElements(
        PageModel $pageModel,
        ComponentProxyRegistry $componentProxyRegistry
    ) {
        $templates = "";
        foreach ($componentProxyRegistry->getAll() as $componentProxy) {
            $componentModel = $componentProxy->component;
            $componentName = $componentModel->name;
            $componentJsPath = $this->themeService->getComponentJsPath(
                $pageModel->theme, $componentModel
            );
            $componentTsPath = $this->convertJsToTsPath($componentJsPath);
            $uniqueName = $this->nameAliasPoolService->getAliasOfPath($componentTsPath);
            $templates .= "<template plunc-name=\"{$uniqueName}\" plunc-namespace=\"{$componentName}\">";
            $templates .= $componentProxy->content;
            $templates .= "</template>";
        }
        return $templates;
    }

    public function convertJsToTsPath(
        string $jsPath
    ) {
        return str_replace(
            '.js', '.ts', $jsPath
        );
    }

}