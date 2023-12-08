<?php

namespace Iloveimg;

use Iloveimg\Exceptions\StartException;
use Iloveimg\Exceptions\PathException;
use Iloveimg\Request\Body;

/**
 * Class Iloveimg
 *
 * @package Iloveimg
 */
class ImageTask extends Iloveimg
{
    // @var string The Iloveimg API Task ID.
    public $task = null;
    //private $server = null;
    public $files = [];
    public $tool;
    public $packaged_filename;
    public $output_filename;
    public $ignore_errors = true;
    public $try_repair = true;

    /**
     * @var string
     */
    public $webhook;

    //custom data
    public $custom_int = null;
    public $custom_string = null;
    private $statusValues = [
        '',
        'TaskSuccess',
        'TaskDeleted',
        'TaskWaiting',
        'TaskProcessing',
        'TaskSuccessWithWarnings',
        'TaskError',
        'TaskNotFound'
    ];

    //results from execute()
    public $result;

    //downloaded file
    public $outputFile;
    public $outputFileName;
    public $outputFileType;


    /**
     * Task constructor.
     *
     * @param null|string $publicKey    Your public key
     * @param null|string $secretKey    Your secret key
     * @param bool $makeStart           Set to false for chained tasks, because we don't need the start
     */
    function __construct($publicKey, $secretKey, $makeStart = false)
    {
        parent::__construct($publicKey, $secretKey);

        if ($makeStart == true) {
            $this->start();
        }
    }

    public function start()
    {
        $data = array('v' => self::VERSION);
        $body = Body::Form($data);
        $response = parent::sendRequest('get', 'start/' . $this->tool, $body);
        if (empty($response->body->server)) {
            throw new StartException('no server assigned on start');
        };
        $this->setWorkerServer('https://' . $response->body->server);
        $this->setTask($response->body->task);
    }

    public function next($nextTool): ImageTask
    {
        $data = [
            'v' => self::VERSION,
            'task' => $this->getTaskId(),
            'tool' => $nextTool
        ];
        $body = Body::Form($data);

        try {
            $response = parent::sendRequest('post', 'task/next', $body);

            if (empty($response->body->task)) {
                throw new StartException('No task assigned on chained start');
            };
        } catch (\Exception $e) {
            throw new StartException('Error on start chained task');
        }

        $next = $this->newTask($nextTool);
        $next->setWorkerServer($this->getWorkerServer());
        $next->setTask($response->body->task);

        //add files chained
        foreach ($response->body->files as $serverFilename => $fileName) {
            $next->files[] = new File($serverFilename, $fileName);
        }

        return $next;
    }

    public function setTask($task)
    {
        $this->task = $task;
    }

    public function getTaskId()
    {
        return $this->task;
    }

    public function getFiles()
    {
        return $this->files;
    }

    public function getFilesArray()
    {
        $filesArray = [];
        foreach ($this->files as $file) {
            $filesArray[] = $file->getFileOptions();
        }
        return $filesArray;
    }

    public function getStatus($server = null, $taskId = null)
    {
        $server = $server ? $server : $this->getWorkerServer();
        $taskId = $taskId ? $taskId : $this->getTaskId();

        if ($server == null || $taskId == null) {
            throw new \Exception('Cannot get status if no file is uploaded');
        }
        return parent::getStatus($this->getWorkerServer(), $this->getTaskId());
    }

    /**
     * @param string $filePath
     * @return File
     */
    public function addFile($filePath)
    {
        $file = $this->uploadFile($this->task, $filePath);
        array_push($this->files, $file);
        return end($this->files);
    }

    /**
     * @param string $url
     * @return File
     */
    public function addFileFromUrl($url)
    {
        $file = $this->uploadUrl($this->task, $url);
        array_push($this->files, $file);
        return end($this->files);
    }

    /**
     * @param string $task
     * @param string $filepath
     *
     * @return File
     *
     * @throws Exceptions\AuthException
     * @throws Exceptions\ProcessException
     * @throws UploadException
     */
    public function uploadFile($task, $filepath)
    {
        if(!file_exists($filepath)){
            throw new \InvalidArgumentException('File '.$filepath.' does not exists');
        }

        $data = array('task' => $task, 'v' => self::VERSION);
        $files = array('file' => $filepath);
        $body = Request\Body::multipart($data, $files);

        $response = $this->sendRequest('post', 'upload', $body);
        return new File($response->body->server_filename, basename($filepath));
    }

