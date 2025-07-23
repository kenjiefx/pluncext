<?php

namespace Kenjiefx\Pluncext\Services;

use Kenjiefx\Pluncext\Modules\ModuleRole;

class ModuleRoleService {

    public function __construct(

    ) {}

    public function getBaseDirByRole(
        string $themeDir,
        ModuleRole $moduleRole
    ): string {
        return match ($moduleRole) {
            ModuleRole::SERVICE => "{$themeDir}/services",
            ModuleRole::CONTROLLER => "{$themeDir}/controllers",
            ModuleRole::INTERFACE => "{$themeDir}/interfaces",
            ModuleRole::VIEW => "{$themeDir}/views",
            ModuleRole::MODEL => "{$themeDir}/models",
            ModuleRole::REPOSITORY => "{$themeDir}/repositories",
            ModuleRole::FACTORY => "{$themeDir}/factories",
            ModuleRole::HELPER => "{$themeDir}/blocks",
            ModuleRole::COMPONENT => "{$themeDir}/components"
        };
    }

    public function getBaseDirNameByRole(
        string $themeDir,
        ModuleRole $moduleRole
    ) {
        return match ($moduleRole) {
            ModuleRole::SERVICE => 'services',
            ModuleRole::CONTROLLER => 'controllers',
            ModuleRole::INTERFACE => 'interfaces',
            ModuleRole::VIEW => 'views',
            ModuleRole::MODEL => 'models',
            ModuleRole::REPOSITORY => 'repositories',
            ModuleRole::FACTORY => 'factories',
            ModuleRole::HELPER => 'blocks',
            ModuleRole::COMPONENT => 'components'
        };
    }

    /**
     * Get the ModuleRole based on the directory path
     * @param string $path
     * @throws \InvalidArgumentException
     * @return ModuleRole
     */
    public function getRoleByBaseDir(
        string $themeDir,
        string $path
    ): ModuleRole {
        // Map directory suffixes to their corresponding ModuleRole
        $directoryRoleMap = [
            'services' => ModuleRole::SERVICE,
            'controllers' => ModuleRole::CONTROLLER,
            'interfaces' => ModuleRole::INTERFACE,
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
     * Get the ModuleRole based on the module absolute path. For example, 
     * "/var/www/html/pluncext/theme/theme_name/services/MyService.php" 
     * will return ModuleRole::SERVICE where "services" is the directory name,
     * and "/var/www/html/pluncext/theme/theme_name/" is the theme directory.
     * @param string $absolutePath
     * @throws \InvalidArgumentException
     * @return ModuleRole
     */
    public function getRoleByModulePath(
        string $themeDir,
        string $absolutePath
    ) {
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
        return $this->getRoleByBaseDir(
            $themeDir, "{$themeDir}/{$parts[0]}"
        );
    }

    /**
     * Get all namespaces for the theme.
     * @return array<string, string>
     */
    public function getAllRoleBaseDirs(string $themeDir): array {
        return [
            ModuleRole::SERVICE->value => "{$themeDir}/services",
            ModuleRole::CONTROLLER->value => "{$themeDir}/controllers",
            ModuleRole::INTERFACE->value => "{$themeDir}/interfaces",
            ModuleRole::VIEW->value => "{$themeDir}/views",
            ModuleRole::MODEL->value => "{$themeDir}/models",
            ModuleRole::REPOSITORY->value => "{$themeDir}/repositories",
            ModuleRole::FACTORY->value => "{$themeDir}/factories",
            ModuleRole::HELPER->value => "{$themeDir}/blocks",
            ModuleRole::COMPONENT->value => "{$themeDir}/components"
        ];
    }

}