<?php

namespace Bookboon\Api\Entity;

use Bookboon\Api\Bookboon;
use Client\OauthGrant;

/**
 * Class BookTest
 * @package Bookboon\Api\Entity
 * @group entity
 * @group book
 */
class BookTest extends \PHPUnit_Framework_TestCase
{
    /** @var Book */
    private static $data = null;

    /** @var Bookboon */
    private static $bookboon = null;

    public static function setUpBeforeClass()
    {
        include_once(__DIR__ . '/../Helpers.php');
        self::$bookboon = \Helpers::getBookboon();
        self::$data = Book::get(self::$bookboon, '3bf58559-034f-4676-bb5f-a2c101015a58');
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

    /**
     * @group down
     */
    public function testBookDownload()
    {
        $url = Book::getDownloadUrl(self::$bookboon, 'db98ac1b-435f-456b-9bdd-a2ba00d41a58', array('handle' => 'phpunit'));
        $this->assertContains('/download/', $url);
    }

    public function testGetSearch()
    {
        // choose a query with almost certain response;
        $search = Book::search(self::$bookboon, 'engineering');
        $this->assertCount(10, $search);
    }

    public function testGetRecommendations()
    {
        $recommendations = Book::recommendations(self::$bookboon);
        $this->assertCount(5, $recommendations);
    }

    public function testGetRecommendationsSpecific()
    {
        $recommendations = Book::recommendations(self::$bookboon, array('3bf58559-034f-4676-bb5f-a2c101015a58'), 8);
        $this->assertCount(8, $recommendations);
    }
}
