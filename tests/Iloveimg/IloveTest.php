<?php

namespace Tests\Iloveimg;

use Iloveimg\CompressTask;
use Iloveimg\Iloveimg;
use PHPUnit\Framework\TestCase;

/**
 * Base class for Stripe test cases, provides some utility methods for creating
 * objects.
 */
class IloveTest extends TestCase
{
    public $publicKey = 'public_key';
    public $secretKey = 'secret_key';


    public $publicKeyTest = "public_key_test";
    public $secretKeyTest = "secret_key_test";

    /**
     * @test
     */
    public function testIloveimgCreateWithParams()
    {

        $iloveimg = new Iloveimg($this->publicKey, $this->secretKey);
        $iloveimgTest = new Iloveimg($this->publicKeyTest, $this->secretKeyTest);

        $this->assertEquals($iloveimg->getPublicKey(), $this->publicKey);
        $this->assertEquals($iloveimgTest->getPublicKey(), $this->publicKeyTest);
    }

    /**
     * @test
     */
    public function testIloveimgEmptyParams()
    {
        $iloveimg = new Iloveimg();
        $iloveimgTest = new Iloveimg();

        $iloveimg->setApiKeys($this->publicKey, $this->secretKey);
        $iloveimgTest->setApiKeys($this->publicKeyTest, $this->secretKeyTest);


        $this->assertEquals($iloveimg->getPublicKey(), $this->publicKey);
        $this->assertEquals($iloveimgTest->getPublicKey(), $this->publicKeyTest);
    }
}
