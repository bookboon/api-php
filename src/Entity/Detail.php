<?php

namespace Bookboon\Api\Entity;

class Detail extends Entity
{
    protected function isValid(Array $array)
    {
        return isset($array['title'], $array['body']);
    }

    /**
     * @return string title of details, e.g. description, table of content etc.
     */
    public function getTitle()
    {
        return $this->safeGet("title");
    }

    /**
     * @return string html body of current book detail
     */
    public function getBody()
    {
        return $this->safeGet("body");
    }
}
