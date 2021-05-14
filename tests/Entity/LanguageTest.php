<?php

namespace Bookboon\Api\Entity;

use Bookboon\Api\Bookboon;
use Bookboon\Api\Exception\EntityDataException;
use PHPUnit\Framework\TestCase;
use Helpers\Helpers;
/**
 * Class LanguageTest
 * @package Bookboon\Api\Entity
 * @group entity
 */
class LanguageTest extends TestCase
{
    /** @var []Language */
    private static $data = null;

    public static function setUpBeforeClass() : void
    {
        $bookboon = Helpers::getBookboon();
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
    public function testNotFalse($method) : void
    {
        self::assertNotFalse(self::$data->$method());
    }

    public function testInvalidLanguage() : void
    {
        $this->expectException(EntityDataException::class);
        $language = new Language(['blah']);
    }
}
