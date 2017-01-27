<?php

namespace Bookboon\Api\Entity;

class ExamQuestion extends Entity
{
    protected function isValid(array $array)
    {
        return isset($array['_id'], $array['phrasing']);
    }

    /**
     * @return string UUID of entity
     */
    public function getId()
    {
        return $this->safeGet('_id');
    }

    /**
     * Answer phrasing
     *
     * @return string
     */
    public function getPhrasing()
    {
        return $this->safeGet('phrasing');
    }

    /**
     * @return ExamAnswer[]
     */
    public function getAnswers()
    {
        return ExamAnswer::getEntitiesFromArray($this->safeGet('answers'));
    }
}