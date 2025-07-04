<?php 

# php -S 127.0.0.1:7743 server.php to start
/**
 * This server is not intended for production use. 
 * While it uses robust framework such as Slim, 
 */

use Kenjiefx\ScratchPHP\App;

define('ROOT', __DIR__);
require 'vendor/autoload.php';

$app = new App();
$app->run();