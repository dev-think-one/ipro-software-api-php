<?php

namespace Angecode\IproSoftware\Exceptions;

use Psr\Http\Message\ResponseInterface;

class IproSoftwareApiAccessTokenException extends IproSoftwareApiException
{
    /** @var ResponseInterface */
    public $response;

    public function __construct(ResponseInterface $response = null, $message = '', $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);

        $this->response = $response;
    }
}
