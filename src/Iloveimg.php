<?php

namespace Iloveimg;

use Iloveimg\Exceptions\DownloadException;
use Iloveimg\Exceptions\ProcessException;
use Iloveimg\Exceptions\TaskException;
use Iloveimg\Exceptions\UploadException;
use Iloveimg\Exceptions\AuthException;
use Iloveimg\Exceptions\StartException;
use Iloveimg\Http\Client;
use Iloveimg\Http\ClientException;
use Firebase\JWT\JWT;
use Psr\Http\Message\ResponseInterface;

/**
 * Class Iloveimg
 *
 * @package Iloveimg
 */
class Iloveimg
{
    // @var string The Iloveimg secret API key to be used for requests.
    private $secretKey = null;

    // @var string The Iloveimg public API key to be used for requests.
    private $publicKey = null;

    // @var string The base URL for the Iloveimg API.
    private static $startServer = 'https://api.iloveimg.com';

    private $workerServer = null;

    // @var string|null The version of the Iloveimg API to use for requests.
    public static $apiVersion = 'v1';

    const VERSION = 'php.1.1.17';

    public $token = null;

    /*
     * @var int delay in seconds, for timezone exceptions.
     * Time sholud be UTC, but some servers maybe are not using NAT.
     * This var is here to correct this delay. Currently 5400 seconds : 1h:30'
     */
    public $timeDelay = 5400;

    private $encrypted = false;
    private $encryptKey;

    public $timeout = 10;
    public $timeoutLarge = null;

    public $info = null;

    /**
     * Iloveimg constructor.
     * @param string $publicKey
     * @param string $secretKey
     * @param bool $makeStart
     */
    public function __construct(?string $publicKey = null, ?string $secretKey = null)
    {
        if ($publicKey && $secretKey)
            $this->setApiKeys($publicKey, $secretKey);
    }

    /**
     * @return string The API secret key used for requests.
     */
    public function getSecretKey()
    {
        return $this->secretKey;
    }

    /**
     * @return string The API secret key used for requests.
     */
    public function getPublicKey()
    {
        return $this->publicKey;
    }

    /**
     * Sets the API key to be used for requests.
     *
     * @param string $apiKey
     */
    public function setApiKeys($publicKey, $secretKey)
    {
        $this->publicKey = $publicKey;
        $this->secretKey = $secretKey;
    }

    /**
     * @return string The API version used for requests. null if we're using the
     *    latest version.
     */
    public static function getApiVersion()
    {
        return self::$apiVersion;
    }

    /**
     * @param string $apiVersion The API version to use for requests.
     */
    public static function setApiVersion($apiVersion)
    {
        self::$apiVersion = $apiVersion;
    }

    /**
     * @return string The JWT to be used on api requests
     */
    public function getJWT()
    {
//        if (!is_null($this->token) && !$this->getFileEncryption()) {
//            return $this->token;
//        }

        // Collect all the data
        $secret = $this->getSecretKey();

        $currentTime = time();
        $request = '';
        $hostInfo = '';

        // Merge token with presets not to miss any params in custom
        // configuration
        $token = array_merge([
            'iss' => $hostInfo,
            'aud' => $hostInfo,
            'iat' => $currentTime - $this->timeDelay,
            'nbf' => $currentTime - $this->timeDelay,
            'exp' => $currentTime + 3600 + $this->timeDelay
        ], []);

        // Set up id
        $token['jti'] = $this->getPublicKey();

        // Set encryptKey
        if ($this->getFileEncryption()) {
            $token['file_encryption_key'] = $this->getEncrytKey();
        }

        $this->token = JWT::encode($token, $secret, static::getTokenAlgorithm());

        return $this->token;
    }


    /**
     * @return string
     */
    public static function getTokenAlgorithm()
    {
        return 'HS256';
    }


