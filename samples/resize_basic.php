<?php
//include the autoloader
require_once('../vendor/autoload.php');
//if manual installation has been used comment line that requires the autoload and uncomment this line:
//require_once('../init.php');

use Iloveimg\ResizeImageTask;


// you can call task class directly
// to get your key pair, please visit https://developer.iloveimg.com/user/projects
$myTask = new ResizeImageTask('project_public_id','project_secret_key');

// file var keeps info about server file id, name...
// it can be used latter to cancel file
$file = $myTask->addFile('/path/to/file/document.jpg');

//set resize mode to percentage and resize 50%
$myTask->setResizeMode('percentage');
$myTask->setPercentage('50');

// process files
$myTask->execute();

// and finally download file. If no path is set, it will be downloaded on current folder
$myTask->download();