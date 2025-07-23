<?php 

namespace Tests\Unit\Modules;

use Kenjiefx\Pluncext\Dependencies\DependencyFactory;
use Kenjiefx\Pluncext\Dependencies\DependencyModel;
use Kenjiefx\Pluncext\Services\TypeScriptParser;
use PHPUnit\Framework\TestCase;

class ModuleFactoryTest extends TestCase {

    /** @test */
    public function itShouldParseDependenciesCorrectly() {
        // Given we have a ModuleFactory and a TypeScript content with import statements
        $moduleContent = "";
        $moduleAbsolutePath = 'C://example/resolved/absolute/path.ts';
        $container = new \League\Container\Container();
        $container->delegate(new \League\Container\ReflectionContainer());
        $container->add(DependencyFactory::class, new class extends DependencyFactory {
            public function __construct() {}
            public function createFromImportStatement(string $importStatement, string $moduleAbsolutePath): DependencyModel {
                // For testing purposes, we can just return a mock DependencyModel
                return new DependencyModel (
                    absolutePath: $moduleAbsolutePath,
                    imports: [
                        'ModuleA' => 'ModuleA',
                        'ModuleB' => 'B',
                        'ModuleC' => 'ModuleC'
                    ]
                );
            }
        });
        $container->add(TypeScriptParser::class, new class extends TypeScriptParser {
            public function extractImportStatements(string $content): array {
                // For testing purposes, we can just return a mock array of import statements
                return [
                    "import { ModuleA, ModuleB as B } from 'module/dependency/location';",
                    "import { ModuleC } from './another/dependency/location';"
                ];
            }
        });
        $factory = $container->get(\Kenjiefx\Pluncext\Modules\ModuleFactory::class);
        
        // When we parse the dependencies from the module content
        $dependencies = $factory->parseDependencies($moduleContent, $moduleAbsolutePath);

        // Then we should have a DependencyIterator with the expected dependencies
        $this->assertInstanceOf(\Kenjiefx\Pluncext\Dependencies\DependencyIterator::class, $dependencies);
        $dependencyModels = iterator_to_array($dependencies);
        $this->assertEquals('ModuleA', $dependencyModels[0]->imports['ModuleA']);
        $this->assertEquals('B', $dependencyModels[0]->imports['ModuleB']);
        $this->assertEquals('ModuleC', $dependencyModels[1]->imports['ModuleC']);
        $this->assertEquals('C://example/resolved/absolute/path.ts', $dependencyModels[0]->absolutePath);

    }

}