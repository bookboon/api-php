<?php

namespace Bookboon\Api\Entity;

use Bookboon\Api\Bookboon;
use PHPUnit\Framework\TestCase;

/**
 * Class LanguageTest
 * @package Bookboon\Api\Entity
 * @group entity
 */
class LanguageTest extends TestCase
{
    /** @var []Language */
    private static $data = null;

    public static function setUpBeforeClass()
    {
        include_once(__DIR__ . '/../Helpers.php');
        $bookboon = \Helpers::getBookboon();
        self::$data = Language::get($bookboon)
            ->getEntityStore()
            ->get()[0];
    }

    public function providerTestGetters()
    {
        return [
            'getName' => ['getName'],
            'getId' => ['getId'],
            'getLocalizedName' => ['getLocalizedName'],
            'getCode' => ['getCode'],
        ];
    }

    /**
     * @dataProvider providerTestGetters
     */
    public function testNotFalse($method)
    {
        $this->assertNotFalse(self::$data->$method());
    }

    /**
     * @expectedException \Bookboon\Api\Exception\EntityDataException
     */
    public function testInvalidLanguage()
    {
        $language = new Language(['blah']);
    }
}
