<?php

namespace Bookboon\Api\Entity;

use Bookboon\Api\Bookboon;

class BookTest extends \PHPUnit_Framework_TestCase
{
    private static $data = null;

    public static function setUpBeforeClass()
    {
        $id = getenv('BOOKBOON_API_ID');
        $key = getenv('BOOKBOON_API_KEY');

        $bookboon = new Bookboon($id, $key);
        self::$data = $bookboon->getBook('3bf58559-034f-4676-bb5f-a2c101015a58');
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
     * @expectedException \Bookboon\Api\Entity\EntityDataException
     */
    public function testInvalidBook()
    {
        $book = new Book(array('blah'));
    }
}
