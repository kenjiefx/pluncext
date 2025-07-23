<?php 

namespace Kenjiefx\Pluncext\Implementations\DorkEngine;

use Kenjiefx\Pluncext\Modules\ModuleModel;
use Kenjiefx\Pluncext\Services\ModuleRoleService;
use Kenjiefx\ScratchPHP\App\Interfaces\ThemeServiceInterface;
use Kenjiefx\ScratchPHP\App\Pages\PageModel;
use Kenjiefx\ScratchPHP\App\Themes\ThemeModel;
use Symfony\Component\Filesystem\Filesystem;

/**
 * This services manages the Javascript files that are produced by 
 * running the `tsc` command on the TypeScript files.
 */
class JSOutputService {

    public function __construct(
        private Filesystem $filesystem,
        private ThemeServiceInterface $themeService,
        private ModuleRoleService $moduleRoleService
    ) {}

    /**
     * Locate the output path of the module's JavaScript file. 
     * Please check the `tsconfig.json` file for the `outDir` property.
     * 
     * @param ModuleModel $moduleModel
     * @param ThemeModel $themeModel
     * @return string The absolute path to the JavaScript output file.
     */
    public function locateModuleJsOutput(
        ModuleModel $moduleModel,
        ThemeModel $themeModel
    ) {
        $themeDir = $this->themeService->getThemeDir($themeModel);
        $moduleRole = $moduleModel->moduleRole;
        $typescriptPath = $this->normalizePath(
            $moduleModel->absolutePath
        );
        $moduleDir = $this->normalizePath(
            $this->moduleRoleService->getBaseDirByRole(
                $themeDir, $moduleRole
            )
        );
        $dirNameByRole = $this->moduleRoleService->getBaseDirNameByRole(
            $themeDir, $moduleRole
        );
        $outputDir = $this->normalizePath(
            $this->joinPath(
                $this->getOutputDir($themeModel), 
                $dirNameByRole
            )
        );
        $tempPath = str_replace(
            $moduleDir, 
            $outputDir, 
            $typescriptPath
        );
        return str_replace('.ts', '.js', $tempPath);
    }

    public function getOutputDir(
        ThemeModel $themeModel
    ) {
        $assetsDir = $this->themeService->getAssetsDir($themeModel);
        $outputDir = $this->joinPath($assetsDir, 'plunc');
        // Typescript "rootDir" value is appended to the "outDir" value
        // This is how the TypeScript compiler works.
        return $this->joinPath($outputDir, $themeModel->name);
    }

    public function joinPath(string $path1, string $path2): string {
        return rtrim($path1, '/') . '/' . ltrim($path2, '/');
    }

    public function normalizePath($path) {
        $path = str_replace('\\', '/', $path);
        $path = preg_replace('#/+#', '/', $path);
        return rtrim($path, '/');
    }

}