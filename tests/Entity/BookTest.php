<?php

namespace Bookboon\Api\Entity;

use Bookboon\Api\Bookboon;

/**
 * Class BookTest
 * @package Bookboon\Api\Entity
 * @group entity
 */
class BookTest extends \PHPUnit_Framework_TestCase
{
    /** @var Book */
    private static $data = null;

    public static function setUpBeforeClass()
    {
        include_once(__DIR__ . '/../Authentication.php');
        $bookboon = new Bookboon(\Authentication::getApiId(), \Authentication::getApiSecret());
        self::$data = Book::get($bookboon, '3bf58559-034f-4676-bb5f-a2c101015a58');
    }

    public function testGetId()
    {
        $this->assertEquals('3bf58559-034f-4676-bb5f-a2c101015a58', self::$data->getId());
    }

    public function providerTestGetters()
    {
        return array(
            'getTitle' => array('getTitle'),
            'getHomepage' => array('getHomepage'),
            'getAuthors' => array('getAuthors'),
            'getIsbn' => array('getIsbn'),
            'getLanguageName' => array('getLanguageName'),
            'getLanguageCode' => array('getLanguageCode'),
            'getPublished' => array('getPublished'),
            'getAbstract' => array('getAbstract'),
            'getEdition' => array('getEdition'),
            'getPages' => array('getPages'),
            'getRatingCount' => array('getRatingCount'),
            'getRatingAverage' => array('getRatingAverage'),
            'getFormats' => array('getFormats'),
            'getVersion' => array('getVersion'),
            'getDetails' => array('getDetails'),
        );
    }

    /**
     * @dataProvider providerTestGetters
     */
    public function testNotFalse($method)
    {
        $this->assertNotFalse(self::$data->$method());
    }

    public function testThumbnail()
    {
        $this->assertContains('.jpg', self::$data->getThumbnail());
    }

    public function testThumbnailSSL()
    {
        $this->assertContains('https://', self::$data->getThumbnail(380, true));
    }

    public function testDetailTitle()
    {
        $details = self::$data->getDetails();
        $firstDetail = $details[0];
        $this->assertNotEmpty($firstDetail->getTitle());
    }

    public function testDetailBody()
    {
        $details = self::$data->getDetails();
        $firstDetail = $details[0];
        $this->assertNotEmpty($firstDetail->getBody());
    }

    public function testHasEpub()
    {
        // probably true!
        $this->assertTrue(self::$data->hasEpub());
    }

    public function testHasPdf()
    {
        // should always be true
        $this->assertTrue(self::$data->hasPdf());
    }

    /**
     * @expectedException \Bookboon\Api\Exception\EntityDataException
     */
    public function testInvalidBook()
    {
        $book = new Book(array('blah'));
    }

    public function testBookDownload()
    {
        $bookboon = new Bookboon(\Authentication::getApiId(), \Authentication::getApiSecret());

        $url = Book::getDownloadUrl($bookboon, 'db98ac1b-435f-456b-9bdd-a2ba00d41a58', array('handle' => 'phpunit'));
        $this->assertContains('/download/', $url);
    }

    public function testGetSearch()
    {
        $bookboon = new Bookboon(\Authentication::getApiId(), \Authentication::getApiSecret());

        // choose a query with almost certain response;
        $search = Book::search($bookboon, 'engineering');
        $this->assertCount(10, $search);
    }

    public function testGetRecommendations()
    {
        $bookboon = new Bookboon(\Authentication::getApiId(), \Authentication::getApiSecret());

        $recommendations = Book::recommendations($bookboon);
        $this->assertCount(5, $recommendations);
    }

    public function testGetRecommendationsSpecific()
    {
        $bookboon = new Bookboon(\Authentication::getApiId(), \Authentication::getApiSecret());

        $recommendations = Book::recommendations($bookboon, array('3bf58559-034f-4676-bb5f-a2c101015a58'), 8);
        $this->assertCount(8, $recommendations);
    }
}
