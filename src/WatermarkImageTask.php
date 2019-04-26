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
     * @param $element
     * @return $this
     */
    public function addElement($element)
    {
        if (get_class($element) == 'Iloveimg\Element') {
            $this->elements[] = $element;
        } elseif (is_array($element)) {
            $this->elements[] = new Element($element);
        }
        return $this;
    }

}
