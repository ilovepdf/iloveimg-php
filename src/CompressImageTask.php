<?php

namespace Iloveimg;
/**
 * Class CompressimageTask
 *
 * @package Iloveimg
 */
class CompressImageTask extends ImageTask
{
    /**
     * @var string
     */
    public $compression_level='recommended';

    private $compressionLevelValues = ["extreme", "recommended", "low"];

    /**
     * CompressTask constructor.
     *
     * @param null|string $publicKey    Your public key
     * @param null|string $secretKey    Your secret key
     * @param bool $makeStart           Set to false for chained tasks, because we don't need the start
     */
    function __construct($publicKey, $secretKey, $makeStart = true)
    {
        $this->tool = 'compressimage';
        parent::__construct($publicKey, $secretKey, $makeStart);
    }

    /**
     * @param $level string
     *
     * values: ["extreme"|"recommended"|"low"]
     * default: "recommended"
     */
    public function setCompressionLevel($level)
    {
        $this->checkValues($level, $this->compressionLevelValues);

        $this->compression_level = $level;

        return $this;
    }
}
