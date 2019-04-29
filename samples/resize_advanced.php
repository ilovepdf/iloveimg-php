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


//set resize mode to pixels
$myTask->setResizeMode('pixels');

//resize to fit into a 1024px square
$myTask->setPixelsWidth('1024');
$myTask->setPixelsHeight('1024');

//do not make it bigger
$myTask->setNoEnlargeIfSmaller(true);

//queep relation
$myTask->setMaintainRatio(true);


// and set name for output file.
// the task will set the correct file extension for you.
$myTask->setOutputFilename('repaired_file');

// process files
$myTask->execute();

// and finally download file. If no path is set, it will be downloaded on current folder
$myTask->download('path/to/download');