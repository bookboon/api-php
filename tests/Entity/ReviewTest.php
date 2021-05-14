<?php

namespace Bookboon\Api\Entity;

use Bookboon\Api\Exception\EntityDataException;
use Helpers\Helpers;
use PHPUnit\Framework\TestCase;

/**
 * Class ReviewTest
 * @package Bookboon\Api\Entity
 * @group entity
 */
class ReviewTest extends TestCase
{
    private static $data = null;

    public static function setUpBeforeClass() : void
    {
        $bookboon = Helpers::getBookboon();
        self::$data = Review::getByBookId($bookboon, '3bf58559-034f-4676-bb5f-a2c101015a58')->getEntityStore()->get();
    }

    /**
     * @group reviewt
     */
    public function testGetAuthor() : void
    {
        $firstReview = self::$data[0];
        self::assertNotEmpty($firstReview->getAuthor());
    }

    public function testGetCreated() : void
    {
        $firstReview = self::$data[0];
        self::assertNotEmpty($firstReview->getCreated());
    }

    public function testGetComment() : void
    {
        $firstReview = self::$data[0];
        self::assertNotEmpty($firstReview->getComment());
    }

    public function testGetRating() : void
    {
        $firstReview = self::$data[0];
        self::assertNotEmpty($firstReview->getRating());
    }

    public function testInvalidReview() : void
    {
        $this->expectException(EntityDataException::class);
        $review = new Review(['blah']);
    }
}
