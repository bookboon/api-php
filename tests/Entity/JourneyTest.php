<?php

namespace Bookboon\Api\Entity;

use Bookboon\Api\Bookboon;
use PHPUnit\Framework\TestCase;

/**
 * Class JourneyTest
 * @package Bookboon\Api\Entity
 * @group entity
 */
class JourneyTest extends TestCase
{
    private $methodsToTest = ['getTitle', 'getId', 'getAbstract', 'getDescription', 'getPublished'];
    private static $bookboon;

    public static function setUpBeforeClass()
    {
        include_once(__DIR__ . '/../Helpers.php');
        self::$bookboon = \Helpers::getBookboon();
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

    public function testFirstAllJourneysData()
    {
        $data = Journey::getAll(self::$bookboon)
            ->getEntityStore()
            ->get()[0];

        foreach ($this->methodsToTest as $method) {
            $this->assertNotFalse($data->$method());
        }
    }

    public function testGetJourney()
    {
        $data = Journey::get(self::$bookboon, '010e0268-0eec-4859-a67a-ce41ee2315c4')
            ->getEntityStore()
            ->get()[0];

        foreach ($this->methodsToTest as $method) {
            $this->assertNotFalse($data->$method());
        }

        $this->assertInstanceOf(Book::class, $data->getBooks()[0]);
    }

    /**
     * @expectedException \Bookboon\Api\Exception\EntityDataException
     */
    public function testInvalidJourney()
    {
        $language = new Language(['blah']);
    }
}
