<?php

namespace Bookboon\Api\Client;

use Psr\SimpleCache\CacheInterface;
use PHPUnit\Framework\TestCase;
use Helpers\Helpers;

/**
 * Class HashTraitTest
 * @package Bookboon\Api\Client
 * @group cache
 */
class HashTraitTest extends TestCase
{
    public function testHashUrl() : void
    {
        $mock = $this->getMockForTrait(HashTrait::class);

        $hash1 = Helpers::invokeMethod($mock, 'hash', ['/test', "WHATEVSID", []]);
        $hash2 = Helpers::invokeMethod($mock, 'hash', ['/test', "WHATEVSID", []]);

        self::assertEquals($hash1, $hash2);
    }

    public function testHashHeaderXff() : void
    {
        $mock = $this->getMockForTrait(HashTrait::class);

        $hash1 = Helpers::invokeMethod(
            $mock,
            'hash',
            ['/test', "WHATEVSID", [Headers::HEADER_XFF => 'One ip']]
        );
        $hash2 = Helpers::invokeMethod(
            $mock,
            'hash',
            ['/test', "WHATEVSID", [Headers::HEADER_XFF => 'Different ip']]
        );

        self::assertEquals($hash1, $hash2);
    }

    public function testHashHeader() : void
    {
        $mock = $this->getMockForTrait(HashTrait::class);
        $hash1 = Helpers::invokeMethod(
            $mock,
            'hash',
            ['/test', "WHATEVSID", [Headers::HEADER_XFF => 'One ip', Headers::HEADER_BRANDING => 'branding-test-1']]
        );
        $hash2 = Helpers::invokeMethod(
            $mock,
            'hash',
            ['/test', "WHATEVSID", [Headers::HEADER_XFF => 'Different ip', Headers::HEADER_BRANDING => 'branding-test-2']]
        );

        self::assertNotEquals($hash1, $hash2);
    }

    public function testHashLanguageHeader() : void
    {
        $mock = $this->getMockForTrait(HashTrait::class);

        $hash1 = Helpers::invokeMethod(
            $mock,
            'hash',
            ['/test', "WHATEVSID", [Headers::HEADER_LANGUAGE => 'en, de', Headers::HEADER_BRANDING => 'branding-test']]
        );
        $hash2 = Helpers::invokeMethod(
            $mock,
            'hash',
            ['/test', "WHATEVSID", [Headers::HEADER_LANGUAGE => 'de, en', Headers::HEADER_BRANDING => 'branding-test']]
        );

        self::assertNotEquals($hash1, $hash2);
    }

    public function testRequestIsCacheable() : void
    {
        $mock = $this->getMockForTrait(HashTrait::class);
        $cache = $this->getMockBuilder(CacheInterface::class)->getMock();
        $mock->method("getCache")->willReturn($cache);

        $result = Helpers::invokeMethod(
            $mock,
            'isCachable',
            ['/test/rul', ClientInterface::HTTP_GET, []]
        );

        self::assertTrue($result);
    }

    public function testRequestNotCacheblePost() : void
    {
        $mock = $this->getMockForTrait('\Bookboon\Api\Client\HashTrait');
        $cache = $this->getMockBuilder(CacheInterface::class)->getMock();
        $mock->method("getCache")->willReturn($cache);

        $result = Helpers::invokeMethod(
            $mock,
            'isCachable',
            ['/test/rul', ClientInterface::HTTP_POST, []]
        );

        self::assertFalse($result);
    }
}