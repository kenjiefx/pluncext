<?php 

namespace Kenjiefx\Pluncext\Handlers;

class HandlerModel {

    public function __construct(
        public readonly string $absolutePath,
        public readonly string $content
    ) {}

}