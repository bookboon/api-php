<?php
/**
 * Created by PhpStorm.
 * User: lasse
 * Date: 04/12/2015
 * Time: 14:31
 */

namespace Bookboon\Api\Entity;


use Bookboon\Api\Bookboon;

class QuestionTest extends \PHPUnit_Framework_TestCase
{
    static private $data = null;
    static private $API_ID;
    static private $API_KEY;

    public static function setUpBeforeClass()
    {
        self::$API_ID = getenv('BOOKBOON_API_ID');
        self::$API_KEY = getenv('BOOKBOON_API_KEY');

        $bookboon = new Bookboon(self::$API_ID, self::$API_KEY);
        self::$data = $bookboon->getQuestions();
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


        $questions = $bookboon->getQuestions(array($firstAnswer->getId()));
        $this->assertGreaterThan(1, count($questions));
    }

    /**
     * @expectedException \Bookboon\Api\Entity\EntityDataException
     */
    public function testInvalidQuestion()
    {
        $question = new Question(array("blah"));
    }

}
