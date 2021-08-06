<?php

namespace Bookboon\Api\Entity;

use Bookboon\Api\Bookboon;
use Bookboon\Api\Client\BookboonResponse;
use Bookboon\Api\Client\ClientInterface;
use Bookboon\Api\Exception\BadUUIDException;

class TimeTag extends Entity
{
    /**
     * @param Bookboon $bookboon
     * @param string $authorId
     * @return BookboonResponse<TimeTag>
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
            "/v1/books/$bookId/timetags",
            [],
            ClientInterface::HTTP_GET,
            true,
            TimeTag::class
        );

        $bResponse->setEntityStore(
            new EntityStore(static::getEntitiesFromArray($bResponse->getReturnArray()), TimeTag::class)
        );

        return $bResponse;
    }

    protected function isValid(array $array) : bool
    {
        return isset($array['_id'], $array['offset'], $array['name']);
    }
    

    /**
     * @return string UUID of entity
     */
    public function getId() : string
    {
        return $this->safeGet('_id');
    }

    /**
     * Return offset of the timetag.
     *
     * @return int offset
     */
    public function getOffset() : int
    {
        return $this->safeGet('offset');
    }

    /**
     * Return name of the chapter
     *
     * @return string name
     */
    public function getName()
    {
        return $this->safeGet('name');
    }
}
