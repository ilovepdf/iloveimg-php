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
     * @param string $publicKey
     * @param string $secretKey
     */
    function __construct($publicKey, $secretKey)
    {
        $this->tool = 'cropimage';
        parent::__construct($publicKey, $secretKey, true);
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
