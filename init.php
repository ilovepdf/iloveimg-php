<?php

//Helpers
require_once __DIR__ . '/src/Lib/JWT.php';
require_once __DIR__ . '/src/File.php';
require_once __DIR__ . '/src/Request/Method.php';
require_once __DIR__ . '/src/Request/Response.php';
require_once __DIR__ . '/src/Request/Request.php';
require_once __DIR__ . '/src/Request/Body.php';
require_once __DIR__ . '/src/Element.php';
//Exceptions
require_once __DIR__ . '/src/Exceptions/ExtendedException.php';
require_once __DIR__ . '/src/Exceptions/DownloadException.php';
require_once __DIR__ . '/src/Exceptions/ProcessException.php';
require_once __DIR__ . '/src/Exceptions/UploadException.php';
require_once __DIR__ . '/src/Exceptions/StartException.php';
require_once __DIR__ . '/src/Exceptions/AuthException.php';
require_once __DIR__ . '/src/Exceptions/PathException.php';

//Iloveimg
require_once __DIR__ . '/src/Iloveimg.php';
require_once __DIR__ . '/src/ImageTask.php';

//Specific processes
require_once __DIR__ . '/src/CompressImageTask.php';
require_once __DIR__ . '/src/WatermarkImageTask.php';
require_once __DIR__ . '/src/RepairImageTask.php';
require_once __DIR__ . '/src/ResizeImageTask.php';
require_once __DIR__ . '/src/RotateImageTask.php';
require_once __DIR__ . '/src/ConvertImageTask.php';
require_once __DIR__ . '/src/CropImageTask.php';

