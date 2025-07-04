<?php 

namespace Kenjiefx\Pluncext\Modules;

use Kenjiefx\ScratchPHP\App\Configurations\ConfigurationInterface;
use Kenjiefx\ScratchPHP\App\Themes\ThemeFactory;
use Kenjiefx\ScratchPHP\App\Themes\ThemeService;

/**
 * ModuleNamespaceRegistry returns the namespace for a module based on its role.
 */
class ModuleNamespaceRegistry {

    public function __construct(
        public readonly ConfigurationInterface $configuration,
        public readonly ThemeFactory $themeFactory,
        public readonly ThemeService $themeService
    ) {}

    public function getNamespaceByRole(ModuleRole $moduleRole): string {
        $themeDir = $this->getThemeDir();
        return match ($moduleRole) {
            ModuleRole::SERVICE => "{$themeDir}/services",
            ModuleRole::CONTROLLER => "{$themeDir}/controllers",
            ModuleRole::VIEW => "{$themeDir}/views",
            ModuleRole::MODEL => "{$themeDir}/models",
            ModuleRole::REPOSITORY => "{$themeDir}/repositories",
            ModuleRole::FACTORY => "{$themeDir}/factories",
            ModuleRole::HELPER => "{$themeDir}/blocks",
            ModuleRole::COMPONENT => "{$themeDir}/components"
        };
    }

    /**
     * Get the ModuleRole based on the provided path.
     * @param string $path
     * @throws \InvalidArgumentException
     * @return ModuleRole
     */
    public function getRoleByNamespace(string $path): ModuleRole {
        $themeDir = $this->getThemeDir();
        // Map directory suffixes to their corresponding ModuleRole
        $directoryRoleMap = [
            'services' => ModuleRole::SERVICE,
            'controllers' => ModuleRole::CONTROLLER,
            'views' => ModuleRole::VIEW,
            'models' => ModuleRole::MODEL,
            'repositories' => ModuleRole::REPOSITORY,
            'factories' => ModuleRole::FACTORY,
            'blocks' => ModuleRole::HELPER,
            'components' => ModuleRole::COMPONENT,
        ];
        foreach ($directoryRoleMap as $folder => $role) {
            $expectedPath = "{$themeDir}/{$folder}";
            if (str_starts_with($path, $expectedPath)) {
                return $role;
            }
        }
        throw new \InvalidArgumentException("Unrecognized module path: {$path}");
    }

    /**
     * Get the ModuleRole based on the absolute path. For example, 
     * "/var/www/html/pluncext/theme/theme_name/services/MyService.php" 
     * will return ModuleRole::SERVICE where "services" is the directory name,
     * and "/var/www/html/pluncext/theme/theme_name/" is the theme directory.
     * @param string $absolutePath
     * @throws \InvalidArgumentException
     * @return ModuleRole
     */
    public function getRoleByPath(string $absolutePath) {
        $themeDir = $this->getThemeDir();
        // Check if the absolute path starts with the theme directory
        if (!str_starts_with($absolutePath, $themeDir)) {
            throw new \InvalidArgumentException("The absolute path does not start with the theme directory: {$themeDir}");
        }
        // Extract the relative path from the theme directory
        $relativePath = substr($absolutePath, strlen($themeDir));
        // Remove leading and trailing slashes
        $relativePath = trim($relativePath, '/');
        // Split the relative path into parts
        $parts = explode('/', $relativePath);
        // The first part should be the directory name
        if (empty($parts[0])) {
            throw new \InvalidArgumentException("The absolute path does not contain a valid module role: {$absolutePath}");
        }
        return $this->getRoleByNamespace("{$themeDir}/{$parts[0]}");
    }

    /**
     * Get all namespaces for the theme.
     * @return array<string, string>
     */
    public function getAllNamespaces(): array {
        $themeDir = $this->getThemeDir();
        return [
            ModuleRole::SERVICE->value => "{$themeDir}/services",
            ModuleRole::CONTROLLER->value => "{$themeDir}/controllers",
            ModuleRole::VIEW->value => "{$themeDir}/views",
            ModuleRole::MODEL->value => "{$themeDir}/models",
            ModuleRole::REPOSITORY->value => "{$themeDir}/repositories",
            ModuleRole::FACTORY->value => "{$themeDir}/factories",
            ModuleRole::HELPER->value => "{$themeDir}/blocks",
            ModuleRole::COMPONENT->value => "{$themeDir}/components"
        ];
    }
    
    public function getThemeDir(){
        $themeName = $this->configuration->getThemeName();
        $themeModel = $this->themeFactory->create($themeName);
        return $this->themeService->getThemeDir($themeModel);
    }

}