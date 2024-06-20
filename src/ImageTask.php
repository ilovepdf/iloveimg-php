<?php

namespace Iloveimg;

use Iloveimg\Exceptions\StartException;
use Iloveimg\Exceptions\PathException;
use Iloveimg\Exceptions\AuthException;
use Iloveimg\Exceptions\DownloadException;
use Iloveimg\Exceptions\ProcessException;
use Iloveimg\Exceptions\UploadException;
use Iloveimg\File;
use Psr\Http\Message\ResponseInterface;

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

    /**
     * @var string
     */
    public $outputFile;

    /**
     * @var string
     */
    public $outputFileName;

    /**
     * @var string
     */
    public $outputFileType;


    /**
     * @var int|null
     */
    public $remainingFiles;


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

    public function start(): void
    {
        if ($this->tool == null) {
            throw new StartException('Tool must be set');
        }
        $data = ['v' => self::VERSION];
        $body = ['form_params' => $data];
        $response = parent::sendRequest('get', 'start/' . $this->tool, $body);
        try {
            $responseBody = json_decode($response->getBody());
        } catch (\Exception $e) {
            throw new StartException('Invalid response');
        }
        if (empty($responseBody->server)) {
            throw new StartException('no server assigned on start');
        };
        $this->_setRemainingFiles($responseBody->remaining_files ?? null);
        $this->setWorkerServer('https://' . $responseBody->server);
        $this->setTask($responseBody->task);
    }

    public function next(string $nextTool): self
    {
        $data = [
            'v' => self::VERSION,
            'task' => $this->getTaskId(),
            'tool' => $nextTool
        ];
        $body = ['form_params' => $data];

        try {
            $response = parent::sendRequest('post', 'task/next', $body);
            $responseBody = json_decode($response->getBody());
            if (empty($responseBody->task)) {
                throw new StartException('No task assigned on chained start');
            };
        } catch (\Exception $e) {
            throw new StartException('Error on start chained task');
        }

        $next = $this->newTask($nextTool);
        $next->setWorkerServer($this->getWorkerServer());

        $next->setTask($responseBody->task);

        //add files chained
        foreach ($responseBody->files as $serverFilename => $fileName) {
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
    public function uploadFile(string $task, string $filepath)
    {
        if (!file_exists($filepath)) {
            throw new \InvalidArgumentException('File ' . $filepath . ' does not exists');
        }

        $body = [
            'multipart' => [
                [
                    'Content-type' => 'multipart/form-data',
                    'name' => 'file',
                    'contents' => fopen($filepath, 'r'),
                    'filename' => basename($filepath)
                ],
                ['name' => 'task', 'contents' => $task],
                ['name' => 'v', 'contents' => self::VERSION]
            ],
        ];

        $response = $this->sendRequest('post', 'upload', $body);
        try {
            $responseBody = json_decode($response->getBody());
        } catch (\Exception $e) {
            throw new UploadException('Upload response error');
        }
        return new File($responseBody->server_filename, basename($filepath));
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
    public function uploadUrl($task, $url, $bearerToken = null)
    {
        //$data = ['task' => $task, 'cloud_file' => $url, 'v' => self::VERSION];
        //$body = ['form_data' => $data];
        $body = [
            'multipart' => [
                ['name' => 'task', 'contents' => $task],
                ['name' => 'v', 'contents' => self::VERSION],
                ['name' => 'cloud_file', 'contents' => $url]
            ],
        ];

        if ($bearerToken) {
            $body['multipart'][] = ['name' => 'cloud_token', 'contents' => $bearerToken];
        }

        $response = parent::sendRequest('post', 'upload', $body);
        $responseBody = json_decode($response->getBody());
        return new File($responseBody->server_filename, basename($url));
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
     * @param string|null $task
     * @param string $path
     *
     * @throws AuthException
     * @throws ProcessException
     * @throws UploadException
     * @throws DownloadException
     */
    private function downloadFile($task): void
    {
        $response = $this->downloadRequestData($task);

        try {
            $this->outputFile = $response->getBody()->getContents();
        } catch (\Exception $e) {
            throw new DownloadException('No file content for download');
        }
    }

    /**
     * @param string $task
     * @return ResponseInterface
     */
    public function downloadStream(): ResponseInterface
    {
        $response = $this->downloadRequestData($this->task);

        return $response;
    }


    /**
     * @param string $task
     * @return ResponseInterface
     * @throws AuthException
     * @throws ProcessException
     * @throws UploadException
     */
    private function downloadRequestData(string $task): ResponseInterface
    {
        $data = array('v' => self::VERSION);
        $body = ['form_params' => $data];
        /** @psalm-suppress PossiblyNullOperand */
        $response = parent::sendRequest('get', 'download/' . $task, $body);
        $responseHeaders = $response->getHeaders();

        $contentDisposition = isset($responseHeaders['Content-Disposition']) ? $responseHeaders['Content-Disposition'] : $responseHeaders['content-disposition'];

        if (preg_match("/filename\*\=utf-8\'\'([\W\w]+)/", $contentDisposition[0], $matchesUtf)) {
            $filename = urldecode(str_replace('"', '', $matchesUtf[1]));
        } else {
            preg_match('/ .*filename=\"([\W\w]+)\"/', $contentDisposition[0], $matches);
            $filename = str_replace('"', '', $matches[1]);
        }

        $this->outputFileName = $filename;
        $this->outputFileType = pathinfo($this->outputFileName, PATHINFO_EXTENSION);

        return $response;
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
        $this->validateTaskStarted();

        $data = array_merge(
            $this->__toArray(),
            ['task' => $this->task, 'files' => $this->files, 'v' => self::VERSION]
        );

        //clean unwanted vars to be sent
        unset($data['timeoutLarge']);
        unset($data['timeout']);
        unset($data['timeDelay']);

        $body = ['form_params' => $data];

        //$response = parent::sendRequest('post', 'process', http_build_query($body, null, '&', PHP_QUERY_RFC3986));
        $response = parent::sendRequest('post', 'process', $body);

        $this->result = json_decode($response->getBody());

        return $this;
    }

    public function __toArray()
    {
        $props = [];
        $reflection = new \ReflectionClass($this);
        $properties = array_filter(
            $reflection->getProperties(\ReflectionProperty::IS_PUBLIC),
            function ($property) {
                return !$property->isStatic();
            }
        );
        foreach ($properties as $property) {
            $name = $property->name;
            $props[$name] = $this->$name;
        }

        return $props;
        // return call_user_func('get_object_vars', $this);
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
    public function deleteFile(File $file)
    {
        $this->validateTaskStarted();

        if (($key = array_search($file, $this->files)) !== false) {
            $body = ['form_params' => ['task' => $this->getTaskId(), 'server_filename' => $file->server_filename, 'v' => self::VERSION]];
            /** @psalm-suppress PossiblyNullOperand */
            $this->sendRequest('delete', 'upload/' . $this->getTaskId() . '/' . $file->server_filename, $body);
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
    public function listTasks(?string $tool = null, ?string $status = null, ?int $customInt = null, ?int $page = null): array
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

        $body = ['form_params' => $data];

        $response = parent::sendRequest('post', 'task', $body, true);
        $this->result = json_decode($response->getBody());

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


    /**
     * @return void
     * @throws \Exception
     */
    private function validateTaskStarted(): void
    {
        if ($this->task === null) {
            throw new \Exception('Current task does not exists. You must start your task');
        }
    }

    /**
     * @param $remainingFiles
     * @return void
     */
    private function _setRemainingFiles($remainingFiles): void
    {
        $this->remainingFiles = $remainingFiles;
    }
}
