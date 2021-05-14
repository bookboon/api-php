<?php

namespace Bookboon\Api\Client;

use Bookboon\Api\Entity\EntityStore;
use Bookboon\Api\Exception\ApiDecodeException;
use Serializable;

/**
 * Class BookboonResponse
 * @package Bookboon\Api\Client
 * @template T extends Entity
 */
class BookboonResponse implements Serializable
{
    /** @var string */
    protected $body;

    /** @var int */
    protected $status;

    /** @var array array */
    protected $headers;

    /** @var EntityStore */
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
        $this->headers = $responseHeaders;
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
     * @return Headers
     */
    public function getHeaders() : Headers
    {
        return new Headers($this->headers);
    }

    /**
     * @return array
     * @throws ApiDecodeException
     */
    public function getReturnArray() : array
    {
        if ($this->getStatus() >= 300) {
            return ['url' => $this->getHeaders()['location']];
        }

        $json = json_decode($this->body, true);

        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new ApiDecodeException();
        }

        return $json;
    }

    /**
     * @return EntityStore<T>
     */
    public function getEntityStore() : EntityStore
    {
        return $this->entityStore;
    }

    /**
     * @param EntityStore<T> $entityStore
     * @return void
     */
    public function setEntityStore(EntityStore $entityStore) : void
    {
        $this->entityStore = $entityStore;
    }

    /**
     * String representation of object
     * @link https://php.net/manual/en/serializable.serialize.php
     * @return string the string representation of the object or null
     * @since 5.1.0
     */
    public function serialize()
    {
        return serialize([
            'body'      => $this->body,
            'status'    => $this->status,
            'headers'    => $this->headers
        ]);
    }

    /**
     * Constructs the object
     * @link https://php.net/manual/en/serializable.unserialize.php
     * @param string $serialized <p>
     * The string representation of the object.
     * </p>
     * @return void
     * @since 5.1.0
     */
    public function unserialize($serialized)
    {
        $data = unserialize($serialized, ['allowed_classes' => [BookboonResponse::class]]);
        $this->body = $data['body'];
        $this->status = $data['status'];
        $this->headers = $data['headers'];
    }
}
