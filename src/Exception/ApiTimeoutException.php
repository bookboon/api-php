<?php

namespace Bookboon\Api\Exception;

use Throwable;

class ApiTimeoutException extends BookboonException
{
    public function __construct()
    {
        parent::__construct('API request timed out');
    }
}
