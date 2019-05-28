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
     *
     * @param null|string $publicKey    Your public key
     * @param null|string $secretKey    Your secret key
     * @param bool $makeStart           Set to false for chained tasks, because we don't need the start
     */
    function __construct($publicKey, $secretKey, $makeStart = true)
    {
        $this->tool = 'rotateimage';
        parent::__construct($publicKey, $secretKey, $makeStart);
    }
}
