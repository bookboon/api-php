<?php

namespace Bookboon\Api\Entity;

use Bookboon\Api\Exception\EntityDataException;
use PHPUnit\Framework\TestCase;
use Helpers\Helpers;

/**
 * Class JourneyTest
 * @package Bookboon\Api\Entity
 * @group entity
 */
class JourneyTest extends TestCase
{
    private $methodsToTest = ['getTitle', 'getId', 'getAbstract', 'getDescription', 'getPublished'];
    private static $bookboon;

    public static function setUpBeforeClass() : void
    {
        self::$bookboon = Helpers::getBookboon();
    }

    public function providerTestGetters()
    {
        return [
            'getTitle' => ['getTitle'],
            'getId' => ['getId'],
            'getAbstract' => ['getAbstract'],
            'getDescription' => ['getDescription'],
            'getPublished' => ['getPublished'],
        ];
    }

    public function testFirstAllJourneysData() : void
    {
        $data = Journey::getAll(self::$bookboon)
            ->getEntityStore()
            ->get()[0];

        foreach ($this->methodsToTest as $method) {
            self::assertNotFalse($data->$method());
        }
    }

    public function testGetJourney() : void
    {
        $data = Journey::get(self::$bookboon, '40b8b453-4ce9-425b-baa9-a8d88a589e3d')
            ->getEntityStore()
            ->get()[0];

        foreach ($this->methodsToTest as $method) {
            self::assertNotFalse($data->$method());
        }

        self::assertInstanceOf(Book::class, $data->getBooks()[0]);
    }

    public function testInvalidJourney() : void
    {
        $this->expectException(EntityDataException::class);
        $language = new Language(['blah']);
    }
}
