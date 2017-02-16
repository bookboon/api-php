<?php

namespace Bookboon\Api\Entity;

use Bookboon\Api\Bookboon;
use Bookboon\Api\Exception\UsageException;

class Frontpage extends Entity
{
    const SLUG_MOST_POPULAR = 'most-popular';
    const SLUG_NEW_TITLES = 'new-titles';
    const SLUG_EDITORS_PICKS = 'editors-picks';
    const SLUG_HIGHEST_RATED = 'highest-rated';

    /**
     * Get all front page books
     *
     * @param Bookboon $bookboon
     * @return Frontpage[]
     */
    public static function get(Bookboon $bookboon)
    {
        return Frontpage::getEntitiesFromArray($bookboon->rawRequest("/frontpage"));
    }

    /**
     * Get specific frontpage
     *
     * @param Bookboon $bookboon
     * @param $slug
     * @return Frontpage
     * @throws UsageException
     */
    public static function getBySlug(Bookboon $bookboon, $slug)
    {
        foreach (self::get($bookboon) as $frontpage) {
            if ($frontpage->getSlug() === $slug) {
                return $frontpage;
            }
        }

        throw new UsageException("Non-existing slug");
    }

    protected function isValid(array $array)
    {
        return isset($array['_slug'], $array['title'], $array['books']);
    }

    /**
     * @return string slug
     */
    public function getSlug()
    {
        return $this->safeGet('_slug');
    }

    /**
     * @return string title
     */
    public function getTitle()
    {
        return $this->safeGet('title');
    }


    /**
     * @return Book[] books in category
     */
    public function getBooks()
    {
        return Book::getEntitiesFromArray($this->safeGet('books', array()));
    }
}