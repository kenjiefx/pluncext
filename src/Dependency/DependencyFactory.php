<?php 

namespace Kenjiefx\Pluncext\Dependency;

class DependencyFactory {

    public function __construct(

    ) {}

    public function create(
        string $absolutePath,
        array $imports = []
    ): DependencyModel {
        return new DependencyModel(
            imports: $imports,
            absolutePath: $absolutePath
        );
    }

}