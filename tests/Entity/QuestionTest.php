<?php

namespace Bookboon\Api\Entity;

use Bookboon\Api\Bookboon;

/**
 * Class QuestionTest
 * @package Bookboon\Api\Entity
 * @group entity
 */
class QuestionTest extends \PHPUnit_Framework_TestCase
{
    private static $data = null;
    private static $API_ID;
    private static $API_KEY;

    public static function setUpBeforeClass()
    {
        self::$API_ID = getenv('BOOKBOON_API_ID');
        self::$API_KEY = getenv('BOOKBOON_API_KEY');

        $bookboon = new Bookboon(self::$API_ID, self::$API_KEY);
        self::$data = Question::get($bookboon);
    }

    public function testGetText()
    {
        $firstQuestion = self::$data[0];
        $this->assertNotEmpty($firstQuestion->getText());
    }

    public function testGetAnsweres()
    {
        $firstQuestion = self::$data[0];
        $this->assertNotEmpty($firstQuestion->getAnswers());
    }

    public function testSecondQuestions()
    {
        $firstQuestion = self::$data[0];
        $answers = $firstQuestion->getAnswers();
        $firstAnswer = $answers[0];

        $bookboon = new Bookboon(self::$API_ID, self::$API_KEY);

        $questions = Question::get($bookboon, array($firstAnswer->getId()));
        $this->assertGreaterThan(1, count($questions));
    }

    /**
     * @expectedException \Bookboon\Api\Entity\EntityDataException
     */
    public function testInvalidQuestion()
    {
        $question = new Question(array('blah'));
    }
}
