<?php

namespace Bookboon\Api\Entity;

use Bookboon\Api\Bookboon;
use Bookboon\Api\Client\Client;
use Bookboon\Api\Exception\BadUUIDException;

class Review extends Entity
{

    /**
     * Get Reviews for specified Book.
     *
     * @param $bookId
     * @return array of Review objects
     *
     * @throws BadUUIDException
     */
    public static function getByBookId(Bookboon $bookboon, $bookId)
    {
        if (Entity::isValidUUID($bookId) === false) {
            throw new BadUUIDException("UUID Not Formatted Correctly");
        }

        $reviews = $bookboon->rawRequest("/books/$bookId/review");

        return Review::getEntitiesFromArray($reviews);
    }

    /**
     * @param array $review
     * @return static
     */
    public static function create($review = [])
    {
        return new static($review);
    }


    /**
     * submit a book review helper method.
     *
     * @param Bookboon $bookboon
     * @param $bookId
     *
     */
    public function submit(Bookboon $bookboon, $bookId)
    {
        if (Entity::isValidUUID($bookId)) {
            $bookboon->rawRequest("/books/$bookId/review", $this->getData(), Client::HTTP_POST);
        }
    }

    /**
     * @param array $array
     * @return bool
     */
    protected function isValid(array $array)
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
