<?php

namespace Iloveimg;

use Iloveimg\Element;

/**
 * Class WatermarkTask
 *
 * @package Iloveimg
 */
class WatermarkImageTask extends ImageTask
{
    /**
     * @var array
     */
    public $elements;

    /**
     * WatermarkTask constructor.
     *
     * @param null|string $publicKey    Your public key
     * @param null|string $secretKey    Your secret key
     * @param bool $makeStart           Set to false for chained tasks, because we don't need the start
     */
    function __construct($publicKey, $secretKey, $makeStart = true)
    {
        $this->tool = 'watermarkimage';
        parent::__construct($publicKey, $secretKey, $makeStart);
    }

    /**
     * @param boolean $mosaic
     * @return $this
     */
    public function setMosaic($mosaic)
    {
        $this->mosaic = $mosaic;
        return $this;
    }


    /**
     * adds a watermark element
     *
     * @param null $element
     * @return \Iloveimg\Element|null
     */
    public function addElement($element = null)
    {
        if (is_array($element)  || $element == null) {
            $element = new Element($element);
        }

        if (get_class($element) !== 'Iloveimg\Element') {
            throw new \InvalidArgumentException();
        }

        $this->elements[] = $element;
        return $element;
    }

}
