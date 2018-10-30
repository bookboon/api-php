<?php

namespace Bookboon\Api\Client;

use Bookboon\Api\Entity\EntityStore;

class BookboonResponse
{
    /**
     * @var array
     */
    protected $responseArray;

    /**
     * @var array
     */
    protected $responseHeaders;

    /**
     * @var EntityStore
     */
    protected $entityStore;

    /**
     * BookboonResponse constructor.
     * @param array $responseArray
     * @param array $responseHeaders
     */
    public function __construct(array $responseArray, array $responseHeaders)
    {
        $this->responseArray = $responseArray;
        $this->responseHeaders = $responseHeaders;
    }

    /**
     * @return array
     */
    public function getHeaders()
    {

        return $this->responseHeaders;
    }

    /**
     * @return array
     */
    public function getReturnArray()
    {
        return $this->responseArray;
    }

    /**
     * @return EntityStore
     */
    public function getEntityStore()
    {
        return $this->entityStore;
    }

    /**
     * @param EntityStore $entityStore
     */
    public function setEntityStore(EntityStore $entityStore)
    {
        $this->entityStore = $entityStore;
    }
}
