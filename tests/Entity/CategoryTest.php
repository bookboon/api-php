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
        include_once(__DIR__ . '/../Authentication.php');
        $bookboon = new Bookboon(\Authentication::getApiId(), \Authentication::getApiSecret());
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

    public function testGetCategoryTree()
    {
        $bookboon = new Bookboon(\Authentication::getApiId(), \Authentication::getApiSecret());
        $categories = Category::getTree($bookboon);
        $this->assertEquals(2, count($categories));
    }

    public function testGetCategoryTreeBlacklist()
    {
        $bookboon = new Bookboon(\Authentication::getApiId(), \Authentication::getApiSecret());
        $categories = Category::getTree($bookboon, array('82403e77-ccbf-4e10-875c-a15700ef8a56', '07651831-1c44-4815-87a2-a2b500f5934a'));
        $this->assertEquals(1, count($categories));
    }

    public function testCategoryDownload()
    {
        $bookboon = new Bookboon(\Authentication::getApiId(), \Authentication::getApiSecret());

        $url = Category::getDownloadUrl($bookboon, '062adfac-844b-4e8c-9242-a1620108325e', array('handle' => 'phpunit'));
        $this->assertContains('/download/', $url);
    }
}