    /**
     * @param string $method
     * @param string $endpoint
     * @param array $params
     * @param bool $start
     *
     * @return ResponseInterface response from server
     *
     * @throws \Iloveimg\Exceptions\AuthException
     * @throws ProcessException
     * @throws UploadException
     */
    public function sendRequest(string $method, string $endpoint, array $params = [], bool $start = false): ResponseInterface
    {
        $to_server = self::getStartServer();
        if (!$start && !is_null($this->getWorkerServer())) {
            $to_server = $this->workerServer;
        }

        /** @psalm-suppress PossiblyNullOperand */
        $timeout = ($endpoint == 'process' || $endpoint == 'upload' || strpos($endpoint, 'download/') === 0) ? $this->timeoutLarge : $this->timeout;
        $requestConfig = [
            'connect_timeout' => $timeout,
            'headers' => [
                'Authorization' => 'Bearer ' . $this->getJWT(),
                'Accept' => 'application/json'
            ],
        ];

        $requestParams = $requestConfig;
        if ($params) {
            $requestParams = array_merge($requestConfig, $params);
        }

        $client = new Client($params);
        $error = null;

        try {
            /** @psalm-suppress PossiblyNullOperand */
            $response = $client->request($method, $to_server . '/v1/' . $endpoint, $requestParams);
        } catch (ClientException $e) {
            $response = $e->getResponse();
            $error = $e;
        }
        $responseCode = $response->getStatusCode();

        if ($responseCode != 200 && $responseCode != 201) {
            $responseBody = json_decode((string)$response->getBody());
            if ($responseCode == 401) {
                throw new AuthException($responseBody->name, $responseBody, $responseCode);
            }
            if ($endpoint == 'upload') {
                if (is_string($responseBody)) {
                    throw new UploadException("Upload error", $responseBody, $responseCode);
                }
                throw new UploadException($responseBody->error->message, $responseBody, $responseCode);
            } elseif ($endpoint == 'process') {
                throw new ProcessException($responseBody->error->message, $responseBody, $responseCode);
            } elseif (strpos($endpoint, 'download') === 0) {
                throw new DownloadException($responseBody->error->message, $responseBody, $responseCode);
            } elseif (strpos($endpoint, 'start') === 0) {
                if (isset($responseBody->error) && isset($responseBody->error->type)) {
                    throw new StartException($responseBody->error->message, $responseBody, $responseCode);
                }
                throw new \Exception('Bad Request');
            } else {
                if ($response->getStatusCode() == 429) {
                    throw new \Exception('Too Many Requests');
                }
                if ($response->getStatusCode() == 400) {
                    //common process exception
                    if (strpos($endpoint, 'task') !== false) {
                        throw new TaskException('Invalid task id');
                    }
                    //signature exception
                    if(strpos($endpoint, 'signature') !== false){
                        throw new ProcessException($responseBody->error->type, $responseBody, $response->getStatusCode());
                    }

                    if (isset($responseBody->error) && isset($responseBody->error->type)) {
                        throw new \Exception($responseBody->error->message);
                    }
                    throw new \Exception('Bad Request');
                }
                if (isset($responseBody->error) && isset($responseBody->error->message)) {
                    throw new \Exception($responseBody->error->message);
                }
                throw new \Exception('Bad Request');
            }
        }

        return $response;
    }

    /**
     * @param string $tool api tool to use
     *
     * @return mixed Return implemented Task class for specified tool
     *
     * @throws \Exception
     */
    public function newTask($tool = '', $makeStart = true)
    {
        $classname = '\\Iloveimg\\' . ucwords(strtolower($tool)) . 'ImageTask';
        if (!class_exists($classname)) {
            throw new \InvalidArgumentException();
        }
        return new $classname($this->getPublicKey(), $this->getSecretKey(), $makeStart);
    }

    public static function setStartServer($server)
    {
        self::$startServer = $server;
    }


    public static function getStartServer()
    {
        return self::$startServer;
    }

    /**
     * @return string Return url
     */
    public function getWorkerServer()
    {
        return $this->workerServer;
    }

    /**
     * @param null $workerServer
     */
    public function setWorkerServer($workerServer)
    {
        $this->workerServer = $workerServer;
    }


    /**
     * @param boolean $value
     */
    public function setFileEncryption($value, $encryptKey = null)
    {
        $this->encrypted = $value;
        if ($this->encrypted) {
            $this->setEncryptKey($encryptKey);
        } else {
            $this->encryptKey = null;
        }
    }


    /**
     * @return bool
     */
    public function getFileEncryption()
    {
        return $this->encrypted;
    }

    /**
     * @return mixed
     */
    public function getEncrytKey()
    {
        return $this->encryptKey;
    }

    /**
     * @param mixed $encrytKey
     */
    public function setEncryptKey($encryptKey = null)
    {
        if ($encryptKey == null) {
            $encryptKey = IloveimgTool::rand_sha1(32);
        }
        $len = strlen($encryptKey);
        if ($len != 16 && $len != 24 && $len != 32) {
            throw new \InvalidArgumentException('Encrypt key shold have 16, 14 or 32 chars length');
        }
        $this->encryptKey = $encryptKey;
    }

    /**
     * @return ImageTask
     */
    public function getStatus($server, $taskId)
    {
        $workerServer = $this->getWorkerServer();
        $this->setWorkerServer($server);
        $response = $this->sendRequest('get', 'task/' . $taskId);
        $this->setWorkerServer($workerServer);

        return $response->body;
    }

    /**
     * @param $verify
     */
    public function verifySsl(bool $verify): void
    {
        Client::setVerify($verify);
    }


    /**
     * @param $follow
     */
    public function followLocation(bool $follow): void
    {
        Client::setAllowRedirects($follow);
    }

    private function getUpdatedInfo(): object
    {
        $data = array('v' => self::VERSION);
        $body = ['form_params' => $data];
        $response = self::sendRequest('get', 'info', $body);
        $this->info = json_decode($response->getBody());
        return $this->info;
    }


    /**
     * @return object
     */
    public function getInfo()
    {
        $info = $this->getUpdatedInfo();
        return $info;
    }

    /**
     * @return integer
     */
    public function getRemainingFiles()
    {
        $info = $this->getUpdatedInfo();
        return $info->remaining_files;
    }
}
