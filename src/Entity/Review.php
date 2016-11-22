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
     * submit a book review helper method.
     *
     * @param Bookboon $bookboon
     * @param $bookId
     * @param Review $review
     *
     */
    public function submit(Bookboon $bookboon, $bookId, Review $review)
    {
        if (Entity::isValidUUID($bookId)) {
            $bookboon->rawRequest("/books/$bookId/review", $review->getData(), Client::HTTP_POST);
        }
    }

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

    public function getRating()
    {
        return $this->safeGet('rating');
    }
}
