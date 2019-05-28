<?php

namespace Iloveimg;
/**
 * Class CompressimageTask
 *
 * @package Iloveimg
 */
class ResizeImageTask extends ImageTask
{
    /**
     * @var string
     */
    public $resize_mode = 'pixels';

    /**
     * @var bool
     */
    public $maintain_ratio = true;

    /**
     * @var bool
     */
    public $no_enlarge_if_smaller = true;

    /**
     * @var int
     */
    public $pixels_width = null;

    /**
     * @var int
     */
    public $pixels_height = null;

    /**
     * @var int
     */
    public $percentage = null;

    /**
     * @var array
     */
    private $resizeModeValues = ["percentage", "pixels"];

    /**
     * CompressTask constructor.
     *
     * @param null|string $publicKey    Your public key
     * @param null|string $secretKey    Your secret key
     * @param bool $makeStart           Set to false for chained tasks, because we don't need the start
     */
    function __construct($publicKey, $secretKey, $makeStart = true)
    {
        $this->tool = 'resizeimage';
        parent::__construct($publicKey, $secretKey, $makeStart);
    }

    /**
     * @param $level string
     *
     * values: ["percentage"|"pixels"]
     * default: "percentage"
     * @return $this
     */
    public function setResizeMode($mode)
    {
        $this->checkValues($mode, $this->resizeModeValues);

        $this->resize_mode = $mode;

        return $this;
    }

    /**
     * @param boolean $maintain_ratio
     * @return $this
     */
    public function setMaintainRatio($maintain_ratio)
    {
        $this->maintain_ratio = $maintain_ratio;
        return $this;
    }

    /**
     * @param boolean $no_enlarge_if_smaller
     * @return ResizeImageTask
     */
    public function setNoEnlargeIfSmaller($no_enlarge_if_smaller)
    {
        $this->no_enlarge_if_smaller = $no_enlarge_if_smaller;
        return $this;
    }

    /**
     * @param int $pixels_width
     * @return ResizeImageTask
     */
    public function setPixelsWidth($pixels_width)
    {
        $this->pixels_width = $pixels_width;
        return $this;
    }

    /**
     * @param int $pixels_height
     * @return ResizeImageTask
     */
    public function setPixelsHeight($pixels_height)
    {
        $this->pixels_height = $pixels_height;
        return $this;
    }

    /**
     * @param int $percentage
     * @return ResizeImageTask
     */
    public function setPercentage($percentage)
    {
        $this->percentage = $percentage;
        return $this;
    }
}
