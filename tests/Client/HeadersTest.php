<?php

namespace Bookboon\Api\Client;


/**
 * Class HeadersTest
 * @package Client
 * @group client
 */
class HeadersTest extends \PHPUnit_Framework_TestCase
{
    /*
     * IP
     */
    public function testInvalidXFFIP()
    {
        $_SERVER['REMOTE_ADDR'] = '127.';
        $headers = new Headers();
        $this->assertFalse($headers->get(Headers::HEADER_XFF));
    }

    public function testValidXFFIP()
    {
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $headers = new Headers();
        $this->assertEquals('127.0.0.1', $headers->get(Headers::HEADER_XFF));
    }

    public function testOverrideXFF()
    {
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $headers = new Headers();
        $headers->set(Headers::HEADER_XFF, 'TEST');
        $this->assertEquals('TEST', $headers->get(Headers::HEADER_XFF));
    }
}