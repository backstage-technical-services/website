<?php

namespace App\Exceptions;

use Symfony\Component\HttpKernel\Exception\HttpException;

class ResourceNotCreatedException extends HttpException
{
    /**
     * ResourceNotCreatedException constructor.
     *
     * @param string $message The exception message
     */
    public function __construct($message)
    {
        parent::__construct(500, $message);
    }
}