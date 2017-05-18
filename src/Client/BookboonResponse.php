<?php

namespace Bookboon\Api\Client;

use Bookboon\Api\Entity\EntityStore;

class BookboonResponse
{
    /**
     * @var array
     */
    protected $returnArray;

    /**
     * @var array
     */
    protected $headers;

    /**
     * @var array
     */
    protected $entityStore;

    /**
     * BookboonResponse constructor.
     * @param $returnArray
     * @param $headers
     */
    public function __construct($returnArray, $headers)
    {
        $this->returnArray = $returnArray;
        $this->headers = $headers;
    }

    /**
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * @return array
     */
    public function getReturnArray()
    {
        return $this->returnArray;
    }

    /**
     * @return array
     */
    public function getEntityStore()
    {
        return $this->entityStore;
    }

    /**
     * @param $entityStore
     */
    public function setEntityStore(EntityStore $entityStore)
    {
        $this->entityStore = $entityStore;
    }
}
