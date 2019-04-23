<?php

namespace Iloveimg;


/**
 * Class RotateTask
 *
 * @package Iloveimg
 */
class RotateImageTask extends ImageTask
{

    /**
     * RotateTask constructor.
     * @param string $publicKey
     * @param string $secretKey
     */
    function __construct($publicKey, $secretKey)
    {
        $this->tool = 'rotateimage';
        parent::__construct($publicKey, $secretKey, true);
    }
}
