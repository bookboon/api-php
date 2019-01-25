<?php

namespace Bookboon\Api\Exception;

class ApiDecodeException extends BookboonException
{
    public function __construct()
    {
        parent::__construct('Failed to decode json response from API');
    }
}
