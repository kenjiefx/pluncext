<?php 

namespace Tests\Unit\Services;

use Kenjiefx\Pluncext\Services\GlobService;
use Kenjiefx\Pluncext\Services\ModuleCollector;
use Kenjiefx\ScratchPHP\App\Directories\DirectoryService;
use League\Container\Container;
use League\Container\ReflectionContainer;


class ModuleCollectorTest extends \PHPUnit\Framework\TestCase
{
    /** @test */
    public function itShouldReturnAllTsFiles() {
        // Given we have a directory with TypeScript files and ModuleCollector service
        $container = new Container();
        $container->delegate(new ReflectionContainer());
        $container->add(GlobService::class, new class extends GlobService {
            public function glob(
                string $pattern,
                int $flags = 0
            ): array {
                // Mocking glob function to return a fixed set of TypeScript files
                return [
                    '/path/to/module1.ts',
                    '/path/to/module2.ts',
                    '/path/to/module3.ts',
                ];
            }
        });
        $container->add(DirectoryService::class, new class extends DirectoryService {
            public function listFiles(string $dir): array {
                // Mocking directory listing to return a fixed set of files
                return [];
            }
            public function isDirectory(string $path): bool {
                // Mocking directory check to always return false
                return false;
            }
        });
        $moduleCollector = $container->get(ModuleCollector::class);
        $themeDir = '/path/to/theme';

        // When we collect TypeScript files from the theme directory
        $allTypescriptFiles = [];
        $moduleCollector->getTsFiles($themeDir, $allTypescriptFiles);

        // Then we expect the collected TypeScript files to match the mocked files
        $expectedFiles = [
            '/path/to/module1.ts',
            '/path/to/module2.ts',
            '/path/to/module3.ts',
        ];
        $this->assertEquals($expectedFiles, $allTypescriptFiles, 'The collected TypeScript files do not match the expected files.');
        // Additionally, we can check if the glob method was called with the correct pattern
        $this->assertContains('/path/to/module1.ts', $allTypescriptFiles);
        $this->assertContains('/path/to/module2.ts', $allTypescriptFiles);
        $this->assertContains('/path/to/module3.ts', $allTypescriptFiles);
    }
}