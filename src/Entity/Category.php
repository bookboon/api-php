<?php

namespace Bookboon\Api\Entity;

use Bookboon\Api\Bookboon;
use Bookboon\Api\Client\Client;
use Bookboon\Api\Exception\BadUUIDException;

class Category extends Entity
{
    const TEXTBOOKS = 'd1fabb36-4eff-4760-a80d-a15700efa9ae';
    const BUSINESS = '82403e77-ccbf-4e10-875c-a15700ef8a56';

    /**
     * Get Category.
     *
     * @param Bookboon $bookboon
     * @param string $categoryId
     * @return Category|bool
     * @throws BadUUIDException
     */
    public static function get(Bookboon $bookboon, $categoryId)
    {
        if (Entity::isValidUUID($categoryId) === false) {
            throw new BadUUIDException("UUID Not Formatted Correctly");
        }

        return new static($bookboon->rawRequest("/categories/$categoryId"));
    }

    /**
     * Returns the entire Category structure.
     *
     * @param Bookboon $bookboon
     * @param array $blacklistedCategoryIds
     * @param int $depth level of recursion (default 2 maximum, 0 no recursion)
     * @return Category[]
     */
    public static function getCategoryTree(Bookboon $bookboon, array $blacklistedCategoryIds = array(), $depth = 2)
    {
        $categories = $bookboon->rawRequest('/categories', array('depth' => $depth));

        if (count($blacklistedCategoryIds) !== 0) {
            self::recursiveBlacklist($categories, $blacklistedCategoryIds);
        }

        return Category::getEntitiesFromArray($categories);
    }

    private static function recursiveBlacklist(&$categories, $blacklistedCategoryIds)
    {
        foreach ($categories as $key => $category) {
            if (in_array($category['_id'], $blacklistedCategoryIds)) {
                unset($categories[$key]);
                continue;
            }
            if (isset($category['categories'])) {
                self::recursiveBlacklist($categories[$key]['categories'], $blacklistedCategoryIds);
            }
        }
    }

    /**
     * Get the download url.
     *
     * @param Bookboon $bookboon
     * @param $categoryId
     * @param array $variables
     * @return string
     */
    public static function getDownloadUrl(Bookboon $bookboon, $categoryId, array $variables)
    {
        $download = $bookboon->rawRequest("/categories/$categoryId/download", $variables, Client::HTTP_POST);

        return $download['url'];
    }

    protected function isValid(array $array)
    {
        return isset($array['_id'], $array['name'], $array['description'], $array['homepage']);
    }

    /**
     * @return string UUID of entity
     */
    public function getId()
    {
        return $this->safeGet('_id');
    }

    /**
     * @return string name of entity
     */
    public function getName()
    {
        return $this->safeGet('name');
    }

    /**
     * @return string link to category on bookboon.com
     */
    public function getHomepage()
    {
        return $this->safeGet('homepage');
    }

    /**
     * @return string category description
     */
    public function getDescription()
    {
        return $this->safeGet('description');
    }

    /**
     * @return Category[] of Category objects
     */
    public function getCategories()
    {
        return self::getEntitiesFromArray($this->safeGet('categories', array()));
    }

    /**
     * @return Book[] books in category
     */
    public function getBooks()
    {
        return Book::getEntitiesFromArray($this->safeGet('books', array()));
    }
}
