<?php

namespace Bookboon\Api\Entity;

use Bookboon\Api\Bookboon;
use PHPUnit\Framework\TestCase;

/**
 * Class CategoryTest
 * @package Bookboon\Api\Entity
 * @group entity
 */
class CategoryTest extends TestCase
{
    private static $data = null;
    private static $bookboon = null;

    public static function setUpBeforeClass()
    {
        include_once(__DIR__ . '/../Helpers.php');
        self::$bookboon = \Helpers::getBookboon();
        self::$data = Category::get(self::$bookboon, '062adfac-844b-4e8c-9242-a1620108325e')
            ->getEntityStore()
            ->getSingle();
    }

    public function testGetId()
    {
        $this->assertEquals('062adfac-844b-4e8c-9242-a1620108325e', self::$data->getId());
    }

    public function providerTestGetters()
    {
        return [
            'getName' => ['getName'],
            'getHomepage' => ['getHomepage'],
            'getDescription' => ['getDescription'],
            'getBooks' => ['getBooks'],
            'getCategories' => ['getCategories'],
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
    public function testInvalidCategory()
    {
        $category = new Category(['blah']);
    }

    public function testGetCategoryTree()
    {
        $categories = Category::getTree(self::$bookboon)->getEntityStore()->get();
        $this->assertGreaterThan(10, count($categories));
    }

    public function testGetCategoryTreeBlacklist()
    {
        $hiddenId = 'a382f37c-dc28-400a-9838-a17700ad5472';
        $categories = Category::getTree(self::$bookboon, [$hiddenId]);

        $categoryIds = array_map(
            static function (Category $item) {
                return $item->getId();
            },
            $categories->getEntityStore()->get()
        );

        $this->assertNotContains($hiddenId, $categoryIds);
    }

    public function testCategoryDownload()
    {
        $url = Category::getDownloadUrl(self::$bookboon, '062adfac-844b-4e8c-9242-a1620108325e', ['handle' => 'phpunit']);
        $this->assertContains('/download/', $url);
    }
}
