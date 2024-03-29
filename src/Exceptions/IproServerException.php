<?php

namespace IproSoftwareApi\Exceptions;

use GuzzleHttp\Exception\ServerException;

class IproServerException extends \Exception
{
    public function __construct(ServerException $previous, $message = '', $code = 0)
    {
        if (!$message) {
            $message = $previous->getMessage();
        }
        if (!$code) {
            $code = $previous->getCode();
        }
        parent::__construct($message, $code, $previous);
    }
}
