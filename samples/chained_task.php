<?php
//include the autoloader
require_once('../vendor/autoload.php');
//if manual installation has been used comment line that requires the autoload and uncomment this line:
//require_once('../init.php');

use Iloveimg\Iloveimg;


//this is a sample for a chined task. You can perform multiple tasks on a files uploading just once.

// you can call task class directly
// to get your key pair, please visit https://developer.iloveimg.com/user/projects
$iloveimg = new Iloveimg('project_public_id','project_secret_key');

$rotateTask = $iloveimg->newTask('rotate');

// file var keeps info about server file id, name...
// it can be used latter to cancel file
$file = $rotateTask->addFile('/path/to/file/document.jpg');
$file->setRotation(90);

// run the task
$rotateTask->execute();

//and create a new task from last action
$compressTask = $rotateTask->next('compress');
$compressTask->setCompressionLevel('extreme');

// process files
$compressTask->execute();

// and finally download file. If no path is set, it will be downloaded on current folder
$compressTask->download();