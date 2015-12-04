<?php

namespace Bookboon\Api\Entity;

class Question extends Entity
{
    protected function isValid(Array $array)
    {
        return isset($array['question'], $array['answers']);
    }

    public function getText()
    {
        return $this->safeGet("question");
    }

    public function getAnswers()
    {
        return Answer::getEntitiesFromArray($this->safeGet("answers"));
    }
}
