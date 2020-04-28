<?php

namespace Bookboon\Api\Entity;

use Bookboon\Api\Bookboon;
use PHPUnit\Framework\TestCase;
use Bookboon\Api\Entity\Book;

/**
 * Class AuthorTest
 * @package Bookboon\Api\Entity
 * @group entity
 */
class AuthorTest extends TestCase
{
    /** @var Author */
    private static $data = null;

    public static function setUpBeforeClass()
    {
        include_once(__DIR__ . '/../Helpers.php');
        $bookboon = \Helpers::getBookboon();
        self::$data = Author::get($bookboon, '0908031c-ce02-9b86-11e6-6dd9268599d1')
            ->getEntityStore()
            ->getSingle();
    }

    public function providerTestGetters()
    {
        return [
            'getName' => ['getName'],
            'getProfile' => ['getProfile'],
            'getBooks' => ['getBooks'],
        ];
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
        $this->assertInstanceOf(Book::class, $books[0]);
    }

    /**
     * @expectedException \Bookboon\Api\Exception\EntityDataException
     */
    public function testInvalidAuthor()
    {
        $author = new Author(array('blah'));
    }
}
