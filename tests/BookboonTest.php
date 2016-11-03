<?php

namespace Bookboon\Api;

include_once(__DIR__ . '/Authentication.php');

class BookboonTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \Bookboon\Api\Exception\UsageException
     */
    public function testBadUrl()
    {
        $bookboon = new Bookboon(\Authentication::getApiId(), \Authentication::getApiSecret());
        $bookboon->rawRequest('bah');
    }

    /**
     * @expectedException \Bookboon\Api\Exception\ApiSyntaxException
     */
    public function testBadRequest()
    {
        $bookboon = new Bookboon(\Authentication::getApiId(), \Authentication::getApiSecret());
        $bookboon->rawRequest('/search', array('get' => array('q' => '')));
    }

    /**
     * @expectedException \Bookboon\Api\Exception\ApiNotFoundException
     */
    public function testNotFound()
    {
        $bookboon = new Bookboon(\Authentication::getApiId(), \Authentication::getApiSecret());
        $bookboon->rawRequest('/bah');
    }

    /**
     * @expectedException \Bookboon\Api\Exception\ApiAuthenticationException
     */
    public function testBadAuthentication()
    {
        $bookboon = new Bookboon('badid', 'badkey');
        $bookboon->rawRequest('/categories/062adfac-844b-4e8c-9242-a1620108325e');
    }

    /**
     * @expectedException \Exception
     */
    public function testEmpty()
    {
        $bookboon = new Bookboon('badid', '');
        $bookboon->rawRequest('/categories/062adfac-844b-4e8c-9242-a1620108325e');
    }
}
