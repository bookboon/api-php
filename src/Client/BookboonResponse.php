<?php

namespace Bookboon\Api\Client;

use Bookboon\Api\Entity\EntityStore;
use Bookboon\Api\Exception\ApiDecodeException;

class BookboonResponse
{
    protected $body;
    protected $status;
    protected $responseHeaders;
    protected $entityStore;

    /**
     * BookboonResponse constructor.
     * @param string $responseBody
     * @param int $responseStatus
     * @param array $responseHeaders
     */
    public function __construct(string $responseBody, int $responseStatus, array $responseHeaders)
    {
        $this->body = $responseBody;
        $this->status = $responseStatus;
        $this->responseHeaders = $responseHeaders;
    }

    /**
     * @return string
     */
    public function getBody(): string
    {
        return $this->body;
    }

    /**
     * @return int
     */
    public function getStatus(): int
    {
        return $this->status;
    }


    /**
     * @return array
     */
    public function getHeaders() : array
    {
        return $this->responseHeaders;
    }

    /**
     * @return array
     * @throws ApiDecodeException
     */
    public function getReturnArray() : array
    {
        if ($this->getStatus() >= 300) {
            return ['url' => $this->responseHeaders['Location']];
        }

        $json = json_decode($this->body, true);

        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new ApiDecodeException();
        }

        return $json;
    }

    /**
     * @return EntityStore
     */
    public function getEntityStore() : EntityStore
    {
        return $this->entityStore;
    }

    /**
     * @param EntityStore $entityStore
     * @return void
     */
    public function setEntityStore(EntityStore $entityStore) : void
    {
        $this->entityStore = $entityStore;
    }
}
