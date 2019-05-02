<?php
//include the autoloader
require_once('../vendor/autoload.php');
//if manual installation has been used comment line that requires the autoload and uncomment this line:
//require_once('../init.php');

use Iloveimg\WatermarkImageTask;

// you can call task class directly
// to get your key pair, please visit https://developer.iloveimg.com/user/projects
$myTask = new WatermarkImageTask('project_public_id', 'project_secret_key');

// file var keeps info about server file id, name...
// it can be used latter to cancel file
$file = $myTask->addFile('/path/to/file/document.jpg');

$element = $myTask->addElement();
$element->setText('watermark text');

// process files
$myTask->execute();

// and finally download the unlocked file. If no path is set, it will be downloaded on current folder
$myTask->download();