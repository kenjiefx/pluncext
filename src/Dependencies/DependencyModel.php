<?php 

namespace Kenjiefx\Pluncext\Dependencies;

class DependencyModel {

    public function __construct(
        public readonly array $imports,
        public readonly string $absolutePath
    ) {}
    
}