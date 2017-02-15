<?php

namespace Bookboon\Api\Entity;

use Bookboon\Api\Bookboon;
use Bookboon\Api\Client\Client;
use Bookboon\Api\Exception\BadUUIDException;

class Book extends Entity
{
    const FORMAT_PDF = 'pdf';
    const FORMAT_EPUB = 'epub';
    const FORMAT_MOBI = 'mobi';
    const FORMAT_VIDEO = 'video';
    const FORMAT_M4B = 'm4b';
    const FORMAT_M3U = 'm3u';

    /**
     * Get Book object.
     *
     * @param Bookboon $bookboon
     * @param string|array $bookId uuid for book
     * @param bool $extendedMetadata bool include reviews and similar books
     * @return Book
     */
    public static function get(Bookboon $bookboon, $bookId, $extendedMetadata = false)
    {
        return static::objectTransformer($bookboon->rawRequest("/books/$bookId", array('extendedMetadata' => $extendedMetadata ? 'true' : 'false')));
    }

    /**
     * Get many books
     *
     * @param Bookboon $bookboon
     * @param string[] $bookIds
     * @param bool $extendedMetadata
     * @return Book[]
     */
    public static function getMultiple(Bookboon $bookboon, array $bookIds, $extendedMetadata = false)
    {
        $variables = array(
            'id' => $bookIds,
            'extendedMetadata' => $extendedMetadata ? 'true' : 'false'
        );

        return static::getEntitiesFromArray($bookboon->rawRequest("/books", $variables));
    }

    /**
     * @param array $objectArray
     * @return Book
     */
    private static function objectTransformer(array $objectArray)
    {
        $className = 'Bookboon\Api\Entity\\' . ucfirst($objectArray['_type']) . 'Book';
        return new $className($objectArray);
    }

    /**
     * @param array $array
     * @return Book[]
     */
    public static function getEntitiesFromArray(array $array)
    {
        $entities = array();
        foreach ($array as $object) {
            $entities[] = self::objectTransformer($object);
        }

        return $entities;
    }


    /**
     * Get the download url.
     *
     * @param Bookboon $bookboon
     * @param string $bookId
     * @param array $variables
     * @param string $format
     * @return string
     */
    public static function getDownloadUrl(Bookboon $bookboon, $bookId, array $variables, $format = self::FORMAT_PDF)
    {
        $variables['format'] = $format;
        $download = $bookboon->rawRequest("/books/$bookId/download", $variables, Client::HTTP_POST);

        return $download['url'];
    }

    /**
     * Search.
     *
     * @param Bookboon $bookboon
     * @param $query string to search for
     * @param int $limit results to return per page
     * @param int $offset offset of results
     * @return Book[]
     */
    public static function search(Bookboon $bookboon, $query, $limit = 10, $offset = 0)
    {
        $search = $bookboon->rawRequest('/search', array('q' => $query, 'limit' => $limit, 'offset' => $offset));
        if (count($search) === 0) {
            return array();
        }

        return Book::getEntitiesFromArray($search);
    }

    /**
     * Recommendations.
     *
     * @param Bookboon $bookboon
     * @param array $bookIds array of book ids to base recommendations on, can be empty
     * @param int $limit
     * @return Book[]
     */
    public static function recommendations(Bookboon $bookboon, array $bookIds = array(), $limit = 5)
    {
        $recommendations = $bookboon->rawRequest('/recommendations', array('limit' => $limit, 'book' => $bookIds));

        return Book::getEntitiesFromArray($recommendations);
    }

    protected function isValid(array $array)
    {
        return isset($array['_id'], $array['title'], $array['authors'], $array['thumbnail']);
    }

    /**
     * @return string UUID of entity
     */
    public function getId()
    {
        return $this->safeGet('_id');
    }

    /**
     * @return string slug
     */
    public function getSlug()
    {
        return $this->safeGet('_slug');
    }

    /**
     * @return string link to category on bookboon.com
     */
    public function getHomepage()
    {
        return $this->safeGet('homepage');
    }

    /**
     * Return the title.
     *
     * @return string title
     */
    public function getTitle()
    {
        return $this->safeGet('title');
    }

