<?php

namespace Bookboon\Api\Entity;

use Bookboon\Api\Bookboon;
use Bookboon\Api\Client\BookboonResponse;
use Bookboon\Api\Exception\UsageException;

class Journey extends Entity
{
    private $books;

    /**
     * @param Bookboon $bookboon
     * @param string $journeyId
     * @return BookboonResponse
     * @throws \Bookboon\Api\Exception\UsageException
     */
    public static function get(Bookboon $bookboon, string $journeyId) : BookboonResponse
    {
        $bResponse = $bookboon->rawRequest("/journeys/$journeyId");

        $journeyEntity = new static($bResponse->getReturnArray());

        if (count($journeyEntity->getBookIds()) > 0) {
            $books = Book::getMultiple($bookboon, $journeyEntity->getBookIds(), false);
        }

        $journeyEntity->setBooks($books->getEntityStore()->get());

        $bResponse->setEntityStore(
            new EntityStore(
                [
                    $journeyEntity
                ]
            )
        );

        return $bResponse;
    }

    /**
     * Get all journeys
     *
     * @param Bookboon $bookboon
     * @param array $bookTypes
     * @return BookboonResponse
     * @throws UsageException
     * @throws \Bookboon\Api\Exception\ApiDecodeException
     */
    public static function getAll(Bookboon $bookboon, array $bookTypes = ['pdf']) : BookboonResponse
    {
        $bResponse = $bookboon->rawRequest('/journeys');

        $bResponse->setEntityStore(
            new EntityStore(Journey::getEntitiesFromArray($bResponse->getReturnArray()))
        );

        return $bResponse;
    }

    /**
     * @return string id
     */
    public function getId() : string
    {
        return $this->safeGet('_id');
    }

    /**
     * @return string title
     */
    public function getTitle() : string
    {
        return $this->safeGet('title');
    }

    /**
     * @return string abstract
     */
    public function getAbstract() : string
    {
        return $this->safeGet('abstract');
    }

    /**
     * @return string description
     */
    public function getDescription() : string
    {
        return $this->safeGet('description');
    }

    /**
     * Return publish date.
     *
     * @return string date of publishing
     */
    public function getPublished() : string
    {
        return $this->safeGet('published');
    }

    /**
     * @return bool is featured
     */
    public function isFeatured() : bool
    {
        return $this->safeGet('isFeatured');
    }

    /**
     * @return array book ids
     */
    public function getBookIds() : array
    {
        return $this->safeGet('books', []);
    }

    /**
     * @return array books
     */
    public function getBooks() : array
    {
        return $this->books;
    }

    /**
     * @param array $books
     * @return void
     */
    public function setBooks(array $books) : void
    {
        $this->books = $books;
    }

    /**
     * Determines whether api response is valid
     *
     * @param array $array
     * @return bool
     */
    protected function isValid(array $array): bool
    {
        return isset($array['_id'], $array['title'], $array['abstract']);
    }
}
