<?php

namespace Iloveimg;
/**
 * Class Iloveimg
 *
 * @package Iloveimg
 */
class File
{
    /**
     * @var string
     */
    public $server_filename;

    /**
     * @var string
     */
    public $filename;

    /**
     * @var integer
     */
    public $rotate;

    /**
     * File constructor.
     * @param string $server_filename
     * @param string $filename
     */
    function __construct($server_filename, $filename)
    {
        $this->server_filename = $server_filename;
        $this->filename = $filename;
    }

    /**
     * @return array
     */
    function getFileOptions()
    {
        return array(
            'server_filename' => $this->server_filename,
            'filename' => $this->filename,
            'rotate' => $this->rotate
        );
    }


    /**
     * @param integer $degrees [0|90|180|270]
     * @return bool
     */
    function setRotation($degrees)
    {
        if($degrees!=0 && $degrees!=90 && $degrees!=180 && $degrees!=270){
            throw new \InvalidArgumentException;
        }
        $this->rotate = $degrees;
        return true;
    }

    /**
     * @return string
     */
    function getServerFilename()
    {
        return $this->server_filename;
    }
}
