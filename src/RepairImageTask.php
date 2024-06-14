<?php

namespace Iloveimg;
/**
 * Class RepairTask
 *
 * @package Iloveimg
 */
class RepairImageTask extends ImageTask
{

    /**
     * RepairTask constructor.
     *
     * @param null|string $publicKey    Your public key
     * @param null|string $secretKey    Your secret key
     * @param bool $makeStart           Set to false for chained tasks, because we don't need the start
     */
    function __construct($publicKey, $secretKey, $makeStart = true)
    {
        $this->tool = 'repairimage';
        parent::__construct($publicKey, $secretKey, $makeStart);
    }
}
