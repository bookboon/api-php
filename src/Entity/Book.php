<?php

namespace Bookboon\Api\Entity;

use Bookboon\Api\Bookboon;
use Bookboon\Api\Client\BookboonResponse;
use Bookboon\Api\Client\ClientInterface;
use Bookboon\Api\Exception\UsageException;

abstract class Book extends Entity
{
    const _OWN_TYPE = '';

    const TYPE_AUDIO = 'audio';
    const TYPE_PDF = 'pdf';
    const TYPE_VIDEO = 'video';
    const TYPE_AUDIOTALK = 'audioTalk';
    const TYPE_CLASSROOM = 'classroom';

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
     * @param array $params
     * @return BookboonResponse<Book>
     * @throws UsageException
     * @throws \Bookboon\Api\Exception\ApiDecodeException
     */
    public static function get(Bookboon $bookboon, string $bookId, bool $extendedMetadata = false, array $params = []) : BookboonResponse
    {
        $bResponse = $bookboon->rawRequest(
            "/v1/books/$bookId",
            array_merge($params, ['extendedMetadata' => $extendedMetadata ? 'true' : 'false']),
            ClientInterface::HTTP_GET,
            true,
            Book::class
        );

        $bResponse->setEntityStore(
            new EntityStore(
                [
                    static::objectTransformer($bResponse->getReturnArray())
                ],
                Book::class
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
     * @return BookboonResponse<Book>
     * @throws \Bookboon\Api\Exception\UsageException
     */
    public static function getMultiple(
        Bookboon $bookboon,
        array $bookIds,
        bool $extendedMetadata = false
    ) : BookboonResponse {
        if (count($bookIds) === 0) {
            throw new UsageException('At least one id is required for bookIds');
        }

        $variables = [
            'id' => $bookIds,
            'extendedMetadata' => $extendedMetadata ? 'true' : 'false'
        ];

        $bResponse = $bookboon->rawRequest(
            '/v1/books',
            $variables,
            ClientInterface::HTTP_GET,
            true,
            Book::class
        );

        $bResponse->setEntityStore(
            new EntityStore(static::getEntitiesFromArray($bResponse->getReturnArray()), Book::class)
        );

        return $bResponse;
    }

    /**
     * Get many books
     *
     * @param Bookboon $bookboon
     * @param bool $extendedMetadata
     * @param array $bookTypes
     * @return BookboonResponse<Book>
     * @throws \Bookboon\Api\Exception\UsageException
     *
     * @deprecated Should not be used for performance reasons
     */
    public static function getAll(
        Bookboon $bookboon,
        bool $extendedMetadata = false,
        array $bookTypes = [self::_OWN_TYPE]
    ) : BookboonResponse {
        $variables = [
            'bookType' => join(',', $bookTypes),
            'extendedMetadata' => $extendedMetadata ? 'true' : 'false'
        ];

        $bResponse = $bookboon->rawRequest(
            '/v1/books',
            $variables,
            ClientInterface::HTTP_GET,
            true,
            Book::class
        );

        $bResponse->setEntityStore(
            new EntityStore(static::getEntitiesFromArray($bResponse->getReturnArray()), Book::class)
        );

        return $bResponse;
    }
    /**
     * Get many books by filter
     *
     * @param Bookboon $bookboon
     * @param array $bookTypes
     * @param array $filters
     * @return BookboonResponse<Book>
     * @throws \Bookboon\Api\Exception\UsageException
     **/
    public static function getByFilters(
        Bookboon $bookboon,
        array $bookTypes = [self::_OWN_TYPE],
        array $filters
    ) : BookboonResponse {
        $variables = [
            'bookType' => join(',', $bookTypes),
        ];

        $variables = array_merge($variables, $filters);

        $bResponse = $bookboon->rawRequest(
            '/v1/books',
            $variables,
            ClientInterface::HTTP_GET,
            true,
            Book::class
        );

        $bResponse->setEntityStore(
            new EntityStore(static::getEntitiesFromArray($bResponse->getReturnArray()), Book::class)
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

        /** @var Book $book */
        $book = new $className($objectArray);

        return $book;
    }

    /**
     * @psalm-suppress LessSpecificImplementedReturnType
     * @param array $array
     * @return array<Book>
     */
    public static function getEntitiesFromArray(array $array) : array
    {
        $entities = [];

        foreach ($array as $object) {
            if (in_array(
                $object['_type'],
                [self::TYPE_PDF, self::TYPE_AUDIO, self::TYPE_VIDEO, self::TYPE_AUDIOTALK, self::TYPE_CLASSROOM],
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

        $bResponse = $bookboon->rawRequest("/v1/books/$bookId/download", $variables, ClientInterface::HTTP_POST);

        return $bResponse->getReturnArray()['url'];
    }

    /**
     * Search.
     *
     * @param Bookboon $bookboon
     * @param string $query
     * @param int $limit
     * @param int $offset
     * @param array $bookTypes
     * @return BookboonResponse<Book>
     * @throws UsageException
     * @throws \Bookboon\Api\Exception\ApiDecodeException
     */
    public static function search(
        Bookboon $bookboon,
        string $query,
        int $limit = 10,
        int $offset = 0,
         array $bookTypes = ['professional']
    ) : BookboonResponse {
        $bResponse = $bookboon->rawRequest(
            '/v1/search',
            ['q' => $query, 'limit' => $limit, 'offset' => $offset, 'bookType' => join(',', $bookTypes)],
            ClientInterface::HTTP_GET,
            true,
            Book::class
        );

        $bResponse->setEntityStore(
            new EntityStore(Book::getEntitiesFromArray($bResponse->getReturnArray()), Book::class)
        );

        return $bResponse;
    }

    /**
     * Recommendations.
     *
     * @param Bookboon $bookboon
     * @param array $bookTypes
     * @param array $bookIds array of book ids to base recommendations on, can be empty
     * @param int $limit
     * @param array $params
     * @return BookboonResponse<Book>
     * @throws \Bookboon\Api\Exception\UsageException
     */
    public static function recommendations(
        Bookboon $bookboon,
        array $bookIds = [],
        int $limit = 5,
        array $bookTypes = ['professional'],
        array $params = []
    ) : BookboonResponse {
        $bResponse = $bookboon->rawRequest(
            '/v1/recommendations',
            array_merge($params, [
                'limit' => $limit,
                'books' => $bookIds,
                'bookType' => join(',', $bookTypes)
            ]),
            ClientInterface::HTTP_GET,
            true,
            Book::class
        );

        $bResponse->setEntityStore(
            new EntityStore(Book::getEntitiesFromArray($bResponse->getReturnArray()), Book::class)
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
     *
     * @return string url for thumbnail
     */
    public function getThumbnail(int $size = 210)
    {
        return $this->thumbnailResolver($this->safeGet('thumbnail', []), $size);
    }

    /**
     * Returns closes raw thumbnail (without branding) size to input, default 210px.
     *
     * @param int $size appromimate size
     * @param string $type
     *
     * @return string url for thumbnail
     */
    public function getThumbnailRaw(int $size = 210, string $type = 'default')
    {
        $thumbUrl = $this->safeGet('thumbnail')[0]['_link'];
        $thumbnailParts = explode('/', $thumbUrl);

        return "{$thumbnailParts[0]}//{$thumbnailParts[2]}/thumbnail/b/$size/{$this->getId()}/$type.jpg";
    }

    /**
     * Return language name.
     *
     * @return string language name
     */
    public function getLanguageName()
    {
        $language = $this->safeGet('language');

        return $language['name'] ?? '';
    }

    /**
     * Returns ISO language code.
     *
     * @return string language ISO 639-1 code
     */
    public function getLanguageCode()
    {
        $language = $this->safeGet('language');

        return $language['code'] ?? '';
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
     * @return string
     */
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
