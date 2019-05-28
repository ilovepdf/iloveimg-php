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
    public $to = 'jpg';

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
    public function setTo(string $to)
    {
        $this->checkValues($to, $this->toValues);
        $this->to = $to;
        return $this;
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
