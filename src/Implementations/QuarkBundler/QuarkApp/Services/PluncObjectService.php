<?php 

namespace Kenjiefx\Pluncext\Implementations\QuarkBundler\QuarkApp\Services;

use Kenjiefx\Pluncext\Modules\ModuleModel;
use Kenjiefx\Pluncext\Modules\ModuleRole;
use Kenjiefx\Pluncext\Services\ModuleRoleService;
use Kenjiefx\ScratchPHP\App\Interfaces\ThemeServiceInterface;
use Kenjiefx\ScratchPHP\App\Themes\ThemeModel;

class PluncObjectService {

    public function __construct(
        private ModuleRoleService $moduleRoleService,
        private ThemeServiceInterface $themeService
    ) {}

    public function isPluncObject(
        ModuleModel $moduleModel
    ) {}

    public function getPluncObjectName(
        ThemeModel $themeModel,
        ModuleModel $moduleModel
    ): string | null {
        $themeDir = $this->themeService->getThemeDir($themeModel);
        $interfacesDir = $this->moduleRoleService->getBaseDirByRole(
            $themeDir, ModuleRole::INTERFACE
        );
        $moduleAbsolutePath = $this->normalizePath(
            $moduleModel->absolutePath
        );
        $pluncObjectNames = ["BlockService", "ComponentReflection", "ComponentScope", "PatchService", "PluncAppService", "PluncElement"];
        foreach ($pluncObjectNames as $pluncObjectName) {
            $pluncObjectPath = $this->normalizePath(
                "{$interfacesDir}/PluncAPI/{$pluncObjectName}.ts"
            );
            if ($moduleAbsolutePath === $pluncObjectPath) {
                return $pluncObjectName;
            }
        }
        return null;
    }

    /**
     * Normalizes a file path by replacing backslashes with forward slashes,
     * removing duplicate slashes, and trimming trailing slashes.
     *
     * @param string $path The file path to normalize.
     * @return string The normalized file path.
     */
    public function normalizePath($path) {
        $path = str_replace('\\', '/', $path);
        $path = preg_replace('#/+#', '/', $path);
        return rtrim($path, '/');
    }


}