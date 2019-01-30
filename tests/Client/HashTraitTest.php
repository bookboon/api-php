<?php

namespace Bookboon\Api\Client;


use Psr\SimpleCache\CacheInterface;

/**
 * Class HashTraitTest
 * @package Bookboon\Api\Client
 * @group cache
 */
class HashTraitTest extends \PHPUnit_Framework_TestCase
{
    public function testHashUrl()
    {
        $mock = $this->getMockForTrait(HashTrait::class);

        $hash1 = \Helpers::invokeMethod($mock, 'hash', ['/test', "WHATEVSID", []]);
        $hash2 = \Helpers::invokeMethod($mock, 'hash', ['/test', "WHATEVSID", []]);

        $this->assertEquals($hash1, $hash2);
    }

    public function testHashHeaderXff()
    {
        $mock = $this->getMockForTrait(HashTrait::class);

        $hash1 = \Helpers::invokeMethod(
            $mock,
            'hash',
            ['/test', "WHATEVSID", [Headers::HEADER_XFF => 'One ip']]
        );
        $hash2 = \Helpers::invokeMethod(
            $mock,
            'hash',
            ['/test', "WHATEVSID", [Headers::HEADER_XFF => 'Different ip']]
        );

        $this->assertEquals($hash1, $hash2);
    }

    public function testHashHeader()
    {
        $mock = $this->getMockForTrait(HashTrait::class);
        $hash1 = \Helpers::invokeMethod(
            $mock,
            'hash',
            ['/test', "WHATEVSID", [Headers::HEADER_XFF => 'One ip', Headers::HEADER_BRANDING => 'branding-test-1']]
        );
        $hash2 = \Helpers::invokeMethod(
            $mock,
            'hash',
            ['/test', "WHATEVSID", [Headers::HEADER_XFF => 'Different ip', Headers::HEADER_BRANDING => 'branding-test-2']]
        );

        $this->assertNotEquals($hash1, $hash2);
    }

    public function testHashLanguageHeader()
    {
        $mock = $this->getMockForTrait(HashTrait::class);

        $hash1 = \Helpers::invokeMethod(
            $mock,
            'hash',
            ['/test', "WHATEVSID", [Headers::HEADER_LANGUAGE => 'en, de', Headers::HEADER_BRANDING => 'branding-test']]
        );
        $hash2 = \Helpers::invokeMethod(
            $mock,
            'hash',
            ['/test', "WHATEVSID", [Headers::HEADER_LANGUAGE => 'de, en', Headers::HEADER_BRANDING => 'branding-test']]
        );

        $this->assertNotEquals($hash1, $hash2);
    }

    public function testRequestIsCacheable()
    {
        $mock = $this->getMockForTrait(HashTrait::class);
        $cache = $this->getMockBuilder(CacheInterface::class)->getMock();
        $mock->method("getCache")->willReturn($cache);

        $result = \Helpers::invokeMethod(
            $mock,
            'isCachable',
            ['/test/rul', ClientInterface::HTTP_GET, []]
        );

        $this->assertTrue($result);
    }

    public function testRequestNotCacheblePost()
    {
        $mock = $this->getMockForTrait('\Bookboon\Api\Client\HashTrait');
        $cache = $this->getMockBuilder(CacheInterface::class)->getMock();
        $mock->method("getCache")->willReturn($cache);

        $result = \Helpers::invokeMethod(
            $mock,
            'isCachable',
            ['/test/rul', ClientInterface::HTTP_POST, []]
        );

        $this->assertFalse($result);
    }
}