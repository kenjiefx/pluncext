<?php 

namespace Kenjiefx\Pluncext\Services;

use Kenjiefx\Pluncext\PluncExtensionSettings;

class TypeScriptService {

    /**
     * Indicates whether the TypeScript compiler has been executed.
     * This is used to prevent multiple compilations during the same request.
     */
    public static bool $hasCompiled = false;

    public function __construct(
        public readonly PluncExtensionSettings $settings
    ) {}

    public function compile() {
        // Execute the TypeScript compiler, if autoTsc is enabled
        if ($this->settings->autoTsc() && !self::$hasCompiled) {
            exec("tsc");
            self::$hasCompiled = true;
        }
    }

}