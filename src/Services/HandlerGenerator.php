<?php

namespace Kenjiefx\Pluncext\Services;

use Kenjiefx\Pluncext\Modules\ModuleModel;
use Kenjiefx\Pluncext\Modules\ModuleRegistry;
use Symfony\Component\Filesystem\Filesystem;

class HandlerGenerator {

    public function __construct(
        private TypeScriptParser $typeScriptParser,
        private Filesystem $filesystem,
    ) {}

    public function generate(
        ModuleModel $moduleModel,
        ModuleRegistry $moduleRegistry
    ) {
        $this->getConstructorDependencies(
            $moduleModel,
            $moduleRegistry
        );
    }

    public function getConstructorDependencies(
        ModuleModel $moduleModel,
        ModuleRegistry $moduleRegistry
    ) {
        $moduleTsContent = $this->filesystem->readFile($moduleModel->absolutePath);
        $constructorArgs = $this->typeScriptParser->getArgumentOfFirstClassConstructor($moduleTsContent);
        /** @var array<string> - the type declared for each constructor argument */
        $constructorTypes = [];
        foreach ($constructorArgs as $constructorArg) {
            $parts = explode(':', $constructorArg);
            if (!isset($parts[1])) {
                $message = "Constructor argument '{$constructorArg}' type declaration is strictly required.";
                throw new \Exception($message);
            }
            $constructorTypes[] = trim($parts[1]);
        }
        echo "<pre>";
        echo $moduleTsContent . PHP_EOL;
        print_r($constructorTypes);
        echo "</pre>";
    }

    public function createPlaceholderScript(
        ModuleModel $moduleModel,
    ): string {
        $role = $moduleModel->moduleRole->value;
        $name = $moduleModel->name;
        $nameAlias = "";
        return <<<EOT
        app.{$role}("{$nameAlias}", (===DEPENDENCY_NAMEALIASES===) => {
            ===IMPORT_VARIABLE_REFERENCE_MAPPINGS===
            ===HANDLER_CONTENT===
            ===HANDLER_RETURN_STATEMENT===
        });
        EOT;
    }

}