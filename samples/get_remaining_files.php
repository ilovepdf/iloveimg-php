<?php
//include the autoloader
require_once('../vendor/autoload.php');
//if manual installation has been used comment line that requires the autoload and uncomment this line:
//require_once('../init.php');

use Iloveimg\Iloveimg;


// you can call task class directly
// to get your key pair, please visit https://developer.iloveimg.com/user/projects
$iloveimg = new Iloveimg('project_public_id', 'project_secret_key');


//get remaining files
$remainingFiles = $iloveimg->getRemainingFiles();


//print your remaining files
echo $remainingFiles;

//only start new process if you have enough files
if($remainingFiles>0) {
    //start the task
    $myTask = $iloveimg->newTask('merge');
}