    /**
     * @return ImageTask
     */
    public function delete()
    {
        $response = $this->sendRequest('delete', 'task/' . $this->getTaskId());
        return $this;
    }

    /**
     * @param string $task
     * @param string $url
     *
     * @return File
     *
     * @throws Exceptions\AuthException
     * @throws Exceptions\ProcessException
     * @throws UploadException
     */
    public function uploadUrl($task, $url)
    {
        $data = array('task' => $task, 'cloud_file' => $url, 'v' => self::VERSION);
        $body = Request\Body::Form($data);
        $response = parent::sendRequest('post', 'upload', $body);
        return new File($response->body->server_filename, basename($url));
    }

    /**
     * @param null|string $path
     * @param null|string $file
     */
    public function download($path = null)
    {
        if ($path != null && !is_dir($path)) {
            if (pathinfo($path, PATHINFO_EXTENSION) == '') {
                throw new PathException('Invalid download path. Use method setOutputFilename() to set the output file name.');
            }
            throw new PathException('Invalid download path. Set a valid folder path to download the file.');
        }

        $this->downloadFile($this->task);

        if (is_null($path)) $path = '.';
        $destination = $path . '/' . $this->outputFileName;
        $file = fopen($destination, "w+");
        fputs($file, $this->outputFile);
        fclose($file);
        return;
    }

    /**
     * @param null|string $path
     * @param null|string $file
     */
    public function blob()
    {
        $this->downloadFile($this->task);
        return $this->outputFile;
    }

    /**
     * @param null|string $path
     * @param null|string $file
     */
    public function toBrowser()
    {
        $this->downloadFile($this->task);

        if ($this->outputFileType == 'pdf') {
            header("Content-type:application/pdf");
            header("Content-Disposition:attachment;filename=\"" . $this->outputFileName . "\"");
        } else {
            if (function_exists('mb_strlen')) {
                $size = mb_strlen($this->outputFile, '8bit');
            } else {
                $size = strlen($this->outputFile);
            }
            header('Content-Type: application/zip');
            header("Content-Disposition: attachment; filename=\"" . $this->outputFileName . "\"");
            header("Content-Length: " . $size);
        }
        echo $this->outputFile;
        return;
    }

    /**
     * @param string $task
     * @param string $path
     *
     * @throws Exceptions\AuthException
     * @throws Exceptions\ProcessException
     * @throws Exceptions\UploadException
     */
    private function downloadFile($task)
    {
        $data = array('v' => self::VERSION);
        $body = Request\Body::Form($data);
        $response = parent::sendRequest('get', 'download/' . $task, $body);
        $contentDispoition = $response->headers['Content-Disposition'] ?? $response->headers['content-disposition'];

        if (preg_match("/filename\*\=utf-8\'\'([\W\w]+)/", $contentDispoition, $matchesUtf)) {
            $filename = urldecode(str_replace('"', '', $matchesUtf[1]));
        } else {
            preg_match('/ .*filename=\"([\W\w]+)\"/', $contentDispoition, $matches);
            $filename = str_replace('"', '', $matches[1]);
        }

        $this->outputFile = $response->raw_body;
        $this->outputFileName = $filename;
        $this->outputFileType = pathinfo($this->outputFileName, PATHINFO_EXTENSION);
    }

    /**
     * @param $value
     */
    public function sendEncryptedFiles($value)
    {
        self::$encrypted = $value;
    }

    /**
     * @param $value
     * @return bool
     */
    public function getEncrypted($value)
    {
        return self::$encrypted;
    }

