<?php

namespace Iloveimg;
/**
 * ClassCropimageTask
 *
 * @package Iloveimg
 */
class CropImageTask extends ImageTask
{

    /**
     * @var int
     */
    public $x = 0;

    /**
     * @var int
     */
    public $y = 0;

    /**
     * @var int
     */
    public $width = 0;

    /**
     * @var int
     */
    public $height = 0;

    /**
     * ClassCropimageTask constructor.
     *
     * @param null|string $publicKey    Your public key
     * @param null|string $secretKey    Your secret key
     * @param bool $makeStart           Set to false for chained tasks, because we don't need the start
     */
    function __construct($publicKey, $secretKey, $makeStart = true)
    {
        $this->tool = 'cropimage';
        parent::__construct($publicKey, $secretKey, $makeStart);
    }

    /**
     * @param int $x
     * @return CropImageTask
     */
    public function setX($x)
    {
        $this->x = $x;
        return $this;
    }

    /**
     * @param int $y
     * @return CropImageTask
     */
    public function setY($y)
    {
        $this->y = $y;
        return $this;
    }

    /**
     * @param int $width
     * @return CropImageTask
     */
    public function setWidth($width)
    {
        $this->width = $width;
        return $this;
    }

    /**
     * @param int $height
     * @return CropImageTask
     */
    public function setHeight($height)
    {
        $this->height = $height;
        return $this;
    }
}
