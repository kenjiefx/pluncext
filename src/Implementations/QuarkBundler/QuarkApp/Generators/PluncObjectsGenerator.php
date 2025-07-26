<?php

namespace Kenjiefx\Pluncext\Implementations\QuarkBundler\QuarkApp\Generators;

class PluncObjectsGenerator {

    public function __construct(

    ) {}

    public function blockService() {
        return <<<JS
        class BlockService {
            constructor(blockApi) {
                this.block = blockApi
            }
            get(elementName, callback){
                this.block(elementName, callback);
            }
        }
        JS;
    }

    public function patchService() {
        return <<<JS
        class PatchService { 
            constructor(patchApi) {
                this.patchApi = patchApi
            }
            async patch(elementName) { 
                if (elementName === undefined) {
                    return await this.patchApi();
                }
                return await this.patchApi(elementName); 
            } 
        }
        JS;
    }

    public function pluncAppService() {
        return <<<JS
        class PluncAppService { 
            constructor(appApi) {
                this.appApi = appApi
            }
            ready(callback) { 
                this.appApi.ready(callback); 
            } 
        }
        JS;
    }

    public function componentReflection() {
        return <<<JS
        class ComponentReflection { 
            constructor(reflectorApi) { 
                this.id = reflectorApi.id; 
                this.name = reflectorApi.name; 
                this.alias = reflectorApi.alias; 
                this.element = reflectorApi.element; 
            }
        }
        JS;
    }

}