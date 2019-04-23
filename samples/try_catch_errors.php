<?php
//include the autoloader
require_once('../vendor/autoload.php');
//if manual installation has been used comment line that requires the autoload and uncomment this line:
//require_once('../init.php');

use Iloveimg\Iloveimg;
use Iloveimg\CompressimageImageTask;


try {
    // start the manager classy
    // to get your key pair, please visit https://developer.iloveimg.com/user/projects
    $iloveimg = new Iloveimg('project_public_id','project_secret_key');

    // and get the task tool
    $myTask = $iloveimg->newTask('compress');

    // or you can call task class directly, this set the same tool as before
    $myTask = new \Iloveimg\CompressimageImageTask('project_public_id','project_secret_key');


    // file var keeps info about server file id, name...
    // it can be used latter to cancel file
    $file = $myTask->addFile('/path/to/file/document.jpg');
    $file2 = $myTask->addFile('/path/to/file/document2.jpg');

    // and set name for output file.
    // in this case it will output a zip file, so we set the package name.
    $myTask->setPackagedFilename('compress_documents');

    // and name for splitted document (inside the zip file)
    $myTask->setOutputFilename('compressed');

    // process files
    $myTask->execute();

    // and finally download file. If no path is set, it will be downloaded on current folder
    $myTask->download('path/to/download');

} catch (\Iloveimg\Exceptions\StartException $e) {
    echo "An error occured on start: " . $e->getMessage() . " ";
} catch (\Iloveimg\Exceptions\AuthException $e) {
    echo "An error occured on auth: " . $e->getMessage() . " ";
    echo implode(', ', $e->getErrors());
} catch (\Iloveimg\Exceptions\UploadException $e) {
    echo "An error occured on upload: " . $e->getMessage() . " ";
    echo implode(', ', $e->getErrors());
} catch (\Iloveimg\Exceptions\ProcessException $e) {
    echo "An error occured on process: " . $e->getMessage() . " ";
    echo implode(', ', $e->getErrors());
} catch (\Exception $e) {
    echo "An error occured: " . $e->getMessage();
}