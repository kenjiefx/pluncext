<?php

namespace Kenjiefx\Pluncext\Interfaces;

interface MinifierInterface {

    public function minify(string $content): string;

}