<?php

namespace Bookboon\Api\Entity;

use Bookboon\Api\Bookboon;
use Bookboon\Api\Client\BookboonResponse;
use Bookboon\Api\Client\ClientInterface;

class Question extends Entity
{
    /**
     * Questions.
     *
     * @param Bookboon $bookboon
     * @param array $answerIds array of answer ids, can be empty
     * @param string $rootSegmentId
     * @return BookboonResponse
     * @throws \Bookboon\Api\Exception\UsageException
     */
    public static function get(
        Bookboon $bookboon,
        array $answerIds = [],
        string $rootSegmentId = ''
    ) : BookboonResponse {
        $url = $rootSegmentId == '' ? '/v1/questions' : '/v1/questions/' . $rootSegmentId;
        $bResponse =  $bookboon->rawRequest($url, ['answer' => $answerIds]);

        $bResponse->setEntityStore(
            new EntityStore(Question::getEntitiesFromArray($bResponse->getReturnArray()))
        );

        return $bResponse;
    }

    /**
     * Post Questions.
     *
     * @param Bookboon $bookboon
     * @param array $variables
     * @param string $rootSegmentId
     * @return BookboonResponse
     * @throws \Bookboon\Api\Exception\UsageException
     */
    public static function send(
        Bookboon $bookboon,
        array $variables = [],
        string $rootSegmentId = ''
    ) : BookboonResponse {
        $url = $rootSegmentId == '' ? '/v1/questions' : '/v1/questions/' . $rootSegmentId;
        $bResponse = $bookboon->rawRequest($url, $variables, ClientInterface::HTTP_POST);
        return $bResponse;
    }

    protected function isValid(array $array) : bool
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
