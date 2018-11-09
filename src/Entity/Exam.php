<?php

namespace Bookboon\Api\Entity;

use Bookboon\Api\Bookboon;
use Bookboon\Api\Client\BookboonResponse;
use Bookboon\Api\Client\Client;
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
     */
    public static function get(Bookboon $bookboon, $examId)
    {
        if (Entity::isValidUUID($examId) === false) {
            throw new BadUUIDException("UUID Not Formatted Correctly");
        }

        $bResponse = $bookboon->rawRequest("/exams/$examId");

        $bResponse->setEntityStore(
            new EntityStore(
                [
                    new static($bResponse->getReturnArray())
                ]
            )
        );

        return $bResponse;
    }

    /**
     * @param Bookboon $bookboon
     * @param $bookId
     * @return BookboonResponse
     * @throws BadUUIDException
     */
    public static function getByBookId(Bookboon $bookboon, $bookId)
    {
        $bResponse = $bookboon->rawRequest("/books/$bookId/exams");

        $bResponse->setEntityStore(
            new EntityStore(
                [
                    Exam::getEntitiesFromArray($bResponse->getReturnArray())
                ]
            )
        );

        return $bResponse;
    }

    /**
     * Get many exams
     *
     * @param Bookboon $bookboon
     * @return BookboonResponse
     */
    public static function getAll(Bookboon $bookboon)
    {
        $bResponse = $bookboon->rawRequest("/exams");

        $bResponse->setEntityStore(
            new EntityStore(
                [
                    static::getEntitiesFromArray($bResponse->getReturnArray())
                ]
            )
        );

        return $bResponse;
    }

    public static function start(Bookboon $bookboon, $examId)
    {
        return $bookboon->rawRequest("/exams/$examId", [], Client::HTTP_POST)->getReturnArray();
    }

    public static function finish(Bookboon $bookboon, $examId, $postVars)
    {
        return $bookboon->rawRequest("/exams/$examId/submit", $postVars, Client::HTTP_POST)->getReturnArray();
    }

    protected function isValid(array $array)
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
        return Book::getEntitiesFromArray($this->safeGet('book', array()));
    }

    /**
     * @return ExamQuestion[]
     */
    public function getQuestions()
    {
        return ExamQuestion::getEntitiesFromArray($this->safeGet('questions'));
    }
}