<?php 

namespace Kenjiefx\Pluncext\Services;

use Kenjiefx\Pluncext\Modules\ModuleModel;
use Kenjiefx\Pluncext\Modules\ModuleRegistry;
use Kenjiefx\Pluncext\Modules\ModuleRole;
use Kenjiefx\ScratchPHP\App\Files\FileFactory;
use Kenjiefx\ScratchPHP\App\Files\FileService;

class PluncHandlerGenerator {

    private static array $PluncAPIReferences = [
        'Pluncx.scope()'     => '$scope',
        'Pluncx.patch'       => '$patch',
        'Pluncx.block'       => '$block',
        'Pluncx.parent()'    => '$parent',
        'Pluncx.app()'       => '$app',
        'Pluncx.component()' => '$this',
        'Pluncx.reflect().namespace' => '',
    ];

    public function __construct(
        public readonly PathShortNamePool $pathShortNamePool,
        public readonly ModuleRegistry $moduleRegistry,
        public readonly FileService $fileService,
        public readonly FileFactory $fileFactory,
        public readonly ModuleDependencyService $moduleDependencyService
    ) {}

    public function generateScript(
        ModuleModel $moduleModel
    ){
        $placeholderScript = $this->createPlaceholderScript($moduleModel);
        $placeholderScript = $this->resolveDependencyShortNames($moduleModel, $placeholderScript);
        $placeholderScript = $this->importVariableReferenceMappings($moduleModel, $placeholderScript);
        $placeholderScript = $this->injectHandlerContent($moduleModel, $placeholderScript);
        return $placeholderScript;
    }

    public function getUsedAPIReferences(
        ModuleModel $moduleModel,
    ): array {
        $fileObject = $this->fileFactory->create($moduleModel->absolutePath);
        $scriptContent = $this->fileService->readFile($fileObject);
        $usedApiReferences = [];
        foreach (self::$PluncAPIReferences as $apiReference => $variableName) {
            if ($apiReference === "Pluncx.reflect().namespace") {
                // Skip this reference
                continue;
            }
            if (strpos($scriptContent, $apiReference) !== false) {
                array_push($usedApiReferences, $variableName);
            }
        }
        return $usedApiReferences;
    }
    
    public function convertApiReferences(
        ModuleModel $moduleModel,
        string $scriptContent
    ){
        foreach (self::$PluncAPIReferences as $apiReference => $variableName) {
            if ($apiReference === "Pluncx.reflect().namespace") {
                $moduleDir = dirname($moduleModel->absolutePath);
                $shortName = $this->pathShortNamePool->getShortName($moduleDir);
                $scriptContent = str_replace($apiReference, '"' . $shortName . '"', $scriptContent);
                continue;
            }
            $scriptContent = str_replace($apiReference, $variableName, $scriptContent);
        }
        return $scriptContent;
    }

    public function createPlaceholderScript(
        ModuleModel $moduleModel,
    ): string {
        $role = $moduleModel->moduleRole->value;
        $name = $moduleModel->name;
        $shortName = $this->pathShortNamePool->getShortName($moduleModel->absolutePath);
        return <<<EOT
        app.{$role}("{$shortName}", (===DEPENDENCY_SHORTNAMES===) => {
            ===IMPORT_VARIABLE_REFERENCE_MAPPINGS===
            ===HANDLER_CONTENT===
            ===HANDLER_RETURN_STATEMENT===
        });
        EOT;
    }

    public function resolveDependencyShortNames(
        ModuleModel $moduleModel,
        $placeholderScript
    ): string {
        $dependencies = $moduleModel->dependencies;
        $dependencyShortNames = [...$this->getUsedAPIReferences($moduleModel)];
        foreach ($dependencies as $dependency) {
            $dependencyShortNames[] = $this->pathShortNamePool->getShortName($dependency->absolutePath);
        }
        $dependencyStatements = implode(", ", $dependencyShortNames);
        return str_replace("===DEPENDENCY_SHORTNAMES===", $dependencyStatements, $placeholderScript);
    }

    public function importVariableReferenceMappings(
        ModuleModel $moduleModel,
        string $placeholderScript
    ): string {
        $referenceDeclarations = "";
        $dependencies = $moduleModel->dependencies;
        foreach ($dependencies as $dependency) {
            $dependencyShortName = $this->pathShortNamePool->getShortName($dependency->absolutePath);
            $dependencyAbsolutePath = $dependency->absolutePath;
            $dependencyModuleModel = $this->moduleRegistry->findByPath($dependencyAbsolutePath);
            $dependencyFullName = $dependencyModuleModel->name;
            // Special case for factory type
            if ($dependencyModuleModel->moduleRole === ModuleRole::FACTORY) {
                $referenceDeclarations .= "const {$dependencyFullName} = {$dependencyShortName};\n";
            } else {
                $referenceDeclarations .= "const {$dependencyFullName} = {$dependencyShortName}.{$dependencyFullName};\n";
            }
        }
        return str_replace("===IMPORT_VARIABLE_REFERENCE_MAPPINGS===", $referenceDeclarations, $placeholderScript);
    }

    public function injectHandlerContent(
        ModuleModel $moduleModel,
        string $placeholderScript
    ): string {
        $handlerContent = "";
        $fileObject = $this->fileFactory->create($moduleModel->absolutePath);
        $fileContent = $this->fileService->readFile($fileObject);
        $fileContent = $this->convertApiReferences($moduleModel, $fileContent);
        # Process each lines
        $lines = explode("\n", $fileContent);
        $exports = [];
        foreach ($lines as $line) {
            $trimmedLine = trim($line);
            // Skip empty lines
            if (empty($trimmedLine)) {
                continue;
            }
            // Skip comments
            if (str_starts_with($trimmedLine, "//") || str_starts_with($trimmedLine, "#")) {
                continue;
            }
            // Skip import statements
            $importData = $this->moduleDependencyService->parseImportStatement($line);
            if ($importData["location"] !== "") continue; 
            // If the line does not start with 'export ', we add it to the handler content
            if (substr($line, 0, 7) !== 'export ') {
                $handlerContent .= '    ' . rtrim($line).PHP_EOL;
                continue;
            }
            // If the line starts with 'export ', we extract the variable name
            $pattern = '/export\s+(?:const|let|var|function|class)\s+([a-zA-Z_$][a-zA-Z0-9_$]*)/';
            if (preg_match_all($pattern, $line, $matches)) {
                $variable = $matches[1];
                array_push($exports, ...$variable);
            } 
            $handlerContent .= '    ' .  substr($line, 7).PHP_EOL;
        }
        // If there are exports, we add a return statement at the end of the handler content
        $returnStatement = '';
        if ($moduleModel->moduleRole !== ModuleRole::FACTORY) {
            $returnStatement = '    return {'.PHP_EOL;
            $i = 1;
            foreach ($exports as $export) {
                $comma = ($i < count($exports)) ? ',' : '';
                $returnStatement .= '        ' . $export . ': '.$export.$comma.PHP_EOL;
                $i++;
            }
            $returnStatement .= '    }';
        } else {
            $returnStatement = '    return ' . $exports[0] . ';';
        }
        $placeholderScript = str_replace(
            '===HANDLER_RETURN_STATEMENT===',
            $returnStatement,
            $placeholderScript
        );
        return str_replace("===HANDLER_CONTENT===", $handlerContent, $placeholderScript);
    }

}