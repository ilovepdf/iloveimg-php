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
     * @param null|string $publicKey
     * @param null|string $secretKey
     */
    function __construct($publicKey, $secretKey)
    {
        $this->tool = 'watermarkimage';
        parent::__construct($publicKey, $secretKey, true);
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
