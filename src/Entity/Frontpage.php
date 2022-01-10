<?php

namespace Bookboon\Api\Entity;

use Bookboon\Api\Bookboon;
use Bookboon\Api\Client\BookboonResponse;
use Bookboon\Api\Client\ClientInterface;
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
     * @param array $bookTypes
     * @return BookboonResponse<Frontpage>
     * @throws UsageException
     * @throws \Bookboon\Api\Exception\ApiDecodeException
     */
    public static function get(Bookboon $bookboon, array $bookTypes = ['professional'], ?int $limit = null) : BookboonResponse
    {
        $bResponse = $bookboon->rawRequest(
            '/v1/frontpage',
            ['bookType' => implode(',', $bookTypes), 'limit' => $limit],
            ClientInterface::HTTP_GET,
            true,
            Frontpage::class
        );

        $bResponse->setEntityStore(
            new EntityStore(self::getEntitiesFromArray($bResponse->getReturnArray()), Frontpage::class)
        );

        return $bResponse;
    }

    /**
     * Get specific frontpage
     *
     * @param Bookboon $bookboon
     * @param string $slug
     * @param array $bookTypes
     * @return BookboonResponse<Frontpage>
     * @throws UsageException
     * @throws \Bookboon\Api\Exception\ApiDecodeException
     * @throws \Bookboon\Api\Exception\EntityDataException
     */
    public static function getBySlug(Bookboon $bookboon, string $slug, array $bookTypes = ['professional'], ?int $limit = null, ?int $seed = null) : BookboonResponse
    {
        $bResponse = $bookboon->rawRequest(
            "/v1/frontpage/$slug",
            ['bookType' => join(',', $bookTypes), 'limit' => $limit, 'seed' => $seed],
            ClientInterface::HTTP_GET,
            true,
            Frontpage::class
        );

        $bResponse->setEntityStore(
            new EntityStore(
                [
                    new self($bResponse->getReturnArray())
                ],
                Frontpage::class
            )
        );

        return $bResponse;
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
