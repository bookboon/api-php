<?php

namespace Bookboon\Api\Entity;


class Answer extends Entity
{
    protected function isValid(Array $array)
    {
        return isset($array['id'], $array['text']);
    }

    /**
     * Returns id
     *
     * @return string guid
     */
    public function getId()
    {
        return $this->safeGet("id");
    }

    /**
     * Returns answer text
     *
     * @return string
     */
    public function getText()
    {
        return $this->safeGet("text");
    }
}