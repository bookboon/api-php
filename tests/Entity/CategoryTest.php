<?php

namespace Bookboon\Api\Entity;

use Bookboon\Api\Bookboon;
use Bookboon\Api\Exception\EntityDataException;
use PHPUnit\Framework\TestCase;
use Helpers\Helpers;

/**
 * Class CategoryTest
 * @package Bookboon\Api\Entity
 * @group entity
 */
class CategoryTest extends TestCase
{
    private static $data = null;
    private static $bookboon = null;

    public static function setUpBeforeClass() : void
    {
        self::$bookboon = Helpers::getBookboon();
        self::$data = Category::get(self::$bookboon, '062adfac-844b-4e8c-9242-a1620108325e')
            ->getEntityStore()
            ->getSingle();
    }

    public function testGetId() : void
    {
        self::assertEquals('062adfac-844b-4e8c-9242-a1620108325e', self::$data->getId());
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
    public function testNotFalse($method) : void
    {
        self::assertNotFalse(self::$data->$method());
    }

    public function testInvalidCategory() : void
    {
        $this->expectException(EntityDataException::class);
        $category = new Category(['blah']);
    }

    public function testGetCategoryTree() : void
    {
        $categories = Category::getTree(self::$bookboon)->getEntityStore()->get();
        self::assertGreaterThan(10, count($categories));
    }

    public function testGetCategoryTreeBlacklist() : void
    {
        $hiddenId = 'a382f37c-dc28-400a-9838-a17700ad5472';
        $categories = Category::getTree(self::$bookboon, [$hiddenId]);

        $categoryIds = array_map(
            static function (Category $item) {
                return $item->getId();
            },
            $categories->getEntityStore()->get()
        );

        self::assertNotContains($hiddenId, $categoryIds);
    }

    public function testCategoryDownload() : void
    {
        $url = Category::getDownloadUrl(self::$bookboon, '062adfac-844b-4e8c-9242-a1620108325e', ['handle' => 'phpunit']);
        self::assertStringContainsString('/download/', $url);
    }
}
