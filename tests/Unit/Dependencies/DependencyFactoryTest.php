<?php 

namespace Tests\Unit\Dependencies;

use Kenjiefx\Pluncext\Dependencies\DependencyFactory;
use Kenjiefx\Pluncext\Services\PathResolver;
use League\Container\Container;
use League\Container\ReflectionContainer;
use PHPUnit\Framework\TestCase;

class DependencyFactoryTest extends TestCase {

    /** @test */
    public function itShouldCreateDependencyModel() {

        // Given we have a DependencyFactory and an import statement
        $container = new Container();
        $container->delegate(new ReflectionContainer());
        $container->add(PathResolver::class, new class extends PathResolver {
            public function resolveRelativePath(string $relativePath, string $basePath): string {
                // For testing purposes, we can just return the relative path as absolute
                return 'C://example/resolved/absolute/path.ts';
            }
        });
        $factory = $container->get(DependencyFactory::class);

        $importStatement = "import { ModuleA, ModuleB as B } from 'module/dependency/location';";
        $expectedImports = [
            'ModuleA' => 'ModuleA',
            'ModuleB' => 'B'
        ];

        // When we create a DependencyModel from the import statement
        $dependencyModel = $factory->createFromImportStatement(
            $importStatement,
            'C://example/resolved/absolute/path.ts' // This is the absolute path of the importing module
        );

        // Then we should have a DependencyModel instance with the expected imports and absolute path
        $this->assertInstanceOf(\Kenjiefx\Pluncext\Dependencies\DependencyModel::class, $dependencyModel);
        $this->assertEquals($expectedImports, $dependencyModel->imports);
        $this->assertEquals('C://example/resolved/absolute/path.ts', $dependencyModel->absolutePath);
    }

    /** @test */
    public function itShouldParseImportStatementCorrectly() {

        // Given we have a DependencyFactory
        $container = new Container();
        $container->delegate(new ReflectionContainer());
        $factory = $container->get(DependencyFactory::class);

        $importStatement = "import { ModuleA, ModuleB as B } from '../module/location';";
        $expected = [
            'imports' => [
                'ModuleA' => 'ModuleA',
                'ModuleB' => 'B'
            ],
            'relativePath' => '../module/location'
        ];

        // When we parse the import statement
        $result = $factory->parseImportStatement($importStatement);

        // Then we should get the expected imports and relative path
        $this->assertEquals($expected, $result);
    }

}