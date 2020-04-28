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
     * @return BookboonResponse
     * @throws BadUUIDException
     * @throws \Bookboon\Api\Exception\EntityDataException
     * @throws \Bookboon\Api\Exception\UsageException
     */
    public static function get(Bookboon $bookboon, string $examId) : BookboonResponse
    {
        if (Entity::isValidUUID($examId) === false) {
            throw new BadUUIDException();
        }

        $bResponse = $bookboon->rawRequest("/v1/exams/$examId");

        $bResponse->setEntityStore(
            new EntityStore(
                [
                    new self($bResponse->getReturnArray())
                ]
            )
        );

        return $bResponse;
    }

    /**
     * @param Bookboon $bookboon
     * @param string $bookId
     * @return BookboonResponse
     * @throws \Bookboon\Api\Exception\UsageException
     */
    public static function getByBookId(Bookboon $bookboon, string $bookId) : BookboonResponse
    {
        $bResponse = $bookboon->rawRequest("/v1/books/$bookId/exams");

        $bResponse->setEntityStore(
            new EntityStore(Exam::getEntitiesFromArray($bResponse->getReturnArray()))
        );

        return $bResponse;
    }

    /**
     * Get many exams
     *
     * @param Bookboon $bookboon
     * @return BookboonResponse
     * @throws \Bookboon\Api\Exception\UsageException
     */
    public static function getAll(Bookboon $bookboon) : BookboonResponse
    {
        $bResponse = $bookboon->rawRequest("/v1/exams");

        $bResponse->setEntityStore(
            new EntityStore(static::getEntitiesFromArray($bResponse->getReturnArray()))
        );

        return $bResponse;
    }

    public static function start(Bookboon $bookboon, $examId)
    {
        return $bookboon->rawRequest("/v1/exams/$examId", [], ClientInterface::HTTP_POST)->getReturnArray();
    }

    public static function finish(Bookboon $bookboon, $examId, $postVars)
    {
        return $bookboon->rawRequest("/v1/exams/$examId/submit", $postVars, ClientInterface::HTTP_POST)->getReturnArray();
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
