<?php 

namespace Kenjiefx\Pluncext\Implementations\QuarkBundler\BundleItem;

class BundleItemModel {

    public function __construct(
        
        /**
         * The Id of the bundle item, typically the path 
         * to the TypeScript file relative to the project root.
         */
        public string $id,

        /**
         * The javascript content to be included in the bundle.
         */
        public string $content

    ) {}

}