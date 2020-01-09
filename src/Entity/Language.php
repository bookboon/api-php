<?php

namespace Bookboon\Api\Entity;

use Bookboon\Api\Bookboon;
use Bookboon\Api\Client\BookboonResponse;
use Bookboon\Api\Exception\UsageException;

class Language extends Entity
{
    /**
     * Get all languages
     *
     * @param Bookboon $bookboon
     * @param array $bookTypes
     * @return BookboonResponse
     * @throws UsageException
     * @throws \Bookboon\Api\Exception\ApiDecodeException
     */
    public static function get(Bookboon $bookboon, array $bookTypes = ['pdf']) : BookboonResponse
    {
        $bResponse = $bookboon->rawRequest('/languages');

        $bResponse->setEntityStore(
            new EntityStore(Language::getEntitiesFromArray($bResponse->getReturnArray()))
        );

        return $bResponse;
    }

    /**
     * @return string id
     */
    public function getId() : string
    {
        return $this->safeGet('id');
    }

    /**
     * @return string code
     */
    public function getCode() : string
    {
        return $this->safeGet('code');
    }

    /**
     * @return string slug
     */
    public function getSlug() : string
    {
        return $this->safeGet('_slug');
    }

    /**
     * @return string name
     */
    public function getName() : string
    {
        return $this->safeGet('name');
    }

    /**
     * @return string localized name
     */
    public function getLocalizedName() : string
    {
        return $this->safeGet('localizedName');
    }

    /**
     * Determines whether api response is valid
     *
     * @param array $array
     * @return bool
     */
    protected function isValid(array $array): bool
    {
        return isset($array['code'], $array['id'], $array['name']);
    }
}
