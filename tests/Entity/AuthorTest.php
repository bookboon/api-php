<?php

namespace Bookboon\Api\Entity;

use Bookboon\Api\Bookboon;

/**
 * Class AuthorTest
 * @package Bookboon\Api\Entity
 * @group entity
 */
class AuthorTest extends \PHPUnit_Framework_TestCase
{
    /** @var Author */
    private static $data = null;

    public static function setUpBeforeClass()
    {
        include_once(__DIR__ . '/../Helpers.php');
        $bookboon = \Helpers::getBookboon();
        self::$data = Author::get($bookboon, '0908031c-ce02-9b86-11e6-6dd9aa4699d1');
    }

    public function providerTestGetters()
    {
        return array(
            'getName' => array('getName'),
            'getProfile' => array('getProfile'),
            'getBooks' => array('getBooks'),
        );
    }

    /**
     * @dataProvider providerTestGetters
     */
    public function testNotFalse($method)
    {
        $this->assertNotFalse(self::$data->$method());
    }

    public function testHasBooks()
    {
        $books = self::$data->getBooks();
        $this->assertInstanceOf('\Bookboon\Api\Entity\Book', $books[0]);
    }

    /**
     * @expectedException \Bookboon\Api\Exception\EntityDataException
     */
    public function testInvalidAuthor()
    {
        $author = new Author(array('blah'));
    }
}
