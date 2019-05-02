<?php
//include the autoloader
require_once('../vendor/autoload.php');
//if manual installation has been used comment line that requires the autoload and uncomment this line:
//require_once('../init.php');

use Iloveimg\WatermarkImageTask;
use Iloveimg\Element;


// you can call task class directly
// to get your key pair, please visit https://developer.iloveimg.com/user/projects
$myTask = new WatermarkImageTask('project_public_id','project_secret_key');

// file var keeps info about server file id, name...
// it can be used latter to cancel file
$file = $myTask->addFile('/path/to/file/document.jpg');

$watermarkElement = $myTask->addElement();

// set the text
$watermarkElement->setText("watermark text");


// set vertical position
$watermarkElement->setGravity("NorthWest");

// adjust vertical position
$watermarkElement->setWidthPercent(20);

// adjust horizontal position
$watermarkElement->setHorizontalPositionAdjustment("100");

// set mode to text
$watermarkElement->setFontFamily("Arial");

// set mode to text
$watermarkElement->setFontStyle("Italic");

// set the font size
$watermarkElement->setFontSize("12");

// set color to red
$watermarkElement->setFontColor("#ff0000");

// set transparency
$watermarkElement->setTransparency("50");


// and set name for output file.
// the task will set the correct file extension for you.
$myTask->setOutputFilename('watermarked');

// process files
$myTask->execute();

// and finally download the unlocked file. If no path is set, it will be downloaded on current folder
$myTask->download('path/to/download');