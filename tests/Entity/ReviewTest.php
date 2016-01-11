<?php
/**
 * Created by PhpStorm.
 * User: lasse
 * Date: 04/12/2015
 * Time: 14:53
 */

namespace Bookboon\Api\Entity;


use Bookboon\Api\Bookboon;

class ReviewTest extends \PHPUnit_Framework_TestCase
{
    static private $data = null;

    public static function setUpBeforeClass()
    {
        $id = getenv('BOOKBOON_API_ID');
        $key = getenv('BOOKBOON_API_KEY');

        $bookboon = new Bookboon($id, $key);
        self::$data = $bookboon->getReviews("3bf58559-034f-4676-bb5f-a2c101015a58");
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
     * @expectedException \Bookboon\Api\Entity\EntityDataException
     */
    public function testInvalidReview()
    {
        $review = new Review(array("blah"));
    }
}
