<?php

namespace Bookboon\Api\Entity;

use Bookboon\Api\Bookboon;
use Bookboon\Api\Client\BasicAuthClient;
use Bookboon\Api\Client\Headers;
use Bookboon\Api\Exception\EntityDataException;
use PHPUnit\Framework\TestCase;
use Helpers\Helpers;

/**
 * Class BookTest
 * @package Bookboon\Api\Entity
 * @group entity
 * @group book
 */
class BookTest extends TestCase
{
    /** @var Book */
    private static $data = null;

    /** @var Bookboon */
    private static $bookboon = null;

    public static function setUpBeforeClass() : void
    {
        self::$bookboon = Helpers::getBookboon();
        self::$data = Book::get(self::$bookboon, '3bf58559-034f-4676-bb5f-a2c101015a58', true)
            ->getEntityStore()
            ->getSingle();
    }

    public function testGetId() : void
    {
        self::assertEquals('3bf58559-034f-4676-bb5f-a2c101015a58', self::$data->getId());
    }


    public function providerTestGetters()
    {
        return [
            'getTitle' => ['getTitle'],
            'getHomepage' => ['getHomepage'],
            'getAuthors' => ['getAuthors'],
            'getIsbn' => ['getIsbn'],
            'getLanguageName' => ['getLanguageName'],
            'getLanguageCode' => ['getLanguageCode'],
            'getPublished' => ['getPublished'],
            'getAbstract' => ['getAbstract'],
            'getEdition' => ['getEdition'],
            'getPages' => ['getPages'],
            'getPriceLevel' => ['getPriceLevel'],
            'getRatingCount' => ['getRatingCount'],
            'getRatingAverage' => ['getRatingAverage'],
            'getFormats' => ['getFormats'],
            'getVersion' => ['getVersion'],
            'getDetails' => ['getDetails'],
        ];
    }

    /**
     * @dataProvider providerTestGetters
     */
    public function testNotFalse($method) : void
    {
        self::assertNotFalse(self::$data->$method());
    }

    public function testThumbnail() : void
    {
        self::assertStringContainsString('.jpg', self::$data->getThumbnail());
    }

    public function testThumbnailSSL() : void
    {
        self::assertStringContainsString('https://', self::$data->getThumbnail(380, true));
    }

    public function testDetailTitle() : void
    {
        $details = self::$data->getDetails();
        $firstDetail = $details[0];
        self::assertNotEmpty($firstDetail->getTitle());
    }

    public function testDetailBody() : void
    {
        $details = self::$data->getDetails();
        $firstDetail = $details[0];
        self::assertNotEmpty($firstDetail->getBody());
    }

    public function testHasEpub() : void
    {
        // probably true!
        self::assertTrue(self::$data->hasEpub());
    }

    public function testHasPdf() : void
    {
        // should always be true
        self::assertTrue(self::$data->hasPdf());
    }

    public function testInvalidBook() : void
    {
        $this->expectException(EntityDataException::class);
        $book = new PdfBook(['blah']);
    }

    public function testBookDownloadOauth() : void
    {
        $url = Book::getDownloadUrl(self::$bookboon, 'db98ac1b-435f-456b-9bdd-a2ba00d41a58', ['handle' => 'phpunit']);
        self::assertStringContainsString('/download/', $url);
    }

    public function testBookDownloadBasic() : void
    {
        $bookboon = new Bookboon(new BasicAuthClient(
            Helpers::getApiId(),
            Helpers::getApiSecret(),
            new Headers())
        );

        $url = Book::getDownloadUrl($bookboon, 'db98ac1b-435f-456b-9bdd-a2ba00d41a58', ['handle' => 'phpunit']);
        self::assertStringContainsString('/download/', $url);
    }

    public function testGetSearch() : void
    {
        // choose a query with almost certain response;
        $search = Book::search(self::$bookboon, 'engineering')->getEntityStore()->get();
        self::assertCount(10, $search);
    }

    public function testGetRecommendations() : void
    {
        $bResponse = Book::recommendations(self::$bookboon);

        self::assertCount(5, $bResponse->getEntityStore()->get());
    }

    public function testGetRecommendationsSpecific() : void
    {
        $recommendations = Book::recommendations(self::$bookboon, ['3bf58559-034f-4676-bb5f-a2c101015a58'], 8)->getEntityStore()->get();
        self::assertCount(8, $recommendations);
    }
}
