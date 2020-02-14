<?php

namespace Bookboon\Api\Client;

use PHPUnit\Framework\TestCase;

/**
 * Class BookboonResponseTest
 * @package Bookboon\Api\Client
 * @group response
 */
class BookboonResponseTest extends TestCase
{
    public function testRedirectResponse() {
        $testUrl = 'http://test.com';
        $bResponse = new BookboonResponse('', 302, ['Location' => $testUrl]);

        $this->assertEquals(['url' => $testUrl], $bResponse->getReturnArray());
    }
}
