<?php

namespace Bookboon\Api\Entity;

use Bookboon\Api\Bookboon;
use Bookboon\Api\Exception\EntityDataException;
use PHPUnit\Framework\TestCase;
use Helpers\Helpers;

/**
 * Class AuthorTest
 * @package Bookboon\Api\Entity
 * @group entity
 */
class AuthorTest extends TestCase
{
    /** @var Author */
    private static $data = null;

    public static function setUpBeforeClass() : void
    {
        $bookboon = Helpers::getBookboon();
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
    public function testNotFalse($method) : void
    {
        self::assertNotFalse(self::$data->$method());
    }

    public function testHasBooks() : void
    {
        $books = self::$data->getBooks();
        self::assertInstanceOf(Book::class, $books[0]);
    }

    public function testInvalidAuthor() : void
    {
        $this->expectException(EntityDataException::class);
        $author = new Author(array('blah'));
    }
}
