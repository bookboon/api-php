<?php

namespace Bookboon\Api\Entity;

use Bookboon\Api\Bookboon;
use Bookboon\Api\Client\BookboonResponse;
use Bookboon\Api\Client\ClientInterface;
use Bookboon\Api\Exception\UsageException;

class Journey extends Entity
{
    /** @var array<Book> */
    private $books;

    /**
     * @param Bookboon $bookboon
     * @param string $journeyId
     * @return BookboonResponse<Journey>
     * @throws \Bookboon\Api\Exception\UsageException
     */
    public static function get(Bookboon $bookboon, string $journeyId) : BookboonResponse
    {
        $bResponse = $bookboon->rawRequest(
            "/v1/journeys/$journeyId",
            [],
            ClientInterface::HTTP_GET,
            true,
            Journey::class
        );

        $journeyEntity = new self($bResponse->getReturnArray());

        if (count($journeyEntity->getBookIds()) > 0) {
            $books = Book::getMultiple($bookboon, $journeyEntity->getBookIds(), false);
            $journeyEntity->setBooks($books->getEntityStore()->get());
        }

        $bResponse->setEntityStore(
            new EntityStore(
                [
                    $journeyEntity
                ],
                Journey::class
            )
        );

        return $bResponse;
    }

    /**
     * Get all journeys
     *
     * @param Bookboon $bookboon
     * @param array<string> $bookTypes
     * @return BookboonResponse<Journey>
     * @throws UsageException
     * @throws \Bookboon\Api\Exception\ApiDecodeException
     */
    public static function getAll(Bookboon $bookboon, array $bookTypes = ['professional']) : BookboonResponse
    {
        $bResponse = $bookboon->rawRequest(
            '/v1/journeys',
            [],
            ClientInterface::HTTP_GET,
            true,
            Journey::class
        );

        $bResponse->setEntityStore(
            new EntityStore(Journey::getEntitiesFromArray($bResponse->getReturnArray()), Journey::class),
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
     * Returns closes thumbnail size to input, default 210px.
     *
     * @param int  $size appromimate size
     *
     * @return string url for thumbnail
     */
    public function getThumbnail(int $size = 210)
    {
        return $this->thumbnailResolver($this->safeGet('thumbnail', []), $size);
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
