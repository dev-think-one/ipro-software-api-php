<?php

namespace Angecode\IproSoftware\Exceptions;

use Throwable;
use Psr\Http\Message\ResponseInterface;

class IproSoftwareApiAccessTokenException extends IproSoftwareApiException
{
    /** @var ResponseInterface */
    protected $response;

    public function __construct(ResponseInterface $response = null, $message = '', Throwable $previous = null)
    {
        parent::__construct($message, ($this->response) ? $this->response->getStatusCode() : 0, $previous);

        $this->response = $response;
    }

    /**
     * @return ResponseInterface
     */
    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }
}
