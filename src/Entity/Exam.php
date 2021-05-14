<?php

namespace Bookboon\Api\Entity;

use Bookboon\Api\Bookboon;
use Bookboon\Api\Client\BookboonResponse;
use Bookboon\Api\Client\ClientInterface;
use Bookboon\Api\Exception\BadUUIDException;

class Exam extends Entity
{
    /**
     * Get Exam.
     *
     * @param Bookboon $bookboon
     * @param string $examId
     * @return BookboonResponse<Exam>
     * @throws BadUUIDException
     * @throws \Bookboon\Api\Exception\EntityDataException
     * @throws \Bookboon\Api\Exception\UsageException
     */
    public static function get(Bookboon $bookboon, string $examId) : BookboonResponse
    {
        if (Entity::isValidUUID($examId) === false) {
            throw new BadUUIDException();
        }

        $bResponse = $bookboon->rawRequest(
            "/v1/exams/$examId",
            [],
            ClientInterface::HTTP_GET,
            true,
            Exam::class
        );

        $bResponse->setEntityStore(
            new EntityStore(
                [
                    new self($bResponse->getReturnArray())
                ],
                Exam::class
            )
        );

        return $bResponse;
    }

    /**
     * @param Bookboon $bookboon
     * @param string $bookId
     * @return BookboonResponse<Exam>
     * @throws \Bookboon\Api\Exception\UsageException
     */
    public static function getByBookId(Bookboon $bookboon, string $bookId) : BookboonResponse
    {
        $bResponse = $bookboon->rawRequest(
            "/v1/books/$bookId/exams",
            [],
            ClientInterface::HTTP_GET,
            true,
            Exam::class
        );

        $bResponse->setEntityStore(
            new EntityStore(Exam::getEntitiesFromArray($bResponse->getReturnArray()), Exam::class)
        );

        return $bResponse;
    }

    /**
     * Get many exams
     *
     * @param Bookboon $bookboon
     * @return BookboonResponse<Exam>
     * @throws \Bookboon\Api\Exception\UsageException
     */
    public static function getAll(Bookboon $bookboon) : BookboonResponse
    {
        $bResponse = $bookboon->rawRequest(
            '/v1/exams',
            [],
            ClientInterface::HTTP_GET,
            true,
            Exam::class
        );

        $bResponse->setEntityStore(
            new EntityStore(static::getEntitiesFromArray($bResponse->getReturnArray()), Exam::class)
        );

        return $bResponse;
    }

    public static function start(Bookboon $bookboon, string $examId) : array
    {
        return $bookboon->rawRequest(
            "/v1/exams/$examId",
            [],
            ClientInterface::HTTP_POST,
            true,
            Exam::class
        )->getReturnArray();
    }

    public static function finish(Bookboon $bookboon, string $examId, array $postVars) : array
    {
        return $bookboon->rawRequest(
            "/v1/exams/$examId/submit",
            $postVars,
            ClientInterface::HTTP_POST,
            false,
            Exam::class
        )->getReturnArray();
    }

    /**
     * @param array $array
     * @return bool
     */
    protected function isValid(array $array) : bool
    {
        return isset($array['_id'], $array['title'], $array['passScore'], $array['timeSeconds']);
    }

    /**
     * @return string UUID of entity
     */
    public function getId()
    {
        return $this->safeGet('_id');
    }

    /**
     * @return string title
     */
    public function getTitle()
    {
        return $this->safeGet('title');
    }

    /**
     * @return string passing score of exam
     */
    public function getPassScore()
    {
        return $this->safeGet('passScore');
    }

    /**
     * @return string passing score of exam
     */
    public function getTimeSeconds()
    {
        return $this->safeGet('timeSeconds');
    }

    /**
     * Return book to which the exam is related
     *
     * @return Book
     */
    public function getBook()
    {
        return Book::objectTransformer($this->safeGet('book', []));
    }

    /**
     * @return ExamQuestion[]
     */
    public function getQuestions()
    {
        return ExamQuestion::getEntitiesFromArray($this->safeGet('questions'));
    }
}
