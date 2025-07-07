<?php 

namespace Kenjiefx\Pluncext\Modules;

enum ModuleRole: string {

    case SERVICE = 'service';
    case CONTROLLER = 'controller';
    case VIEW = 'view';
    case MODEL = 'model';
    case REPOSITORY = 'repository';
    case FACTORY = 'factory';
    case HELPER = 'helper';
    case COMPONENT = 'component';

}