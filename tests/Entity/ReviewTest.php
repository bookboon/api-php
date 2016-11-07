<?php

namespace Bookboon\Api\Entity;

use Bookboon\Api\Bookboon;

/**
 * Class ReviewTest
 * @package Bookboon\Api\Entity
 * @group entity
 */
class ReviewTest extends \PHPUnit_Framework_TestCase
{
    private static $data = null;

    public static function setUpBeforeClass()
    {
        include_once(__DIR__ . '/../Authentication.php');
        $bookboon = new Bookboon(\Authentication::getApiId(), \Authentication::getApiSecret());
        self::$data = Review::getByBookId($bookboon, '3bf58559-034f-4676-bb5f-a2c101015a58');
    }

    public function testGetAuthor()
    {
        $firstReview = self::$data[0];
        $this->assertNotEmpty($firstReview->getAuthor());
    }

    public function testGetCreated()
    {
        $firstReview = self::$data[0];
        $this->assertNotEmpty($firstReview->getCreated());
    }

    public function testGetComment()
    {
        $firstReview = self::$data[0];
        $this->assertNotEmpty($firstReview->getComment());
    }

    public function testGetRating()
    {
        $firstReview = self::$data[0];
        $this->assertNotEmpty($firstReview->getRating());
    }

    /**
     * @expectedException \Bookboon\Api\Exception\EntityDataException
     */
    public function testInvalidReview()
    {
        $review = new Review(array('blah'));
    }
}
