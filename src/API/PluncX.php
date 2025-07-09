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
use \Kenjiefx\ScratchPHP\App\Components\ComponentModel;

class PluncX {

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

    public static function namespace(string $dirpath): string {
        return self::$pathShortNamePool->getShortName($dirpath);
    }

}