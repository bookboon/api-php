<?php

namespace Bookboon\Api\Entity;

use Serializable;

abstract class Entity implements Serializable
{
    protected $data = array();

    /**
     * Category constructor.
     * @param array $array json_decode response from API
     * @throws EntityDataException
     */
    public function __construct(Array $array)
    {
        if (!$this->isValid($array)) {
            throw new EntityDataException("Not valid entity");
        }

        $this->data = $array;
    }

    abstract protected function isValid(Array $array);

    /**
     * @param $key
     * @param bool|false $default
     * @return mixed will default default if keys doens't exist
     */
    protected function safeGet($key, $default = false)
    {
        if (isset($this->data[$key])) {
            return $this->data[$key];
        }

        return $default;
    }

    /**
     * String representation of object
     * @link http://php.net/manual/en/serializable.serialize.php
     * @return string the string representation of the object or null
     * @since 5.1.0
     */
    public function serialize()
    {
        return serialize($this->data);
    }

    /**
     * Constructs the object
     * @link http://php.net/manual/en/serializable.unserialize.php
     * @param string $serialized <p>
     * The string representation of the object.
     * </p>
     * @return void
     * @since 5.1.0
     */
    public function unserialize($serialized)
    {
        $this->data = unserialize($serialized);
    }

    public static function getEntitiesFromArray(Array $array)
    {
        $entities = array();
        foreach ($array as $object) {
            $entities[] = new static($object);
        }
        return $entities;
    }

}
