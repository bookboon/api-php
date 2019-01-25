<?php

namespace Bookboon\Api\Entity;

use Bookboon\Api\Exception\EntityDataException;
use JsonSerializable;
use Serializable;

abstract class Entity implements Serializable, JsonSerializable
{
    protected $data = [];

    /**
     * Category constructor.
     *
     * @param array $array json_decode response from API
     *
     * @throws EntityDataException
     */
    public function __construct(array $array)
    {
        if (!$this->isValid($array)) {
            throw new EntityDataException('Not valid '.get_class($this));
        }

        $this->data = $array;
    }

    /**
     * Determines whether api response is valid
     *
     * @param array $array
     * @return bool
     */
    abstract protected function isValid(array $array) : bool;

    /**
     * @param string $key
     * @param mixed $default
     *
     * @return mixed will default default if keys doens't exist
     */
    protected function safeGet(string $key, $default = false)
    {
        return $this->data[$key] ?? $default;
    }

    /**
     * String representation of object.
     *
     * @link http://php.net/manual/en/serializable.serialize.php
     *
     * @return string the string representation of the object or null
     *
     * @since 5.1.0
     */
    public function serialize()
    {
        return serialize($this->data);
    }

    /**
     * Constructs the object.
     *
     * @link http://php.net/manual/en/serializable.unserialize.php
     *
     * @param string $serialized <p>
     *                           The string representation of the object.
     *                           </p>
     *
     * @since 5.1.0
     */
    public function unserialize($serialized)
    {
        $this->data = unserialize($serialized);
    }

    public static function getEntitiesFromArray(array $array)
    {
        $entities = [];
        foreach ($array as $object) {
            $entities[] = new static($object);
        }

        return $entities;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Useful UUID validator to validate input in scripts.
     *
     * @param string $uuid
     * @return bool true if valid, false if not
     * @internal param string $uuid UUID to validate
     *
     */
    public static function isValidUUID(string $uuid) : bool
    {
        return preg_match('/^([0-9a-fA-F]){8}-([0-9a-fA-F]){4}-([0-9a-fA-F]){4}-([0-9a-fA-F]){4}-([0-9a-fA-F]){12}$/', $uuid) == true;
    }

    public function jsonSerialize()
    {
        return $this->getData();
    }
}
