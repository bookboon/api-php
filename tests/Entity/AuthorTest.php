<?php

namespace Bookboon\Api\Entity;

use Bookboon\Api\Bookboon;

class AuthorTest extends \PHPUnit_Framework_TestCase
{
    static private $data = null;

    public static function setUpBeforeClass()
    {
        $id = getenv('BOOKBOON_API_ID');
        $key = getenv('BOOKBOON_API_KEY');

        $bookboon = new Bookboon($id, $key);
        self::$data = $bookboon->getAuthor("0908031c-ce02-9b86-11e6-6dd9aa4699d1");
    }

    public function providerTestGetters()
    {
        return array(
            "getName" => array("getName"),
            "getProfile" => array("getProfile"),
            "getBooks" => array("getBooks")
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
     * @expectedException \Bookboon\Api\Entity\EntityDataException
     */
    public function testInvalidAuthor()
    {
        $author = new Author(array("blah"));
    }
}