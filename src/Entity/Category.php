<?php

namespace Bookboon\Api\Entity;

class Category extends Entity
{
    const TEXTBOOKS = "d1fabb36-4eff-4760-a80d-a15700efa9ae";
    const BUSINESS = "82403e77-ccbf-4e10-875c-a15700ef8a56";

    protected function isValid(Array $array)
    {
        return isset($array['_id'], $array['name'], $array['description'], $array['homepage']);
    }

    /**
     * @return string UUID of entity
     */
    public function getId()
    {
        return $this->safeGet("_id");
    }

    /**
     * @return string name of entity
     */
    public function getName()
    {
        return $this->safeGet("name");
    }

    /**
     * @return string link to category on bookboon.com
     */
    public function getHomepage()
    {
        return $this->safeGet("homepage");
    }

    /**
     * @return string category description
     */
    public function getDescription()
    {
        return $this->safeGet("description");
    }

    /**
     * @return array of Category objects
     */
    public function getCategories()
    {
        return Category::getEntitiesFromArray($this->safeGet("categories", array()));
    }

    /**
     * @return array of Book objects
     */
    public function getBooks()
    {
        return Book::getEntitiesFromArray($this->safeGet("books", array()));
    }
}
