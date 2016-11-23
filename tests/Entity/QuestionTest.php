<?php

namespace Bookboon\Api\Entity;


/**
 * Class QuestionTest
 * @package Bookboon\Api\Entity
 * @group entity
 */
class QuestionTest extends \PHPUnit_Framework_TestCase
{
    private static $data = null;
    private static $bookboon = null;
    private static $API_ID;
    private static $API_KEY;

    public static function setUpBeforeClass()
    {
        include_once(__DIR__ . '/../Helpers.php');
        self::$bookboon = \Helpers::getBookboon();
        self::$data = Question::get(self::$bookboon);
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

        $questions = Question::get(self::$bookboon, array($firstAnswer->getId()));
        $this->assertGreaterThan(1, count($questions));
    }

    /**
     * @expectedException \Bookboon\Api\Exception\EntityDataException
     */
    public function testInvalidQuestion()
    {
        $question = new Question(array('blah'));
    }
}
