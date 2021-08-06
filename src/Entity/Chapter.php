<?php

namespace Bookboon\Api\Entity;

use Bookboon\Api\Bookboon;
use Bookboon\Api\Client\BookboonResponse;
use Bookboon\Api\Client\ClientInterface;
use Bookboon\Api\Exception\BadUUIDException;

class Chapter extends Entity
{
    /**
     * @param Bookboon $bookboon
     * @param string $authorId
     * @return BookboonResponse<Chapter>
     * @throws BadUUIDException
     * @throws \Bookboon\Api\Exception\EntityDataException
     * @throws \Bookboon\Api\Exception\UsageException
     */
    public static function getByBookId(Bookboon $bookboon, string $bookId) : BookboonResponse
    {
        if (Entity::isValidUUID($bookId) === false) {
            throw new BadUUIDException();
        }

        $bResponse = $bookboon->rawRequest(
            "/v1/books/$bookId/chapters",
            [],
            ClientInterface::HTTP_GET,
            true,
            Chapter::class
        );

        $bResponse->setEntityStore(
            new EntityStore(static::getEntitiesFromArray($bResponse->getReturnArray()), Chapter::class)
        );

        return $bResponse;
    }

    protected function isValid(array $array) : bool
    {
        return isset($array['_id'], $array['duration'], $array['position'], $array['title']);
    }
    

    /**
     * @return string UUID of entity
     */
    public function getId() : string
    {
        return $this->safeGet('_id');
    }

    /**
     * Return position of the chapter.
     *
     * @return int position
     */
    public function getPosition() : int
    {
        return $this->safeGet('position');
    }

    /**
     * Return duration of the chapter.
     *
     * @return int duration
     */
    public function getDuration() : int
    {
        return $this->safeGet('duration');
    }

    /**
     * Return title of the chapter
     *
     * @return string title
     */
    public function getTitle()
    {
        return $this->safeGet('title');
    }
}
