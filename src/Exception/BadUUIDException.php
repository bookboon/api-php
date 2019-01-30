<?php

namespace Bookboon\Api\Exception;

class BadUUIDException extends BookboonException
{
    public function __construct()
    {
        parent::__construct("UUID Not Formatted Correctly");
    }
}
