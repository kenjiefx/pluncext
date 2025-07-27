<?php 

namespace Kenjiefx\Pluncext\ComponentProxy;

use Kenjiefx\ScratchPHP\App\Components\ComponentModel;

class ComponentProxyModel {

    public function __construct(
        public readonly ComponentModel $component,
        public readonly string $content
    ) {}

}