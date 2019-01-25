<?php

namespace Bookboon\Api\Entity;

use Bookboon\Api\Bookboon;
use Bookboon\Api\Client\BookboonResponse;
use Bookboon\Api\Client\ClientInterface;
use Bookboon\Api\Exception\BadUUIDException;

class Review extends Entity
{
    /**
     * Get Reviews for specified Book.
     *
     * @param Bookboon $bookboon
     * @param string $bookId
     * @return BookboonResponse
     *
     * @throws BadUUIDException
     * @throws \Bookboon\Api\Exception\UsageException
     */
    public static function getByBookId(Bookboon $bookboon, string $bookId) : BookboonResponse
    {
        if (Entity::isValidUUID($bookId) === false) {
            throw new BadUUIDException();
        }

        $bResponse = $bookboon->rawRequest("/books/$bookId/review");

        $bResponse->setEntityStore(
            new EntityStore(Review::getEntitiesFromArray($bResponse->getReturnArray()))
        );

        return $bResponse;
    }

    /**
     * @param array $review
     * @return static
     * @throws \Bookboon\Api\Exception\EntityDataException
     */
    public static function create(array $review = []) : Review
    {
        return new static($review);
    }


    /**
     * submit a book review helper method.
     *
     * @param Bookboon $bookboon
     * @param string $bookId
     *
     * @throws \Bookboon\Api\Exception\UsageException
     * @return void
     */
    public function submit(Bookboon $bookboon, string $bookId) : void
    {
        if (Entity::isValidUUID($bookId)) {
            $bookboon->rawRequest("/books/$bookId/review", $this->getData(), ClientInterface::HTTP_POST);
        }
    }

    /**
     * @param array $array
     * @return bool
     */
    protected function isValid(array $array) : bool
    {
        return isset($array['comment'], $array['rating']);
    }

    /**
     * @return string author of review
     */
    public function getAuthor()
    {
        return $this->safeGet('author');
    }

    /**
     * @return string created date
     */
    public function getCreated()
    {
        return $this->safeGet('created');
    }

    /**
     * @return string user inputted comment
     */
    public function getComment()
    {
        return $this->safeGet('comment');
    }

    /**
     * @return mixed
     */
    public function getRating()
    {
        return $this->safeGet('rating');
    }
}
