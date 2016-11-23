<?php

namespace Bookboon\Api\Entity;

use Bookboon\Api\Bookboon;

/**
 * Class AnswerTest
 * @package Bookboon\Api\Entity
 * @group entity
 */
class AnswerTest extends \PHPUnit_Framework_TestCase
{
    /** @var Question[] */
    private static $data = null;

    public static function setUpBeforeClass()
    {
        include_once(__DIR__ . '/../Helpers.php');
        $bookboon = \Helpers::getBookboon();
        self::$data = Question::get($bookboon);
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
