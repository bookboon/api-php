<?php

namespace Bookboon\Api\Entity;

use Bookboon\Api\Bookboon;
use Bookboon\Api\Client\Client;
use Bookboon\Api\Exception\BadUUIDException;

class Exam extends Entity
{
    /**
     * Get Exam.
     *
     * @param Bookboon $bookboon
     * @param string $examId
     * @return Exam
     * @throws BadUUIDException
     */
    public static function get(Bookboon $bookboon, $examId)
    {
        if (Entity::isValidUUID($examId) === false) {
            throw new BadUUIDException("UUID Not Formatted Correctly");
        }

        return new static($bookboon->rawRequest("/exams/$examId"));
    }

    public static function start(Bookboon $bookboon, $examId)
    {
        return $bookboon->rawRequest("/exams/$examId", [], Client::HTTP_POST);
    }

    public static function finish(Bookboon $bookboon, $examId, $postVars)
    {
        return $bookboon->rawRequest("/exams/$examId/submit", $postVars, Client::HTTP_POST);
    }

    protected function isValid(array $array)
    {
        return isset($array['_id'], $array['title'], $array['book'], $array['passScore'], $array['timeSeconds']);
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