<?php

namespace Iloveimg;
/**
 * Class ConvertimageTask
 *
 * @package Iloveimg
 */
class ConvertImageTask extends ImageTask
{
    /**
     * @var string
     */
    public $convert_to = 'jpg';

    private $toValues = ["jpg", "png", "gif", "gif_animation"];


    /**
     * @var int
     */
    public $gif_time = 50;

    /**
     * @var bool
     */
    public $gif_loop = true;

    /**
     * ConvertimageTask constructor.
     *
     * @param null|string $publicKey    Your public key
     * @param null|string $secretKey    Your secret key
     * @param bool $makeStart           Set to false for chained tasks, because we don't need the start
     */
    function __construct($publicKey, $secretKey, $makeStart = true)
    {
        $this->tool = 'convertimage';
        parent::__construct($publicKey, $secretKey, $makeStart);
    }

    /**
     * @param $level string
     *
     * values: ["jpg"|"png"|"gif"]
     * default: "jpg"
     */
    public function setConvertTo(string $convertTo)
    {
        $this->checkValues($convertTo, $this->toValues);
        $this->convert_to = $convertTo;
        return $this;
    }

    /**
     * Alias for setConvertTo
     * @param string $convertTo
     * @return $this
     */
    public function setTo(string $convertTo)
    {
        return $this->setConvertTo($convertTo);
    }

    /**
     * @param int $gif_time
     * @return $this
     */
    public function setGifTime(int $gif_time)
    {
        $this->gif_time = $gif_time;
        return $this;
    }

    /**
     * @param bool $gif_loop
     * @return $this
     */
    public function setGifLoop(bool $gif_loop)
    {
        $this->gif_loop = $gif_loop;
        return $this;
    }
}
