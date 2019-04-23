<?php

namespace Tests\iloveimg;

use Iloveimg\Iloveimg;
use PHPUnit\Framework\TestCase;

class IloveimgTest extends TestCase
{

    public $iloveimg;

    public $publicKey = "public_key";
    public $secretKey = "secret_key";

    public function setUp()
    {
        $this->iloveimg = new Iloveimg();
        $this->iloveimg->setApiKeys($this->publicKey, $this->secretKey);
    }

    /**
     * @test
     */
    public function testShouldHaveSecretKey()
    {
        $secretKey = $this->iloveimg->getSecretKey();
        $this->assertEquals($this->secretKey, $secretKey);
    }

    /**
     * @test
     */
    public function testShouldHavePublictKey()
    {
        $publicKey = $this->iloveimg->getPublicKey();
        $this->assertEquals($this->publicKey, $publicKey);
    }

    /**
     * @test
     */
    public function testCanSetApiKeys()
    {
        $public = "public";
        $secret = "private";
        $this->iloveimg->setApiKeys($public, $secret);
        $this->assertEquals($public, $this->iloveimg->getPublicKey());
        $this->assertEquals($secret, $this->iloveimg->getSecretKey());
    }

    /**
     * @test
     */
    public function testCanGetJwt()
    {
        $jwt = $this->iloveimg->getJWT();
        $this->assertNotNull($jwt, "jwt should not be null");
    }

    /**
     * @test
     */
    public function testEmptyTaskShouldThrowException()
    {
        $task = $this->iloveimg->newTask("");
        $this->assertNotNull($task);
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function testNotExistingTaskShouldThrowException()
    {
        $this->iloveimg->newTask("tralara");
    }

    /**
     * @test
     */
    public function testEncryptSetDefaultKey()
    {
        $this->iloveimg->setFileEncryption(true);
        $this->assertNotNull($this->iloveimg->getEncrytKey());
        $this->assertEquals(strlen($this->iloveimg->getEncrytKey()), 32);
    }


    /**
     * @test
     */
    public function testCanSetEncrypt()
    {
        $key = '1234123412341234';
        $this->iloveimg->setFileEncryption(true, $key );
        $this->assertEquals($this->iloveimg->getEncrytKey(), $key);
    }

    /**
     * @test
     */
    public function testUnsetEncryptRemovesKey()
    {
        $key = '1234123412341234';
        $this->iloveimg->setFileEncryption(true, $key );
        $this->iloveimg->setFileEncryption(false);
        $this->assertNull($this->iloveimg->getEncrytKey());
    }


    /**
     * @test
     * @dataProvider invalidKeys
     * @expectedException \InvalidArgumentException
     */
    public function testWrongEncryptKeyThrowsException($key)
    {
        $this->iloveimg->setFileEncryption(true, $key );
    }


    public function invalidKeys()
    {
        return [
            ['1234'],
            ['asdfqwe'],
        ];
    }
}