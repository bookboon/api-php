<?php

namespace Bookboon\Api\Entity;

class ExamAnswer extends Entity
{
    protected function isValid(array $array) : bool
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
}