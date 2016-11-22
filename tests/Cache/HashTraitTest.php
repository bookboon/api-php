<?php

namespace Bookboon\Api\Client;


/**
 * Class HashTraitTest
 * @package Bookboon\Api\Client
 * @group cache
 */
class HashTraitTest extends \PHPUnit_Framework_TestCase
{
    public function testHashUrl()
    {
        $mock = $this->getMockForTrait('\Bookboon\Api\Cache\HashTrait');

        $hash1 = $mock->hash('/test', "WHATEVSID", array());
        $hash2 = $mock->hash('/test', "WHATEVSID", array());

        $this->assertEquals($hash1, $hash2);
    }

    public function testHashHeaderXff()
    {
        $mock = $this->getMockForTrait('\Bookboon\Api\Cache\HashTrait');

        $hash1 = $mock->hash('/test', "WHATEVSID", array(Headers::HEADER_XFF => 'One ip'));
        $hash2 = $mock->hash('/test', "WHATEVSID", array(Headers::HEADER_XFF => 'Different ip'));

        $this->assertEquals($hash1, $hash2);
    }

    public function testHashHeader()
    {
        $mock = $this->getMockForTrait('\Bookboon\Api\Cache\HashTrait');

        $hash1 = $mock->hash('/test', "WHATEVSID", array(Headers::HEADER_XFF => 'One ip', Headers::HEADER_BRANDING => 'branding-test-1'));
        $hash2 = $mock->hash('/test', "WHATEVSID", array(Headers::HEADER_XFF => 'Different ip', Headers::HEADER_BRANDING => 'branding-test-2'));

        $this->assertNotEquals($hash1, $hash2);
    }
}