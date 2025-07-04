<?php 

namespace Kenjiefx\Pluncext\Modules;

/**
 * ModuleRegistry is a singleton class that manages the registration and retrieval of ModuleModel instances.
 * It allows for registering modules by their absolute path and provides methods to find and retrieve them.
 */
class ModuleRegistry {

    private static array $registry = [];

    public function __construct(

    ) {}

    public function register(
        ModuleModel $moduleModel
    ) {
        $absolutePath = $moduleModel->absolutePath;
        // Normalize the absolute path to ensure consistent registration
        $absolutePath = realpath($absolutePath) ?: $absolutePath;
        if (!isset(self::$registry[$absolutePath])) {
            self::$registry[$absolutePath] = $moduleModel;
        }
    }

    public function findByPath(
        string $absolutePath
    ): ?ModuleModel {
        // Normalize the absolute path to ensure consistent retrieval
        $absolutePath = realpath($absolutePath) ?: $absolutePath;
        return self::$registry[$absolutePath] ?? null;
    }

    public function getAll(): array {
        return self::$registry;
    }

    public function clear() {
        self::$registry = [];
    }

}