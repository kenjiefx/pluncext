<?php 

namespace Kenjiefx\Pluncext\API;

use Kenjiefx\Pluncext\Services\PathShortNamePool;
use Kenjiefx\ScratchPHP\App\Builders\BuildMessage;
use Kenjiefx\ScratchPHP\App\Builders\BuildMessageChannel;
use Kenjiefx\ScratchPHP\App\Components\ComponentFactory;
use Kenjiefx\ScratchPHP\App\Components\ComponentService;
use Kenjiefx\ScratchPHP\Container;
use Kenjiefx\ScratchPHP\App\Configurations\ConfigurationInterface;
use Kenjiefx\ScratchPHP\App\Themes\ThemeFactory;
use Kenjiefx\ScratchPHP\App\Themes\ThemeService;
use Kenjiefx\ScratchPHP\App\Themes\THemeModel;

class Component {

    public static $registry = [];
    private static string $componentDir = "";
    private static ThemeModel $themeModel;
    private static ComponentService $componentService;
    private static PathShortNamePool $pathShortNamePool;

    public function __construct(
        ConfigurationInterface $configuration,
        ComponentService $componentService,
        ThemeFactory $themeFactory,
        PathShortNamePool $pathShortNamePool
    ) {
        $themeName = $configuration->getThemeName();
        $themeModel = $themeFactory->create($themeName);
        self::$themeModel = $themeModel;
        self::$componentService = $componentService;
        self::$pathShortNamePool = $pathShortNamePool;
    }

    public static function register(
        string $name, 
        string $classlist = '', 
        string|null $as = null, 
        string $tag = 'section'
    ) {
        $componentModel = ComponentFactory::create($name);
        $componentPath = self::$componentService->getJsPath(
            $componentModel, self::$themeModel
        );
        $unique = self::$pathShortNamePool->getShortName($componentPath->path);
        $normalizedPath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $componentPath->path);
        if (!isset(self::$registry[$normalizedPath])) {
            self::$registry[$normalizedPath] = $componentModel;
        }
        $alias = $as ? ' as '.$as : '';
        echo "<{$tag} plunc-component=\"{$unique}{$alias}\" class=\"{$classlist}\"></{$tag}>";
    }

    public static function export() {
        $completed = false;
        $accumulator = [];
        while (!$completed) {
            foreach (self::$registry as $normalizedPath => $componentModel) {
                $unique = self::$pathShortNamePool->getShortName($normalizedPath);
                $name = $componentModel->namespace;
                if (in_array($name, $accumulator)) continue;
                array_push($accumulator, $name);
                echo "<template plunc-name=\"{$unique}\" plunc-namespace=\"{$name}\">";
                component($name);
                echo '</template>';
            }
            $completed = count(self::$registry) === count($accumulator);
        }
    }

    public static function clear(){
        self::$registry = [];
    }

}