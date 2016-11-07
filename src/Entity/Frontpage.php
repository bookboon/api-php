<?php

namespace Entity;


use Bookboon\Api\Bookboon;
use Bookboon\Api\Entity\Book;
use Bookboon\Api\Entity\Entity;

class Frontpage extends Entity
{
    const SLUG_MOST_POPULAR = 'most-popular';
    const SLUG_NEW_TITLES = 'new-titles';
    const SLUG_EDITORS_PICKS = 'editors-picks';
    const SLUG_HIGHEST_RATED = 'highest-rated';

    /**
     * Get Frontpages.
     *
     * @param Bookboon $bookboon
     * @return Frontpage|bool
     */
    public static function get(Bookboon $bookboon)
    {
        return new static($bookboon->rawRequest("/frontpage"));
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