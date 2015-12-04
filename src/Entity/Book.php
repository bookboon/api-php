<?php
namespace Bookboon\Api\Entity;

use Serializable;


class Book extends Entity
{
    protected function isValid(Array $array)
    {
        return isset($array['_id'], $array['title'], $array['authors'], $array['thumbnail']);
    }

    /**
     * @return string UUID of entity
     */
    public function getId()
    {
        return $this->safeGet("_id");
    }

    /**
     * @return string link to category on bookboon.com
     */
    public function getHomepage()
    {
        return $this->safeGet("homepage");
    }

    /**
     * Return the title
     *
     * @return string title
     */
    public function getTitle()
    {
        return $this->safeGet("title");
    }

    /**
     * Returns the subtitle
     *
     * @return string subtitle
     */
    public function getSubtitle()
    {
        return $this->safeGet("subtitle");
    }

    /**
     * Return authors
     *
     * @return string semicolon separated list of authors
     */
    public function getAuthors()
    {
        return $this->safeGet("authors");
    }

    /**
     * Return ISBN number
     *
     * @return string ISBN number
     */
    public function getIsbn()
    {
        return $this->safeGet("isbn");
    }


    /**
     * Returns closes thumbnail size to input, default 210px
     *
     * @param int $size appromimate size
     * @return string url for thumbnail
     */
    public function getThumbnail($size = 210, $ssl = false)
    {
        $thumbs = array();
        foreach ($this->safeGet("thumbnail") as $thumb) {
            $thumbs[$thumb['width']] = $thumb['_link'];
        }

        $sizes = array_keys($thumbs);
        while (true) {
            $thumb_size = array_shift($sizes);
            if ((int)$size <= (int)$thumb_size || count($sizes) == 0) {
                if ($ssl) {
                    return str_replace('http://', 'https://', $thumbs[$thumb_size]);
                } else {
                    return $thumbs[$thumb_size];
                }
            }
        }
    }

    /**
     * Return language name
     *
     * @return string language name
     */
    public function getLanguageName()
    {
        $language = $this->safeGet("language");
        return isset($language["name"]) ? $language["name"] : false;
    }


    /**
     * Returns ISO language code
     *
     * @return String language ISO 639-1 code
     */
    public function getLanguageCode()
    {
        $language = $this->safeGet("language");
        return isset($language["code"]) ? $language["code"] : false;
    }

    /**
     * Return publish date
     *
     * @return string date of publishing
     */
    public function getPublished()
    {
        return $this->safeGet("published");
    }

    /**
     * Return book abstract
     *
     * @return string
     */
    public function getAbstract()
    {
        return $this->safeGet("abstract");
    }

    /**
     * Return book edition
     *
     * @return int
     */
    public function getEdition()
    {
        return $this->safeGet("edition");
    }

    /**
     * Return number of pages
     *
     * @return int number of pages
     */
    public function getPages()
    {
        return $this->safeGet("pages");
    }

    /**
     * Return average rating
     *
     * @return float|false if no ratings given returns false
     */
    public function getRatingAverage()
    {
        $rating = $this->safeGet("rating");
        return isset($rating["average"]) ? $rating["average"] : false;
    }

    /**
     * Return number of ratings given
     *
     * @return int
     */
    public function getRatingCount()
    {
        $rating = $this->safeGet("rating");
        return isset($rating["count"]) ? $rating["count"] : 0;
    }

    /**
     * Return available formats
     *
     * @return array of strings
     */
    public function getFormats()
    {
        return $this->safeGet("formats");
    }

    /**
     * Return price set on book
     *
     * @return float
     */
    public function getPrice()
    {
        return $this->safeGet("price");
    }

    /**
     * Return book version
     *
     * @return int version
     */
    public function getVersion()
    {
        return $this->safeGet("version");
    }

    /**
     * Return search context, only on search calls
     *
     * @return string|false if not set
     */
    public function getContext()
    {
        return $this->safeGet("context");
    }

    /**
     * Returns array of book details
     *
     * @return array of Details
     */
    public function getDetails()
    {
        return Detail::getEntitiesFromArray($this->safeGet("details", array()));
    }

    /**
     * Returns true if Epub format is available
     *
     * @return bool
     */
    public function hasEpub()
    {
        return in_array("epub", $this->getFormats());
    }

    /**
     * Returns true if PDF format is available
     *
     * @return bool
     */
    public function hasPdf()
    {
        return in_array("pdf", $this->getFormats());
    }

}