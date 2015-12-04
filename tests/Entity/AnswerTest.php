<?php

namespace Bookboon\Api\Entity;


use Bookboon\Api\Bookboon;

class AnswerTest extends \PHPUnit_Framework_TestCase
{
    static private $data = null;

    public static function setUpBeforeClass()
    {
        $id = getenv('BOOKBOON_API_ID');
        $key = getenv('BOOKBOON_API_KEY');

        $bookboon = new Bookboon($id, $key);
        self::$data = $bookboon->getQuestions();
    }

    public function testGetText()
    {
        $firstQuestion = self::$data[0];
        $answers = $firstQuestion->getAnswers();
        $firstAnswer = $answers[0];
        $this->assertNotEmpty($firstAnswer->getText());
    }

    public function testGetId()
    {
        $firstQuestion = self::$data[0];
        $answers = $firstQuestion->getAnswers();
        $firstAnswer = $answers[0];
        $this->assertNotEmpty($firstAnswer->getId());
    }
}
