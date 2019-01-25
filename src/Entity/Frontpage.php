<?php

namespace Bookboon\Api\Entity;

use Bookboon\Api\Bookboon;
use Bookboon\Api\Client\BookboonResponse;
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
     * @return BookboonResponse
     * @throws UsageException
     */
    public static function get(Bookboon $bookboon) : BookboonResponse
    {
        $bResponse = $bookboon->rawRequest("/frontpage");

        $bResponse->setEntityStore(
            new EntityStore(
                [
                    Frontpage::getEntitiesFromArray($bResponse->getReturnArray())
                ]
            )
        );

        return $bResponse;
    }

    /**
     * Get specific frontpage
     *
     * @param Bookboon $bookboon
     * @param string $slug
     * @return Frontpage
     * @throws UsageException
     */
    public static function getBySlug(Bookboon $bookboon, string $slug) : Frontpage
    {
        $frontpageArray =  self::get($bookboon)->getEntityStore()->get();

        foreach ($frontpageArray as $frontpage) {
            if ($frontpage->getSlug() === $slug) {
                return $frontpage;
            }
        }

        throw new UsageException("Non-existing slug");
    }

    protected function isValid(array $array) : bool
    {
        return isset($array['_slug'], $array['title'], $array['books']);
    }

    /**
     * @param string $slug
     * @return bool
     */
    public static function isValidSlug(string $slug) : bool
    {
        return in_array(
            $slug,
            [
                static::SLUG_MOST_POPULAR,
                static::SLUG_NEW_TITLES,
                static::SLUG_EDITORS_PICKS,
                static::SLUG_HIGHEST_RATED
            ]
        );
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
    public function getBooks() : array
    {
        return Book::getEntitiesFromArray($this->safeGet('books', []));
    }
}