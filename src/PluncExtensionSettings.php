<?php 

namespace Kenjiefx\Pluncext;

class PluncExtensionSettings {

    private static array $settings = [];

    public function __construct(

    ) {}

    /**
     * Determines if TypeScript compilation should be automatically triggered.
     */
    public function autoTsc() {
        return static::$settings['autoTsc'] ?? false;
    }

    /**
     * Determines if the handler names should be obfuscated.
     */
    public function obfuscateHandlers() {
        return static::$settings['obfuscateHandlers'] ?? false;
    }

    public function minify() {
        return static::$settings['minify'] ?? false;
    }

    public function load(
        array $settings
    ){
        // Load the settings only once
        if ($this->hasLoaded()) {
            return;
        }
        static::$settings = $settings;
    }

    /**
     * Determines if the settings have been loaded.
     * @return bool
     */
    public function hasLoaded(){
        return !empty(static::$settings);
    }

}