    /**
     * Returns the subtitle.
     *
     * @return string subtitle
     */
    public function getSubtitle()
    {
        return $this->safeGet('subtitle');
    }

    /**
     * Return authors.
     *
     * @return string semicolon separated list of authors
     */
    public function getAuthors()
    {
        return $this->safeGet('authors');
    }

    /**
     * Return ISBN number.
     *
     * @return string ISBN number
     */
    public function getIsbn()
    {
        return $this->safeGet('isbn');
    }

    /**
     * Returns closes thumbnail size to input, default 210px.
     *
     * @param int  $size appromimate size
     * @param bool $ssl  Whether or not to return https:// url for thumbnail
     *
     * @return string url for thumbnail
     */
    public function getThumbnail($size = 210, $ssl = false)
    {
        $thumbs = array();
        foreach ($this->safeGet('thumbnail') as $thumb) {
            $thumbs[$thumb['width']] = $thumb['_link'];
        }

        $sizes = array_keys($thumbs);
        while (true) {
            $thumbSize = array_shift($sizes);
            if ((int) $size <= (int) $thumbSize || count($sizes) == 0) {
                if ($ssl) {
                    return str_replace('http://', 'https://', $thumbs[$thumbSize]);
                }

                return $thumbs[$thumbSize];
            }
        }
    }

    /**
     * Return language name.
     *
     * @return string language name
     */
    public function getLanguageName()
    {
        $language = $this->safeGet('language');

        return isset($language['name']) ? $language['name'] : false;
    }

    /**
     * Returns ISO language code.
     *
     * @return string language ISO 639-1 code
     */
    public function getLanguageCode()
    {
        $language = $this->safeGet('language');

        return isset($language['code']) ? $language['code'] : false;
    }

    /**
     * Return publish date.
     *
     * @return string date of publishing
     */
    public function getPublished()
    {
        return $this->safeGet('published');
    }

    /**
     * Return book abstract.
     *
     * @return string
     */
    public function getAbstract()
    {
        return $this->safeGet('abstract');
    }

    /**
     * Return book edition.
     *
     * @return int
     */
    public function getEdition()
    {
        return $this->safeGet('edition');
    }

    /**
     * Return number of pages.
     *
     * @return int number of pages
     */
    public function getPages()
    {
        return $this->safeGet('pages');
    }

    /**
     * Return average rating.
     *
     * @return float|false if no ratings given returns false
     */
    public function getRatingAverage()
    {
        $rating = $this->safeGet('rating');

        return isset($rating['average']) ? $rating['average'] : false;
    }

    /**
     * Return number of ratings given.
     *
     * @return int
     */
    public function getRatingCount()
    {
        $rating = $this->safeGet('rating');

        return isset($rating['count']) ? $rating['count'] : 0;
    }

    /**
     * Return available formats.
     *
     * @return array of strings
     */
    public function getFormats()
    {
        return $this->safeGet('formats');
    }

    /**
     * Return price set on book.
     *
     * @return float
     */
    public function getPrice()
    {
        return $this->safeGet('price');
    }

    /**
     * Return book version.
     *
     * @return int version
     */
    public function getVersion()
    {
        return $this->safeGet('version');
    }

    /**
     * Return search context, only on search calls.
     *
     * @return string|false if not set
     */
    public function getContext()
    {
        return $this->safeGet('context');
    }

    /**
     * Returns array of book details.
     *
     * @return array of Details
     */
    public function getDetails()
    {
        return Detail::getEntitiesFromArray($this->safeGet('details', array()));
    }

    /**
     * Return array of similar books.
     *
     * @return array of Book
     */
    public function getSimilarBooks()
    {
        return self::getEntitiesFromArray($this->safeGet('similar', array()));
    }

    /**
     * Return array of book reviews.
     *
     * @return array of Review
     */
    public function getReviews()
    {
        return Review::getEntitiesFromArray($this->safeGet('reviews', array()));
    }

    /**
     * Returns true if Epub format is available.
     *
     * @return bool
     */
    public function hasEpub()
    {
        return in_array('epub', $this->getFormats());
    }

    /**
     * Returns true if PDF format is available.
     *
     * @return bool
     */
    public function hasPdf()
    {
        return in_array('pdf', $this->getFormats());
    }
}
