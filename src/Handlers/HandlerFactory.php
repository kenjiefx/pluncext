<?php 

namespace Kenjiefx\Pluncext\Handlers;

use Kenjiefx\Pluncext\Modules\ModuleModel;
use Kenjiefx\ScratchPHP\App\Files\FileFactory;
use Kenjiefx\ScratchPHP\App\Files\FileService;

class HandlerFactory {

    public function __construct(
        public readonly FileFactory $fileFactory,
        public readonly FileService $fileService
    ) {}

    public function create(
        ModuleModel $moduleModel
    ) {
        $content = $this->fileService->readFile(
            $this->fileFactory->create($moduleModel->absolutePath)
        );
        
    }

    

}