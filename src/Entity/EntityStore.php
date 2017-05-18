<?php

namespace Bookboon\Api\Entity;

class EntityStore
{
    /**
     * @var array
     */
    protected $contents;

    /**
     * EntityStore constructor.
     * @param array $contents
     */
    public function __construct(array $contents = [])
    {
        $this->contents = $contents;
    }

    /**
     * @return array|mixed
     */
    public function get()
    {
        if (count($this->contents) === 1) {
            return $this->contents[0];
        }

        return $this->contents;
    }
}
