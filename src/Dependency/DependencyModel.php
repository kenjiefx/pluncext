<?php 

namespace Kenjiefx\Pluncext\Dependency;

class DependencyModel {

    public function __construct(
        public readonly array $imports,
        public readonly string $absolutePath
    ) {}

}