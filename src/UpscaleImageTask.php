<?php

namespace Iloveimg;
/**
 * Class UpscaleImageTask
 *
 * @package Iloveimg
 */
class UpscaleImageTask extends ImageTask
{
    /**
     * @var integer
     */
    public $multiplier=2;

    private $multiplierValues = [2, 4];

    /**
     * CompressTask constructor.
     *
     * @param null|string $publicKey    Your public key
     * @param null|string $secretKey    Your secret key
     * @param bool $makeStart           Set to false for chained tasks, because we don't need the start
     */
    function __construct($publicKey, $secretKey, $makeStart = true)
    {
        $this->tool = 'upscaleimage';
        parent::__construct($publicKey, $secretKey, $makeStart);
    }

    /**
     * @param $multiplier integer
     *
     * values: [2|4]
     */
    public function setMultiplier($multiplier)
    {
        $this->checkValues($multiplier, $this->multiplierValues);

        $this->multiplier = $multiplier;

        return $this;
    }


    public function setScale($cale){
        return $this->setMultiplier($cale);
    }


}
