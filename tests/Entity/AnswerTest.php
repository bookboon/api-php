<?php

namespace Bookboon\Api\Entity;

use PHPUnit\Framework\TestCase;
use Helpers\Helpers;

/**
 * Class AnswerTest
 * @package Bookboon\Api\Entity
 * @group entity
 */
class AnswerTest extends TestCase
{
    /** @var Question[] */
    private static $data = null;

    public static function setUpBeforeClass() : void
    {
        $bookboon = Helpers::getBookboon();
        self::$data = Question::get($bookboon)->getEntityStore()->get();
    }

    public function testGetText() : void
    {
        $firstQuestion = self::$data[0];
        $answers = $firstQuestion->getAnswers();
        $firstAnswer = $answers[0];
        self::assertNotEmpty($firstAnswer->getText());
    }

    public function testGetId() : void
    {
        $firstQuestion = self::$data[0];
        $answers = $firstQuestion->getAnswers();
        $firstAnswer = $answers[0];
        self::assertNotEmpty($firstAnswer->getId());
    }
}
