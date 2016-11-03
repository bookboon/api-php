<?php

namespace Bookboon\Api\Entity;

use Bookboon\Api\Bookboon;

/**
 * Class CategoryTest
 * @package Bookboon\Api\Entity
 * @group entity
 */
class CategoryTest extends \PHPUnit_Framework_TestCase
{
    private static $data = null;

    public static function setUpBeforeClass()
    {
        $id = getenv('BOOKBOON_API_ID');
        $key = getenv('BOOKBOON_API_KEY');

        $bookboon = new Bookboon($id, $key);
        self::$data = Category::get($bookboon, '062adfac-844b-4e8c-9242-a1620108325e');
    }

    public function testGetId()
    {
        $this->assertEquals('062adfac-844b-4e8c-9242-a1620108325e', self::$data->getId());
    }

    public function providerTestGetters()
    {
        return array(
            'getName' => array('getName'),
            'getHomepage' => array('getHomepage'),
            'getDescription' => array('getDescription'),
            'getBooks' => array('getBooks'),
            'getCategories' => array('getCategories'),
        );
    }

    /**
     * @dataProvider providerTestGetters
     */
    public function testNotFalse($method)
    {
        $this->assertNotFalse(self::$data->$method());
    }

    /**
     * @expectedException \Bookboon\Api\Entity\EntityDataException
     */
    public function testInvalidCategory()
    {
        $category = new Category(array('blah'));
    }
}
