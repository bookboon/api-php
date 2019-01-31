<?php

namespace Bookboon\Api\Entity;

use Bookboon\Api\Bookboon;
use Bookboon\Api\Client\BookboonResponse;
use Bookboon\Api\Client\ClientInterface;

abstract class Book extends Entity
{
    const _OWN_TYPE = '';

    const TYPE_AUDIO = 'audio';
    const TYPE_PDF = 'pdf';
    const TYPE_VIDEO = 'video';
    const TYPE_AUDIOTALK = 'audioTalk';

    const FORMAT_PDF = 'pdf';
    const FORMAT_EPUB = 'epub';
    const FORMAT_MOBI = 'mobi';
    const FORMAT_VIDEO = 'video';
    const FORMAT_M4B = 'm4b';
    const FORMAT_M3U = 'm3u';

    /**
     * @param Bookboon $bookboon
     * @param string $bookId
     * @param bool $extendedMetadata
     * @return BookboonResponse
     * @throws \Bookboon\Api\Exception\UsageException
     */
    public static function get(Bookboon $bookboon, string $bookId, bool $extendedMetadata = false) : BookboonResponse
    {
        $bResponse = $bookboon->rawRequest("/books/$bookId", ['extendedMetadata' => $extendedMetadata ? 'true' : 'false']);

        $bResponse->setEntityStore(
            new EntityStore(
                [
                    static::objectTransformer($bResponse->getReturnArray())
                ]
            )
        );

        return $bResponse;
    }

    /**
     * Get many books
     *
     * @param Bookboon $bookboon
     * @param string[] $bookIds
     * @param bool $extendedMetadata
     * @return BookboonResponse
     * @throws \Bookboon\Api\Exception\UsageException
     */
    public static function getMultiple(
        Bookboon $bookboon,
        array $bookIds,
        bool $extendedMetadata = false
    ) : BookboonResponse {
        $variables = [
            'id' => $bookIds,
            'extendedMetadata' => $extendedMetadata ? 'true' : 'false'
        ];

        $bResponse = $bookboon->rawRequest("/books", $variables);

        $bResponse->setEntityStore(
            new EntityStore(static::getEntitiesFromArray($bResponse->getReturnArray()))
        );

        return $bResponse;
    }

    /**
     * Get many books
     *
     * @param Bookboon $bookboon
     * @param bool $extendedMetadata
     * @param string $type
     * @return BookboonResponse
     * @throws \Bookboon\Api\Exception\UsageException
     */
    public static function getAll(
        Bookboon $bookboon,
        bool $extendedMetadata = false,
        string $type = self::_OWN_TYPE
    ) : BookboonResponse {
        $variables = [
            'bookType' => $type,
            'extendedMetadata' => $extendedMetadata ? 'true' : 'false'
        ];

        $bResponse = $bookboon->rawRequest("/books", $variables);

        $bResponse->setEntityStore(
            new EntityStore(static::getEntitiesFromArray($bResponse->getReturnArray()))
        );

        return $bResponse;
    }

    /**
     * @param array $objectArray
     * @return Book
     */
    public static function objectTransformer(array $objectArray)
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
        $entities = [];
        foreach ($array as $object) {
            if (in_array(
                $object['_type'],
                [self::TYPE_PDF, self::TYPE_AUDIO, self::TYPE_VIDEO, self::TYPE_AUDIOTALK],
                true)
            ) {
                $entities[] = self::objectTransformer($object);
            }
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
     * @throws \Bookboon\Api\Exception\UsageException
     */
    public static function getDownloadUrl(
        Bookboon $bookboon,
        string $bookId,
        array $variables,
        string $format = self::FORMAT_PDF
    ) : string {
        $variables['format'] = $format;

        $bResponse = $bookboon->rawRequest("/books/$bookId/download", $variables, ClientInterface::HTTP_POST);

        return $bResponse->getReturnArray()['url'];
    }

    /**
     * Search.
     *
     * @param Bookboon $bookboon
     * @param string $query string to search for
     * @param int $limit results to return per page
     * @param int $offset offset of results
     * @return BookboonResponse
     * @throws \Bookboon\Api\Exception\UsageException
     */
    public static function search(
        Bookboon $bookboon,
        string $query,
        int $limit = 10,
        int $offset = 0
    ) : BookboonResponse {
        $bResponse = $bookboon->rawRequest('/search', ['q' => $query, 'limit' => $limit, 'offset' => $offset]);

        $bResponse->setEntityStore(
            new EntityStore(Book::getEntitiesFromArray($bResponse->getReturnArray()))
        );

        return $bResponse;
    }

    /**
     * Recommendations.
     *
     * @param Bookboon $bookboon
     * @param string $bookType
     * @param array $bookIds array of book ids to base recommendations on, can be empty
     * @param int $limit
     * @return BookboonResponse
     * @throws \Bookboon\Api\Exception\UsageException
     */
    public static function recommendations(
        Bookboon $bookboon,
        array $bookIds = [],
        int $limit = 5,
        string $bookType = 'pdf'
    ) : BookboonResponse {
        $bResponse = $bookboon->rawRequest('/recommendations', ['limit' => $limit, 'book' => $bookIds, 'bookType' => $bookType]);

        $bResponse->setEntityStore(
            new EntityStore(Book::getEntitiesFromArray($bResponse->getReturnArray()))
        );

        return $bResponse;
    }

    protected function isValid(array $array) : bool
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
    public function getThumbnail(int $size = 210, bool $ssl = false)
    {
        $thumbs = [];
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

    public function getPriceLevel()
    {
        return $this->safeGet('priceLevel');
    }

    /**
     * Return price level set on book.
     *
     * @return int
     */
    public function getPremiumLevel()
    {
        return $this->safeGet('premiumLevel');
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
        return Detail::getEntitiesFromArray($this->safeGet('details', []));
    }

    /**
     * Return array of similar books.
     *
     * @return array of Book
     */
    public function getSimilarBooks()
    {
        return self::getEntitiesFromArray($this->safeGet('similar', []));
    }

    /**
     * Return array of book reviews.
     *
     * @return array of Review
     */
    public function getReviews()
    {
        return Review::getEntitiesFromArray($this->safeGet('reviews', []));
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
