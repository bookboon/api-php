<?php

namespace Bookboon\Api\Entity;

use Bookboon\Api\Bookboon;
use Bookboon\Api\Client\BookboonResponse;

class Question extends Entity
{
    /**
     * Questions.
     *
     * @param Bookboon $bookboon
     * @param array $answerIds array of answer ids, can be empty
     * @return BookboonResponse
     */
    public static function get(Bookboon $bookboon, array $answerIds = array())
    {
        $bResponse =  $bookboon->rawRequest('/questions', array('answer' => $answerIds));

        $bResponse->setEntityStore(
            new EntityStore(
                [
                    Question::getEntitiesFromArray($bResponse->getReturnArray())
                ]
            )
        );

        return $bResponse;
    }

    protected function isValid(array $array)
    {
        return isset($array['question'], $array['answers']);
    }

    /**
     * Returns question text.
     *
     * @return string
     */
    public function getText()
    {
        return $this->safeGet('question');
    }

    /**
     * @return Answer[]
     */
    public function getAnswers()
    {
        return Answer::getEntitiesFromArray($this->safeGet('answers'));
    }
}
