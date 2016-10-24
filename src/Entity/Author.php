<?php

namespace Bookboon\Api\Entity;

class Author extends Entity
{
    protected function isValid(array $array)
    {
        return isset($array['_id'], $array['name'], $array['books']);
    }

    /**
     * @return string UUID of entity
     */
    public function getId()
    {
        return $this->safeGet('_id');
    }

    /**
     * Return the name.
     *
     * @return string name
     */
    public function getName()
    {
        return $this->safeGet('name');
    }

    /**
     * Return the academic or professional title.
     *
     * @return string title
     */
    public function getTitle()
    {
        return $this->safeGet('title');
    }

    /**
     * Return the institution.
     *
     * @return string institution
     */
    public function getInstitution()
    {
        return $this->safeGet('institution');
    }

    /**
     * Return the twitter.
     *
     * @return string twitter
     */
    public function getTwitter()
    {
        return $this->safeGet('twitter');
    }

    /**
     * Return the linkedin.
     *
     * @return string linkedin
     */
    public function getLinkedin()
    {
        return $this->safeGet('linkedin');
    }

    /**
     * Return the website.
     *
     * @return string website
     */
    public function getWebsite()
    {
        return $this->safeGet('website');
    }

    /**
     * Return the profile.
     *
     * @return string profile
     */
    public function getProfile()
    {
        return $this->safeGet('profile');
    }

    /**
     * Return books.
     *
     * @return Book[] books by author
     */
    public function getBooks()
    {
        return Book::getEntitiesFromArray($this->safeGet('books', array()));
    }
}
