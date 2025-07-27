<?php 

namespace Tests\Unit\Services;

use PHPUnit\Framework\TestCase;

class PathResolverTest extends TestCase {

    /** @test */
    public function itShouldResolveRelativePathCorrectly() {

        // Given that we have a PathResolver service
        $pathResolver = new \Kenjiefx\Pluncext\Services\PathResolver();
        $referencePath = '../relative/path/to/file';
        $sourceFilePath = 'C://jdoe/absolute/path/to/source/file.txt';
        
        $expectedPath = 'C:\jdoe\absolute\path\to\relative\path\to\file.txt';
        
        // When we resolve the relative path
        $resolvedPath = $pathResolver->resolveRelativePath($referencePath, $sourceFilePath);
        
        // Then we should get the expected absolute path
        $this->assertEquals($expectedPath, $resolvedPath);
        
    }

}