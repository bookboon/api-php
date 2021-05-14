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
    public function testRedirectResponse()  : void{
        $testUrl = 'http://test.com';
        $bResponse = new BookboonResponse('', 302, ['Location' => $testUrl]);

        self::assertEquals(['url' => $testUrl], $bResponse->getReturnArray());
    }
}
