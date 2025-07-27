<?php 

namespace Kenjiefx\Pluncext\Bindings;

use Kenjiefx\Pluncext\Modules\ModuleModel;

class BindingModel {

    public function __construct(
        /**
         * The interface module that this binding is for.
         * @var ModuleModel
         */
        public readonly ModuleModel $interface,
        /**
         * The implementation module that this binding provides.
         * @var ModuleModel
         */
        public readonly ModuleModel $implementation
    ) {}

}