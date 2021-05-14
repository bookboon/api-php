<?php

namespace Bookboon\Api\Entity;

use Bookboon\Api\Exception\EntityDataException;
use PHPUnit\Framework\TestCase;
use Helpers\Helpers;

/**
 * Class QuestionTest
 * @package Bookboon\Api\Entity
 * @group entity
 */
class QuestionTest extends TestCase
{
    private static $data = null;
    private static $bookboon = null;
    private static $API_ID;
    private static $API_KEY;

    public static function setUpBeforeClass() : void
    {
        self::$bookboon = Helpers::getBookboon();
        self::$data = Question::get(self::$bookboon)->getEntityStore()->get();
    }

    public function testGetText() : void
    {
        $firstQuestion = self::$data[0];
        self::assertNotEmpty($firstQuestion->getText());
    }

    public function testGetAnsweres() : void
    {
        $firstQuestion = self::$data[0];
        self::assertNotEmpty($firstQuestion->getAnswers());
    }

    public function testSecondQuestions() : void
    {
        $firstQuestion = self::$data[0];
        $answers = $firstQuestion->getAnswers();
        $firstAnswer = $answers[0];

        $questions = Question::get(self::$bookboon, [$firstAnswer->getId()])->getEntityStore()->get();
        self::assertGreaterThan(1, count($questions));
    }

    public function testInvalidQuestion() : void
    {
        $this->expectException(EntityDataException::class);
        $question = new Question(['blah']);
    }
}
