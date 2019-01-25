<?php

namespace Bookboon\Api\Entity;

use Bookboon\Api\Exception\UsageException;

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
     * @return Entity[]
     */
    public function get() : array
    {
        return $this->contents;
    }

    /**
     * @return Entity
     * @throws UsageException
     */
    public function getSingle() : Entity
    {
        if (count($this->contents) === 1) {
            return $this->contents[0];
        }

        throw new UsageException("Multiple responses exists");
    }
}