    /**
     * @return ImageTask
     * @throws Exceptions\AuthException
     * @throws Exceptions\ProcessException
     * @throws Exceptions\UploadException
     */
    public function execute()
    {
        if ($this->task === null) {
            throw new \Exception('Current task not exists');
        }

        $data = array_merge(
            $this->getPublicVars($this),
            array('task' => $this->task, 'files' => $this->files, 'v' => self::VERSION));

        //clean unwanted vars to be sent
        unset($data['timeoutLarge']);
        unset($data['timeout']);
        unset($data['timeDelay']);

        $body = Request\Body::multipart($data);

        $response = parent::sendRequest('post', 'process', urldecode(http_build_query($body)));

        $this->result = $response->body;

        return $this;
    }

    public function getPublicVars()
    {
        return call_user_func('get_object_vars', $this);
    }


    /**
     * @param string $filename Set filename for downloaded zip file
     * @return ImageTask
     */
    public function setPackagedFilename($filename)
    {
        $this->packaged_filename = $filename;
        return $this;
    }

    /**
     * @param string $filename Set filename for individual file/s
     * @return ImageTask
     */
    public function setOutputFilename($filename)
    {
        $this->output_filename = $filename;
        return $this;
    }

    /**
     * @param $file File
     * @return ImageTask
     * @throws Exceptions\AuthException
     * @throws Exceptions\DownloadException
     * @throws Exceptions\ProcessException
     * @throws Exceptions\UploadException
     * @throws \Exception
     */
    public function deleteFile($file)
    {
        if (($key = array_search($file, $this->files)) !== false) {
            $body = Request\Body::multipart(['task' => $this->getTaskId(), 'server_filename' => $file->server_filename, 'v' => self::VERSION]);
            $this->sendRequest('post', 'upload/delete', $body);
            unset($this->files[$key]);
        }
        return $this;
    }

    /**
     * @param mixed $value
     * @param array $allowed
     *
     * @return ImageTask
     */
    public function checkValues($value, $allowedValues)
    {
        if (!in_array($value, $allowedValues)) {
            throw new \InvalidArgumentException('Invalid ' . $this->tool . ' value "' . $value . '". Must be one of: ' . implode(',', $allowedValues));
        }
    }

    /**
     * @param boolean $try_repair
     * @return ImageTask
     */
    public function setTryRepair($try_repair)
    {
        $this->try_repair = $try_repair;

        return $this;
    }

    /**
     * @param boolean $ignore_errors
     */
    public function setIgnoreErrors($ignore_errors)
    {
        $this->ignore_errors = $ignore_errors;

        return $this;
    }


    /**
     * alias for setIgnoreError
     *
     * Will be deprecated on v2.0
     *
     * @param boolean $value If true, and multiple archives are processed it will ignore files with errors and continue process for all others
     * @return ImageTask
     */
    public function ignoreErrors($value)
    {
        $this->ignore_errors = $value;

        return $this;
    }

    /**
     * @param boolean $value
     * @return ImageTask
     */
    public function setFileEncryption($value, $encryptKey = null)
    {
        if (count($this->files) > 0) {
            throw new \Exception('Encrypth mode cannot be set after file upload');
        }

        parent::setFileEncryption($value, $encryptKey);

        return $this;
    }

    /**
     * @param null $custom_int
     * @return $this
     */
    public function setCustomInt($customInt)
    {
        $this->custom_int = $customInt;
        return $this;
    }

    /**
     * @param null $custom_string
     * @return $this
     */
    public function setCustomString($customString)
    {
        $this->custom_string = $customString;
        return $this;
    }

    /**
     * @param null $tool
     * @param null $status
     * @param null $customInt
     * @param null $page
     *
     * @throws \Exception
     */
    public function listTasks($tool = null, $status = null, $customInt = null, $page = null)
    {

        $this->checkValues($status, $this->statusValues);

        $data = [
            'tool' => $tool,
            'status' => $status,
            'custom_int' => $customInt,
            'page' => $page,
            'v' => self::VERSION,
            'secret_key' => $this->getSecretKey()
        ];

        $body = Request\Body::multipart($data);

        $response = parent::sendRequest('post', 'task', $body, true);

        $this->result = $response->body;

        return $this->result;
    }

    /**
     * @param string $webhook
     * @return $this
     */
    public function setWebhook($webhook)
    {
        $this->webhook = $webhook;
        return $this;
    }
}
